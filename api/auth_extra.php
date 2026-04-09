<?php

declare(strict_types=1);

$resetOp = $_GET['action'] ?? '';
$resetPayload = json_decode(file_get_contents('php://input'), true) ?? [];

$accountsDb = new UserModel();

if ($resetOp === 'forgot_password' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $contactEmail = trim($resetPayload['email'] ?? '');
    if (!filter_var($contactEmail, FILTER_VALIDATE_EMAIL)) {
        jsonResponse(['success' => false, 'message' => 'Некорректный email'], 400);
    }

    $foundAccount = $accountsDb->findByEmail($contactEmail);
    if (!$foundAccount) jsonResponse(['success' => true, 'message' => 'Если аккаунт существует, письмо отправлено']);

    $resetToken = $accountsDb->createPasswordReset($foundAccount['id']);
    $resetLink = APP_URL . '/reset-password.php?token=' . $resetToken;

    if (!MAIL_ENABLED) {
        jsonResponse(['success' => true, 'dev_token' => $resetToken, 'dev_url' => $resetLink]);
    }

    mail(
        $contactEmail,
        'Сброс пароля — ' . APP_NAME,
        "Перейдите по ссылке для сброса пароля:\n$resetLink\n\nСсылка действительна 1 час."
    );
    jsonResponse(['success' => true]);
}


if ($resetOp === 'reset_password' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $resetTokenVal    = trim($resetPayload['token'] ?? '');
    $newPassword = $resetPayload['password'] ?? '';

    if (!$resetTokenVal || strlen($newPassword) < 8) {
        jsonResponse(['success' => false, 'message' => 'Некорректные данные'], 400);
    }

    $resetEntry = $accountsDb->findPasswordReset($resetTokenVal);
    if (!$resetEntry) jsonResponse(['success' => false, 'message' => 'Ссылка недействительна или истекла'], 400);

    $accountsDb->updatePassword($resetEntry['user_id'], $newPassword);
    $accountsDb->usePasswordReset($resetEntry['id']);

    jsonResponse(['success' => true, 'message' => 'Пароль успешно изменён']);
}
