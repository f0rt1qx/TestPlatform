<?php

require_once __DIR__ . '/../src/bootstrap.php';

setCORSHeaders();
setSecurityHeaders();
header('Content-Type: application/json; charset=utf-8');

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';
$input  = json_decode(file_get_contents('php://input'), true) ?? [];

$profileModel = new ProfileModel();

$authTokenPayload = AuthMiddleware::require();
$currentUser      = (int)$authTokenPayload['sub'];

match (true) {
    $action === 'get' && $method === 'GET' => (function () use ($profileModel, $currentUser): void {
        $profile = $profileModel->getProfile($currentUser);
        !$profile && jsonResponse(['success' => false, 'message' => 'Профиль не найден'], 404);

        $profileModel->updateLastVisit($currentUser);

        $stats           = $profileModel->getStatistics($currentUser);
        $achievements    = $profileModel->getAchievements($currentUser);
        $recentResults   = $profileModel->getRecentResults($currentUser, 5);

        jsonResponse([
            'success'        => true,
            'profile'        => $profile,
            'statistics'     => $stats,
            'achievements'   => $achievements,
            'recent_results' => $recentResults,
            'csrf_token'     => generateCsrfToken(),
        ]);
    })(),

    $action === 'update' && $method === 'POST' => (function () use ($input, $profileModel, $currentUser): void {
        !validateCsrfToken($input['csrf_token'] ?? '') && jsonResponse(['success' => false, 'message' => 'CSRF token invalid'], 403);

        $pick = fn(?string $field): string => trim($input[$field] ?? '');

        $incomingPayload = array_filter([
            'bio'        => isset($input['bio']) ? tap($pick('bio'), fn($t) => strlen($t) > 500 && jsonResponse(['success' => false, 'message' => 'О себе: максимум 500 символов'], 422)) : null,
            'phone'      => isset($input['phone']) ? tap($pick('phone'), fn($t) => $t && !preg_match('/^[\d\+\-\(\)\s]{10,20}$/', $t) && jsonResponse(['success' => false, 'message' => 'Некорректный номер телефона'], 422)) : null,
            'city'       => isset($input['city']) ? tap($pick('city'), fn($t) => strlen($t) > 100 && jsonResponse(['success' => false, 'message' => 'Город: максимум 100 символов'], 422)) : null,
            'website'    => isset($input['website']) ? tap($pick('website'), fn($t) => $t && !filter_var($t, FILTER_VALIDATE_URL) && jsonResponse(['success' => false, 'message' => 'Некорректный URL веб-сайта'], 422)) : null,
            'social_vk'  => isset($input['social_vk']) ? $pick('social_vk') : null,
            'social_tg'  => isset($input['social_tg']) ? $pick('social_tg') : null,
            'birth_date' => isset($input['birth_date']) ? tap($pick('birth_date'), fn($t) => $t && !DateTime::createFromFormat('Y-m-d', $t) && jsonResponse(['success' => false, 'message' => 'Некорректная дата рождения'], 422)) ?: null : null,
            'first_name' => isset($input['first_name']) ? tap($pick('first_name'), fn($t) => strlen($t) > 80 && jsonResponse(['success' => false, 'message' => 'Имя: максимум 80 символов'], 422)) : null,
            'last_name'  => isset($input['last_name']) ? tap($pick('last_name'), fn($t) => strlen($t) > 80 && jsonResponse(['success' => false, 'message' => 'Фамилия: максимум 80 символов'], 422)) : null,
        ], fn($v) => $v !== null);

        $profileModel->updateProfile($currentUser, $incomingPayload)
            ? jsonResponse(['success' => true, 'message' => 'Профиль обновлён'])
            : jsonResponse(['success' => false, 'message' => 'Не удалось обновить профиль'], 500);
    })(),

    $action === 'change_email' && $method === 'POST' => (function () use ($input, $profileModel, $currentUser): void {
        !validateCsrfToken($input['csrf_token'] ?? '') && jsonResponse(['success' => false, 'message' => 'CSRF token invalid'], 403);

        $email = trim($input['email'] ?? '');
        !filter_var($email, FILTER_VALIDATE_EMAIL) && jsonResponse(['success' => false, 'message' => 'Некорректный email'], 422);
        $profileModel->isEmailTaken($email, $currentUser) && jsonResponse(['success' => false, 'message' => 'Email уже занят'], 409);

        $profileModel->updateEmail($currentUser, $email)
            ? jsonResponse(['success' => true, 'message' => 'Email обновлён'])
            : jsonResponse(['success' => false, 'message' => 'Ошибка при обновлении email'], 500);
    })(),

    $action === 'change_username' && $method === 'POST' => (function () use ($input, $profileModel, $currentUser): void {
        !validateCsrfToken($input['csrf_token'] ?? '') && jsonResponse(['success' => false, 'message' => 'CSRF token invalid'], 403);

        $username = trim($input['username'] ?? '');
        (strlen($username) < 3 || strlen($username) > 50) && jsonResponse(['success' => false, 'message' => 'Имя пользователя: от 3 до 50 символов'], 422);
        !preg_match('/^[a-zA-Z0-9_]+$/', $username) && jsonResponse(['success' => false, 'message' => 'Имя пользователя: только латиница, цифры, _'], 422);
        $profileModel->isUsernameTaken($username, $currentUser) && jsonResponse(['success' => false, 'message' => 'Имя пользователя занято'], 409);

        $profileModel->updateUsername($currentUser, $username)
            ? jsonResponse(['success' => true, 'message' => 'Имя пользователя обновлено'])
            : jsonResponse(['success' => false, 'message' => 'Ошибка при обновлении имени'], 500);
    })(),

    $action === 'change_password' && $method === 'POST' => (function () use ($input, $profileModel, $currentUser): void {
        !validateCsrfToken($input['csrf_token'] ?? '') && jsonResponse(['success' => false, 'message' => 'CSRF token invalid'], 403);

        $currentPassword = $input['current_password'] ?? '';
        $newPassword     = $input['new_password'] ?? '';
        $confirmPassword = $input['confirm_password'] ?? '';

        (!$currentPassword || !$newPassword || !$confirmPassword) && jsonResponse(['success' => false, 'message' => 'Заполните все поля'], 422);
        !$profileModel->verifyPassword($currentUser, $currentPassword) && jsonResponse(['success' => false, 'message' => 'Неверный текущий пароль'], 401);
        strlen($newPassword) < 8 && jsonResponse(['success' => false, 'message' => 'Пароль: минимум 8 символов'], 422);
        $newPassword !== $confirmPassword && jsonResponse(['success' => false, 'message' => 'Пароли не совпадают'], 422);

        $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);
        $profileModel->updatePassword($currentUser, $passwordHash)
            ? jsonResponse(['success' => true, 'message' => 'Пароль изменён'])
            : jsonResponse(['success' => false, 'message' => 'Ошибка при смене пароля'], 500);
    })(),

    $action === 'upload_avatar' && $method === 'POST' => (function () use ($profileModel, $currentUser): void {
        !validateCsrfToken($_POST['csrf_token'] ?? '') && jsonResponse(['success' => false, 'message' => 'CSRF token invalid'], 403);
        (!isset($_FILES['avatar']) || $_FILES['avatar']['error'] !== UPLOAD_ERR_OK) && jsonResponse(['success' => false, 'message' => 'Файл не загружен'], 400);

        $file         = $_FILES['avatar'];
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $maxSize      = 5 * 1024 * 1024;

        $finfo    = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        !in_array($mimeType, $allowedTypes, true) && jsonResponse(['success' => false, 'message' => 'Разрешены только JPG, PNG, GIF, WebP'], 400);
        $file['size'] > $maxSize && jsonResponse(['success' => false, 'message' => 'Максимальный размер 5MB'], 400);

        $uploadDir = __DIR__ . '/../uploads/avatars/';
        is_dir($uploadDir) || mkdir($uploadDir, 0755, true);

        $extension   = pathinfo($file['name'], PATHINFO_EXTENSION);
        $newFilename = 'avatar_' . $currentUser . '_' . time() . '.' . $extension;
        $destination = $uploadDir . $newFilename;

        $profileModel->removeAvatar($currentUser);

        !move_uploaded_file($file['tmp_name'], $destination) && jsonResponse(['success' => false, 'message' => 'Ошибка загрузки файла'], 500);

        $avatarPath = 'uploads/avatars/' . $newFilename;
        if ($profileModel->setAvatar($currentUser, $avatarPath)) {
            jsonResponse(['success' => true, 'message' => 'Аватарка загружена', 'avatar_url' => $avatarPath]);
        }
        unlink($destination);
        jsonResponse(['success' => false, 'message' => 'Ошибка сохранения'], 500);
    })(),

    $action === 'remove_avatar' && $method === 'POST' => (function () use ($input, $profileModel, $currentUser): void {
        !validateCsrfToken($input['csrf_token'] ?? '') && jsonResponse(['success' => false, 'message' => 'CSRF token invalid'], 403);
        $profileModel->removeAvatar($currentUser)
            ? jsonResponse(['success' => true, 'message' => 'Аватарка удалена'])
            : jsonResponse(['success' => false, 'message' => 'Ошибка удаления'], 500);
    })(),

    $action === 'activity' && $method === 'GET' => (function (): void {
        $days = min((int)($_GET['days'] ?? 30), 365);
        jsonResponse(['success' => true, 'activity' => $profileModel->getActivityHeatmap($currentUser, $days), 'days' => $days]);
    })(),

    default => jsonResponse(['success' => false, 'message' => 'Unknown action'], 404),
};
