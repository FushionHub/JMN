<?php
declare(strict_types=1);
/**
 * CLI e2e for the admin back office. Drives the real HTTP server.
 * Usage: php tools/e2e_admin.php [base_url]
 */

$base = rtrim($argv[1] ?? 'http://127.0.0.1:8099', '/');
$pass = 0; $fail = 0;

function chk(string $name, bool $ok, string $extra = ''): void {
    global $pass, $fail;
    if ($ok) { $pass++; echo "  PASS  $name" . ($extra !== '' ? "  -> $extra" : '') . "\n"; }
    else     { $fail++; echo "  FAIL  $name" . ($extra !== '' ? "  -> $extra" : '') . "\n"; }
}

function req(string $url, array $opts = []): array {
    $ch = curl_init($url);
    // $opts wins on key collisions (PHP "+" keeps the left side).
    curl_setopt_array($ch, $opts + [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_TIMEOUT        => 15,
        CURLOPT_HEADER         => true,
    ]);
    $raw = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    curl_close($ch);
    $headers = substr((string)$raw, 0, $headerSize);
    $body    = substr((string)$raw, $headerSize);
    return ['status' => $status, 'headers' => $headers, 'body' => $body];
}

function csrf_from(string $html): string {
    if (preg_match('/name="csrf_token"\s+value="([a-f0-9]{64})"/', $html, $m)) return $m[1];
    if (preg_match('/value="([a-f0-9]{64})"\s+name="csrf_token"/', $html, $m)) return $m[1];
    return '';
}

function cookie_session(array $loginResp): string {
    // Extract JMNSESSID from Set-Cookie headers.
    if (preg_match('/Set-Cookie:\s*JMNSESSID=([^;]+)/i', $loginResp['headers'], $m)) {
        return 'JMNSESSID=' . $m[1];
    }
    return '';
}

echo "== Admin e2e ==\n";

$jar = tempnam(sys_get_temp_dir(), 'jar');

// 1. Login page renders.
$r = req($base . '/admin/login.php', [CURLOPT_COOKIEJAR => $jar, CURLOPT_COOKIEFILE => $jar]);
chk('GET /admin/login.php -> 200', $r['status'] === 200, (string)$r['status']);
chk('login page has csrf', csrf_from($r['body']) !== '');
chk('login page noindex', str_contains($r['headers'], 'noindex'));

// 2. Protected pages redirect to login without a session.
foreach (['index.php', 'inquiries.php', 'blog.php', 'blog-edit.php', 'settings.php',
          'projects.php', 'project-edit.php', 'services.php', 'service-edit.php',
          'testimonials.php', 'testimonial-edit.php', 'skills.php', 'skill-edit.php'] as $p) {
    $r = req($base . '/admin/' . $p);
    chk("unauth /admin/$p -> 302 to login", $r['status'] === 302 && str_contains($r['headers'], 'login.php'), (string)$r['status']);
}

// 3. Login POST (bad password first).
$login = req($base . '/admin/login.php', [CURLOPT_COOKIEJAR => $jar, CURLOPT_COOKIEFILE => $jar]);
$csrf  = csrf_from($login['body']);
$r = req($base . '/admin/login.php', [
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => http_build_query(['csrf_token' => $csrf, 'username' => 'admin', 'password' => 'wrong-password']),
    CURLOPT_COOKIEJAR      => $jar,
    CURLOPT_COOKIEFILE     => $jar,
]);
chk('bad login stays on login page', $r['status'] === 200 && str_contains($r['body'], 'Invalid username'), (string)$r['status']);

// The login endpoint throttles to one attempt per 2 s; wait it out.
sleep(3);

