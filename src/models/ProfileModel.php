<?php
/**
 * ProfileModel.php — работа с профилем пользователя
 */

class ProfileModel {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Получить полный профиль пользователя
     */
    public function getProfile(int $userId): ?array {
        $stmt = $this->db->prepare(
            'SELECT id, username, email, avatar, bio, phone, city, website,
                    social_vk, social_tg, birth_date, first_name, last_name,
                    role, created_at, last_visit_at,
                    COALESCE((SELECT COUNT(*) FROM attempts WHERE user_id = users.id), 0) as total_attempts,
                    COALESCE((SELECT COUNT(*) FROM results WHERE user_id = users.id AND passed = 1), 0) as passed_tests,
                    COALESCE((SELECT AVG(percentage) FROM results WHERE user_id = users.id), 0) as avg_score,
                    COALESCE((SELECT SUM(time_spent) FROM results WHERE user_id = users.id), 0) as total_time
             FROM users
             WHERE id = ?'
        );
        $stmt->execute([$userId]);
        return $stmt->fetch() ?: null;
    }

    /**
     * Обновить основные данные профиля
     */
    public function updateProfile(int $userId, array $data): bool {
        $allowed = ['bio', 'phone', 'city', 'website', 'social_vk', 'social_tg', 'birth_date', 'first_name', 'last_name'];
        $fields = [];
        $params = [];

        foreach ($allowed as $field) {
            if (array_key_exists($field, $data)) {
                $fields[] = "$field = :$field";
                $params[":$field"] = $data[$field] ?: null;
            }
        }

        if (empty($fields)) {
            return false;
        }

        $params[':id'] = $userId;
        $stmt = $this->db->prepare(
            "UPDATE users SET " . implode(', ', $fields) . " WHERE id = :id"
        );
        return $stmt->execute($params);
    }

    /**
     * Обновить email
     */
    public function updateEmail(int $userId, string $email): bool {
        $stmt = $this->db->prepare('UPDATE users SET email = ?, email_verified = 0 WHERE id = ?');
        return $stmt->execute([$email, $userId]);
    }

    /**
     * Проверить, занят ли email другим пользователем
     */
    public function isEmailTaken(string $email, int $excludeUserId = 0): bool {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM users WHERE email = ? AND id != ?');
        $stmt->execute([$email, $excludeUserId]);
        return (int)$stmt->fetchColumn() > 0;
    }

    /**
     * Проверить, занят ли username другим пользователем
     */
    public function isUsernameTaken(string $username, int $excludeUserId = 0): bool {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM users WHERE username = ? AND id != ?');
        $stmt->execute([$username, $excludeUserId]);
        return (int)$stmt->fetchColumn() > 0;
    }

    /**
     * Обновить username
     */
    public function updateUsername(int $userId, string $username): bool {
        $stmt = $this->db->prepare('UPDATE users SET username = ? WHERE id = ?');
        return $stmt->execute([$username, $userId]);
    }

    /**
     * Обновить пароль
     */
    public function updatePassword(int $userId, string $passwordHash): bool {
        $stmt = $this->db->prepare('UPDATE users SET password_hash = ? WHERE id = ?');
        return $stmt->execute([$passwordHash, $userId]);
    }

    /**
     * Проверить текущий пароль
     */
    public function verifyPassword(int $userId, string $password): bool {
        $stmt = $this->db->prepare('SELECT password_hash FROM users WHERE id = ?');
        $stmt->execute([$userId]);
        $hash = $stmt->fetchColumn();
        return $hash && password_verify($password, $hash);
    }

    /**
     * Установить аватарку
     */
    public function setAvatar(int $userId, string $avatarPath): bool {
        // Удаляем старую аватарку если есть
        $stmt = $this->db->prepare('SELECT avatar FROM users WHERE id = ?');
        $stmt->execute([$userId]);
        $oldAvatar = $stmt->fetchColumn();

        if ($oldAvatar && file_exists(__DIR__ . '/../' . $oldAvatar)) {
            unlink(__DIR__ . '/../' . $oldAvatar);
        }

        $stmt = $this->db->prepare('UPDATE users SET avatar = ? WHERE id = ?');
        return $stmt->execute([$avatarPath, $userId]);
    }

    /**
     * Удалить аватарку
     */
    public function removeAvatar(int $userId): bool {
        $stmt = $this->db->prepare('SELECT avatar FROM users WHERE id = ?');
        $stmt->execute([$userId]);
        $avatar = $stmt->fetchColumn();

        if ($avatar && file_exists(__DIR__ . '/../' . $avatar)) {
            unlink(__DIR__ . '/../' . $avatar);
        }

        $stmt = $this->db->prepare('UPDATE users SET avatar = NULL WHERE id = ?');
        return $stmt->execute([$userId]);
    }

