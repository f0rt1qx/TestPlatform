<?php


class UserModel {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function findByEmail(string $email): ?array {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([strtolower($email)]);
        return $stmt->fetch() ?: null;
    }

    public function findByUsername(string $username): ?array {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE username = ? LIMIT 1');
        $stmt->execute([$username]);
        return $stmt->fetch() ?: null;
    }

    public function findById(int $id): ?array {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function create(array $data): int {
        $stmt = $this->db->prepare(
            'INSERT INTO users (username, email, password_hash, first_name, last_name, role, email_verified)
             VALUES (:username, :email, :password_hash, :first_name, :last_name, :role, :email_verified)'
        );
        $stmt->execute([
            ':username'       => $data['username'],
            ':email'          => strtolower($data['email']), 
            ':password_hash'  => password_hash($data['password'], PASSWORD_BCRYPT, ['cost' => BCRYPT_COST]),
            ':first_name'     => $data['first_name'] ?? '',
            ':last_name'      => $data['last_name'] ?? '',
            ':role'           => $data['role'] ?? 'student',
            ':email_verified' => MAIL_ENABLED ? 0 : 1,
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function verifyPassword(string $plain, string $hash): bool {
        return password_verify($plain, $hash);
    }

    public function updatePassword(int $userId, string $newPassword): void {
        $stmt = $this->db->prepare('UPDATE users SET password_hash = ? WHERE id = ?');
        $stmt->execute([
            password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => BCRYPT_COST]),
            $userId
        ]);
    }

    public function setEmailVerified(int $userId): void {
        $stmt = $this->db->prepare('UPDATE users SET email_verified = 1 WHERE id = ?');
        $stmt->execute([$userId]);
    }

    public function getAll(int $limit = 50, int $offset = 0): array {
        $stmt = $this->db->prepare(
            'SELECT id, username, email, role, first_name, last_name, is_active, is_blocked, email_verified, created_at
             FROM users ORDER BY created_at DESC LIMIT :limit OFFSET :offset'
        );
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function toggleBlock(int $userId, bool $block): void {
        $stmt = $this->db->prepare('UPDATE users SET is_blocked = ? WHERE id = ?');
        $stmt->execute([(int)$block, $userId]);
    }

    public function createPasswordReset(int $userId): string {
        $token = bin2hex(random_bytes(32));
        $stmt = $this->db->prepare(
            'INSERT INTO password_resets (user_id, token, expires_at) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 1 HOUR))'
        );
        $stmt->execute([$userId, $token]);
        return $token;
    }

    public function findPasswordReset(string $token): ?array {
        $stmt = $this->db->prepare(
            'SELECT * FROM password_resets WHERE token = ? AND used = 0 AND expires_at > NOW() LIMIT 1'
        );
        $stmt->execute([$token]);
        return $stmt->fetch() ?: null;
    }

    public function usePasswordReset(int $id): void {
        $stmt = $this->db->prepare('UPDATE password_resets SET used = 1 WHERE id = ?');
        $stmt->execute([$id]);
    }

    public function createEmailVerification(int $userId): string {
        $token = bin2hex(random_bytes(32));
        $stmt = $this->db->prepare(
            'INSERT INTO email_verifications (user_id, token, expires_at)
             VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 24 HOUR))
             ON DUPLICATE KEY UPDATE token = VALUES(token), expires_at = VALUES(expires_at)'
        );
        $stmt->execute([$userId, $token]);
        return $token;
    }

    public function getDashboardStats(int $userId): array {
        $pdo = $this->db;

        $stats = $pdo->prepare(
            'SELECT 
                COUNT(*) as tests_taken,
                COALESCE(AVG(percentage), 0) as avg_score,
                SUM(CASE WHEN passed = 1 THEN 1 ELSE 0 END) as passed_count
             FROM results WHERE user_id = ?'
        );
        $stats->execute([$userId]);
        $row = $stats->fetch();

        return [
            'tests_taken' => (int)$row['tests_taken'],
            'avg_score'   => round((float)$row['avg_score'], 1),
            'passed'      => (int)$row['passed_count'],
        ];
    }

    
    public function updateLastLogin(int $userId): void {
        $stmt = $this->db->prepare(
            'UPDATE users SET last_login = NOW() WHERE id = ?'
        );
        $stmt->execute([$userId]);
    }
}
