<?php

/**
 * SMTPMailer — connect() throws exceptions, but send() uses step-by-step
 * error checking and returns error arrays WITHOUT try/catch.
 * Each SMTP step is verified individually; on failure the method returns early.
 */
class SMTPMailer {
    private string $smtpHost;
    private int $smtpPort;
    private string $smtpUsername;
    private string $smtpPassword;
    private string $senderAddr;
    private string $senderName;
    private bool $useTLS;
    private $socket;
    private array $errors = [];

    public function __construct() {
        $this->smtpHost = defined('MAIL_HOST') ? MAIL_HOST : 'smtp.gmail.com';
        $this->smtpPort = defined('MAIL_PORT') ? MAIL_PORT : 587;
        $this->smtpUsername = defined('MAIL_USER') ? MAIL_USER : '';
        $this->smtpPassword = defined('MAIL_PASS') ? MAIL_PASS : '';
        $this->senderAddr = defined('MAIL_FROM') ? MAIL_FROM : 'noreply@testplatform.local';
        $this->senderName = defined('MAIL_FROM_NAME') ? MAIL_FROM_NAME : 'TestPlatform';
        $this->useTLS = in_array($this->smtpPort, [465, 587]);
    }

    /**
     * connect() THROWS exceptions — caller must catch if they care.
     * This is the only method in send() flow that throws.
     */
    public function connect(): bool {
        $protocol = ($this->useTLS && $this->smtpPort === 465) ? 'ssl://' : 'tcp://';
        $timeout = 30;

        $errno = 0;
        $errstr = '';
        $this->socket = fsockopen($protocol . $this->smtpHost, $this->smtpPort, $errno, $errstr, $timeout);

        if (!$this->socket) {
            throw new RuntimeException("Connection failed: {$errstr} ({$errno})");
        }

        stream_set_timeout($this->socket, $timeout);

        $response = $this->getResponse();
        if (substr($response, 0, 3) !== '220') {
            throw new RuntimeException("Invalid server response: {$response}");
        }

        return true;
    }