    /**
     * Получить аватарку пользователя
     */
    public function getAvatar(int $userId): ?string {
        $stmt = $this->db->prepare('SELECT avatar FROM users WHERE id = ?');
        $stmt->execute([$userId]);
        return $stmt->fetchColumn();
    }

    /**
     * Обновить последний визит
     */
    public function updateLastVisit(int $userId): bool {
        $stmt = $this->db->prepare('UPDATE users SET last_visit_at = NOW() WHERE id = ?');
        return $stmt->execute([$userId]);
    }

    /**
     * Получить статистику пользователя
     */
    public function getStatistics(int $userId): array {
        $stmt = $this->db->prepare(
            'SELECT 
                COUNT(DISTINCT a.id) as total_attempts,
                COUNT(DISTINCT r.id) as completed_tests,
                COALESCE(SUM(CASE WHEN r.passed = 1 THEN 1 ELSE 0 END), 0) as passed_tests,
                COALESCE(AVG(r.percentage), 0) as avg_percentage,
                COALESCE(SUM(r.time_spent), 0) as total_time_seconds,
                COALESCE(AVG(r.cheat_score), 0) as avg_cheat_score
             FROM attempts a
             LEFT JOIN results r ON a.id = r.attempt_id
             WHERE a.user_id = ?'
        );
        $stmt->execute([$userId]);
        return $stmt->fetch() ?: [
            'total_attempts' => 0,
            'completed_tests' => 0,
            'passed_tests' => 0,
            'avg_percentage' => 0,
            'total_time_seconds' => 0,
            'avg_cheat_score' => 0,
        ];
    }

    /**
     * Получить последние результаты
     */
    public function getRecentResults(int $userId, int $limit = 5): array {
        $stmt = $this->db->prepare(
            'SELECT r.*, t.title as test_title, a.attempt_number
             FROM results r
             JOIN attempts a ON r.attempt_id = a.id
             JOIN tests t ON a.test_id = t.id
             WHERE r.user_id = ?
             ORDER BY r.created_at DESC
             LIMIT ?'
        );
        $stmt->execute([$userId, $limit]);
        return $stmt->fetchAll();
    }

    /**
     * Получить достижения пользователя
     */
    public function getAchievements(int $userId): array {
        $stats = $this->getStatistics($userId);
        $achievements = [];

        // Достижение: Первый тест
        if ($stats['total_attempts'] >= 1) {
            $achievements[] = [
                'id' => 'first_test',
                'name' => 'Первый шаг',
                'description' => 'Пройти первый тест',
                'icon' => '🎯',
                'unlocked' => true,
            ];
        }

        // Достижение: 10 тестов
        if ($stats['total_attempts'] >= 10) {
            $achievements[] = [
                'id' => 'ten_tests',
                'name' => 'Опытный пользователь',
                'description' => 'Пройти 10 тестов',
                'icon' => '🏆',
                'unlocked' => true,
            ];
        }

        // Достижение: 100% средний балл
        if ($stats['avg_percentage'] >= 100) {
            $achievements[] = [
                'id' => 'perfect_score',
                'name' => 'Перфекционист',
                'description' => 'Средний балл 100%',
                'icon' => '💎',
                'unlocked' => true,
            ];
        }

        // Достижение: 50 пройденных тестов
        if ($stats['passed_tests'] >= 50) {
            $achievements[] = [
                'id' => 'fifty_passed',
                'name' => 'Мастер тестов',
                'description' => 'Пройти 50 тестов успешно',
                'icon' => '👑',
                'unlocked' => true,
            ];
        }

        // Достижение: Честный игрок
        if ($stats['avg_cheat_score'] < 10 && $stats['total_attempts'] >= 5) {
            $achievements[] = [
                'id' => 'honest_player',
                'name' => 'Честный игрок',
                'description' => 'Низкий уровень подозрений',
                'icon' => '✅',
                'unlocked' => true,
            ];
        }

        // Достижение: Скоростной прохождение
        if ($stats['total_time_seconds'] > 0) {
            $achievements[] = [
                'id' => 'time_master',
                'name' => 'Тайм-менеджер',
                'description' => 'Проведено в тестах более ' . floor($stats['total_time_seconds'] / 3600) . ' ч.',
                'icon' => '⏱️',
                'unlocked' => true,
            ];
        }

        return $achievements;
    }

    /**
     * Получить активность по дням (для графика)
     */
    public function getActivityHeatmap(int $userId, int $days = 30): array {
        $stmt = $this->db->prepare(
            'SELECT DATE(created_at) as date, COUNT(*) as count
             FROM results
             WHERE user_id = ? AND created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
             GROUP BY DATE(created_at)
             ORDER BY date ASC'
        );
        $stmt->execute([$userId, $days]);
        return $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    }
}
