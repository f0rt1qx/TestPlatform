<?php
/**
 * SMTPMailer.php — отправка email через SMTP на чистых сокетах
 * Без внешних зависимостей — нативная реализация PHP
 */

class SMTPMailer {
    private string $host;
    private int $port;
    private string $username;
    private string $password;
    private string $from;
    private string $fromName;
    private bool $useTLS;
    private $socket;
    private array $errors = [];

    public function __construct() {
        $this->host = defined('MAIL_HOST') ? MAIL_HOST : 'smtp.gmail.com';
        $this->port = defined('MAIL_PORT') ? MAIL_PORT : 587;
        $this->username = defined('MAIL_USER') ? MAIL_USER : '';
        $this->password = defined('MAIL_PASS') ? MAIL_PASS : '';
        $this->from = defined('MAIL_FROM') ? MAIL_FROM : 'noreply@testplatform.local';
        $this->fromName = defined('MAIL_FROM_NAME') ? MAIL_FROM_NAME : 'TestPlatform';
        $this->useTLS = in_array($this->port, [465, 587]);
    }

    /**
     * Отправка email
     */
    public function send(string $to, string $subject, string $body, bool $isHTML = true): array {
        try {
            // Подключение к SMTP серверу
            if (!$this->connect()) {
                return $this->errorResponse('Не удалось подключиться к SMTP серверу');
            }

            // EHLO/HELO
            $this->sendCommand('EHLO testplatform.local');

            // STARTTLS если нужно
            if ($this->useTLS && $this->port === 587) {
                $this->sendCommand('STARTTLS');
                stream_socket_enable_crypto($this->socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
                $this->sendCommand('EHLO testplatform.local');
            }

            // Авторизация
            if ($this->username && $this->password) {
                $this->sendCommand('AUTH LOGIN');
                $this->sendCommand(base64_encode($this->username));
                $this->sendCommand(base64_encode($this->password));
            }

            // Отправка письма
            $this->sendCommand('MAIL FROM: <' . $this->from . '>');
            $this->sendCommand('RCPT TO: <' . $to . '>');
            $this->sendCommand('DATA');

            // Формируем письмо
            $message = $this->buildMessage($to, $subject, $body, $isHTML);
            $this->sendCommand($message . "\r\n.");

            // Завершение
            $this->sendCommand('QUIT');
            fclose($this->socket);

            return ['success' => true, 'message' => 'Email отправлен на ' . $to];

        } catch (Exception $e) {
            $this->errors[] = $e->getMessage();
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Подключение к SMTP
     */
    private function connect(): bool {
        $protocol = ($this->useTLS && $this->port === 465) ? 'ssl://' : 'tcp://';
        $timeout = 30;

        $this->socket = fsockopen($protocol . $this->host, $this->port, $errno, $errstr, $timeout);

        if (!$this->socket) {
            throw new Exception("Connection failed: {$errstr} ({$errno})");
        }

        stream_set_timeout($this->socket, $timeout);
        
        // Читаем приветствие сервера
        $response = $this->getResponse();
        if (substr($response, 0, 3) !== '220') {
            throw new Exception("Invalid server response: {$response}");
        }

        return true;
    }

    /**
     * Отправка команды на сервер
     */
    private function sendCommand(string $command): string {
        fwrite($this->socket, $command . "\r\n");
        $response = $this->getResponse();
        
        $code = (int)substr($response, 0, 3);
        
        // Проверяем коды ответов (разрешаем 250, 235, 334, 354)
        if (!in_array($code, [220, 221, 235, 250, 251, 334, 354])) {
            throw new Exception("SMTP Error ({$code}): {$response}");
        }

        return $response;
    }

    /**
     * Получение ответа от сервера
     */
    private function getResponse(): string {
        $response = '';
        while ($line = fgets($this->socket, 515)) {
            $response .= $line;
            // Если строка не продолжается (4й символ пробел)
            if (substr($line, 3, 1) === ' ') {
                break;
            }
        }
        return trim($response);
    }

    /**
     * Формирование письма
     */
    private function buildMessage(string $to, string $subject, string $body, bool $isHTML): string {
        $boundary = '----=_Part_' . md5(uniqid(time()));
        
        $headers = "From: {$this->fromName} <{$this->from}>\r\n";
        $headers .= "To: {$to}\r\n";
        $headers .= "Subject: =?UTF-8?B?" . base64_encode($subject) . "?=\r\n";
        $headers .= "Date: " . date('r') . "\r\n";
        $headers .= "Message-ID: <" . uniqid() . "@" . $_SERVER['SERVER_NAME'] . ">\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        
        if ($isHTML) {
            $headers .= "Content-Type: multipart/alternative; boundary=\"{$boundary}\"\r\n";
            $headers .= "\r\n";
            
            // Plain text версия
            $message = "--{$boundary}\r\n";
            $message .= "Content-Type: text/plain; charset=UTF-8\r\n";
            $message .= "Content-Transfer-Encoding: quoted-printable\r\n";
            $message .= "\r\n";
            $message .= $this->htmlToText($body) . "\r\n\r\n";
            
            // HTML версия
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

    /**
     * Конвертация HTML в текст для fallback
     */
    private function htmlToText(string $html): string {
        $text = strip_tags($html);
        $text = html_entity_decode($text, ENT_QUOTES, 'UTF-8');
        $text = preg_replace('/\s+/', ' ', $text);
        return trim($text);
    }

    /**
     * Формирование ответа с ошибкой
     */
    private function errorResponse(string $message): array {
        return [
            'success' => false,
            'message' => $message,
            'errors' => $this->errors,
            'smtp_host' => $this->host,
            'smtp_port' => $this->port,
        ];
    }

    /**
     * Тестовое подключение к SMTP
     */
    public function testConnection(): array {
        try {
            if (!$this->connect()) {
                return $this->errorResponse('Не удалось подключиться');
            }
            
            $this->sendCommand('EHLO testplatform.local');
            
            if ($this->useTLS && $this->port === 587) {
                $this->sendCommand('STARTTLS');
                stream_socket_enable_crypto($this->socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
                $this->sendCommand('EHLO testplatform.local');
            }

            if ($this->username && $this->password) {
                $this->sendCommand('AUTH LOGIN');
                $this->sendCommand(base64_encode($this->username));
                $this->sendCommand(base64_encode($this->password));
            }

            $this->sendCommand('QUIT');
            fclose($this->socket);

            return [
                'success' => true,
                'message' => 'Подключение к SMTP успешно установлено',
                'host' => $this->host,
                'port' => $this->port,
                'tls' => $this->useTLS,
            ];
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }
}