// 4. Login POST (correct password).
$login = req($base . '/admin/login.php', [CURLOPT_COOKIEJAR => $jar, CURLOPT_COOKIEFILE => $jar]);
$csrf  = csrf_from($login['body']);
$adminPass = getenv('ADMIN_PASSWORD') ?: 'ChangeMe!Admin2026';
$r = req($base . '/admin/login.php', [
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => http_build_query(['csrf_token' => $csrf, 'username' => 'admin', 'password' => $adminPass]),
    CURLOPT_COOKIEJAR      => $jar,
    CURLOPT_COOKIEFILE     => $jar,
    CURLOPT_FOLLOWLOCATION => false,
]);
chk('good login -> 302 to dashboard', $r['status'] === 302 && str_contains($r['headers'], 'index.php'), (string)$r['status']);
$cookie = cookie_session($r);
if ($cookie === '' && is_readable($jar)) {
    $jarTxt = (string)file_get_contents($jar);
    if (preg_match('/JMNSESSID\s+(\S+)/', $jarTxt, $m)) { $cookie = 'JMNSESSID=' . $m[1]; }
}
chk('session cookie captured', $cookie !== '');

// 5. Authenticated pages render.
$auth = [CURLOPT_HTTPHEADER => ['Cookie: ' . $cookie]];
foreach (['index.php' => 'Dashboard', 'inquiries.php' => 'Inquiries', 'blog.php' => 'Blog', 'blog-edit.php' => 'New Post', 'settings.php' => 'Settings',
          'projects.php' => 'Projects', 'project-edit.php' => 'New Project',
          'services.php' => 'Services', 'service-edit.php' => 'New Service',
          'testimonials.php' => 'Testimonials', 'testimonial-edit.php' => 'New Testimonial',
          'skills.php' => 'Skills', 'skill-edit.php' => 'New Skill'] as $p => $needle) {
    $r = req($base . '/admin/' . $p, $auth);
    chk("auth /admin/$p -> 200", $r['status'] === 200, (string)$r['status']);
    chk("auth /admin/$p has title", str_contains($r['body'], $needle));
}

// 6. Dashboard shows stat labels + analytics charts.
$r = req($base . '/admin/index.php', $auth);
chk('dashboard shows Total inquiries', str_contains($r['body'], 'Total inquiries'));
chk('dashboard shows Blog posts stat', str_contains($r['body'], 'Blog posts'));
chk('dashboard has Analytics overview section', str_contains($r['body'], 'Analytics overview'));
chk('dashboard loads self-hosted Chart.js', str_contains($r['body'], 'chart.umd.min.js'));
chk('dashboard has trend chart canvas', str_contains($r['body'], 'id="chartTrend"'));
chk('dashboard has status chart canvas', str_contains($r['body'], 'id="chartStatus"'));
chk('dashboard has content chart canvas', str_contains($r['body'], 'id="chartContent"'));
chk('dashboard embeds chart JSON payload', str_contains($r['body'], '"trend"'));

// 7. Inquiries list shows filter chips.
$r = req($base . '/admin/inquiries.php', $auth);
chk('inquiries page has filter chips', str_contains($r['body'], 'admin-chip'));

// 8. Open the first inquiry + reply form present.
$r = req($base . '/admin/inquiry.php?id=1', $auth);
chk('inquiry #1 -> 200', $r['status'] === 200, (string)$r['status']);
chk('inquiry page has reply form', str_contains($r['body'], 'reply_message'));
chk('inquiry page has status form', str_contains($r['body'], 'action" value="status"'));
$csrf2 = csrf_from($r['body']);

// 9. Status change works.
$r = req($base . '/admin/inquiry.php', [
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => http_build_query(['csrf_token' => $csrf2, 'id' => 1, 'action' => 'status', 'status' => 'read']),
    CURLOPT_HTTPHEADER => ['Cookie: ' . $cookie],
]);
chk('status change -> 302 back', $r['status'] === 302, (string)$r['status']);

// The inquiry endpoint throttles to one action per 2 s; wait it out.
sleep(3);

