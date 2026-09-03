<?php
/**
 * Shri Krishna Dental Hospital - Unified Production Form Mailer Backend
 * Target Recipient: shrikrishnadental2@gmail.com
 * Handles: appointment.html, contact.html, and dental-clinic-services.html
 */

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');

// Set Indian Standard Time
date_default_timezone_set('Asia/Kolkata');
$timestamp = date('d-M-Y, h:i A') . ' IST';
$ip_address = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';

// Target Recipient Email & Hospital Info
$to_email      = 'shrikrishnadental2@gmail.com';
$hospital_name = 'Shri Krishna Dental Hospital';
$from_email    = 'noreply@shrikrishnadental.com';

/* --------------------------------------------------------------------------
   1. BUILT-IN DIAGNOSTIC TEST MODE (GET /send-mail.php?test=1)
   -------------------------------------------------------------------------- */
if (isset($_GET['test'])) {
    header('Content-Type: text/html; charset=UTF-8');
    
    $test_subject = "[DIAGNOSTIC TEST] Shri Krishna Dental Hospital Mail Check (" . date('h:i:s A') . ")";
    $test_body = <<<HTML
<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"></head>
<body style="font-family: Arial, sans-serif; background: #f8fafc; padding: 20px;">
  <div style="max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 10px; padding: 25px; border: 1px solid #e2e8f0;">
    <h2 style="color: #031958; margin-top: 0;">🏥 Shri Krishna Dental Hospital - System Test</h2>
    <p>This is a test notification verifying delivery to <strong>$to_email</strong>.</p>
    <p><strong>Sent At:</strong> $timestamp<br><strong>Server:</strong> {$_SERVER['SERVER_NAME']}</p>
    <hr style="border: 0; border-top: 1px solid #e2e8f0; margin: 20px 0;">
    <p style="color: #16a34a; font-weight: bold;">✅ If you are reading this email in your Inbox, your mail system is working perfectly!</p>
  </div>
</body>
</html>
HTML;

    $headers = [];
    $headers[] = 'MIME-Version: 1.0';
    $headers[] = 'Content-Type: text/html; charset=UTF-8';
    $headers[] = "From: $hospital_name <$from_email>";
    $headers[] = "Reply-To: $to_email";
    $headers[] = 'X-Mailer: PHP/' . phpversion();
    $headers[] = 'Date: ' . date('r');
    $headers[] = 'Message-ID: <' . time() . '-' . md5($to_email) . '@' . ($_SERVER['SERVER_NAME'] ?? 'shrikrishnadental.com') . '>';

    $sent = @mail($to_email, $test_subject, $test_body, implode("\r\n", $headers), "-f$from_email");

    echo "<!DOCTYPE html><html><head><title>Mail System Diagnostic Test</title>";
    echo "<style>body{font-family:system-ui,-apple-system,sans-serif;max-width:700px;margin:30px auto;padding:20px;background:#f8fafc;color:#1e293b;} .card{background:#fff;padding:25px;border-radius:12px;box-shadow:0 4px 12px rgba(0,0,0,0.08);border:1px solid #e2e8f0;} .success{color:#16a34a;font-weight:700;} .btn{display:inline-block;background:#031958;color:#fff;text-decoration:none;padding:10px 18px;border-radius:6px;font-weight:600;margin-top:10px;}</style></head><body>";
    echo "<div class='card'>";
    echo "<h2 style='color:#031958;margin-top:0;'>🏥 Shri Krishna Dental Hospital - Mail Diagnostic Test</h2>";
    echo "<p>Recipient: <strong>$to_email</strong></p>";
    if ($sent) {
        echo "<p class='success'>✅ SUCCESS: Diagnostic email sent directly to $to_email!</p>";
        echo "<p>Please check your Gmail inbox (and Spam folder) to confirm receipt.</p>";
    } else {
        echo "<p style='color:#dc2626;font-weight:700;'>❌ Error sending test mail.</p>";
    }
    echo "<a href='appointment.html' class='btn'>📅 Test Appointment Form Now</a>";
    echo "</div></body></html>";
    exit;
}

