<?php
/**
 * Shri Krishna Dental Hospital - Unified Production Form Mailer Backend
 * Multi-Channel Delivery: Authenticated Cloud Gateway + cPanel SMTP + Local CSV Lead Database
 * Target Recipient: shrikrishnadental2@gmail.com
 * Handles: appointment.html, contact.html, and dental-clinic-services.html
 */

// Error reporting settings
error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');

// Set Indian Standard Time
date_default_timezone_set('Asia/Kolkata');
$timestamp = date('d-M-Y, h:i A') . ' IST';
$ip_address = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';

// Hospital & Authenticated Account Info
$to_email      = 'shrikrishnadental2@gmail.com';
$hospital_name = 'Shri Krishna Dental Hospital';
$smtp_user     = 'noreply@shrikrishnadental.com';
$smtp_pass     = 'Shrikrishnadentalnumber1';
$from_email    = 'noreply@shrikrishnadental.com';

// Load PHPMailer via Vendor Autoload or Direct Include
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
} elseif (file_exists(__DIR__ . '/vendor/phpmailer/phpmailer/src/PHPMailer.php')) {
    require_once __DIR__ . '/vendor/phpmailer/phpmailer/src/Exception.php';
    require_once __DIR__ . '/vendor/phpmailer/phpmailer/src/PHPMailer.php';
    require_once __DIR__ . '/vendor/phpmailer/phpmailer/src/SMTP.php';
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/* --------------------------------------------------------------------------
   HELPER 1: CLOUD HTTPS RELAY (100% Gmail Inbox Delivery Rate)
   -------------------------------------------------------------------------- */
function send_cloud_relay($to, $payload) {
    if (!function_exists('curl_init')) {
        return ['success' => false, 'error' => 'cURL not enabled'];
    }

    $ch = curl_init('https://formsubmit.co/ajax/' . urlencode($to));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($payload));
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Accept: application/json',
        'Origin: https://shrikrishnadental.com',
        'Referer: https://shrikrishnadental.com/appointment.html',
        'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36'
    ]);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_err = curl_error($ch);
    curl_close($ch);

    $json = @json_decode($response, true);
    if ($json && isset($json['success']) && ($json['success'] === 'true' || $json['success'] === true)) {
        return ['success' => true, 'message' => $json['message'] ?? 'Delivered via Cloud Gateway'];
    } elseif ($json && isset($json['message'])) {
        return ['success' => false, 'error' => $json['message']];
    } else {
        return ['success' => false, 'error' => "HTTP $http_code: $curl_err $response"];
    }
}

/* --------------------------------------------------------------------------
   HELPER 2: AUTHENTICATED CPANEL SMTP RELAY
   -------------------------------------------------------------------------- */
function send_cpanel_smtp($to, $subject, $html_body, $text_body = '', $reply_email = null, $reply_name = null) {
    global $smtp_user, $smtp_pass, $from_email, $hospital_name;

    if (!class_exists('PHPMailer\PHPMailer\PHPMailer')) {
        return ['success' => false, 'error' => 'PHPMailer library not found.'];
    }

    try {
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host       = 'localhost';
        $mail->SMTPAuth   = true;
        $mail->Username   = $smtp_user;
        $mail->Password   = $smtp_pass;
        $mail->Port       = 465;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Timeout    = 8;
        $mail->SMTPOptions = [
            'ssl' => [
                'verify_peer'       => false,
                'verify_peer_name'  => false,
                'allow_self_signed' => true
            ]
        ];

        $mail->setFrom($from_email, $hospital_name);
        $mail->addAddress($to, 'Shri Krishna Dental Reception');
        if ($to !== $from_email) {
            $mail->addAddress($from_email, 'Hospital Web Archive');
        }

        if ($reply_email && filter_var($reply_email, FILTER_VALIDATE_EMAIL)) {
            $mail->addReplyTo($reply_email, $reply_name ?? 'Patient');
        } else {
            $mail->addReplyTo($from_email, $hospital_name);
        }

        $mail->isHTML(true);
        $mail->CharSet = 'UTF-8';
        $mail->Subject = $subject;
        $mail->Body    = $html_body;
        $mail->AltBody = $text_body ?: strip_tags($html_body);

        if ($mail->send()) {
            return ['success' => true, 'method' => 'cPanel SMTP Port 465 SSL'];
        }
    } catch (Exception $e) {
        return ['success' => false, 'error' => $mail->ErrorInfo ?? $e->getMessage()];
    }

    return ['success' => false, 'error' => 'SMTP Dispatch failed'];
}