// 10. Blog list + editor.
$r = req($base . '/admin/blog.php', $auth);
chk('blog list has New post button', str_contains($r['body'], 'blog-edit.php'));
$r = req($base . '/admin/blog-edit.php', $auth);
chk('blog editor has TinyMCE loader', str_contains($r['body'], 'cdn.tiny.cloud'));
chk('blog editor has content textarea', str_contains($r['body'], 'id="f_content"'));
chk('blog editor has slug lock', str_contains($r['body'], 'slug_lock'));

// 11. Create a post via POST.
$editor = req($base . '/admin/blog-edit.php', $auth);
$csrf3  = csrf_from($editor['body']);
$r = req($base . '/admin/blog-edit.php', [
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => http_build_query([
        'csrf_token' => $csrf3, 'id' => 0,
        'title' => 'E2E Test Post ' . time(),
        'slug' => 'e2e-test-post-' . time(),
        'subtitle' => 'Automated', 'category' => 'Testing',
        'read_time' => '3 min read', 'image' => 'assets/img/blog/1.jpg',
        'excerpt' => 'This is an automated excerpt created by the e2e suite.',
        'content' => '<p>Automated body content for the e2e test.</p>',
        'status' => 'draft', 'published_at' => date('Y-m-d\TH:i'),
    ]),
    CURLOPT_HTTPHEADER => ['Cookie: ' . $cookie],
]);
chk('create post -> 302 to list', $r['status'] === 302, (string)$r['status']);

// 12. Confirm the draft exists and capture its id for cleanup.
$r = req($base . '/admin/blog.php?status=draft', $auth);
chk('draft appears in list', str_contains($r['body'], 'E2E Test Post'));
$draftId = 0;
if (preg_match('/blog-edit\.php\?id=(\d+)[^>]*>\s*E2E Test Post/', $r['body'], $m)) {
    $draftId = (int)$m[1];
}
chk('extracted draft post id', $draftId > 0, (string)$draftId);

// 12b. Publish a post and verify the full public round-trip.
// The editor rate-limits to one save per 2 s; wait it out after the draft POST.
sleep(3);
$pubSlug = 'e2e-published-' . time();
$editor = req($base . '/admin/blog-edit.php', $auth);
$csrfP  = csrf_from($editor['body']);
$r = req($base . '/admin/blog-edit.php', [
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => http_build_query([
        'csrf_token' => $csrfP, 'id' => 0,
        'title' => 'E2E Published Post', 'slug' => $pubSlug,
        'subtitle' => 'Public round-trip', 'category' => 'Testing',
        'read_time' => '2 min read', 'image' => 'assets/img/blog/1.jpg',
        'excerpt' => 'Published by e2e suite to verify public visibility.',
        'content' => '<p>Published body content.</p>',
        'status' => 'published', 'published_at' => date('Y-m-d\TH:i'),
    ]),
    CURLOPT_HTTPHEADER => ['Cookie: ' . $cookie],
]);
chk('publish POST -> 302 to list', $r['status'] === 302, (string)$r['status']);
// The admin list is now on its rate limit; wait before the next admin GET.
sleep(3);
$r = req($base . '/admin/blog.php?status=published', $auth);
chk('published post in admin list', str_contains($r['body'], 'E2E Published Post'));
// Extract the new post's id for later cleanup.
$postId = 0;
if (preg_match('/blog-edit\.php\?id=(\d+)[^>]*>\s*E2E Published Post/', $r['body'], $m)) {
    $postId = (int)$m[1];
}
chk('extracted published post id', $postId > 0, (string)$postId);

// Public site must show the published post and hide the draft.
$pub = req($base . '/blog.php');
chk('published post on public blog list', str_contains($pub['body'], $pubSlug));
chk('draft absent from public blog list', !str_contains($pub['body'], 'e2e-test-post-'));
$det = req($base . '/blog-detail.php?slug=' . urlencode($pubSlug));
chk('published post public detail -> 200', $det['status'] === 200, (string)$det['status']);
$det = req($base . '/blog-detail.php?slug=e2e-test-post-nonexistent');
chk('draft/unknown slug public detail -> 404', $det['status'] === 404, (string)$det['status']);

