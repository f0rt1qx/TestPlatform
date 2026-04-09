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

/**
 * Result object — replaces exception-throwing with structured returns.
 * Functions return Result(success, data, error, httpCode) instead of throwing.
 * Only the top-level converts Result to JSON.
 */
class Result {
    public function __construct(
        public readonly bool $success,
        public readonly mixed $data = null,
        public readonly ?string $error = null,
        public readonly int $httpCode = 200,
    ) {}

    public static function ok(mixed $data = null, int $code = 200): self {
        return new self(success: true, data: $data, httpCode: $code);
    }

    public static function fail(string $error, int $code = 400): self {
        return new self(success: false, error: $error, httpCode: $code);
    }

    public function respond(): never {
        if ($this->success) {
            $payload = ['success' => true];
            if ($this->data !== null) {
                $payload = [...$payload, ...(array)$this->data];
            }
            jsonResponse($payload, $this->httpCode);
        } else {
            jsonResponse(['success' => false, 'message' => $this->error], $this->httpCode);
        }
    }
}

/**
 * Handler functions return Result objects — no exceptions for business logic.
 */
match (true) {
    $action === 'get' && $method === 'GET' => (function () use ($profileModel, $currentUser): Result {
        $profile = $profileModel->getProfile($currentUser);
        if (!$profile) {
            return Result::fail('Профиль не найден', 404);
        }

        $profileModel->updateLastVisit($currentUser);

        $stats           = $profileModel->getStatistics($currentUser);
        $achievements    = $profileModel->getAchievements($currentUser);
        $recentResults   = $profileModel->getRecentResults($currentUser, 5);

        return Result::ok([
            'profile'        => $profile,
            'statistics'     => $stats,
            'achievements'   => $achievements,
            'recent_results' => $recentResults,
            'csrf_token'     => generateCsrfToken(),
        ]);
    })()->respond(),

    $action === 'update' && $method === 'POST' => (function () use ($input, $profileModel, $currentUser): Result {
        if (!validateCsrfToken($input['csrf_token'] ?? '')) {
            return Result::fail('CSRF token invalid', 403);
        }

        $pick = fn(?string $field): string => trim($input[$field] ?? '');

        // Validation via Result — each check returns early on failure
        if (isset($input['bio'])) {
            $bio = $pick('bio');
            if (strlen($bio) > 500) return Result::fail('О себе: максимум 500 символов', 422);
        }
        if (isset($input['phone'])) {
            $phone = $pick('phone');
            if ($phone && !preg_match('/^[\d\+\-\(\)\s]{10,20}$/', $phone)) {
                return Result::fail('Некорректный номер телефона', 422);
            }
        }
        if (isset($input['city'])) {
            $city = $pick('city');
            if (strlen($city) > 100) return Result::fail('Город: максимум 100 символов', 422);
        }
        if (isset($input['website'])) {
            $website = $pick('website');
            if ($website && !filter_var($website, FILTER_VALIDATE_URL)) {
                return Result::fail('Некорректный URL веб-сайта', 422);
            }
        }
        if (isset($input['birth_date'])) {
            $bd = $pick('birth_date');
            if ($bd && !DateTime::createFromFormat('Y-m-d', $bd)) {
                return Result::fail('Некорректная дата рождения', 422);
            }
        }
        if (isset($input['first_name'])) {
            $fn = $pick('first_name');
            if (strlen($fn) > 80) return Result::fail('Имя: максимум 80 символов', 422);
        }
        if (isset($input['last_name'])) {
            $ln = $pick('last_name');
            if (strlen($ln) > 80) return Result::fail('Фамилия: максимум 80 символов', 422);
        }

        $incomingPayload = array_filter([
            'bio'        => isset($input['bio']) ? $pick('bio') : null,
            'phone'      => isset($input['phone']) ? $pick('phone') : null,
            'city'       => isset($input['city']) ? $pick('city') : null,
            'website'    => isset($input['website']) ? $pick('website') : null,
            'social_vk'  => isset($input['social_vk']) ? $pick('social_vk') : null,
            'social_tg'  => isset($input['social_tg']) ? $pick('social_tg') : null,
            'birth_date' => isset($input['birth_date']) ? $pick('birth_date') : null,
            'first_name' => isset($input['first_name']) ? $pick('first_name') : null,
            'last_name'  => isset($input['last_name']) ? $pick('last_name') : null,
        ], fn($v) => $v !== null);

        $updated = $profileModel->updateProfile($currentUser, $incomingPayload);
        return $updated
            ? Result::ok(null, 200)
            : Result::fail('Не удалось обновить профиль', 500);
    })()->respond(),

    $action === 'change_email' && $method === 'POST' => (function () use ($input, $profileModel, $currentUser): Result {
        if (!validateCsrfToken($input['csrf_token'] ?? '')) {
            return Result::fail('CSRF token invalid', 403);
        }

        $email = trim($input['email'] ?? '');
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return Result::fail('Некорректный email', 422);
        }
        if ($profileModel->isEmailTaken($email, $currentUser)) {
            return Result::fail('Email уже занят', 409);
        }

        $updated = $profileModel->updateEmail($currentUser, $email);
        return $updated
            ? Result::ok(null, 200)
            : Result::fail('Ошибка при обновлении email', 500);
    })()->respond(),

    $action === 'change_username' && $method === 'POST' => (function () use ($input, $profileModel, $currentUser): Result {
        if (!validateCsrfToken($input['csrf_token'] ?? '')) {
            return Result::fail('CSRF token invalid', 403);
        }

        $username = trim($input['username'] ?? '');
        if (strlen($username) < 3 || strlen($username) > 50) {
            return Result::fail('Имя пользователя: от 3 до 50 символов', 422);
        }
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
            return Result::fail('Имя пользователя: только латиница, цифры, _', 422);
        }
        if ($profileModel->isUsernameTaken($username, $currentUser)) {
            return Result::fail('Имя пользователя занято', 409);
        }

        $updated = $profileModel->updateUsername($currentUser, $username);
        return $updated
            ? Result::ok(null, 200)
            : Result::fail('Ошибка при обновлении имени', 500);
    })()->respond(),

    $action === 'change_password' && $method === 'POST' => (function () use ($input, $profileModel, $currentUser): Result {
        if (!validateCsrfToken($input['csrf_token'] ?? '')) {
            return Result::fail('CSRF token invalid', 403);
        }

        $currentPassword = $input['current_password'] ?? '';
        $newPassword     = $input['new_password'] ?? '';
        $confirmPassword = $input['confirm_password'] ?? '';

        if (!$currentPassword || !$newPassword || !$confirmPassword) {
            return Result::fail('Заполните все поля', 422);
        }
        if (!$profileModel->verifyPassword($currentUser, $currentPassword)) {
            return Result::fail('Неверный текущий пароль', 401);
        }
        if (strlen($newPassword) < 8) {
            return Result::fail('Пароль: минимум 8 символов', 422);
        }
        if ($newPassword !== $confirmPassword) {
            return Result::fail('Пароли не совпадают', 422);
        }

        $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);
        $updated = $profileModel->updatePassword($currentUser, $passwordHash);
        return $updated
            ? Result::ok(null, 200)
            : Result::fail('Ошибка при смене пароля', 500);
    })()->respond(),

    $action === 'upload_avatar' && $method === 'POST' => (function () use ($profileModel, $currentUser): Result {
        if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
            return Result::fail('CSRF token invalid', 403);
        }
        if (!isset($_FILES['avatar']) || $_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
            return Result::fail('Файл не загружен', 400);
        }

        $file         = $_FILES['avatar'];
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $maxSize      = 5 * 1024 * 1024;

        $finfo    = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mimeType, $allowedTypes, true)) {
            return Result::fail('Разрешены только JPG, PNG, GIF, WebP', 400);
        }
        if ($file['size'] > $maxSize) {
            return Result::fail('Максимальный размер 5MB', 400);
        }

        $uploadDir = __DIR__ . '/../uploads/avatars/';
        is_dir($uploadDir) || mkdir($uploadDir, 0755, true);

        $extension   = pathinfo($file['name'], PATHINFO_EXTENSION);
        $newFilename = 'avatar_' . $currentUser . '_' . time() . '.' . $extension;
        $destination = $uploadDir . $newFilename;

        $profileModel->removeAvatar($currentUser);

        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            return Result::fail('Ошибка загрузки файла', 500);
        }

        $avatarPath = 'uploads/avatars/' . $newFilename;
        $saved = $profileModel->setAvatar($currentUser, $avatarPath);
        if (!$saved) {
            unlink($destination);
            return Result::fail('Ошибка сохранения', 500);
        }

        return Result::ok(['message' => 'Аватарка загружена', 'avatar_url' => $avatarPath]);
    })()->respond(),

    $action === 'remove_avatar' && $method === 'POST' => (function () use ($input, $profileModel, $currentUser): Result {
        if (!validateCsrfToken($input['csrf_token'] ?? '')) {
            return Result::fail('CSRF token invalid', 403);
        }
        $removed = $profileModel->removeAvatar($currentUser);
        return $removed
            ? Result::ok(['message' => 'Аватарка удалена'])
            : Result::fail('Ошибка удаления', 500);
    })()->respond(),

    $action === 'activity' && $method === 'GET' => (function () use ($profileModel, $currentUser): Result {
        $days = min((int)($_GET['days'] ?? 30), 365);
        return Result::ok([
            'activity' => $profileModel->getActivityHeatmap($currentUser, $days),
            'days'     => $days,
        ]);
    })()->respond(),

    default => Result::fail('Unknown action', 404)->respond(),
};