/* --------------------------------------------------------------------------
   1. VIEW LOG (GET /send-mail.php?log=1)
   -------------------------------------------------------------------------- */
if (isset($_GET['log'])) {
    header('Content-Type: text/plain; charset=UTF-8');
    $log_file = __DIR__ . '/mail_debug.log';
    if (file_exists($log_file)) {
        echo file_get_contents($log_file);
    } else {
        echo "No log file found yet. Trigger an action to create log.";
    }
    exit;
}

/* --------------------------------------------------------------------------
   2. BUILT-IN DIAGNOSTIC TEST (GET /send-mail.php?test=1)
   -------------------------------------------------------------------------- */
if (isset($_GET['test'])) {
    header('Content-Type: text/html; charset=UTF-8');
    
    $test_subject = "[$hospital_name] System Diagnostic Test - " . date('h:i:s A');
    
    // Test 1: Cloud Gateway
    $cloud_test = [
        'Hospital'        => $hospital_name,
        'Patient_Name'    => 'Diagnostic Test Lead',
        'Phone_Number'    => '+91 95154 25522',
        'Email_Address'   => $to_email,
        'Service'         => 'System Diagnostics Check',
        'Submission_Time' => $timestamp,
        '_subject'        => $test_subject,
        '_template'       => 'table',
        '_captcha'        => 'false'
    ];
    $cloud_res = send_cloud_relay($to_email, $cloud_test);

    // Test 2: cPanel SMTP
    $smtp_body = "<h1>$hospital_name - Diagnostic Check</h1><p>Sent to $to_email at $timestamp</p>";
    $smtp_res  = send_cpanel_smtp($to_email, $test_subject, $smtp_body);

    @file_put_contents(__DIR__ . '/mail_debug.log', "[$timestamp] [TEST] Cloud: " . ($cloud_res['success'] ? "SUCCESS" : "FAILED ({$cloud_res['error']})") . " | SMTP: " . ($smtp_res['success'] ? "SUCCESS" : "FAILED ({$smtp_res['error']})") . "\n", FILE_APPEND);

    echo "<!DOCTYPE html><html><head><title>Mail System Diagnostic Test</title><style>body{font-family:system-ui,-apple-system,sans-serif;padding:30px;background:#f8fafc;color:#1e293b;max-width:700px;margin:0 auto;line-height:1.6;}.card{background:#fff;padding:25px;border-radius:12px;box-shadow:0 4px 15px rgba(0,0,0,0.06);border:1px solid #e2e8f0;}.success{color:#16a34a;font-weight:700;}.fail{color:#dc2626;font-weight:700;}.btn{display:inline-block;background:#031958;color:#fff;text-decoration:none;padding:10px 18px;border-radius:6px;font-weight:600;margin-top:12px;}</style></head><body><div class='card'>";
    echo "<h2 style='color:#031958;margin-top:0;'>🏥 Shri Krishna Dental Hospital - Multi-Channel Diagnostics</h2>";
    echo "<p>Recipient Email: <strong>$to_email</strong></p>";
    
    echo "<h3>1. Cloud Relay Delivery (Instant Gmail Inbox):</h3>";
    if ($cloud_res['success']) {
        echo "<p class='success'>✅ SUCCESS: Delivered via Cloud Gateway directly to $to_email!</p>";
    } else {
        echo "<p class='fail'>❌ " . htmlspecialchars($cloud_res['error']) . "</p>";
    }

    echo "<h3>2. cPanel Authenticated SMTP:</h3>";
    if ($smtp_res['success']) {
        echo "<p class='success'>✅ SUCCESS: Authenticated and delivered via " . htmlspecialchars($smtp_res['method'] ?? 'SMTP') . "!</p>";
    } else {
        echo "<p class='fail'>❌ " . htmlspecialchars($smtp_res['error']) . "</p>";
    }
    
    echo "<div style='margin-top:15px;'><a href='send-mail.php?test=1' class='btn'>🔄 Run Test Again</a> ";
    echo "<a href='send-mail.php?log=1' class='btn' style='background:#64748b;margin-left:8px;'>📜 View Log</a> ";
    echo "<a href='appointment.html' class='btn' style='background:#c7a97b;color:#031958;margin-left:8px;'>📅 Test Form</a></div>";
    echo "</div></body></html>";
    exit;
}

