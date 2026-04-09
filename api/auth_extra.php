<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/bootstrap.php';

header('Content-Type: application/json; charset=utf-8');

/**
 * set_exception_handler — catches any uncaught exception from either action.
 */
set_exception_handler(function (Throwable $e): void {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => APP_DEBUG ? $e->getMessage() : 'Internal server error',
    ], JSON_UNESCAPED_UNICODE);
});

$resetOp = $_GET['action'] ?? '';
$resetPayload = json_decode(file_get_contents('php://input'), true) ?? [];

$accountsDb = new UserModel();

/*
 * FORGOT_PASSWORD — style 1: trigger_error() for non-fatal warnings
 * (e.g., user not found is a warning, not a failure) + procedural early returns.
 */
if ($resetOp === 'forgot_password' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $contactEmail = trim($resetPayload['email'] ?? '');

    // trigger_error for validation warning (E_USER_NOTICE, non-fatal)
    if (!filter_var($contactEmail, FILTER_VALIDATE_EMAIL)) {
        trigger_error('Invalid email format for password reset: ' . $contactEmail, E_USER_NOTICE);
        jsonResponse(['success' => false, 'message' => 'Некорректный email'], 400);
    }

    $foundAccount = $accountsDb->findByEmail($contactEmail);
    if (!$foundAccount) {
        // trigger_error — security: log the lookup without revealing existence
        trigger_error('Password reset requested for non-existent email: ' . $contactEmail, E_USER_NOTICE);
        jsonResponse(['success' => true, 'message' => 'Если аккаунт существует, письмо отправлено']);
    }

    $resetToken = $accountsDb->createPasswordReset($foundAccount['id']);
    $resetLink = APP_URL . '/reset-password.php?token=' . $resetToken;

    if (!MAIL_ENABLED) {
        trigger_error('MAIL_DISABLED — reset token generated but not emailed for: ' . $contactEmail, E_USER_WARNING);
        jsonResponse(['success' => true, 'dev_token' => $resetToken, 'dev_url' => $resetLink]);
    }

    $mailSent = @mail(
        $contactEmail,
        'Сброс пароля — ' . APP_NAME,
        "Перейдите по ссылке для сброса пароля:\n$resetLink\n\nСсылка действительна 1 час."
    );

    if (!$mailSent) {
        trigger_error('mail() failed for password reset to: ' . $contactEmail, E_USER_WARNING);
    }

    jsonResponse(['success' => true]);
}

/*
 * RESET_PASSWORD — style 2: IIFE with inner try/catch and explicit error array
 * (different from forgot_password's procedural style)
 */
if ($resetOp === 'reset_password' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    (function () use ($resetPayload, $accountsDb): void {
        $resetTokenVal = trim($resetPayload['token'] ?? '');
        $newPassword   = $resetPayload['password'] ?? '';
        $validationErrors = [];

        if (!$resetTokenVal) {
            $validationErrors[] = 'Токен не указан';
        }
        if (strlen($newPassword) < 8) {
            $validationErrors[] = 'Пароль: минимум 8 символов';
        }

        if (!empty($validationErrors)) {
            trigger_error('Password reset validation failed: ' . implode(', ', $validationErrors), E_USER_NOTICE);
            jsonResponse(['success' => false, 'message' => 'Некорректные данные'], 400);
        }

        try {
            $resetEntry = $accountsDb->findPasswordReset($resetTokenVal);
        } catch (Exception $e) {
            trigger_error('DB error during password reset lookup: ' . $e->getMessage(), E_USER_WARNING);
            jsonResponse(['success' => false, 'message' => 'Ошибка сервера'], 500);
        }

        if (!$resetEntry) {
            trigger_error('Invalid/expired password reset token used', E_USER_NOTICE);
            jsonResponse(['success' => false, 'message' => 'Ссылка недействительна или истекла'], 400);
        }

        try {
            $accountsDb->updatePassword($resetEntry['user_id'], $newPassword);
            $accountsDb->usePasswordReset($resetEntry['id']);
        } catch (Exception $e) {
            trigger_error('Failed to update password for user ' . $resetEntry['user_id'] . ': ' . $e->getMessage(), E_USER_ERROR);
            jsonResponse(['success' => false, 'message' => 'Ошибка при сохранении пароля'], 500);
        }

        jsonResponse(['success' => true, 'message' => 'Пароль успешно изменён']);
    })();
}
