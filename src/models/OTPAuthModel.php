<?php


class OTPAuthModel {
    private PDO $db;
    private const CODE_LENGTH = 6;
    private const CODE_TTL = 300; 
    private const MAX_ATTEMPTS = 3;
    private const RESEND_COOLDOWN = 60; 

    public function __construct() {
        $this->db = Database::getInstance();
    }

    
    public function generateCode(): string {
        
        $code = '';
        for ($i = 0; $i < self::CODE_LENGTH; $i++) {
            $code .= random_int(0, 9);
        }
        return $code;
    }

    
    public function createOTP(string $email, string $type = 'login'): array {
        
        $this->cleanupExpiredCodes();

        
        $email = strtolower(trim($email));

        
        $code = $this->generateCode();
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '';

        
        $stmt = $this->db->prepare('SELECT id, username, phone FROM users WHERE LOWER(email) = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user) {
            return ['success' => false, 'message' => 'Пользователь не найден'];
        }

        
        $stmt = $this->db->prepare(
            'INSERT INTO otp_codes (user_id, email, code, type, ip_address, expires_at, attempts)
             VALUES (:uid, :email, :code, :type, :ip, NOW() + INTERVAL :ttl SECOND, 0)'
        );
        $stmt->execute([
            ':uid' => $user['id'],
            ':email' => $email,
            ':code' => password_hash($code, PASSWORD_DEFAULT),
            ':type' => $type,
            ':ip' => $ipAddress,
            ':ttl' => self::CODE_TTL,
        ]);

        
        $this->logOTPEvent($user['id'], 'otp_created', $type);

        return [
            'success' => true,
            'user_id' => $user['id'],
            'username' => $user['username'],
            'has_phone' => !empty($user['phone']),
            'expires_in' => self::CODE_TTL,
            'code' => $code, 
        ];
    }

    
    public function verifyCode(string $email, string $code): array {
        
        $email = strtolower(trim($email));

        $stmt = $this->db->prepare(
            'SELECT o.*, u.id as user_id, u.username, u.role, u.is_blocked
             FROM otp_codes o
             JOIN users u ON o.user_id = u.id
             WHERE LOWER(o.email) = ?
             AND o.used = 0
             AND o.expires_at > NOW()
             ORDER BY o.created_at DESC
             LIMIT 1'
        );
        $stmt->execute([$email]);
        $otpRecord = $stmt->fetch();

        
        if (!$otpRecord) {
            $stmt2 = $this->db->prepare('SELECT email, expires_at, used, created_at FROM otp_codes WHERE LOWER(email) = ? ORDER BY created_at DESC LIMIT 3');
            $stmt2->execute([$email]);
            $allCodes = $stmt2->fetchAll();
            error_log("OTP Verify FAILED: email={$email}, codes in DB: " . print_r($allCodes, true) . ", now=" . date('Y-m-d H:i:s'));
        }

        if (!$otpRecord) {
            return ['success' => false, 'message' => 'Код не найден или истек'];
        }

        
        if ($otpRecord['attempts'] >= self::MAX_ATTEMPTS) {
            $this->logOTPEvent($otpRecord['user_id'], 'otp_max_attempts', $otpRecord['type']);
            return ['success' => false, 'message' => 'Превышено количество попыток. Запросите новый код'];
        }

        
        $this->db->prepare(
            'UPDATE otp_codes SET attempts = attempts + 1 WHERE id = ?'
        )->execute([$otpRecord['id']]);

        
        if (!password_verify($code, $otpRecord['code'])) {
            $remaining = self::MAX_ATTEMPTS - $otpRecord['attempts'] - 1;
            $this->logOTPEvent($otpRecord['user_id'], 'otp_failed', $otpRecord['type']);
            return [
                'success' => false, 
                'message' => 'Неверный код',
                'remaining_attempts' => max(0, $remaining)
            ];
        }

        
        $this->db->prepare(
            'UPDATE otp_codes SET used = 1, used_at = NOW() WHERE id = ?'
        )->execute([$otpRecord['id']]);

        $this->logOTPEvent($otpRecord['user_id'], 'otp_verified', $otpRecord['type']);

        return [
            'success' => true,
            'user_id' => $otpRecord['user_id'],
            'username' => $otpRecord['username'],
            'role' => $otpRecord['role'],
            'is_blocked' => $otpRecord['is_blocked'],
        ];
    }

    
    public function sendCode(string $email, string $code, string $method = 'email'): array {
        if ($method === 'email') {
            return $this->sendViaEmail($email, $code);
        } elseif ($method === 'sms') {
            return $this->sendViaSMS($email, $code);
        }
        return ['success' => false, 'message' => 'Неподдерживаемый метод'];
    }

    
    private function sendViaEmail(string $email, string $code): array {
        if (MAIL_ENABLED) {
            try {
                
                require_once __DIR__ . '/../helpers/SMTPMailer.php';
                
                $mailer = new SMTPMailer();
                $subject = 'Код подтверждения Sapienta';
                $message = $this->getEmailTemplate($code);
                
                $result = $mailer->send($email, $subject, $message, true);
                
                if ($result['success']) {
                    return ['success' => true, 'message' => 'Код отправлен на email ' . $this->maskEmail($email)];
                }
                
                
                return [
                    'success' => true,
                    'message' => 'SMTP ошибка, но код сгенерирован',
                    'development_code' => $code,
                    'development_info' => 'SMTP ошибка: ' . ($result['message'] ?? 'Неизвестная ошибка')
                ];
            } catch (Exception $e) {
                
                return [
                    'success' => true,
                    'message' => 'Ошибка SMTP, используйте код',
                    'development_code' => $code,
                    'development_info' => 'Ошибка: ' . $e->getMessage()
                ];
            }
        }

        
        return [
            'success' => true,
            'message' => 'Код сгенерирован (режим разработки)',
            'development_code' => $code,
            'development_info' => 'В production режиме код будет отправлен на email через SMTP'
        ];
    }

    
    private function sendViaSMS(string $email, string $code): array {
        
        $stmt = $this->db->prepare('SELECT phone FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (empty($user['phone'])) {
            return ['success' => false, 'message' => 'Номер телефона не привязан'];
        }

        
        
        return [
            'success' => true,
            'message' => 'SMS код сгенерирован (режим разработки)',
            'development_code' => $code,
            'phone_masked' => $this->maskPhone($user['phone']),
            'development_info' => 'В production режиме код будет отправлен через SMS'
        ];
    }

    
    private function getEmailTemplate(string $code): string {
        return '<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
</head>
<body style="margin:0;padding:0;background:#f3f4f6;">
<table width="100%" cellpadding="0" cellspacing="0" style="max-width:600px;margin:40px auto;background:#fff;border-radius:12px;overflow:hidden;box-shadow:0 4px 6px rgba(0,0,0,0.1);">
    <tr>
        <td style="background:linear-gradient(135deg,#2563eb,#1e40af);padding:30px;text-align:center;">
            <h1 style="margin:0;color:#fff;font-size:24px;">🎓 Sapienta</h1>
        </td>
    </tr>
    <tr>
        <td style="padding:40px 30px;">
            <h2 style="margin:0 0 20px;color:#1e293b;font-size:20px;">Код подтверждения</h2>
            <p style="color:#64748b;font-size:14px;line-height:1.6;margin:0 0 30px;">
                Используйте этот код для входа в ваш аккаунт. Код действителен <strong>5 минут</strong>.
            </p>
            <div style="background:#f0f4f8;border-radius:8px;padding:20px;text-align:center;margin-bottom:30px;">
                <div style="font-size:36px;font-weight:900;letter-spacing:8px;color:#2563eb;font-family:monospace;">' . $code . '</div>
            </div>
            <p style="color:#94a3b8;font-size:12px;line-height:1.6;margin:0;">
                Если вы не запрашивали этот код, просто проигнорируйте это письмо.<br>
                Никогда не передавайте этот код третьим лицам.
            </p>
        </td>
    </tr>
    <tr>
        <td style="background:#f8fafc;padding:20px;text-align:center;border-top:1px solid #e2e8f0;">
            <p style="margin:0;color:#94a3b8;font-size:11px;">
                © ' . date('Y') . ' Sapienta. Все права защищены.
            </p>
        </td>
    </tr>
</table>
</body>
</html>';
    }

    
    private function maskPhone(string $phone): string {
        $clean = preg_replace('/[^0-9]/', '', $phone);
        if (strlen($clean) >= 10) {
            return substr($clean, 0, 2) . '***-***-' . substr($clean, -2);
        }
        return $phone;
    }

    
    private function maskEmail(string $email): string {
        [$name, $domain] = explode('@', $email);
        $masked = substr($name, 0, 2) . '***@' . $domain;
        return $masked;
    }

    
    private function cleanupExpiredCodes(): void {
        $this->db->exec(
            'DELETE FROM otp_codes WHERE expires_at < NOW() OR used = 1'
        );
    }

    
    private function logOTPEvent(int $userId, string $event, string $type): void {
        $stmt = $this->db->prepare(
            'INSERT INTO logs (user_id, event_type, event_data, severity)
             VALUES (:uid, :type, :data, :sev)'
        );
        $stmt->execute([
            ':uid' => $userId,
            ':type' => $event,
            ':data' => json_encode(['otp_type' => $type], JSON_UNESCAPED_UNICODE),
            ':sev' => 'low',
        ]);
    }

    
    public function checkResendCooldown(string $email): array {
        $stmt = $this->db->prepare(
            'SELECT created_at FROM otp_codes 
             WHERE email = ? AND used = 0 AND expires_at > NOW()
             ORDER BY created_at DESC LIMIT 1'
        );
        $stmt->execute([$email]);
        $lastCode = $stmt->fetch();

        if (!$lastCode) {
            return ['can_resend' => true];
        }

        $createdAt = strtotime($lastCode['created_at']);
        $elapsed = time() - $createdAt;
        $remaining = self::RESEND_COOLDOWN - $elapsed;

        if ($remaining > 0) {
            return [
                'can_resend' => false,
                'remaining_seconds' => $remaining,
                'message' => "Повторная отправка возможна через {$remaining} сек"
            ];
        }

        return ['can_resend' => true];
    }
}