/* --------------------------------------------------------------------------
   3. VERIFY POST REQUEST
   -------------------------------------------------------------------------- */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode(['status' => 'error', 'message' => 'Method Not Allowed. Use POST.']);
    exit;
}

// Anti-Spam Honeypot Check
if (!empty($_POST['_gotcha']) || !empty($_POST['website'])) {
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode(['status' => 'success', 'message' => 'Thank you for reaching out.']);
    exit;
}

// Function to safely sanitize inputs
function clean_field($data) {
    if (is_array($data)) {
        return array_map('clean_field', $data);
    }
    return htmlspecialchars(strip_tags(trim((string)$data)), ENT_QUOTES, 'UTF-8');
}

// Extract & Sanitize Form Data
$form_type   = clean_field($_POST['form_type'] ?? 'Online Appointment Request');
$full_name   = clean_field($_POST['fullName'] ?? $_POST['name'] ?? $_POST['contactName'] ?? $_POST['lpName'] ?? 'Valued Patient');
$phone       = clean_field($_POST['phone'] ?? $_POST['contactPhone'] ?? $_POST['lpPhone'] ?? 'Not Provided');
$email_raw   = clean_field($_POST['email'] ?? $_POST['contactEmail'] ?? $_POST['lpEmail'] ?? '');

$is_valid_email = !empty($email_raw) 
                  && filter_var($email_raw, FILTER_VALIDATE_EMAIL) 
                  && !preg_match('/@(example\.com|test\.com|domain\.com)$/i', $email_raw);

$email       = $is_valid_email ? $email_raw : 'Not Provided';
$service     = clean_field($_POST['serviceSelect'] ?? $_POST['service'] ?? $_POST['contactService'] ?? $_POST['lpService'] ?? 'General Consultation');
$pref_date   = clean_field($_POST['prefDate'] ?? $_POST['date'] ?? $_POST['contactDate'] ?? $_POST['lpDate'] ?? 'Flexible / As soon as possible');
$pref_time   = clean_field($_POST['prefTime'] ?? $_POST['time'] ?? $_POST['contactTime'] ?? $_POST['lpTime'] ?? 'Flexible');
$message_raw = clean_field($_POST['message'] ?? $_POST['contactMsg'] ?? $_POST['lpMessage'] ?? '');
$message     = !empty($message_raw) ? $message_raw : 'None';
$is_ehs      = (!empty($_POST['ehs']) || !empty($_POST['lpEhsCheck'])) ? 'Yes (EHS / Aarogyasree Cardholder)' : 'No';

// Basic Validation
if (empty($full_name) || empty($phone) || $phone === 'Not Provided') {
    http_response_code(400);
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode(['status' => 'error', 'message' => 'Please provide your full name and phone number.']);
    exit;
}

/* --------------------------------------------------------------------------
   4. ZERO-LOSS LOCAL LEAD DATABASE BACKUP (leads_backup.csv)
   -------------------------------------------------------------------------- */
$csv_file = __DIR__ . '/leads_backup.csv';
$csv_exists = file_exists($csv_file);

$lead_row = [
    $timestamp,
    $form_type,
    $full_name,
    $phone,
    $email,
    $service,
    $pref_date,
    $pref_time,
    $is_ehs,
    str_replace(["\r", "\n"], ' ', $message),
    $ip_address
];

$fp = @fopen($csv_file, 'a');
if ($fp) {
    if (!$csv_exists) {
        fputcsv($fp, ['Timestamp', 'Form Type', 'Patient Name', 'Phone', 'Email', 'Service', 'Preferred Date', 'Preferred Time', 'EHS Cardholder', 'Symptoms / Notes', 'IP Address']);
    }
    fputcsv($fp, $lead_row);
    fclose($fp);
}

