<?php


require_once __DIR__ . '/../src/bootstrap.php';

setCORSHeaders();
setSecurityHeaders();
header('Content-Type: application/json; charset=utf-8');

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';
$input = json_decode(file_get_contents('php://input'), true) ?? [];

$profileModel = new ProfileModel();


$payload = AuthMiddleware::require();
$userId = (int)$payload['sub'];


if ($action === 'get' && $method === 'GET') {
    $profile = $profileModel->getProfile($userId);
    
    if (!$profile) {
        jsonResponse(['success' => false, 'message' => 'Профиль не найден'], 404);
    }

    
    $profileModel->updateLastVisit($userId);

    
    $stats = $profileModel->getStatistics($userId);
    $achievements = $profileModel->getAchievements($userId);
    $recentResults = $profileModel->getRecentResults($userId, 5);

    jsonResponse([
        'success' => true,
        'profile' => $profile,
        'statistics' => $stats,
        'achievements' => $achievements,
        'recent_results' => $recentResults,
        'csrf_token' => generateCsrfToken(),
    ]);
}


if ($action === 'update' && $method === 'POST') {
    if (!validateCsrfToken($input['csrf_token'] ?? '')) {
        jsonResponse(['success' => false, 'message' => 'CSRF token invalid'], 403);
    }

    $data = [];
    
    
    if (isset($input['bio'])) {
        $bio = trim($input['bio']);
        if (strlen($bio) > 500) {
            jsonResponse(['success' => false, 'message' => 'О себе: максимум 500 символов'], 422);
        }
        $data['bio'] = $bio;
    }

    
    if (isset($input['phone'])) {
        $phone = trim($input['phone']);
        if ($phone && !preg_match('/^[\d\+\-\(\)\s]{10,20}$/', $phone)) {
            jsonResponse(['success' => false, 'message' => 'Некорректный номер телефона'], 422);
        }
        $data['phone'] = $phone;
    }

    
    if (isset($input['city'])) {
        $city = trim($input['city']);
        if (strlen($city) > 100) {
            jsonResponse(['success' => false, 'message' => 'Город: максимум 100 символов'], 422);
        }
        $data['city'] = $city;
    }

    
    if (isset($input['website'])) {
        $website = trim($input['website']);
        if ($website && !filter_var($website, FILTER_VALIDATE_URL)) {
            jsonResponse(['success' => false, 'message' => 'Некорректный URL веб-сайта'], 422);
        }
        $data['website'] = $website;
    }

    
    if (isset($input['social_vk'])) {
        $data['social_vk'] = trim($input['social_vk']);
    }

    
    if (isset($input['social_tg'])) {
        $data['social_tg'] = trim($input['social_tg']);
    }

    
    if (isset($input['birth_date'])) {
        $birthDate = trim($input['birth_date']);
        if ($birthDate && !DateTime::createFromFormat('Y-m-d', $birthDate)) {
            jsonResponse(['success' => false, 'message' => 'Некорректная дата рождения'], 422);
        }
        $data['birth_date'] = $birthDate ?: null;
    }

    
    if (isset($input['first_name'])) {
        $firstName = trim($input['first_name']);
        if (strlen($firstName) > 80) {
            jsonResponse(['success' => false, 'message' => 'Имя: максимум 80 символов'], 422);
        }
        $data['first_name'] = $firstName;
    }

    
    if (isset($input['last_name'])) {
        $lastName = trim($input['last_name']);
        if (strlen($lastName) > 80) {
            jsonResponse(['success' => false, 'message' => 'Фамилия: максимум 80 символов'], 422);
        }
        $data['last_name'] = $lastName;
    }

    if ($profileModel->updateProfile($userId, $data)) {
        jsonResponse(['success' => true, 'message' => 'Профиль обновлён']);
    } else {
        jsonResponse(['success' => false, 'message' => 'Не удалось обновить профиль'], 500);
    }
}


if ($action === 'change_email' && $method === 'POST') {
    if (!validateCsrfToken($input['csrf_token'] ?? '')) {
        jsonResponse(['success' => false, 'message' => 'CSRF token invalid'], 403);
    }

    $email = trim($input['email'] ?? '');

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        jsonResponse(['success' => false, 'message' => 'Некорректный email'], 422);
    }

    if ($profileModel->isEmailTaken($email, $userId)) {
        jsonResponse(['success' => false, 'message' => 'Email уже занят'], 409);
    }

    if ($profileModel->updateEmail($userId, $email)) {
        jsonResponse(['success' => true, 'message' => 'Email обновлён']);
    } else {
        jsonResponse(['success' => false, 'message' => 'Ошибка при обновлении email'], 500);
    }
}


