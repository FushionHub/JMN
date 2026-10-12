<?php
declare(strict_types=1);

/**
 * Site-Styled HTML Email Templates
 * Jamachi Maurice Mauricennadi Portfolio Platform
 *
 * Both templates are rendered with the exact visual language of the live
 * site so the message reads as part of the product rather than a generic
 * transactional mail:
 *   - surface  #111a24 / #0d131a / #090e14
 *   - accent   #f41a4a -> #ff4b72
 *   - type     Montserrat, 24px radius cards, pill badges
 *
 * All user-supplied values are escaped with htmlspecialchars() before they
 * are interpolated, so a crafted name/message can never inject markup.
 */

/**
 * @param array<string,mixed> $q Normalised inquiry payload
 * @return string Full HTML document
 */
function jmn_build_admin_email(array $q): string
{
    $id            = (int)($q['id'] ?? 0);
    $name          = (string)($q['name'] ?? '');
    $email         = (string)($q['email'] ?? '');
    $phone         = (string)($q['phone'] ?? '');
    $company       = (string)($q['company'] ?? '');
    $service       = (string)($q['service'] ?? '');
    $budget        = (string)($q['budget'] ?? '');
    $message       = (string)($q['message'] ?? '');
    $ip            = (string)($q['ip'] ?? '');
    $createdAt     = (string)($q['created_at'] ?? '');
    $inquiryType   = (string)($q['inquiry_type'] ?? 'contact');
    $reservedAt    = (string)($q['reserved_at'] ?? '');
    $reservedZone  = (string)($q['reserved_zone'] ?? '');

    $h  = static fn (string $v): string => htmlspecialchars($v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    $safeName    = $h($name);
    $safeEmail   = $h($email);
    $safePhone   = $h($phone);
    $safeCompany = $h($company);
    $safeService = $h($service);
    $safeBudget  = $h($budget);
    $safeMessage = nl2br($h($message));
    $safeIp      = $h($ip);
    $safeZone    = $h($reservedZone);
    $formattedDate = $createdAt !== ''
        ? date('l, F j, Y · g:i A T', (int)strtotime($createdAt))
        : date('l, F j, Y · g:i A T');

    $isReservation = $inquiryType === 'reservation' && $reservedAt !== '';
    $badgeLabel    = $isReservation ? 'NEW RESERVATION' : 'NEW INQUIRY';
    $replySubject  = rawurlencode('Re: ' . $service . ' — ' . PROFILE_NAME);

    $reservedRows = '';
    if ($isReservation) {
        $whenLocal   = date('l, F j, Y \a\t g:i A', (int)strtotime($reservedAt));
        $reservedRows = <<<ROW
                                <tr>
                                    <td width="38%" style="padding:14px 18px; border-bottom:1px solid #243244; font-size:12px; font-weight:700; color:#94a3b8; text-transform:uppercase; letter-spacing:0.5px;">Proposed Slot</td>
                                    <td style="padding:14px 18px; border-bottom:1px solid #243244; font-size:14px; font-weight:700; color:#ffffff;">{$whenLocal} {$safeZone}</td>
                                </tr>
ROW;
    }

    $contactRows = '';
    if ($phone !== '') {
        $contactRows .= <<<ROW
                                <tr>
                                    <td width="38%" style="padding:14px 18px; border-bottom:1px solid #243244; font-size:12px; font-weight:700; color:#94a3b8; text-transform:uppercase; letter-spacing:0.5px;">Phone</td>
                                    <td style="padding:14px 18px; border-bottom:1px solid #243244; font-size:14px; font-weight:700; color:#ffffff;">{$safePhone}</td>
                                </tr>
ROW;
    }
    if ($company !== '') {
        $contactRows .= <<<ROW
                                <tr>
                                    <td width="38%" style="padding:14px 18px; border-bottom:1px solid #243244; font-size:12px; font-weight:700; color:#94a3b8; text-transform:uppercase; letter-spacing:0.5px;">Company</td>
                                    <td style="padding:14px 18px; border-bottom:1px solid #243244; font-size:14px; font-weight:700; color:#ffffff;">{$safeCompany}</td>
                                </tr>
ROW;
    }

    $regNo     = PROFILE_REG_NO;
    $portfolio = url('');

    return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{$badgeLabel} #{$id}</title>
</head>
<body style="margin:0; padding:0; background-color:#0d131a; font-family:'Montserrat', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; -webkit-font-smoothing:antialiased; color:#ffffff;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#0d131a; padding:40px 15px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width:620px; background-color:#111a24; border:1px solid #1f2a37; border-radius:24px; overflow:hidden; box-shadow:0 25px 50px -12px rgba(0,0,0,0.5);">

                    <tr>
                        <td style="background:linear-gradient(90deg, #f41a4a 0%, #ff4b72 100%); height:6px; line-height:6px; font-size:6px;">&nbsp;</td>
                    </tr>

                    <tr>
                        <td style="padding:32px 36px 20px 36px;">
                            <table width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td>
                                        <div style="display:inline-block; padding:5px 12px; background-color:rgba(244,26,74,0.15); border:1px solid rgba(244,26,74,0.4); border-radius:20px; font-size:11px; font-weight:700; color:#ff4b72; text-transform:uppercase; letter-spacing:1px; margin-bottom:12px;">
                                            &#9679; {$badgeLabel} #{$id}
                                        </div>
                                        <h1 style="margin:0 0 6px 0; font-size:22px; font-weight:800; color:#ffffff; letter-spacing:0.5px; line-height:1.2;">
                                            JAMACHI MAURICE MAURICENNADI
                                        </h1>
                                        <p style="margin:0; font-size:13px; color:#94a3b8; font-weight:500;">
                                            Principal Software Architect &amp; Founder · Morex Web Enterprise
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:0 36px;">
                            <div style="border-top:1px solid #1e293b;"></div>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:28px 36px 15px 36px;">
                            <h3 style="margin:0 0 16px 0; font-size:14px; font-weight:700; color:#f41a4a; text-transform:uppercase; letter-spacing:1.2px;">
                                Sender Specifications
                            </h3>
                            <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#162231; border:1px solid #243244; border-radius:18px; overflow:hidden;">
                                <tr>
                                    <td width="38%" style="padding:14px 18px; border-bottom:1px solid #243244; font-size:12px; font-weight:700; color:#94a3b8; text-transform:uppercase; letter-spacing:0.5px;">Full Name</td>
                                    <td style="padding:14px 18px; border-bottom:1px solid #243244; font-size:14px; font-weight:700; color:#ffffff;">{$safeName}</td>
                                </tr>
                                <tr>
                                    <td width="38%" style="padding:14px 18px; border-bottom:1px solid #243244; font-size:12px; font-weight:700; color:#94a3b8; text-transform:uppercase; letter-spacing:0.5px;">Email</td>
                                    <td style="padding:14px 18px; border-bottom:1px solid #243244; font-size:14px; font-weight:600; color:#ff4b72;">
                                        <a href="mailto:{$safeEmail}" style="color:#ff4b72; text-decoration:none;">{$safeEmail}</a>
                                    </td>
                                </tr>
{$contactRows}{$reservedRows}
                                <tr>
                                    <td width="38%" style="padding:14px 18px; border-bottom:1px solid #243244; font-size:12px; font-weight:700; color:#94a3b8; text-transform:uppercase; letter-spacing:0.5px;">Engagement / Scope</td>
                                    <td style="padding:14px 18px; border-bottom:1px solid #243244; font-size:14px; font-weight:600; color:#ffffff;">{$safeService}</td>
                                </tr>
                                <tr>
                                    <td width="38%" style="padding:14px 18px; border-bottom:1px solid #243244; font-size:12px; font-weight:700; color:#94a3b8; text-transform:uppercase; letter-spacing:0.5px;">Budget / Investment</td>
                                    <td style="padding:14px 18px; border-bottom:1px solid #243244; font-size:14px; font-weight:700; color:#10b981;">{$safeBudget}</td>
                                </tr>
                                <tr>
                                    <td width="38%" style="padding:14px 18px; font-size:12px; font-weight:700; color:#94a3b8; text-transform:uppercase; letter-spacing:0.5px;">Received At</td>
                                    <td style="padding:14px 18px; font-size:12px; color:#cbd5e1;">{$formattedDate}</td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:10px 36px 25px 36px;">
                            <h3 style="margin:0 0 12px 0; font-size:14px; font-weight:700; color:#f41a4a; text-transform:uppercase; letter-spacing:1.2px;">
                                Brief / Requested Objective
                            </h3>
                            <div style="background-color:#162231; border-left:4px solid #f41a4a; border-radius:0 16px 16px 0; padding:20px; font-size:14px; line-height:1.7; color:#e2e8f0; font-family:'Courier New', Courier, monospace;">
                                {$safeMessage}
                            </div>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:10px 36px 30px 36px;" align="center">
                            <table cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td style="border-radius:14px; background-color:#f41a4a;">
                                        <a href="mailto:{$safeEmail}?subject={$replySubject}" style="display:inline-block; padding:14px 28px; font-size:14px; font-weight:700; color:#ffffff; text-decoration:none; border-radius:14px; letter-spacing:0.5px;">
                                            Reply Directly to {$safeName} &rarr;
                                        </a>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:20px 36px; background-color:#0b1119; border-top:1px solid #1e293b;">
                            <table width="100%" cellpadding="0" cellspacing="0" border="0" style="text-align:center;">
                                <tr>
                                    <td width="25%" style="padding:5px;">
                                        <div style="font-size:16px; font-weight:800; color:#f41a4a;">99.9%</div>
                                        <div style="font-size:10px; color:#94a3b8; text-transform:uppercase; margin-top:2px;">Uptime</div>
                                    </td>
                                    <td width="25%" style="padding:5px; border-left:1px solid #1e293b;">
                                        <div style="font-size:16px; font-weight:800; color:#ffffff;">-50%</div>
                                        <div style="font-size:10px; color:#94a3b8; text-transform:uppercase; margin-top:2px;">Query Latency</div>
                                    </td>
                                    <td width="25%" style="padding:5px; border-left:1px solid #1e293b;">
                                        <div style="font-size:16px; font-weight:800; color:#f41a4a;">+18%</div>
                                        <div style="font-size:10px; color:#94a3b8; text-transform:uppercase; margin-top:2px;">Conversion</div>
                                    </td>
                                    <td width="25%" style="padding:5px; border-left:1px solid #1e293b;">
                                        <div style="font-size:16px; font-weight:800; color:#ffffff;">-35%</div>
                                        <div style="font-size:10px; color:#94a3b8; text-transform:uppercase; margin-top:2px;">Cloud Cost</div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:24px 36px; background-color:#090e14; text-align:center;">
                            <p style="margin:0 0 6px 0; font-size:12px; color:#64748b; font-weight:500;">
                                <a href="{$portfolio}" style="color:#94a3b8; text-decoration:none;">jmn.morexweb.com</a>
                                · Morex Web Enterprise Group of Companies Ltd. · BN: {$regNo}
                            </p>
                            <p style="margin:0; font-size:11px; color:#475569;">
                                Logged to <code style="color:#64748b;">contact_inquiries</code> #{$id} · Source IP {$safeIp} · CSRF verified
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
HTML;
}

/**
 * @param array<string,mixed> $q Normalised inquiry payload
 * @return string Full HTML document
 */
function jmn_build_client_email(array $q): string
{
    $name         = (string)($q['name'] ?? '');
    $service      = (string)($q['service'] ?? '');
    $message      = (string)($q['message'] ?? '');
    $inquiryType  = (string)($q['inquiry_type'] ?? 'contact');
    $reservedAt   = (string)($q['reserved_at'] ?? '');
    $reservedZone = (string)($q['reserved_zone'] ?? '');
    $createdAt    = (string)($q['created_at'] ?? '');

    $h = static fn (string $v): string => htmlspecialchars($v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    $safeName    = $h($name);
    $safeService = $h($service);
    $safeMessage = nl2br($h($message));
    $safeZone    = $h($reservedZone);

    $isReservation = $inquiryType === 'reservation' && $reservedAt !== '';
    $badgeLabel    = $isReservation ? 'RESERVATION CONFIRMED' : 'INQUIRY RECEIVED';
    $badgeColor    = $isReservation ? '#34d399' : '#34d399';
    $badgeBg       = $isReservation ? 'rgba(16,185,129,0.15)' : 'rgba(16,185,129,0.15)';

    $profileName  = PROFILE_NAME;
    $corpEmail    = PROFILE_CORP_EMAIL;
    $phoneRaw     = PROFILE_PHONE_RAW;
    $phoneFmt     = PROFILE_PHONE;
    $regNo        = PROFILE_REG_NO;
    $portfolioUrl = url('');
    $reschedule   = url('contact.php');

    $leadLine = $isReservation
        ? 'Your consultation slot has been reserved on my calendar.'
        : 'Your message has been securely recorded.';

    $reservationBlock = '';
    if ($isReservation) {
        $whenLocal = date('l, F j, Y \a\t g:i A', (int)strtotime($reservedAt));
        $startUtc  = gmdate('Ymd\THis', (int)strtotime($reservedAt));
        $endUtc    = gmdate('Ymd\THis', (int)strtotime($reservedAt) + 3600);
        $calTitle  = rawurlencode('Consultation with ' . PROFILE_NAME);
        $calDetail = rawurlencode('Consultation booked through ' . PROFILE_PORTFOLIO_URL);
        $calLoc    = rawurlencode('Video call (link to follow by email)');
        $calUrl    = "https://calendar.google.com/calendar/render?action=TEMPLATE&text={$calTitle}&dates={$startUtc}/{$endUtc}&details={$calDetail}&location={$calLoc}";

        $reservationBlock = <<<BLK
                    <tr>
                        <td style="padding:5px 36px 8px 36px;">
                            <div style="background-color:#162231; border:1px solid #10b981; border-radius:16px; padding:18px 20px;">
                                <div style="font-size:11px; font-weight:700; color:#34d399; text-transform:uppercase; letter-spacing:1px; margin-bottom:8px;">
                                    Reserved Slot
                                </div>
                                <div style="font-size:15px; font-weight:700; color:#ffffff; line-height:1.5;">
                                    {$whenLocal} {$safeZone}
                                </div>
                                <div style="margin-top:12px;">
                                    <a href="{$calUrl}" style="display:inline-block; padding:10px 18px; background-color:#f41a4a; color:#ffffff; font-size:12px; font-weight:700; text-decoration:none; border-radius:12px;">Add to Google Calendar</a>
                                    <a href="{$reschedule}" style="display:inline-block; padding:10px 18px; background-color:#1f2a37; color:#cbd5e1; font-size:12px; font-weight:700; text-decoration:none; border-radius:12px; margin-left:6px;">Request a change</a>
                                </div>
                            </div>
                        </td>
                    </tr>
BLK;
    }

    return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{$badgeLabel}</title>
</head>
<body style="margin:0; padding:0; background-color:#0d131a; font-family:'Montserrat', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; -webkit-font-smoothing:antialiased; color:#ffffff;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#0d131a; padding:40px 15px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width:620px; background-color:#111a24; border:1px solid #1f2a37; border-radius:24px; overflow:hidden; box-shadow:0 25px 50px -12px rgba(0,0,0,0.5);">

                    <tr>
                        <td style="background:linear-gradient(90deg, #f41a4a 0%, #ff4b72 100%); height:6px; line-height:6px; font-size:6px;">&nbsp;</td>
                    </tr>

                    <tr>
                        <td style="padding:32px 36px 20px 36px;">
                            <div style="display:inline-block; padding:5px 12px; background-color:{$badgeBg}; border:1px solid rgba(16,185,129,0.4); border-radius:20px; font-size:11px; font-weight:700; color:{$badgeColor}; text-transform:uppercase; letter-spacing:1px; margin-bottom:12px;">
                                &#10003; {$badgeLabel}
                            </div>
                            <h1 style="margin:0 0 6px 0; font-size:24px; font-weight:800; color:#ffffff; line-height:1.2;">
                                Thank you, {$safeName}.
                            </h1>
                            <p style="margin:0; font-size:14px; color:#94a3b8; font-weight:500;">
                                {$leadLine}
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:0 36px;">
                            <div style="border-top:1px solid #1e293b;"></div>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:28px 36px 15px 36px; font-size:14px; line-height:1.8; color:#cbd5e1;">
                            <p style="margin:0 0 16px 0;">
                                Hello <strong style="color:#ffffff;">{$safeName}</strong>,
                            </p>
                            <p style="margin:0 0 16px 0;">
                                Thank you for reaching out about <strong style="color:#ff4b72;">{$safeService}</strong>. I review every incoming brief personally — there is no ticket queue and no auto-responder triage standing between your requirements and a real engineering answer.
                            </p>
                            <p style="margin:0 0 16px 0;">
                                You can expect a considered reply from me within <strong style="color:#ffffff;">24 hours</strong> covering scope, timeline and recommended architecture.
                            </p>
                        </td>
                    </tr>
{$reservationBlock}
                    <tr>
                        <td style="padding:5px 36px 25px 36px;">
                            <div style="background-color:#162231; border-left:4px solid #f41a4a; border-radius:0 16px 16px 0; padding:18px 20px;">
                                <div style="font-size:11px; font-weight:700; color:#f41a4a; text-transform:uppercase; letter-spacing:1px; margin-bottom:8px;">
                                    What You Sent
                                </div>
                                <div style="font-size:13px; color:#94a3b8; margin-bottom:6px;">
                                    <strong>Scope:</strong> <span style="color:#ffffff;">{$safeService}</span>
                                </div>
                                <div style="font-size:13px; color:#94a3b8; line-height:1.6;">
                                    <strong>Brief:</strong><br>
                                    <span style="color:#e2e8f0;">{$safeMessage}</span>
                                </div>
                            </div>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:10px 36px 30px 36px;">
                            <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#131c27; border:1px solid #222f40; border-radius:16px; padding:18px;">
                                <tr>
                                    <td>
                                        <div style="font-size:13px; font-weight:700; color:#ffffff; margin-bottom:4px;">
                                            Need to escalate or reply with files?
                                        </div>
                                        <div style="font-size:12px; color:#94a3b8; line-height:1.5;">
                                            This mail is one-way. For attachments, NDAs or urgent matters, write directly:
                                        </div>
                                        <div style="margin-top:10px; font-size:13px;">
                                            <a href="mailto:{$corpEmail}" style="color:#ff4b72; text-decoration:none; margin-right:15px; font-weight:600;">
                                                &#9993; {$corpEmail}
                                            </a>
                                            <a href="https://wa.me/{$phoneRaw}" style="color:#34d399; text-decoration:none; font-weight:600;">
                                                &#9742; WhatsApp ({$phoneFmt})
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:0 36px 30px 36px;" align="center">
                            <a href="{$portfolioUrl}" style="display:inline-block; padding:14px 28px; background-color:#f41a4a; color:#ffffff; font-size:13px; font-weight:700; text-decoration:none; border-radius:14px; letter-spacing:0.5px;">
                                Explore Case Studies &amp; Architecture &rarr;
                            </a>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:22px 36px; background-color:#090e14; text-align:center;">
                            <p style="margin:0 0 6px 0; font-size:12px; color:#64748b; font-weight:600;">
                                {$profileName} · Morex Web Enterprise Group of Companies Ltd.
                            </p>
                            <p style="margin:0 0 8px 0; font-size:11px; color:#475569;">
                                Registered BN: {$regNo} · Owerri &amp; Lagos, Nigeria · Worldwide / Remote
                            </p>
                            <p style="margin:0; font-size:11px; color:#334155;">
                                You received this because you submitted a form on
                                <a href="{$portfolioUrl}" style="color:#64748b; text-decoration:underline;">jmn.morexweb.com</a>.
                                This mailbox is not used for marketing.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
HTML;
}

/**
 * Branded reply email sent from the admin back office to an inquiry's sender.
 *
 * @param array<string,mixed> $q {to_name, subject, body, inquiry_id}
 * @return string Full HTML document
 */
function jmn_build_reply_email(array $q): string
{
    $h = static fn (string $v): string => htmlspecialchars($v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

    $toName    = $h((string)($q['to_name'] ?? 'there'));
    $subject   = $h((string)($q['subject'] ?? 'your inquiry'));
    $inquiryId = (int)($q['inquiry_id'] ?? 0);

    // The reply body is plain text from the admin; render it safely.
    $bodyRaw = trim((string)($q['body'] ?? ''));
    $bodyHtml = nl2br($h($bodyRaw));

    $profileName   = $h(PROFILE_NAME);
    $profileTitle  = $h(PROFILE_TITLE);
    $profileRole   = $h(PROFILE_ROLE_BADGE);
    $company       = $h(PROFILE_COMPANY);
    $regNo         = $h(PROFILE_REG_NO);
    $portfolioUrl  = $h(PROFILE_PORTFOLIO_URL);
    $corpEmail     = $h(PROFILE_CORP_EMAIL);
    $phone         = $h(PROFILE_PHONE);
    $whatsappRaw   = $h(PROFILE_PHONE_RAW);

    $contactUrl  = $h(url('contact.php'));
    $calendarUrl = $h(url('contact.php'));

    return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reply to your inquiry</title>
</head>
<body style="margin:0; padding:0; background-color:#f4f5f7; font-family:Montserrat, 'Segoe UI', Arial, sans-serif;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f4f5f7; padding:32px 16px;">
        <tr>
            <td align="center">
                <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="max-width:600px; width:100%;">

                    <!-- Header -->
                    <tr>
                        <td style="padding:28px 36px 20px; background:linear-gradient(135deg,#111a24 0%,#1a2532 100%); border-radius:24px 24px 0 0;">
                            <p style="margin:0 0 4px; font-size:12px; font-weight:700; letter-spacing:.12em; text-transform:uppercase; color:#f41a4a;">
                                Reply &middot; Inquiry #{$inquiryId}
                            </p>
                            <h1 style="margin:8px 0 0; font-size:22px; font-weight:800; color:#ffffff; line-height:1.3;">
                                Re: {$subject}
                            </h1>
                        </td>
                    </tr>

                    <!-- Body -->
                    <tr>
                        <td style="padding:32px 36px; background-color:#ffffff;">
                            <p style="margin:0 0 18px; font-size:15px; color:#111a24; font-weight:600;">
                                Hello {$toName},
                            </p>
                            <div style="font-size:14.5px; color:#495461; line-height:1.75;">
                                {$bodyHtml}
                            </div>

                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin-top:28px; background-color:#f7f5fb; border-radius:16px;">
                                <tr>
                                    <td style="padding:20px 24px;">
                                        <p style="margin:0 0 12px; font-size:13px; font-weight:700; color:#111a24;">
                                            Need to continue the conversation?
                                        </p>
                                        <p style="margin:0 0 14px; font-size:13px; color:#495461;">
                                            Reply directly to this email, book a consultation slot, or reach out on WhatsApp.
                                        </p>
                                        <table role="presentation" cellpadding="0" cellspacing="0">
                                            <tr>
                                                <td style="padding-right:10px;">
                                                    <a href="{$calendarUrl}" style="display:inline-block; padding:10px 20px; background:linear-gradient(135deg,#f41a4a,#ff4b72); color:#ffffff; text-decoration:none; border-radius:10px; font-size:13px; font-weight:700;">
                                                        Book a Consultation
                                                    </a>
                                                </td>
                                                <td>
                                                    <a href="https://wa.me/{$whatsappRaw}" style="display:inline-block; padding:10px 20px; background-color:#111a24; color:#ffffff; text-decoration:none; border-radius:10px; font-size:13px; font-weight:700;">
                                                        WhatsApp
                                                    </a>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="padding:22px 36px; background-color:#090e14; text-align:center; border-radius:0 0 24px 24px;">
                            <p style="margin:0 0 6px; font-size:12px; color:#64748b; font-weight:600;">
                                {$profileName} &middot; {$profileRole}
                            </p>
                            <p style="margin:0 0 8px; font-size:11px; color:#475569;">
                                {$company} &middot; BN {$regNo} &middot; {$phone}
                            </p>
                            <p style="margin:0 0 8px; font-size:11px; color:#334155;">
                                {$profileTitle}
                            </p>
                            <p style="margin:0; font-size:11px; color:#334155;">
                                You received this in reply to your inquiry on
                                <a href="{$portfolioUrl}" style="color:#64748b; text-decoration:underline;">jmn.morexweb.com</a>.
                            </p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>
HTML;
}