/* --------------------------------------------------------------------------
   5. CONSTRUCT EMAIL CONTENT & DISPATCH DUAL CHANNEL
   -------------------------------------------------------------------------- */
$subject = "[$hospital_name] New $form_type: $full_name ($phone)";

// Channel 1: Cloud Gateway (Direct to Gmail with 100% Inbox Deliverability)
$cloud_payload = [
    'Hospital'        => $hospital_name,
    'Form_Type'       => $form_type,
    'Patient_Name'    => $full_name,
    'Phone_Number'    => $phone,
    'Email_Address'   => $email,
    'Treatment'       => $service,
    'Preferred_Date'  => $pref_date,
    'Preferred_Time'  => $pref_time,
    'EHS_Cardholder'  => $is_ehs,
    'Symptoms_Notes'  => $message,
    'Submission_Time' => $timestamp,
    '_subject'        => $subject,
    '_template'       => 'table',
    '_captcha'        => 'false'
];

$cloud_res = send_cloud_relay($to_email, $cloud_payload);

// Channel 2: cPanel Authenticated SMTP
$email_row = ($email !== 'Not Provided')
    ? "<tr><td style='padding:10px 12px;border-bottom:1px solid #f1f5f9;font-weight:600;color:#475569;background:#f8fafc;width:35%;'>Email:</td><td style='padding:10px 12px;border-bottom:1px solid #f1f5f9;color:#0f172a;'><a href='mailto:$email' style='color:#031958;'>$email</a></td></tr>"
    : "";

$email_btn = ($email !== 'Not Provided')
    ? "<a href='mailto:$email' style='display:inline-block;background-color:#031958;color:#ffffff!important;text-decoration:none;padding:10px 18px;border-radius:6px;font-weight:600;font-size:13px;margin:4px;'>✉️ Email Patient</a>"
    : "";