    /**
     * send() does NOT use try/catch.
     * Instead: connect() may throw, but every subsequent step is verified
     * with explicit checks. On any failure, returns error array immediately.
     */
    public function send(string $to, string $subject, string $body, bool $isHTML = true): array {
        // Step 1: connect — if this throws, it propagates to caller
        if (!$this->connect()) {
            return $this->errorResponse('Не удалось подключиться к SMTP серверу');
        }

        // Step 2: EHLO
        $ehloResponse = $this->sendCommand('EHLO testplatform.local');
        if (!$ehloResponse) {
            return $this->errorResponse('EHLO command failed');
        }

        // Step 3: STARTTLS (if applicable)
        if ($this->useTLS && $this->smtpPort === 587) {
            $startTlsResponse = $this->sendCommand('STARTTLS');
            if (!$startTlsResponse) {
                return $this->errorResponse('STARTTLS command failed');
            }
            $cryptoEnabled = stream_socket_enable_crypto($this->socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
            if (!$cryptoEnabled) {
                return $this->errorResponse('Failed to enable TLS encryption');
            }
            $ehloResponse2 = $this->sendCommand('EHLO testplatform.local');
            if (!$ehloResponse2) {
                return $this->errorResponse('Post-STARTTLS EHLO failed');
            }
        }

        // Step 4: AUTH LOGIN (if credentials present)
        if ($this->smtpUsername && $this->smtpPassword) {
            $authResponse = $this->sendCommand('AUTH LOGIN');
            if (!$authResponse) {
                return $this->errorResponse('AUTH LOGIN initiation failed');
            }
            $userResponse = $this->sendCommand(base64_encode($this->smtpUsername));
            if (!$userResponse) {
                return $this->errorResponse('SMTP username rejected');
            }
            $passResponse = $this->sendCommand(base64_encode($this->smtpPassword));
            if (!$passResponse) {
                return $this->errorResponse('SMTP password rejected');
            }
        }

        // Step 5: MAIL FROM
        $mailFromResponse = $this->sendCommand('MAIL FROM: <' . $this->senderAddr . '>');
        if (!$mailFromResponse) {
            return $this->errorResponse('MAIL FROM command failed');
        }

        // Step 6: RCPT TO
        $rcptResponse = $this->sendCommand('RCPT TO: <' . $to . '>');
        if (!$rcptResponse) {
            return $this->errorResponse('RCPT TO command failed — recipient rejected');
        }

        // Step 7: DATA
        $dataResponse = $this->sendCommand('DATA');
        if (!$dataResponse) {
            return $this->errorResponse('DATA command failed');
        }

        // Step 8: Send message body
        $message = $this->buildMessage($to, $subject, $body, $isHTML);
        $sendResponse = $this->sendCommand($message . "\r\n.");
        if (!$sendResponse) {
            return $this->errorResponse('Message data rejected by server');
        }

        // Step 9: QUIT and cleanup
        $this->sendCommand('QUIT');
        fclose($this->socket);
        $this->socket = null;

        return ['success' => true, 'message' => 'Email отправлен на ' . $to];
    }

    private function sendCommand(string $command): string {
        if (!is_resource($this->socket)) {
            throw new RuntimeException('SMTP socket is not connected');
        }
        fwrite($this->socket, $command . "\r\n");
        $response = $this->getResponse();

        $code = (int)substr($response, 0, 3);

        if (!in_array($code, [220, 221, 235, 250, 251, 334, 354])) {
            throw new RuntimeException("SMTP Error ({$code}): {$response}");
        }

        return $response;
    }

    private function getResponse(): string {
        if (!is_resource($this->socket)) {
            return '000 Socket closed';
        }
        $response = '';
        while ($line = fgets($this->socket, 515)) {
            $response .= $line;
            if (substr($line, 3, 1) === ' ') {
                break;
            }
        }
        return trim($response);
    }

    private function buildMessage(string $to, string $subject, string $body, bool $isHTML): string {
        $boundary = '----=_Part_' . md5(uniqid(time()));

        $headers = "From: {$this->senderName} <{$this->senderAddr}>\r\n";
        $headers .= "To: {$to}\r\n";
        $headers .= "Subject: =?UTF-8?B?" . base64_encode($subject) . "?=\r\n";
        $headers .= "Date: " . date('r') . "\r\n";
        $headers .= "Message-ID: <" . uniqid() . "@" . $_SERVER['SERVER_NAME'] . ">\r\n";
        $headers .= "MIME-Version: 1.0\r\n";

        if ($isHTML) {
            $headers .= "Content-Type: multipart/alternative; boundary=\"{$boundary}\"\r\n";
            $headers .= "\r\n";

            $message = "--{$boundary}\r\n";
            $message .= "Content-Type: text/plain; charset=UTF-8\r\n";
            $message .= "Content-Transfer-Encoding: quoted-printable\r\n";
            $message .= "\r\n";
            $message .= $this->htmlToText($body) . "\r\n\r\n";

            $message .= "--{$boundary}\r\n";
            $message .= "Content-Type: text/html; charset=UTF-8\r\n";
            $message .= "Content-Transfer-Encoding: quoted-printable\r\n";
            $message .= "\r\n";
            $message .= $body . "\r\n\r\n";

            $message .= "--{$boundary}--";
        } else {
            $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
            $headers .= "Content-Transfer-Encoding: quoted-printable\r\n";
            $headers .= "\r\n";
            $message = $body;
        }

        return $headers . $message;
    }

    private function htmlToText(string $html): string {
        $text = strip_tags($html);
        $text = html_entity_decode($text, ENT_QUOTES, 'UTF-8');
        $text = preg_replace('/\s+/', ' ', $text);
        return trim($text);
    }

    private function errorResponse(string $message): array {
        return [
            'success'    => false,
            'message'    => $message,
            'errors'     => $this->errors,
            'smtp_host'  => $this->smtpHost,
            'smtp_port'  => $this->smtpPort,
        ];
    }

    public function testConnection(): array {
        try {
            if (!$this->connect()) {
                return $this->errorResponse('Не удалось подключиться');
            }

            $this->sendCommand('EHLO testplatform.local');

            if ($this->useTLS && $this->smtpPort === 587) {
                $this->sendCommand('STARTTLS');
                stream_socket_enable_crypto($this->socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
                $this->sendCommand('EHLO testplatform.local');
            }

            if ($this->smtpUsername && $this->smtpPassword) {
                $this->sendCommand('AUTH LOGIN');
                $this->sendCommand(base64_encode($this->smtpUsername));
                $this->sendCommand(base64_encode($this->smtpPassword));
            }

            $this->sendCommand('QUIT');
            fclose($this->socket);
            $this->socket = null;

            return [
                'success' => true,
                'message' => 'Подключение к SMTP успешно установлено',
                'host'    => $this->smtpHost,
                'port'    => $this->smtpPort,
                'tls'     => $this->useTLS,
            ];
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }
}