// Clean up the published test post so it never reaches production data.
if ($postId > 0) {
    $blogList = req($base . '/admin/blog.php', $auth);
    $csrfD = csrf_from($blogList['body']);
    $r = req($base . '/admin/blog-delete.php', [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query(['csrf_token' => $csrfD, 'id' => $postId]),
        CURLOPT_HTTPHEADER => ['Cookie: ' . $cookie],
    ]);
    chk('delete published test post -> 302', $r['status'] === 302, (string)$r['status']);
    sleep(3);
    $pub = req($base . '/blog.php');
    chk('deleted post gone from public list', !str_contains($pub['body'], $pubSlug));
}

// Clean up the draft test post too, so it never leaks into production data.
if ($draftId > 0) {
    $blogList = req($base . '/admin/blog.php', $auth);
    $csrfDd = csrf_from($blogList['body']);
    sleep(3);
    $r = req($base . '/admin/blog-delete.php', [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query(['csrf_token' => $csrfDd, 'id' => $draftId]),
        CURLOPT_HTTPHEADER => ['Cookie: ' . $cookie],
    ]);
    chk('delete draft test post -> 302', $r['status'] === 302, (string)$r['status']);
    sleep(3);
    $r = req($base . '/admin/blog.php?status=draft', $auth);
    // The success flash ("Deleted E2E Test Post...") contains the title, so
    // assert on the draft's edit-link id, which vanishes only when the row is gone.
    $draftGone = !str_contains($r['body'], 'blog-edit.php?id=' . $draftId);
    $diag = 'http=' . $r['status'];
    if (preg_match('/admin-flash--(ok|err)[^>]*>(.*?)<\/div>/s', $r['body'], $fm)) {
        $diag .= ' flash[' . $fm[1] . ']=' . trim(preg_replace('/\s+/', ' ', strip_tags($fm[2])));
    }
    chk('draft post gone after delete', $draftGone, $diag);
}

// 12c. Projects CRUD round-trip (create -> public visible -> delete -> gone).
$projSlug = 'e2e-project-' . time();
$editor = req($base . '/admin/project-edit.php', $auth);
$csrfPj = csrf_from($editor['body']);
$r = req($base . '/admin/project-edit.php', [
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => http_build_query([
        'csrf_token' => $csrfPj, 'id' => 0,
        'title' => 'E2E Test Project', 'slug' => $projSlug,
        'subtitle' => 'Automated', 'role' => 'Tester', 'client' => 'E2E',
        'date_period' => '2026', 'featured' => '0',
        'category_name' => 'Testing', 'category_filter' => 'testing',
        'categories' => "E2E\nAutomated",
        'summary' => 'An automated project created by the e2e suite.',
        'image' => 'img/project/1.jpg', 'image_large' => 'img/project/11.jpg',
        'metrics' => "Coverage: 100%\nRaces: 0",
        'stack' => "PHP\nPHPUnit",
        'architectural_highlights' => 'Automated highlight entry.',
        'problem_statement' => 'Automated problem statement.',
        'solution' => 'Automated solution statement.',
        'gallery' => 'img/project/1.jpg', 'live_url' => '',
    ]),
    CURLOPT_HTTPHEADER => ['Cookie: ' . $cookie],
]);
chk('create project -> 302 to list', $r['status'] === 302, (string)$r['status']);
sleep(3);
$r = req($base . '/admin/projects.php', $auth);
chk('project appears in admin list', str_contains($r['body'], 'E2E Test Project'));
$pub = req($base . '/projects.php');
chk('project on public projects page', str_contains($pub['body'], $projSlug));
$det = req($base . '/single-project.php?slug=' . urlencode($projSlug));
chk('project public detail -> 200', $det['status'] === 200, (string)$det['status']);
// Delete it.
$r = req($base . '/admin/projects.php', $auth);
$csrfPd = csrf_from($r['body']);
$projRow = $dbRow = null;
if (preg_match('/project-edit\.php\?id=(\d+)[^>]*>[^<]*<\/a>\s*<span[^>]*>' . preg_quote($projSlug, '/') . '/s', $r['body'], $m)
    || preg_match('/project-edit\.php\?id=(\d+)[^>]*>\s*E2E Test Project/', $r['body'], $m)) {
    $projId = (int)$m[1];
    $r = req($base . '/admin/project-delete.php', [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query(['csrf_token' => $csrfPd, 'id' => $projId]),
        CURLOPT_HTTPHEADER => ['Cookie: ' . $cookie],
    ]);
    chk('delete test project -> 302', $r['status'] === 302, (string)$r['status']);
    sleep(3);
    $pub = req($base . '/projects.php');
    chk('deleted project gone from public list', !str_contains($pub['body'], $projSlug));
}

