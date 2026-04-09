<?php


require_once __DIR__ . '/../src/bootstrap.php';

header('Content-Type: application/json; charset=utf-8');

$action = $_GET['action'] ?? '';
$input = json_decode(file_get_contents('php://input'), true) ?? [];


if (!APP_DEBUG) {
    echo json_encode(['success' => false, 'message' => 'Disabled in production']);
    exit;
}

require_once __DIR__ . '/../src/helpers/SMTPMailer.php';

$mailer = new SMTPMailer();

if ($action === 'connect') {
    $result = $mailer->testConnection();
    echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

if ($action === 'send') {
    $email = $input['email'] ?? '';
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Invalid email']);
        exit;
    }

    $subject = 'Тестовое письмо TestPlatform';
    $body = '<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"></head>
<body style="margin:0;padding:40px;background:#f3f4f6;">
<table width="100%" cellpadding="0" cellspacing="0" style="max-width:600px;margin:0 auto;background:#fff;border-radius:12px;overflow:hidden;box-shadow:0 4px 6px rgba(0,0,0,0.1);">
    <tr>
        <td style="background:linear-gradient(135deg,#2563eb,#1e40af);padding:30px;text-align:center;">
            <h1 style="margin:0;color:#fff;font-size:24px;">🎓 TestPlatform</h1>
        </td>
    </tr>
    <tr>
        <td style="padding:40px 30px;">
            <h2 style="margin:0 0 20px;color:#1e293b;">✅ SMTP работает!</h2>
            <p style="color:#64748b;font-size:14px;line-height:1.6;margin:0 0 20px;">
                Это тестовое письмо. Если вы его видите — значит SMTP настройки правильные и отправка работает корректно.
            </p>
            <div style="background:#f0f4f8;border-radius:8px;padding:20px;text-align:center;">
                <div style="font-size:48px;margin-bottom:12px;">🎉</div>
                <div style="font-size:16px;font-weight:700;color:#2563eb;">Поздравляем!</div>
            </div>
            <p style="color:#94a3b8;font-size:12px;margin-top:30px;">
                Отправлено: ' . date('d.m.Y H:i:s') . '<br>
                SMTP: ' . MAIL_HOST . ':' . MAIL_PORT . '
            </p>
        </td>
    </tr>
</table>
</body>
</html>';

    $result = $mailer->send($email, $subject, $body, true);
    echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

echo json_encode(['success' => false, 'message' => 'Unknown action']);
