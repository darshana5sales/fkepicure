<?php
/**
 * ═══════════════════════════════════════════════════════════
 * FK EPICURE FOODS — Contact Form Mailer
 * Receives POST from the contact form (assets/js/main.js)
 * and sends via PHP mail(). Returns JSON.
 *
 * CONFIG: edit the two constants below to change recipients.
 * If your host requires SMTP auth, swap mail() for PHPMailer
 * (see the commented block at the bottom).
 * ═══════════════════════════════════════════════════════════
 */

define('MAIL_TO',   'info@fkepicurefoods.in');       // where enquiries land
define('MAIL_FROM', 'noreply@fkepicurefoods.in');    // must be a mailbox / alias on your domain

header('Content-Type: application/json; charset=utf-8');

function respond($success, $message)
{
    echo json_encode(['success' => $success, 'message' => $message]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    respond(false, 'Invalid request method.');
}

/* ---------- honeypot: bots fill the hidden field, humans don't ---------- */
if (!empty($_POST['website'])) {
    respond(true, 'Thank you — your message has been sent.'); // silently drop
}

/* ---------- collect + sanitize ---------- */
function clean($key, $max = 500)
{
    $v = isset($_POST[$key]) ? trim($_POST[$key]) : '';
    $v = strip_tags($v);
    $v = str_replace(["\r", "\n", "%0a", "%0d"], ' ', $v); // header-injection guard
    return mb_substr($v, 0, $max);
}

$name    = clean('name', 100);
$company = clean('company', 120);
$phone   = clean('phone', 30);
$email   = clean('email', 150);
$subject = clean('interest', 120);
$message = isset($_POST['message']) ? mb_substr(strip_tags(trim($_POST['message'])), 0, 3000) : '';

/* ---------- validate ---------- */
if ($name === '' || $email === '') {
    respond(false, 'Please fill in your name and email.');
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    respond(false, 'Please enter a valid email address.');
}
if ($subject === '') {
    $subject = 'General Enquiry';
}

/* ---------- compose ---------- */
$mailSubject = 'FK Epicure Foods — ' . $subject . ' — ' . $name;

$body  = "New enquiry from fkepicurefoods.in\n";
$body .= "──────────────────────────────────\n\n";
$body .= "Name:      $name\n";
$body .= "Company:   " . ($company !== '' ? $company : '—') . "\n";
$body .= "Email:     $email\n";
$body .= "Phone:     " . ($phone !== '' ? $phone : '—') . "\n";
$body .= "Interest:  $subject\n\n";
$body .= "Message:\n$message\n\n";
$body .= "──────────────────────────────────\n";
$body .= "Sent: " . date('d M Y, h:i A') . " (server time)\n";
$body .= "IP:   " . ($_SERVER['REMOTE_ADDR'] ?? 'unknown') . "\n";

$headers  = "From: FK Epicure Foods <" . MAIL_FROM . ">\r\n";
$headers .= "Reply-To: $name <$email>\r\n";
$headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";
$headers .= "MIME-Version: 1.0\r\n";
$headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

/* ---------- send ---------- */
if (@mail(MAIL_TO, $mailSubject, $body, $headers)) {
    respond(true, 'Thank you, ' . $name . ' — your message has been sent. We’ll be in touch shortly.');
} else {
    respond(false, 'Mail could not be sent right now. Please email us directly at ' . MAIL_TO . '.');
}

/* ═══════════════════════════════════════════════════════════
 * OPTIONAL — SMTP via PHPMailer (if your host blocks mail()):
 *
 * 1. composer require phpmailer/phpmailer
 * 2. Replace the send block above with:
 *
 * use PHPMailer\PHPMailer\PHPMailer;
 * require 'vendor/autoload.php';
 * $mail = new PHPMailer(true);
 * $mail->isSMTP();
 * $mail->Host       = 'smtp.hostinger.com';   // your SMTP host
 * $mail->SMTPAuth   = true;
 * $mail->Username   = MAIL_FROM;
 * $mail->Password   = 'YOUR_MAILBOX_PASSWORD';
 * $mail->SMTPSecure = 'ssl';
 * $mail->Port       = 465;
 * $mail->setFrom(MAIL_FROM, 'FK Epicure Foods');
 * $mail->addAddress(MAIL_TO);
 * $mail->addReplyTo($email, $name);
 * $mail->Subject = $mailSubject;
 * $mail->Body    = $body;
 * $mail->send();
 * respond(true, 'Thank you — your message has been sent.');
 * ═══════════════════════════════════════════════════════════ */