// 12d. Services CRUD round-trip.
$svcSlug = 'e2e-service-' . time();
$editor = req($base . '/admin/service-edit.php', $auth);
$csrfSv = csrf_from($editor['body']);
$r = req($base . '/admin/service-edit.php', [
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => http_build_query([
        'csrf_token' => $csrfSv, 'id' => 0,
        'title' => 'E2E Test Service', 'slug' => $svcSlug,
        'tagline' => 'Automated', 'bg_color' => '#ffffff', 'icon' => 'img/services/1.png',
        'summary' => 'An automated service summary created by the e2e suite.',
        'description' => 'An automated service description created by the e2e suite.',
        'deliverables' => "Automated deliverable one\nAutomated deliverable two",
        'sort_order' => '999',
    ]),
    CURLOPT_HTTPHEADER => ['Cookie: ' . $cookie],
]);
chk('create service -> 302 to list', $r['status'] === 302, (string)$r['status']);
sleep(3);
$r = req($base . '/admin/services.php', $auth);
chk('service appears in admin list', str_contains($r['body'], 'E2E Test Service'));
$pub = req($base . '/services.php');
chk('service on public services page', str_contains($pub['body'], 'E2E Test Service'));
// Delete it.
$r = req($base . '/admin/services.php', $auth);
$csrfSd = csrf_from($r['body']);
if (preg_match('/service-edit\.php\?id=(\d+)[^>]*>\s*E2E Test Service/', $r['body'], $m)) {
    $svcId = (int)$m[1];
    $r = req($base . '/admin/service-delete.php', [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query(['csrf_token' => $csrfSd, 'id' => $svcId]),
        CURLOPT_HTTPHEADER => ['Cookie: ' . $cookie],
    ]);
    chk('delete test service -> 302', $r['status'] === 302, (string)$r['status']);
    sleep(3);
    $pub = req($base . '/services.php');
    chk('deleted service gone from public list', !str_contains($pub['body'], 'E2E Test Service'));
}

// 12e. Testimonials CRUD round-trip.
$editor = req($base . '/admin/testimonial-edit.php', $auth);
$csrfTm = csrf_from($editor['body']);
$r = req($base . '/admin/testimonial-edit.php', [
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => http_build_query([
        'csrf_token' => $csrfTm, 'id' => 0,
        'name' => 'E2E Client', 'role' => 'QA', 'company' => 'E2E Ltd',
        'image' => 'assets/img/testimonials/1.jpg',
        'content' => 'An automated testimonial created by the e2e suite.',
        'sort_order' => '999',
    ]),
    CURLOPT_HTTPHEADER => ['Cookie: ' . $cookie],
]);
chk('create testimonial -> 302 to list', $r['status'] === 302, (string)$r['status']);
sleep(3);
$r = req($base . '/admin/testimonials.php', $auth);
chk('testimonial appears in admin list', str_contains($r['body'], 'E2E Client'));
$pub = req($base . '/about.php');
chk('testimonial on public about page', str_contains($pub['body'], 'E2E Client'));
// Delete it.
$r = req($base . '/admin/testimonials.php', $auth);
$csrfTd = csrf_from($r['body']);
if (preg_match('/testimonial-edit\.php\?id=(\d+)[^>]*>\s*E2E Client/', $r['body'], $m)) {
    $tmId = (int)$m[1];
    $r = req($base . '/admin/testimonial-delete.php', [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query(['csrf_token' => $csrfTd, 'id' => $tmId]),
        CURLOPT_HTTPHEADER => ['Cookie: ' . $cookie],
    ]);
    chk('delete test testimonial -> 302', $r['status'] === 302, (string)$r['status']);
}