/* --------------------------------------------------------------------------
   2. VERIFY POST REQUEST
   -------------------------------------------------------------------------- */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode(['status' => 'error', 'message' => 'Method Not Allowed. Use POST to submit form data.']);
    exit;
}

// Anti-Spam Honeypot Check
if (!empty($_POST['_gotcha']) || !empty($_POST['website'])) {
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode(['status' => 'success', 'message' => 'Thank you for reaching out.']);
    exit;
}

// Function to safely sanitize inputs
function clean_input($data) {
    if (is_array($data)) {
        return array_map('clean_input', $data);
    }
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

// Extract & Sanitize Form Data
$form_type   = clean_input($_POST['form_type'] ?? 'Online Appointment Request');
$full_name   = clean_input($_POST['fullName'] ?? $_POST['name'] ?? $_POST['contactName'] ?? $_POST['lpName'] ?? 'Valued Patient');
$phone       = clean_input($_POST['phone'] ?? $_POST['contactPhone'] ?? $_POST['lpPhone'] ?? 'Not Provided');
$email_raw   = clean_input($_POST['email'] ?? $_POST['contactEmail'] ?? '');
$email       = (!empty($email_raw) && filter_var($email_raw, FILTER_VALIDATE_EMAIL)) ? $email_raw : 'Not Provided';
$service     = clean_input($_POST['serviceSelect'] ?? $_POST['service'] ?? $_POST['contactService'] ?? $_POST['lpService'] ?? 'General Consultation');
$pref_date   = clean_input($_POST['prefDate'] ?? $_POST['date'] ?? $_POST['contactDate'] ?? $_POST['lpDate'] ?? 'Flexible / As soon as possible');
$pref_time   = clean_input($_POST['prefTime'] ?? $_POST['time'] ?? $_POST['contactTime'] ?? $_POST['lpTime'] ?? 'Flexible');
$message_raw = clean_input($_POST['message'] ?? $_POST['contactMsg'] ?? '');
$message     = !empty($message_raw) ? $message_raw : 'None';
$is_ehs      = !empty($_POST['ehs']) || !empty($_POST['lpEhsCheck']) ? 'Yes (EHS / Aarogyasree Cardholder)' : 'No';

// Basic Validation
if (empty($full_name) || empty($phone) || $phone === 'Not Provided') {
    http_response_code(400);
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode(['status' => 'error', 'message' => 'Please provide your name and phone number.']);
    exit;
}

/* --------------------------------------------------------------------------
   3. ZERO-LOSS LOCAL LEAD DATABASE BACKUP (leads_backup.csv)
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
   4. CONSTRUCT EMAIL CONTENT
   -------------------------------------------------------------------------- */
$subject = "[$hospital_name] New $form_type: $full_name ($phone)";

$email_btn = ($email !== 'Not Provided') 
    ? "<a href=\"mailto:$email\" style=\"display: inline-block; background-color: #031958; color: #ffffff !important; text-decoration: none; padding: 10px 20px; border-radius: 6px; font-weight: 600; font-size: 14px; margin: 4px;\">✉️ Reply via Email</a>" 
    : "";

$html_body = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <style>
    body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; background-color: #f4f6fa; margin: 0; padding: 20px; color: #1e293b; }
    .container { max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.08); border: 1px solid #e2e8f0; }
    .header { background: linear-gradient(135deg, #031958 0%, #0a2575 100%); color: #ffffff; padding: 25px 30px; text-align: center; border-bottom: 3px solid #c7a97b; }
    .header h1 { margin: 0; font-size: 20px; font-weight: 700; letter-spacing: 0.5px; color: #ffffff; }
    .header p { margin: 5px 0 0; font-size: 13px; color: #e2ebf5; }
    .badge { display: inline-block; background: #c7a97b; color: #031958; font-weight: 700; font-size: 12px; padding: 4px 12px; border-radius: 20px; margin-top: 10px; text-transform: uppercase; }
    .content { padding: 25px 30px; }
    .table-details { width: 100%; border-collapse: collapse; margin-top: 15px; }
    .table-details td { padding: 10px 12px; border-bottom: 1px solid #f1f5f9; font-size: 14px; vertical-align: top; }
    .table-details td.label { width: 35%; font-weight: 600; color: #475569; background-color: #f8fafc; }
    .table-details td.value { color: #0f172a; font-weight: 500; }
    .action-box { margin-top: 25px; padding: 15px; background-color: #f0f4f9; border-radius: 8px; text-align: center; }
    .action-btn-gold { display: inline-block; background-color: #c7a97b; color: #031958 !important; text-decoration: none; padding: 10px 20px; border-radius: 6px; font-weight: 700; font-size: 14px; margin: 4px; }
    .footer { background-color: #f8fafc; padding: 15px 30px; text-align: center; font-size: 12px; color: #94a3b8; border-top: 1px solid #e2e8f0; }
  </style>
</head>
<body>
  <div class="container">
    <div class="header">
      <h1>$hospital_name</h1>
      <p>Ground Floor, Allwyn X Roads, Hafeezpet / Miyapur, Hyderabad</p>
      <span class="badge">$form_type</span>
    </div>
    
    <div class="content">
      <h2 style="font-size: 16px; color: #031958; margin-top: 0;">New Patient Submission Received:</h2>
      
      <table class="table-details">
        <tr>
          <td class="label">Patient Name:</td>
          <td class="value"><strong>$full_name</strong></td>
        </tr>
        <tr>
          <td class="label">Phone Number:</td>
          <td class="value"><a href="tel:$phone" style="color: #031958; font-weight: 700; text-decoration: none;">$phone</a></td>
        </tr>
        <tr>
          <td class="label">Email Address:</td>
          <td class="value">{$email}</td>
        </tr>
        <tr>
          <td class="label">Service / Treatment:</td>
          <td class="value"><strong style="color: #b89255;">$service</strong></td>
        </tr>
        <tr>
          <td class="label">Preferred Date:</td>
          <td class="value">$pref_date</td>
        </tr>
        <tr>
          <td class="label">Preferred Time:</td>
          <td class="value">$pref_time</td>
        </tr>
        <tr>
          <td class="label">EHS / Aarogyasree:</td>
          <td class="value">$is_ehs</td>
        </tr>
        <tr>
          <td class="label">Symptoms / Notes:</td>
          <td class="value">{$message}</td>
        </tr>
        <tr>
          <td class="label">Submitted At:</td>
          <td class="value" style="font-size: 12px; color: #64748b;">$timestamp</td>
        </tr>
      </table>

      <div class="action-box">
        <a href="tel:$phone" class="action-btn-gold">📞 Call Patient Now ($phone)</a>
        $email_btn
      </div>
    </div>

    <div class="footer">
      This notification was generated automatically from the official website inquiry form on <a href="https://shrikrishnadental.com/" style="color: #64748b;">shrikrishnadental.com</a>.
    </div>
  </div>
</body>
</html>
HTML;

/* --------------------------------------------------------------------------
   5. SEND EMAIL DIRECTLY VIA WORKING PHP MAIL()
   -------------------------------------------------------------------------- */
$headers = [];
$headers[] = 'MIME-Version: 1.0';
$headers[] = 'Content-Type: text/html; charset=UTF-8';
$headers[] = "From: $hospital_name <$from_email>";

if ($email !== 'Not Provided') {
    $headers[] = "Reply-To: $full_name <$email>";
} else {
    $headers[] = "Reply-To: $to_email";
}

$headers[] = 'X-Mailer: PHP/' . phpversion();
$headers[] = 'Date: ' . date('r');
$headers[] = 'Message-ID: <' . time() . '-' . md5($phone . $timestamp) . '@' . ($_SERVER['SERVER_NAME'] ?? 'shrikrishnadental.com') . '>';

$headers_str = implode("\r\n", $headers);

// Primary Attempt: Use mail() with -f envelope flag (Proven working on GoDaddy/SecureServer!)
$mail_sent = @mail($to_email, $subject, $html_body, $headers_str, "-f$from_email");

// Secondary Fallback if 5th parameter is restricted
if (!$mail_sent) {
    $mail_sent = @mail($to_email, $subject, $html_body, $headers_str);
}

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
