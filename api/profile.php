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

        $pick = fn(?string $f): string => trim($input[$f] ?? '');

        // Combined validation — single pass through all checks
        $validations = [
            ['bio',         fn() => strlen($pick('bio')) <= 500, 'О себе: максимум 500 символов'],
            ['phone',       fn() => !$pick('phone') || preg_match('/^[\d\+\-\(\)\s]{10,20}$/', $pick('phone')), 'Некорректный номер телефона'],
            ['city',        fn() => strlen($pick('city')) <= 100, 'Город: максимум 100 символов'],
            ['website',     fn() => !$pick('website') || filter_var($pick('website'), FILTER_VALIDATE_URL), 'Некорректный URL веб-сайта'],
            ['birth_date',  fn() => !$pick('birth_date') || DateTime::createFromFormat('Y-m-d', $pick('birth_date')), 'Некорректная дата рождения'],
            ['first_name',  fn() => strlen($pick('first_name')) <= 80, 'Имя: максимум 80 символов'],
            ['last_name',   fn() => strlen($pick('last_name')) <= 80, 'Фамилия: максимум 80 символов'],
        ];
        foreach ($validations as [$field, $check, $msg]) {
            if (isset($input[$field]) && !$check()) return Result::fail($msg, 422);
        }

        $incomingPayload = array_filter([
            'bio'        => isset($input['bio']) ? $pick('bio') : null,
            'phone'      => isset($input['phone']) ? $pick('phone') : null,
            'city'       => isset($input['city']) ? $pick('city') : null,
            'website'    => isset($input['website']) ? $pick('website') : null,
            'social_vk'  => $pick('social_vk') ?: null,
            'social_tg'  => $pick('social_tg') ?: null,
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
        !validateCsrfToken($input['csrf_token'] ?? '') && Result::fail('CSRF token invalid', 403);
        $email = trim($input['email'] ?? '');
        filter_var($email, FILTER_VALIDATE_EMAIL) || Result::fail('Некорректный email', 422);
        $profileModel->isEmailTaken($email, $currentUser) && Result::fail('Email уже занят', 409);
        $updated = $profileModel->updateEmail($currentUser, $email);
        return $updated ? Result::ok() : Result::fail('Ошибка при обновлении email', 500);
    })()->respond(),

    $action === 'change_username' && $method === 'POST' => (function () use ($input, $profileModel, $currentUser): Result {
        !validateCsrfToken($input['csrf_token'] ?? '') && Result::fail('CSRF token invalid', 403);
        $username = trim($input['username'] ?? '');
        (strlen($username) < 3 || strlen($username) > 50) && Result::fail('Имя пользователя: от 3 до 50 символов', 422);
        !preg_match('/^[a-zA-Z0-9_]+$/', $username) && Result::fail('Имя пользователя: только латиница, цифры, _', 422);
        $profileModel->isUsernameTaken($username, $currentUser) && Result::fail('Имя пользователя занято', 409);
        $updated = $profileModel->updateUsername($currentUser, $username);
        return $updated ? Result::ok() : Result::fail('Ошибка при обновлении имени', 500);
    })()->respond(),

    $action === 'change_password' && $method === 'POST' => (function () use ($input, $profileModel, $currentUser): Result {
        !validateCsrfToken($input['csrf_token'] ?? '') && Result::fail('CSRF token invalid', 403);
        [$curPwd, $newPwd, $confirmPwd] = [$input['current_password'] ?? '', $input['new_password'] ?? '', $input['confirm_password'] ?? ''];
        (!$curPwd || !$newPwd || !$confirmPwd) && Result::fail('Заполните все поля', 422);
        !$profileModel->verifyPassword($currentUser, $curPwd) && Result::fail('Неверный текущий пароль', 401);
        strlen($newPwd) < 8 && Result::fail('Пароль: минимум 8 символов', 422);
        $newPwd !== $confirmPwd && Result::fail('Пароли не совпадают', 422);
        $hash = password_hash($newPwd, PASSWORD_DEFAULT);
        $updated = $profileModel->updatePassword($currentUser, $hash);
        return $updated ? Result::ok() : Result::fail('Ошибка при смене пароля', 500);
    })()->respond(),

    $action === 'upload_avatar' && $method === 'POST' => (function () use ($profileModel, $currentUser): Result {
        !validateCsrfToken($_POST['csrf_token'] ?? '') && Result::fail('CSRF token invalid', 403);
        (!isset($_FILES['avatar']) || $_FILES['avatar']['error'] !== UPLOAD_ERR_OK) && Result::fail('Файл не загружен', 400);

        $file = $_FILES['avatar'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        in_array($mimeType, ['image/jpeg', 'image/png', 'image/gif', 'image/webp'], true)
            || Result::fail('Разрешены только JPG, PNG, GIF, WebP', 400);
        $file['size'] <= 5 * 1024 * 1024 || Result::fail('Максимальный размер 5MB', 400);

        $uploadDir = __DIR__ . '/../uploads/avatars/';
        is_dir($uploadDir) || mkdir($uploadDir, 0755, true);

        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $dest = $uploadDir . 'avatar_' . $currentUser . '_' . time() . '.' . $ext;

        $profileModel->removeAvatar($currentUser);
        move_uploaded_file($file['tmp_name'], $dest) || Result::fail('Ошибка загрузки файла', 500);

        $avatarPath = 'uploads/avatars/' . basename($dest);
        $profileModel->setAvatar($currentUser, $avatarPath) || (unlink($dest) && Result::fail('Ошибка сохранения', 500));
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