// 12f. Skills CRUD round-trip.
$skillSlug = 'e2e-skill-' . time();
$editor = req($base . '/admin/skill-edit.php', $auth);
$csrfSk = csrf_from($editor['body']);
$r = req($base . '/admin/skill-edit.php', [
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => http_build_query([
        'csrf_token' => $csrfSk, 'id' => 0,
        'category' => 'E2E Testing Skill', 'slug' => $skillSlug,
        'percentage' => '77', 'summary' => 'An automated skill created by the e2e suite.',
        'badges' => "E2E\nAutomation", 'sort_order' => '999',
    ]),
    CURLOPT_HTTPHEADER => ['Cookie: ' . $cookie],
]);
chk('create skill -> 302 to list', $r['status'] === 302, (string)$r['status']);
sleep(3);
$r = req($base . '/admin/skills.php', $auth);
chk('skill appears in admin list', str_contains($r['body'], 'E2E Testing Skill'));
$pub = req($base . '/about.php');
chk('skill on public about page', str_contains($pub['body'], 'E2E Testing Skill'));
// Delete it.
$r = req($base . '/admin/skills.php', $auth);
$csrfSkd = csrf_from($r['body']);
if (preg_match('/skill-edit\.php\?id=(\d+)[^>]*>\s*E2E Testing Skill/', $r['body'], $m)) {
    $skId = (int)$m[1];
    $r = req($base . '/admin/skill-delete.php', [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query(['csrf_token' => $csrfSkd, 'id' => $skId]),
        CURLOPT_HTTPHEADER => ['Cookie: ' . $cookie],
    ]);
    chk('delete test skill -> 302', $r['status'] === 302, (string)$r['status']);
    sleep(3);
    $pub = req($base . '/about.php');
    chk('deleted skill gone from public page', !str_contains($pub['body'], 'E2E Testing Skill'));
}

// 13. Reply flow (dry: send to a test address, expect recorded reply).
$r = req($base . '/admin/inquiry.php?id=1', $auth);
$csrf4 = csrf_from($r['body']);
$r = req($base . '/admin/inquiry.php', [
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => http_build_query([
        'csrf_token' => $csrf4, 'id' => 1, 'action' => 'reply',
        'reply_message' => 'Thank you for your inquiry. This is an automated e2e reply.',
    ]),
    CURLOPT_HTTPHEADER => ['Cookie: ' . $cookie],
]);
chk('reply POST -> 302 back', $r['status'] === 302, (string)$r['status']);
$r = req($base . '/admin/inquiry.php?id=1', $auth);
chk('reply recorded on inquiry', str_contains($r['body'], 'automated e2e reply'));
chk('status now replied', str_contains($r['body'], 'value="replied" selected'));

// 14. Logout.
$r = req($base . '/admin/logout.php', $auth);
chk('logout -> 302 to login', $r['status'] === 302 && str_contains($r['headers'], 'login.php'), (string)$r['status']);

@unlink($jar);

echo "\n" . ($fail === 0
    ? "ALL ADMIN TESTS PASSED ($pass)"
    : "FAILED ($fail) of ($pass + $fail)") . "\n";
exit($fail === 0 ? 0 : 1);
