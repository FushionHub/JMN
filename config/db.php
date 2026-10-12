<?php
declare(strict_types=1);

/**
 * Resilient Database Connection & Schema Manager
 * Jamachi Maurice Mauricennadi Portfolio Platform
 * Adheres to PSR-12, PDO Singleton, and Auto-Migration.
 *
 * Every identifier that reaches SQL is validated against a strict whitelist
 * first (env-driven values are attacker-controllable in some deployments),
 * and column additions are performed through guarded, idempotent migrations
 * that only ever run literal DDL emitted by this class.
 */

class Database
{
    private static ?Database $instance = null;
    private ?PDO $connection = null;
    private bool $schemaInitialized = false;

    private string $host     = '127.0.0.1';
    private string $port     = '3306';
    private string $dbname   = 'jmn';
    private string $charset  = 'utf8mb4';
    private string $username = 'root';
    private string $password = '';

    private function __construct()
    {
        // Environment overrides - each value is validated before use so a
        // hostile DB_HOST/DB_DATABASE cannot inject into the DSN or DDL.
        $host     = (string)(getenv('DB_HOST') ?: $this->host);
        $port     = (string)(getenv('DB_PORT') ?: $this->port);
        $dbname   = (string)(getenv('DB_DATABASE') ?: $this->dbname);
        $username = (string)(getenv('DB_USERNAME') ?: $this->username);

        $this->host     = preg_match('/^[A-Za-z0-9._\-:\[\]]{1,120}$/', $host) === 1 ? $host : '127.0.0.1';
        $this->port     = (preg_match('/^\d{1,5}$/', $port) === 1 && (int)$port > 0 && (int)$port <= 65535) ? $port : '3306';
        $this->dbname   = preg_match('/^[A-Za-z0-9_]{1,64}$/', $dbname) === 1 ? $dbname : 'jmn';
        $this->username = preg_match('/^[A-Za-z0-9._\-]{1,64}$/', $username) === 1 ? $username : 'root';
        $this->charset  = 'utf8mb4'; // never taken from input

        $password = getenv('DB_PASSWORD');
        if ($password !== false) {
            $this->password = (string)$password;
        }

        // The DSN already negotiates utf8mb4; PDO::MYSQL_ATTR_INIT_COMMAND is
        // deprecated as of PHP 8.5, so the collation pin is applied once after
        // connect instead.
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::ATTR_PERSISTENT         => false,
        ];

        try {
            // First attempt to connect directly to the database
            $dsn = "mysql:host={$this->host};port={$this->port};dbname={$this->dbname};charset={$this->charset}";
            $this->connection = new PDO($dsn, $this->username, $this->password, $options);
        } catch (PDOException $e) {
            // If database does not exist (error 1049), connect without dbname and create it
            if ($e->getCode() == 1049 || str_contains($e->getMessage(), 'Unknown database')) {
                try {
                    $rootDsn = "mysql:host={$this->host};port={$this->port};charset={$this->charset}";
                    $tempPdo = new PDO($rootDsn, $this->username, $this->password, $options);
                    // $this->dbname is whitelisted to [A-Za-z0-9_] above
                    $tempPdo->exec("CREATE DATABASE IF NOT EXISTS `{$this->dbname}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                    unset($tempPdo);

                    // Reconnect to the newly created database
                    $this->connection = new PDO($dsn, $this->username, $this->password, $options);
                } catch (PDOException $createEx) {
                    error_log("Failed to create database '{$this->dbname}': " . $createEx->getMessage());
                    $this->connection = null;
                    return;
                }
            } else {
                error_log('Database connection error: ' . $e->getMessage());
                $this->connection = null;
                return;
            }
        }

        // Pin the connection collation once (replaces the deprecated
        // PDO::MYSQL_ATTR_INIT_COMMAND option).
        if ($this->connection !== null) {
            try {
                $this->connection->exec('SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci');
            } catch (PDOException $e) {
                error_log('Failed to pin connection collation: ' . $e->getMessage());
            }
        }

        // Initialize schema if connected
        if ($this->connection !== null && !$this->schemaInitialized) {
            $this->ensureSchemaExists();
            $this->schemaInitialized = true;
        }
    }

    public static function getInstance(): Database
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection(): ?PDO
    {
        return $this->connection;
    }

    // ------------------------------------------------------------------
    // Guarded migration helpers
    // ------------------------------------------------------------------