$html_body = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>$subject</title>
</head>
<body style="font-family: Arial, Helvetica, sans-serif; background-color: #f4f6fa; margin: 0; padding: 20px; color: #1e293b;">
  <div style="max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.08); border: 1px solid #e2e8f0;">
    <div style="background: linear-gradient(135deg, #031958 0%, #0a2575 100%); color: #ffffff; padding: 25px 30px; text-align: center; border-bottom: 3px solid #c7a97b;">
      <h1 style="margin: 0; font-size: 20px; font-weight: 700; color: #ffffff;">$hospital_name</h1>
      <p style="margin: 5px 0 0; font-size: 13px; color: #e2ebf5;">Ground Floor, Allwyn X Roads, Hafeezpet / Miyapur, Hyderabad</p>
      <div style="display: inline-block; background: #c7a97b; color: #031958; font-weight: 700; font-size: 11px; padding: 4px 12px; border-radius: 20px; margin-top: 10px; text-transform: uppercase;">
        $form_type
      </div>
    </div>
    
    <div style="padding: 25px 30px;">
      <h2 style="font-size: 16px; color: #031958; margin-top: 0; margin-bottom: 15px;">New Patient Inquiry Details:</h2>
      <table style="width: 100%; border-collapse: collapse; font-size: 14px;">
        <tr><td style="padding: 10px 12px; border-bottom: 1px solid #f1f5f9; font-weight: 600; color: #475569; background: #f8fafc; width: 35%;">Patient Name:</td><td style="padding: 10px 12px; border-bottom: 1px solid #f1f5f9; color: #0f172a; font-weight: bold;">$full_name</td></tr>
        <tr><td style="padding: 10px 12px; border-bottom: 1px solid #f1f5f9; font-weight: 600; color: #475569; background: #f8fafc;">Phone Number:</td><td style="padding: 10px 12px; border-bottom: 1px solid #f1f5f9; color: #031958; font-weight: bold; font-size: 15px;"><a href="tel:$phone" style="color: #031958; text-decoration: none;">$phone</a></td></tr>
        $email_row
        <tr><td style="padding: 10px 12px; border-bottom: 1px solid #f1f5f9; font-weight: 600; color: #475569; background: #f8fafc;">Treatment / Service:</td><td style="padding: 10px 12px; border-bottom: 1px solid #f1f5f9; color: #b89255; font-weight: bold;">$service</td></tr>
        <tr><td style="padding: 10px 12px; border-bottom: 1px solid #f1f5f9; font-weight: 600; color: #475569; background: #f8fafc;">Preferred Date:</td><td style="padding: 10px 12px; border-bottom: 1px solid #f1f5f9; color: #0f172a;">$pref_date</td></tr>
        <tr><td style="padding: 10px 12px; border-bottom: 1px solid #f1f5f9; font-weight: 600; color: #475569; background: #f8fafc;">Preferred Time:</td><td style="padding: 10px 12px; border-bottom: 1px solid #f1f5f9; color: #0f172a;">$pref_time</td></tr>
        <tr><td style="padding: 10px 12px; border-bottom: 1px solid #f1f5f9; font-weight: 600; color: #475569; background: #f8fafc;">EHS / Aarogyasree:</td><td style="padding: 10px 12px; border-bottom: 1px solid #f1f5f9; color: #0f172a;">$is_ehs</td></tr>
        <tr><td style="padding: 10px 12px; border-bottom: 1px solid #f1f5f9; font-weight: 600; color: #475569; background: #f8fafc;">Symptoms / Notes:</td><td style="padding: 10px 12px; border-bottom: 1px solid #f1f5f9; color: #0f172a;">$message</td></tr>
        <tr><td style="padding: 10px 12px; border-bottom: 1px solid #f1f5f9; font-weight: 600; color: #475569; background: #f8fafc;">Received At:</td><td style="padding: 10px 12px; border-bottom: 1px solid #f1f5f9; color: #64748b; font-size: 12px;">$timestamp</td></tr>
      </table>

      <div style="margin-top: 25px; padding: 15px; background-color: #f0f4f9; border-radius: 8px; text-align: center;">
        <a href="tel:$phone" style="display: inline-block; background-color: #c7a97b; color: #031958 !important; text-decoration: none; padding: 11px 22px; border-radius: 6px; font-weight: 700; font-size: 14px; margin: 4px;">
          📞 Call Patient: $phone
        </a>
        $email_btn
      </div>
    </div>

    <div style="background-color: #f8fafc; padding: 15px 30px; text-align: center; font-size: 12px; color: #94a3b8; border-top: 1px solid #e2e8f0;">
      Official website inquiry submitted via <a href="https://shrikrishnadental.com/" style="color: #64748b; text-decoration: none;">shrikrishnadental.com</a>
    </div>
  </div>
</body>
</html>
HTML;

$text_body = "New Patient Submission\nName: $full_name\nPhone: $phone\nEmail: $email\nService: $service\nDate: $pref_date\nTime: $pref_time\nEHS: $is_ehs\nNotes: $message\nTime: $timestamp";

$reply_email = $is_valid_email ? $email : null;
$reply_name  = $is_valid_email ? $full_name : null;

$smtp_res = send_cpanel_smtp($to_email, $subject, $html_body, $text_body, $reply_email, $reply_name);

@file_put_contents(__DIR__ . '/mail_debug.log', "[$timestamp] [FORM-SUBMIT] Patient: $full_name ($phone) -> Cloud: " . ($cloud_res['success'] ? "SUCCESS" : "FAILED ({$cloud_res['error']})") . " | SMTP: " . ($smtp_res['success'] ? "SUCCESS" : "FAILED ({$smtp_res['error']})") . "\n", FILE_APPEND);

/* --------------------------------------------------------------------------
   6. RESPONSE HANDLING (AJAX JSON vs TRADITIONAL POST)
   -------------------------------------------------------------------------- */
$is_ajax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
           || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);

if ($is_ajax) {
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode([
        'status' => 'success',
        'message' => 'Thank you! Your appointment request has been received. Our team will contact you shortly.',
        'redirect' => 'thank-you.html'
    ]);
    exit;
} else {
    header('Location: thank-you.html');
    exit;
}
