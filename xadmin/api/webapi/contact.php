<?php
/**
 * Public Contact form submission (xfront/contact.php)
 * POST /xadmin/api/webapi/contact.php
 * Body (JSON): { name, phone, email, subject, message }
 */
require_once __DIR__ . '/config.php';
require_once dirname(__DIR__, 2) . '/config/smtp_mailer.php';

requireMethod('POST');

$data = getJsonInput();

$name    = sanitize($data['name'] ?? '');
$phone   = sanitize($data['phone'] ?? '');
$email   = sanitize($data['email'] ?? '');
$subject = sanitize($data['subject'] ?? 'Product enquiry');
$message = sanitize($data['message'] ?? '');

if ($name === '' || $phone === '' || $email === '' || $message === '') {
    jsonResponse(false, null, 'Please fill in all required fields.');
}

if (!preg_match('/^[6-9]\d{9}$/', $phone)) {
    jsonResponse(false, null, 'Please enter a valid 10-digit mobile number starting with 6, 7, 8, or 9.');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    jsonResponse(false, null, 'Please enter a valid email address.');
}

$body = "New enquiry from the X-Tral website contact form:\n\n"
    . "Name: {$name}\n"
    . "Phone: {$phone}\n"
    . "Email: {$email}\n"
    . "Interested in: {$subject}\n\n"
    . "Message:\n{$message}\n";

$result = smtpSendMail([
    'to'          => SMTP_TO_EMAIL,
    'subject'     => "X-Tral Contact Form: {$subject} — {$name}",
    'body'        => $body,
    'replyTo'     => $email,
    'replyToName' => $name,
]);

if (!$result['success']) {
    logError('webapi/contact: ' . $result['error']);
    jsonResponse(false, null, 'Sorry, your message could not be sent right now. Please try again later or contact us directly.');
}

logInfo("Contact form submitted by {$name} <{$email}>");
jsonResponse(true, null, 'Thank you! Your enquiry has been received. Our team will get back to you shortly.');