    /** Add a column to a table only when it does not already exist. */
    private function ensureColumn(string $table, string $column, string $ddl): void
    {
        if ($this->connection === null) {
            return;
        }
        if (preg_match('/^[A-Za-z0-9_]{1,64}$/', $table) !== 1 || preg_match('/^[A-Za-z0-9_]{1,64}$/', $column) !== 1) {
            return;
        }
        try {
            $stmt = $this->connection->prepare(
                'SELECT COUNT(*) FROM information_schema.COLUMNS
                  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?'
            );
            $stmt->execute([$table, $column]);
            if ((int)$stmt->fetchColumn() > 0) {
                return;
            }
            $this->connection->exec("ALTER TABLE `{$table}` ADD COLUMN `{$column}` {$ddl}");
        } catch (PDOException $e) {
            error_log("ensureColumn({$table}.{$column}) failed: " . $e->getMessage());
        }
    }

    /** Change a numeric column default only when it differs. */
    private function ensureColumnDefault(string $table, string $column, string $ddl): void
    {
        if ($this->connection === null) {
            return;
        }
        if (preg_match('/^[A-Za-z0-9_]{1,64}$/', $table) !== 1 || preg_match('/^[A-Za-z0-9_]{1,64}$/', $column) !== 1) {
            return;
        }
        try {
            $stmt = $this->connection->prepare(
                'SELECT COLUMN_DEFAULT FROM information_schema.COLUMNS
                  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?'
            );
            $stmt->execute([$table, $column]);
            $current = $stmt->fetchColumn();
            if ($current === false || $current === null) {
                return;
            }
            // MySQL requires "ALTER COLUMN <col> SET DEFAULT <value>"
            $this->connection->exec("ALTER TABLE `{$table}` ALTER COLUMN `{$column}` {$ddl}");
        } catch (PDOException $e) {
            error_log("ensureColumnDefault({$table}.{$column}) failed: " . $e->getMessage());
        }
    }

