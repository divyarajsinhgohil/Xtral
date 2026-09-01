<?php
/**
 * Minimal SMTP client (STARTTLS + AUTH LOGIN) — no external library needed.
 * Built for sending the public Contact form to a Gmail inbox via
 * smtp.gmail.com:587, but works with any standard SMTP+STARTTLS server.
 *
 * Usage:
 *   $result = smtpSendMail([
 *     'to'       => 'xtralcare@gmail.com',
 *     'subject'  => 'New enquiry from X-Tral website',
 *     'body'     => "Name: ...\nMessage: ...",
 *     'replyTo'  => 'customer@example.com', // optional
 *     'replyToName' => 'Customer Name',     // optional
 *   ]);
 *   // $result = ['success' => bool, 'error' => string|null]
 */

function smtpReadResponse($socket): array
{
    $lines = [];
    while (!feof($socket)) {
        $line = fgets($socket, 515);
        if ($line === false) break;
        $lines[] = $line;
        // Multi-line responses continue with "250-", final line is "250 "
        if (strlen($line) >= 4 && $line[3] === ' ') break;
    }
    $last = end($lines) ?: '';
    $code = (int) substr($last, 0, 3);
    return ['code' => $code, 'text' => implode('', $lines)];
}

function smtpCommand($socket, string $command, int $expectCode): array
{
    fwrite($socket, $command . "\r\n");
    $resp = smtpReadResponse($socket);
    if ($resp['code'] !== $expectCode) {
        throw new Exception("SMTP error after '{$command}': expected {$expectCode}, got {$resp['code']} — {$resp['text']}");
    }
    return $resp;
}

function smtpSendMail(array $opts): array
{
    $host        = $opts['host']        ?? (defined('SMTP_HOST') ? SMTP_HOST : '');
    $port        = $opts['port']        ?? (defined('SMTP_PORT') ? SMTP_PORT : 587);
    $username    = $opts['username']    ?? (defined('SMTP_USER') ? SMTP_USER : '');
    $password    = $opts['password']    ?? (defined('SMTP_PASS') ? SMTP_PASS : '');
    $fromEmail   = $opts['fromEmail']   ?? (defined('SMTP_FROM_EMAIL') ? SMTP_FROM_EMAIL : $username);
    $fromName    = $opts['fromName']    ?? (defined('SMTP_FROM_NAME') ? SMTP_FROM_NAME : 'X-Tral Website');
    $to          = $opts['to']          ?? '';
    $subject     = $opts['subject']     ?? '(no subject)';
    $body        = $opts['body']        ?? '';
    $replyTo     = $opts['replyTo']     ?? null;
    $replyToName = $opts['replyToName'] ?? '';

    if (empty($host) || empty($username) || empty($password) || empty($to)) {
        return ['success' => false, 'error' => 'SMTP is not configured yet (missing host/username/password/recipient).'];
    }

    try {
        $socket = @stream_socket_client(
            "tcp://{$host}:{$port}",
            $errno,
            $errstr,
            15,
            STREAM_CLIENT_CONNECT
        );
        if (!$socket) {
            throw new Exception("Could not connect to {$host}:{$port} — {$errstr} ({$errno})");
        }
        stream_set_timeout($socket, 15);

        smtpReadResponse($socket); // 220 greeting
        smtpCommand($socket, 'EHLO ' . ($_SERVER['HTTP_HOST'] ?? 'localhost'), 250);
        smtpCommand($socket, 'STARTTLS', 220);

        if (!stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
            throw new Exception('Could not start TLS encryption with the SMTP server.');
        }

        smtpCommand($socket, 'EHLO ' . ($_SERVER['HTTP_HOST'] ?? 'localhost'), 250);
        smtpCommand($socket, 'AUTH LOGIN', 334);
        smtpCommand($socket, base64_encode($username), 334);
        smtpCommand($socket, base64_encode($password), 235);

        smtpCommand($socket, "MAIL FROM:<{$fromEmail}>", 250);
        smtpCommand($socket, "RCPT TO:<{$to}>", 250);
        smtpCommand($socket, 'DATA', 354);

        $headers = [];
        $headers[] = 'From: ' . mimeEncodeHeader($fromName) . " <{$fromEmail}>";
        $headers[] = "To: <{$to}>";
        if ($replyTo) {
            $headers[] = 'Reply-To: ' . ($replyToName ? mimeEncodeHeader($replyToName) . " <{$replyTo}>" : $replyTo);
        }
        $headers[] = 'Subject: ' . mimeEncodeHeader($subject);
        $headers[] = 'MIME-Version: 1.0';
        $headers[] = 'Content-Type: text/plain; charset=UTF-8';
        $headers[] = 'Date: ' . date('r');

        // Dot-stuff any line that starts with "." per SMTP DATA rules.
        $escapedBody = preg_replace('/^\./m', '..', $body);
        $message = implode("\r\n", $headers) . "\r\n\r\n" . $escapedBody . "\r\n.";

        smtpCommand($socket, $message, 250);
        smtpCommand($socket, 'QUIT', 221);
        fclose($socket);

        return ['success' => true, 'error' => null];
    } catch (Exception $e) {
        if (isset($socket) && is_resource($socket)) fclose($socket);
        return ['success' => false, 'error' => $e->getMessage()];
    }
}

function mimeEncodeHeader(string $text): string
{
    // Only needs encoding if it has non-ASCII chars; keep plain ASCII untouched.
    if (preg_match('/[^\x20-\x7E]/', $text)) {
        return '=?UTF-8?B?' . base64_encode($text) . '?=';
    }
    return $text;
}
