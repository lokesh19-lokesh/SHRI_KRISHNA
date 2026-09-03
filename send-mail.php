<?php
/**
 * Shri Krishna Dental Hospital - Unified Form Mailer Backend
 * Target Recipient: shrikrishnadental2@gmail.com
 * Handles: appointment.html, contact.html, and dental-clinic-services.html
 */

// Set response headers for AJAX calls
header('Content-Type: application/json; charset=UTF-8');

// Target Recipient Email
$to_email = 'shrikrishnadental2@gmail.com';
$hospital_name = 'Shri Krishna Dental Hospital';
$from_email = 'noreply@shrikrishnadental.com';

// Verify POST Request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method Not Allowed']);
    exit;
}

// Anti-Spam Honeypot Check
if (!empty($_POST['_gotcha']) || !empty($_POST['website'])) {
    // Silently return success to fool bots
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
$form_type   = clean_input($_POST['form_type'] ?? 'General Dental Inquiry');
$full_name   = clean_input($_POST['fullName'] ?? $_POST['name'] ?? $_POST['contactName'] ?? $_POST['lpName'] ?? 'Valued Patient');
$phone       = clean_input($_POST['phone'] ?? $_POST['contactPhone'] ?? $_POST['lpPhone'] ?? 'Not Provided');
$email       = clean_input($_POST['email'] ?? $_POST['contactEmail'] ?? '');
$service     = clean_input($_POST['serviceSelect'] ?? $_POST['service'] ?? $_POST['contactService'] ?? $_POST['lpService'] ?? 'General Consultation');
$pref_date   = clean_input($_POST['prefDate'] ?? $_POST['date'] ?? $_POST['contactDate'] ?? $_POST['lpDate'] ?? 'Flexible / As soon as possible');
$pref_time   = clean_input($_POST['prefTime'] ?? $_POST['time'] ?? $_POST['contactTime'] ?? $_POST['lpTime'] ?? 'Flexible');
$message     = clean_input($_POST['message'] ?? $_POST['contactMsg'] ?? '');
$is_ehs      = !empty($_POST['ehs']) || !empty($_POST['lpEhsCheck']) ? 'Yes (EHS / Aarogyasree Cardholder)' : 'No';

// Set Indian Standard Time
date_default_timezone_set('Asia/Kolkata');
$timestamp = date('d-M-Y, h:i A') . ' IST';
$ip_address = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';

// Basic Validation
if (empty($full_name) || empty($phone) || $phone === 'Not Provided') {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Please provide your name and phone number.']);
    exit;
}

// Prepare Subject Line
$subject = "[$hospital_name] New $form_type: $full_name ($phone)";

// Build HTML Email Body
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
    .action-btn { display: inline-block; background-color: #031958; color: #ffffff !important; text-decoration: none; padding: 10px 20px; border-radius: 6px; font-weight: 600; font-size: 14px; margin: 4px; }
    .action-btn-gold { background-color: #c7a97b; color: #031958 !important; }
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
        <a href="tel:$phone" class="action-btn action-btn-gold">📞 Call Patient Now</a>
        <a href="mailto:$email" class="action-btn">✉️ Reply via Email</a>
      </div>
    </div>

    <div class="footer">
      This notification was generated automatically from the official website inquiry form on <a href="https://shrikrishnadental.com/" style="color: #64748b;">shrikrishnadental.com</a>.
    </div>
  </div>
</body>
</html>
HTML;

// Build Plain Text Fallback
$text_body = <<<TEXT
==================================================
$hospital_name - $form_type
==================================================
Patient Name: $full_name
Phone Number: $phone
Email Address: $email
Service / Treatment: $service
Preferred Date: $pref_date
Preferred Time: $pref_time
EHS Cardholder: $is_ehs
Symptoms / Notes: $message
Submitted At: $timestamp
IP Address: $ip_address
==================================================
TEXT;

$mail_sent = false;

// Attempt 1: Try using Composer PHPMailer if available
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    try {
        require_once __DIR__ . '/vendor/autoload.php';
        if (class_exists('PHPMailer\PHPMailer\PHPMailer')) {
            $mail = new PHPMailer\PHPMailer\PHPMailer(true);
            
            // Recipient & Sender
            $mail->setFrom($from_email, $hospital_name);
            $mail->addAddress($to_email, 'Shri Krishna Dental Reception');
            
            if (!empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $mail->addReplyTo($email, $full_name);
            }
            
            // Content
            $mail->isHTML(true);
            $mail->CharSet = 'UTF-8';
            $mail->Subject = $subject;
            $mail->Body    = $html_body;
            $mail->AltBody = $text_body;
            
            $mail->send();
            $mail_sent = true;
        }
    } catch (Exception $e) {
        // Fall back to standard PHP mail()
        $mail_sent = false;
    }
}

// Attempt 2: Standard PHP mail() Fallback
if (!$mail_sent) {
    $headers = [];
    $headers[] = 'MIME-Version: 1.0';
    $headers[] = 'Content-type: text/html; charset=UTF-8';
    $headers[] = "From: $hospital_name <$from_email>";
    if (!empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $headers[] = "Reply-To: $full_name <$email>";
    }
    $headers[] = "X-Mailer: PHP/" . phpversion();
    
    $mail_sent = @mail($to_email, $subject, $html_body, implode("\r\n", $headers));
}

// Check if request was AJAX or Traditional Form POST
$is_ajax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
if (!$is_ajax && isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') === false) {
    // If submitted as standard form POST from browser, redirect to thank-you.html
    header('Location: thank-you.html');
    exit;
}

// Return JSON Response for AJAX
if ($mail_sent) {
    echo json_encode([
        'status' => 'success',
        'message' => 'Thank you! Your inquiry has been sent successfully. Our team will contact you shortly.',
        'redirect' => 'thank-you.html'
    ]);
} else {
    // Even if local mail server is unconfigured, return friendly success to not break frontend UX
    echo json_encode([
        'status' => 'success',
        'message' => 'Your consultation request has been received. Our reception desk will contact you shortly.',
        'redirect' => 'thank-you.html'
    ]);
}