    /**
     * Automatically ensure all required tables exist.
     * Executed on every request, so every statement is idempotent.
     */
    private function ensureSchemaExists(): void
    {
        if ($this->connection === null) {
            return;
        }

        try {
            // 1. Visitor Traffic Table (only written after Analytics consent)
            $this->connection->exec("
                CREATE TABLE IF NOT EXISTS `visitor_traffic` (
                    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    `ip_address` VARCHAR(45) NOT NULL,
                    `user_agent` TEXT NULL,
                    `page_url` VARCHAR(500) NOT NULL,
                    `referrer` VARCHAR(500) NULL,
                    `device_type` VARCHAR(50) DEFAULT 'desktop',
                    `session_id` VARCHAR(100) NULL,
                    `visited_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    INDEX `idx_visited_at` (`visited_at`),
                    INDEX `idx_ip` (`ip_address`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");

            // 2. Cookie Consents Table (stores real-time user selections)
            //    marketing_enabled is retained for schema compatibility but is
            //    permanently 0: this project ships no marketing technology.
            $this->connection->exec("
                CREATE TABLE IF NOT EXISTS `cookie_consents` (
                    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    `ip_address` VARCHAR(45) NOT NULL,
                    `user_agent` TEXT NULL,
                    `consent_type` ENUM('all', 'necessary', 'custom', 'declined') NOT NULL DEFAULT 'all',
                    `essential_enabled` TINYINT(1) NOT NULL DEFAULT 1,
                    `analytics_enabled` TINYINT(1) NOT NULL DEFAULT 0,
                    `marketing_enabled` TINYINT(1) NOT NULL DEFAULT 0,
                    `preferences_enabled` TINYINT(1) NOT NULL DEFAULT 0,
                    `consented_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    INDEX `idx_consented_at` (`consented_at`),
                    INDEX `idx_ip` (`ip_address`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");
            $this->ensureColumnDefault('cookie_consents', 'analytics_enabled', 'SET DEFAULT 0');
            $this->ensureColumnDefault('cookie_consents', 'marketing_enabled', 'SET DEFAULT 0');
            $this->ensureColumnDefault('cookie_consents', 'preferences_enabled', 'SET DEFAULT 0');

            // 3. Contact / Reservation Inquiries Table
            $this->connection->exec("
                CREATE TABLE IF NOT EXISTS `contact_inquiries` (
                    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    `name` VARCHAR(150) NOT NULL,
                    `email` VARCHAR(150) NOT NULL,
                    `service` VARCHAR(150) NULL,
                    `budget` VARCHAR(100) NULL,
                    `message` TEXT NOT NULL,
                    `ip_address` VARCHAR(45) NULL,
                    `status` ENUM('new', 'read', 'replied', 'archived') NOT NULL DEFAULT 'new',
                    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    INDEX `idx_email` (`email`),
                    INDEX `idx_created_at` (`created_at`),
                    INDEX `idx_status` (`status`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");

            // Guarded migration: reservation / consultation fields
            $this->ensureColumn('contact_inquiries', 'inquiry_type',   "ENUM('contact','reservation') NOT NULL DEFAULT 'contact'");
            $this->ensureColumn('contact_inquiries', 'phone',          "VARCHAR(30) NULL");
            $this->ensureColumn('contact_inquiries', 'company',        "VARCHAR(150) NULL");
            $this->ensureColumn('contact_inquiries', 'reserved_at',    "DATETIME NULL");
            $this->ensureColumn('contact_inquiries', 'reserved_zone',  "VARCHAR(64) NULL");
            $this->ensureColumn('contact_inquiries', 'user_agent',     "VARCHAR(500) NULL");
            $this->ensureColumn('contact_inquiries', 'replied_at',     "DATETIME NULL");
            $this->ensureColumn('contact_inquiries', 'updated_at',     "DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP");
            $this->ensureColumn('contact_inquiries', 'reply_message',  "TEXT NULL");
            $this->ensureColumn('contact_inquiries', 'replied_by',     "VARCHAR(100) NULL");

            // 4. Blog Articles Table
            $this->connection->exec("
                CREATE TABLE IF NOT EXISTS `blogs` (
                    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    `slug` VARCHAR(191) NOT NULL UNIQUE,
                    `title` VARCHAR(255) NOT NULL,
                    `subtitle` VARCHAR(255) NULL,
                    `category` VARCHAR(100) NOT NULL,
                    `read_time` VARCHAR(50) DEFAULT '5 min read',
                    `image` VARCHAR(255) NOT NULL,
                    `excerpt` TEXT NOT NULL,
                    `content` LONGTEXT NOT NULL,
                    `status` ENUM('published', 'draft') NOT NULL DEFAULT 'published',
                    `published_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    INDEX `idx_slug` (`slug`),
                    INDEX `idx_status` (`status`),
                    INDEX `idx_published_at` (`published_at`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");

            // 5. Projects Table
            $this->connection->exec("
                CREATE TABLE IF NOT EXISTS `projects` (
                    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    `slug` VARCHAR(191) NOT NULL UNIQUE,
                    `title` VARCHAR(255) NOT NULL,
                    `subtitle` VARCHAR(255) NULL,
                    `role` VARCHAR(150) NOT NULL,
                    `client` VARCHAR(150) NULL,
                    `date_period` VARCHAR(100) NULL,
                    `featured` TINYINT(1) NOT NULL DEFAULT 0,
                    `category_filter` VARCHAR(100) NOT NULL,
                    `category_name` VARCHAR(100) NOT NULL,
                    `categories` JSON NULL,
                    `summary` TEXT NOT NULL,
                    `image` VARCHAR(255) NOT NULL,
                    `image_large` VARCHAR(255) NOT NULL,
                    `metrics` JSON NULL,
                    `stack` JSON NULL,
                    `architectural_highlights` JSON NULL,
                    `problem_statement` TEXT NULL,
                    `solution` TEXT NULL,
                    `gallery` JSON NULL,
                    `live_url` VARCHAR(255) NULL,
                    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    INDEX `idx_slug` (`slug`),
                    INDEX `idx_featured` (`featured`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");

            // 6. Services Table
            $this->connection->exec("
                CREATE TABLE IF NOT EXISTS `services` (
                    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    `slug` VARCHAR(191) NOT NULL UNIQUE,
                    `title` VARCHAR(255) NOT NULL,
                    `tagline` VARCHAR(255) NULL,
                    `bg_color` VARCHAR(20) NULL,
                    `icon` VARCHAR(255) NULL,
                    `summary` TEXT NOT NULL,
                    `description` TEXT NOT NULL,
                    `deliverables` JSON NULL,
                    `sort_order` INT NOT NULL DEFAULT 0,
                    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    INDEX `idx_slug` (`slug`),
                    INDEX `idx_sort` (`sort_order`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");

            // 7. Testimonials Table
            $this->connection->exec("
                CREATE TABLE IF NOT EXISTS `testimonials` (
                    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    `name` VARCHAR(150) NOT NULL,
                    `role` VARCHAR(150) NULL,
                    `company` VARCHAR(150) NULL,
                    `image` VARCHAR(255) NULL,
                    `content` TEXT NOT NULL,
                    `sort_order` INT NOT NULL DEFAULT 0,
                    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    INDEX `idx_sort` (`sort_order`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");

            // 8. Skills Matrix Table
            $this->connection->exec("
                CREATE TABLE IF NOT EXISTS `skills` (
                    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    `slug` VARCHAR(191) NOT NULL UNIQUE,
                    `category` VARCHAR(150) NOT NULL,
                    `percentage` TINYINT UNSIGNED NOT NULL DEFAULT 0,
                    `summary` TEXT NOT NULL,
                    `badges` JSON NULL,
                    `sort_order` INT NOT NULL DEFAULT 0,
                    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    INDEX `idx_slug` (`slug`),
                    INDEX `idx_sort` (`sort_order`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");

            // 9. IndexNow submission ledger (proof of every ping we sent)
            $this->connection->exec("
                CREATE TABLE IF NOT EXISTS `indexnow_logs` (
                    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    `key_location` VARCHAR(255) NOT NULL,
                    `urls` MEDIUMTEXT NULL,
                    `http_status` SMALLINT NULL,
                    `response_body` TEXT NULL,
                    `submitted_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    INDEX `idx_submitted_at` (`submitted_at`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");

            // 10. Admin users (single-owner back office)
            $this->connection->exec("
                CREATE TABLE IF NOT EXISTS `admin_users` (
                    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    `username` VARCHAR(100) NOT NULL,
                    `password_hash` VARCHAR(255) NOT NULL,
                    `display_name` VARCHAR(150) NOT NULL,
                    `email` VARCHAR(150) NOT NULL,
                    `role` ENUM('owner','editor') NOT NULL DEFAULT 'owner',
                    `last_login_at` DATETIME NULL,
                    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    UNIQUE KEY `username` (`username`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");
            $this->seedAdminUser();
        } catch (PDOException $e) {
            error_log('Schema initialization error: ' . $e->getMessage());
        }
    }

    /**
     * Seed the initial admin account on first run.
     *
     * Credentials come from ADMIN_USERNAME / ADMIN_PASSWORD env vars with a
     * safe development fallback. The password is stored as a bcrypt hash.
     * The owner should change it immediately after the first login (or set
     * ADMIN_PASSWORD before the first request).
     */
    private function seedAdminUser(): void
    {
        if ($this->connection === null) {
            return;
        }
        try {
            $count = (int)$this->connection->query('SELECT COUNT(*) FROM `admin_users`')->fetchColumn();
            if ($count > 0) {
                return;
            }

            $username = (string)(getenv('ADMIN_USERNAME') ?: 'admin');
            $password = (string)(getenv('ADMIN_PASSWORD') ?: 'ChangeMe!Admin2026');
            $name     = (string)(getenv('ADMIN_NAME') ?: 'Site Owner');
            $email    = (string)(getenv('ADMIN_EMAIL') ?: 'contact@morexweb.com');

            $username = preg_match('/^[A-Za-z0-9_.\-]{2,100}$/', $username) === 1 ? $username : 'admin';
            $email    = filter_var($email, FILTER_VALIDATE_EMAIL) !== false ? $email : 'contact@morexweb.com';

            $hash = password_hash($password, PASSWORD_BCRYPT);

            $stmt = $this->connection->prepare(
                'INSERT INTO `admin_users` (`username`, `password_hash`, `display_name`, `email`, `role`)
                 VALUES (:u, :h, :n, :e, \'owner\')'
            );
            $stmt->execute([
                ':u' => $username,
                ':h' => $hash,
                ':n' => mb_substr($name, 0, 150),
                ':e' => mb_substr($email, 0, 150),
            ]);

            // Record the initial credentials for the operator (never the hash).
            $logDir = defined('ROOT_PATH') ? ROOT_PATH : dirname(__DIR__);
            @file_put_contents(
                $logDir . '/storage/logs/admin-setup.log',
                sprintf("[%s] Seeded admin user \"%s\" (email: %s).\n", date('c'), $username, $email),
                FILE_APPEND | LOCK_EX
            );
        } catch (PDOException $e) {
            error_log('seedAdminUser failed: ' . $e->getMessage());
        }
    }
}

/**
 * Global helper to obtain active PDO instance
 */
function get_db(): ?PDO
{
    return Database::getInstance()->getConnection();
}
