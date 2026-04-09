<?php
/**
 * api/auth.php дополнение — forgot_password и reset_password
 * Добавьте этот блок в api/auth.php перед финальным jsonResponse
 */

// ─── FORGOT PASSWORD ──────────────────────────────────────────────────────────
if ($action === 'forgot_password' && $method === 'POST') {
    $email = trim($input['email'] ?? '');
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        jsonResponse(['success' => false, 'message' => 'Некорректный email'], 400);
    }

    $user = $userModel->findByEmail($email);
    // Не раскрываем, существует ли email
    if (!$user) {
        jsonResponse(['success' => true, 'message' => 'Если аккаунт существует, письмо отправлено']);
    }

    $token = $userModel->createPasswordReset($user['id']);

    $resetUrl = APP_URL . '/reset-password.php?token=' . $token;

    if (MAIL_ENABLED) {
        // TODO: реализовать отправку через PHPMailer или mail()
        mail(
            $email,
            'Сброс пароля — ' . APP_NAME,
            "Перейдите по ссылке для сброса пароля:\n$resetUrl\n\nСсылка действительна 1 час."
        );
        jsonResponse(['success' => true]);
    } else {
        // DEV mode: возвращаем токен напрямую
        jsonResponse(['success' => true, 'dev_token' => $token, 'dev_url' => $resetUrl]);
    }
}

// ─── RESET PASSWORD ───────────────────────────────────────────────────────────
if ($action === 'reset_password' && $method === 'POST') {
    $token    = trim($input['token'] ?? '');
    $password = $input['password'] ?? '';

    if (!$token || strlen($password) < 8) {
        jsonResponse(['success' => false, 'message' => 'Некорректные данные'], 400);
    }

    $reset = $userModel->findPasswordReset($token);
    if (!$reset) {
        jsonResponse(['success' => false, 'message' => 'Ссылка недействительна или истекла'], 400);
    }

    $userModel->updatePassword($reset['user_id'], $password);
    $userModel->usePasswordReset($reset['id']);

    jsonResponse(['success' => true, 'message' => 'Пароль успешно изменён']);
}
