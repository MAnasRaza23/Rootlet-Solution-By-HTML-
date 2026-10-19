<?php
declare(strict_types=1);
function send_contact_email(array $config, array $fields): void {
    $subject = 'Rootlet Contact: ' . $fields['subject'];
    $body = "New website enquiry\r\n\r\nName: {$fields['name']}\r\nEmail: {$fields['email']}\r\nPhone: {$fields['phone']}\r\nSubject: {$fields['subject']}\r\n\r\nMessage:\r\n{$fields['message']}";
    $body = str_replace(["\r\n", "\r"], "\n", $body);
    $body = str_replace("\n", "\r\n", $body);
    $encodedSubject = '=?UTF-8?B?' . base64_encode($subject) . '?=';
    $headers = [
        'From: Rootlet Solutions <' . $config['from'] . '>',
        'Reply-To: ' . $fields['email'],
        'MIME-Version: 1.0',
        'Content-Type: text/plain; charset=UTF-8',
        'Content-Transfer-Encoding: base64',
    ];
    $encodedBody = rtrim(chunk_split(base64_encode($body), 76, "\r\n"));
    if ($config['transport'] === 'mail') {
        if (!mail(implode(', ', $config['recipients']), $encodedSubject, $encodedBody, implode("\r\n", $headers))) {
            throw new RuntimeException('Hosting mail transport rejected the message.');
        }
        return;
    }
    if ($config['host'] === '' || $config['password'] === '' || !in_array($config['encryption'], ['tls', 'ssl'], true)) {
        throw new RuntimeException('Configure SMTP host, password and encryption in includes/contact-mail.php.');
    }
    $context = stream_context_create(['ssl' => ['verify_peer' => true, 'verify_peer_name' => true]]);
    $socket = stream_socket_client(($config['encryption'] === 'ssl' ? 'ssl://' : 'tcp://') . $config['host'] . ':' . $config['port'], $errno, $error, 15, STREAM_CLIENT_CONNECT, $context);
    if (!$socket) throw new RuntimeException('SMTP connection failed.');
    stream_set_timeout($socket, 15);
    $read = static function (array $codes) use ($socket): void {
        do {
            $line = fgets($socket, 4096);
            if ($line === false || !preg_match('/^([0-9]{3})([ -])/', $line, $match)) throw new RuntimeException('Invalid SMTP response or timeout.');
        } while ($match[2] === '-');
        if (!in_array((int) $match[1], $codes, true)) throw new RuntimeException('SMTP request rejected with code ' . $match[1]);
    };
    $write = static function (string $data) use ($socket): void {
        while ($data !== '') {
            $written = fwrite($socket, $data);
            if ($written === false || $written === 0) throw new RuntimeException('SMTP write failed.');
            $data = substr($data, $written);
        }
    };
    $command = static function (string $command, array $codes) use ($write, $read): void {
        $write($command . "\r\n"); $read($codes);
    };
    try {
        $read([220]); $command('EHLO rootletsolutions.com', [250]);
        if ($config['encryption'] === 'tls') {
            $command('STARTTLS', [220]);
            if (stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT) !== true) throw new RuntimeException('SMTP TLS negotiation failed.');
            $command('EHLO rootletsolutions.com', [250]);
        }
        $command('AUTH LOGIN', [334]);
        $command(base64_encode($config['username']), [334]);
        $command(base64_encode($config['password']), [235]);
        $command('MAIL FROM:<' . $config['from'] . '>', [250]);
        foreach ($config['recipients'] as $recipient) $command('RCPT TO:<' . $recipient . '>', [250, 251]);
        $command('DATA', [354]);
        $message = 'Date: ' . date(DATE_RFC2822) . "\r\n" . 'To: ' . implode(', ', $config['recipients']) . "\r\nSubject: " . $encodedSubject . "\r\n" . implode("\r\n", $headers) . "\r\n\r\n" . $encodedBody;
        $write($message . "\r\n.\r\n"); $read([250]);
        // Once accepted, a failed QUIT must not report failure and cause duplicate retries.
        @fwrite($socket, "QUIT\r\n");
    } finally { fclose($socket); }
}