if ($action === 'change_username' && $method === 'POST') {
    if (!validateCsrfToken($input['csrf_token'] ?? '')) {
        jsonResponse(['success' => false, 'message' => 'CSRF token invalid'], 403);
    }

    $username = trim($input['username'] ?? '');

    if (strlen($username) < 3 || strlen($username) > 50) {
        jsonResponse(['success' => false, 'message' => 'Имя пользователя: от 3 до 50 символов'], 422);
    }

    if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        jsonResponse(['success' => false, 'message' => 'Имя пользователя: только латиница, цифры, _'], 422);
    }

    if ($profileModel->isUsernameTaken($username, $userId)) {
        jsonResponse(['success' => false, 'message' => 'Имя пользователя занято'], 409);
    }

    if ($profileModel->updateUsername($userId, $username)) {
        jsonResponse(['success' => true, 'message' => 'Имя пользователя обновлено']);
    } else {
        jsonResponse(['success' => false, 'message' => 'Ошибка при обновлении имени'], 500);
    }
}


if ($action === 'change_password' && $method === 'POST') {
    if (!validateCsrfToken($input['csrf_token'] ?? '')) {
        jsonResponse(['success' => false, 'message' => 'CSRF token invalid'], 403);
    }

    $currentPassword = $input['current_password'] ?? '';
    $newPassword = $input['new_password'] ?? '';
    $confirmPassword = $input['confirm_password'] ?? '';

    if (!$currentPassword || !$newPassword || !$confirmPassword) {
        jsonResponse(['success' => false, 'message' => 'Заполните все поля'], 422);
    }

    if (!$profileModel->verifyPassword($userId, $currentPassword)) {
        jsonResponse(['success' => false, 'message' => 'Неверный текущий пароль'], 401);
    }

    if (strlen($newPassword) < 8) {
        jsonResponse(['success' => false, 'message' => 'Пароль: минимум 8 символов'], 422);
    }

    if ($newPassword !== $confirmPassword) {
        jsonResponse(['success' => false, 'message' => 'Пароли не совпадают'], 422);
    }

    $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);
    if ($profileModel->updatePassword($userId, $passwordHash)) {
        jsonResponse(['success' => true, 'message' => 'Пароль изменён']);
    } else {
        jsonResponse(['success' => false, 'message' => 'Ошибка при смене пароля'], 500);
    }
}


if ($action === 'upload_avatar' && $method === 'POST') {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        jsonResponse(['success' => false, 'message' => 'CSRF token invalid'], 403);
    }

    if (!isset($_FILES['avatar']) || $_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
        jsonResponse(['success' => false, 'message' => 'Файл не загружен'], 400);
    }

    $file = $_FILES['avatar'];
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $maxSize = 5 * 1024 * 1024; 

    
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mimeType, $allowedTypes)) {
        jsonResponse(['success' => false, 'message' => 'Разрешены только JPG, PNG, GIF, WebP'], 400);
    }

    
    if ($file['size'] > $maxSize) {
        jsonResponse(['success' => false, 'message' => 'Максимальный размер 5MB'], 400);
    }

    
    $uploadDir = __DIR__ . '/../uploads/avatars/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $newFilename = 'avatar_' . $userId . '_' . time() . '.' . $extension;
    $destination = $uploadDir . $newFilename;

    
    $profileModel->removeAvatar($userId);

    
    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        jsonResponse(['success' => false, 'message' => 'Ошибка загрузки файла'], 500);
    }

    
    $avatarPath = 'uploads/avatars/' . $newFilename;
    if ($profileModel->setAvatar($userId, $avatarPath)) {
        jsonResponse([
            'success' => true,
            'message' => 'Аватарка загружена',
            'avatar_url' => $avatarPath,
        ]);
    } else {
        unlink($destination);
        jsonResponse(['success' => false, 'message' => 'Ошибка сохранения'], 500);
    }
}


if ($action === 'remove_avatar' && $method === 'POST') {
    if (!validateCsrfToken($input['csrf_token'] ?? '')) {
        jsonResponse(['success' => false, 'message' => 'CSRF token invalid'], 403);
    }

    if ($profileModel->removeAvatar($userId)) {
        jsonResponse(['success' => true, 'message' => 'Аватарка удалена']);
    } else {
        jsonResponse(['success' => false, 'message' => 'Ошибка удаления'], 500);
    }
}


if ($action === 'activity' && $method === 'GET') {
    $days = min((int)($_GET['days'] ?? 30), 365);
    $activity = $profileModel->getActivityHeatmap($userId, $days);
    jsonResponse(['success' => true, 'activity' => $activity, 'days' => $days]);
}

jsonResponse(['success' => false, 'message' => 'Unknown action'], 404);
