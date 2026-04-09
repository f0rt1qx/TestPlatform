<?php


class ResultModel {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    

    public function createAttempt(int $userId, int $testId): int {
        $count = $this->getAttemptNumber($userId, $testId);
        $stmt = $this->db->prepare(
            'INSERT INTO attempts (user_id, test_id, attempt_number, ip_address, user_agent)
             VALUES (:uid, :tid, :num, :ip, :ua)'
        );
        $stmt->execute([
            ':uid' => $userId,
            ':tid' => $testId,
            ':num' => $count + 1,
            ':ip'  => $_SERVER['REMOTE_ADDR'] ?? '',
            ':ua'  => substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 500),
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function getAttemptNumber(int $userId, int $testId): int {
        $stmt = $this->db->prepare(
            'SELECT COUNT(*) FROM attempts WHERE user_id = ? AND test_id = ?'
        );
        $stmt->execute([$userId, $testId]);
        return (int)$stmt->fetchColumn();
    }

    public function hasDisqualifiedAttempt(int $userId, int $testId): bool {
        $stmt = $this->db->prepare(
            'SELECT COUNT(*) FROM attempts WHERE user_id = ? AND test_id = ? AND status = "abandoned"'
        );
        $stmt->execute([$userId, $testId]);
        return (int)$stmt->fetchColumn() > 0;
    }

    public function markDisqualified(int $attemptId, int $userId, int $testId, int $timeSpent): void {
        $stmt = $this->db->prepare(
            'SELECT COALESCE(SUM(points), 0) FROM questions WHERE test_id = ?'
        );
        $stmt->execute([$testId]);
        $maxScore = (int)$stmt->fetchColumn();

        $stmt = $this->db->prepare(
            'INSERT INTO results
             (attempt_id, user_id, test_id, score, max_score, percentage, passed, answers_json, time_spent, cheat_score)
             VALUES (?, ?, ?, 0, ?, 0, 0, ?, ?, 100)'
        );
        $stmt->execute([
            $attemptId,
            $userId,
            $testId,
            $maxScore,
            json_encode(['disqualified' => true]),
            $timeSpent,
        ]);
    }

    public function getActiveAttempt(int $userId, int $testId): ?array {
        $stmt = $this->db->prepare(
            'SELECT * FROM attempts WHERE user_id = ? AND test_id = ? AND status = "in_progress"
             ORDER BY started_at DESC LIMIT 1'
        );
        $stmt->execute([$userId, $testId]);
        return $stmt->fetch() ?: null;
    }

    public function findAttempt(int $attemptId): ?array {
        $stmt = $this->db->prepare('SELECT * FROM attempts WHERE id = ? LIMIT 1');
        $stmt->execute([$attemptId]);
        return $stmt->fetch() ?: null;
    }

    public function updateAttemptStatus(int $attemptId, string $status): void {
        $stmt = $this->db->prepare(
            'UPDATE attempts SET status = ?, finished_at = NOW() WHERE id = ?'
        );
        $stmt->execute([$status, $attemptId]);
    }

    

    
    public function calculateAndSave(
        int $attemptId,
        int $userId,
        int $testId,
        array $userAnswers,
        array $questions,
        int $timeSpent
    ): array {
        $score = 0;
        $maxScore = 0;
        $answersSnapshot = [];

        foreach ($questions as $q) {
            $maxScore += $q['points'];
            $questionId = (int)$q['id'];
            
            
            $userAnswer = $userAnswers[$questionId] ?? [];
            $givenIds = array_map('intval', (array)$userAnswer);
            sort($givenIds);

            
            $correctIds = [];
            foreach ($q['answers'] as $a) {
                if ($a['is_correct']) {
                    $correctIds[] = (int)$a['id'];
                }
            }
            sort($correctIds);

            
            $isCorrect = array_values($givenIds) === array_values($correctIds);
            
            if ($isCorrect) {
                $score += $q['points'];
            }

            $answersSnapshot[$questionId] = [
                'given'      => $givenIds,
                'correct'    => array_values($correctIds),
                'is_correct' => $isCorrect,
                'points'     => $isCorrect ? $q['points'] : 0,
            ];
        }

        $percentage = $maxScore > 0 ? round($score / $maxScore * 100, 2) : 0;
        $test = (new TestModel())->findById($testId);
        $passed = $percentage >= ($test['pass_score'] ?? 60);

        $cheatScore = $this->calculateCheatScore($attemptId);

        $stmt = $this->db->prepare(
            'INSERT INTO results (attempt_id, user_id, test_id, score, max_score, percentage, passed, answers_json, time_spent, cheat_score)
             VALUES (:aid, :uid, :tid, :score, :max, :pct, :passed, :answers, :time, :cheat)'
        );
        $stmt->execute([
            ':aid'     => $attemptId,
            ':uid'     => $userId,
            ':tid'     => $testId,
            ':score'   => $score,
            ':max'     => $maxScore,
            ':pct'     => $percentage,
            ':passed'  => (int)$passed,
            ':answers' => json_encode($answersSnapshot, JSON_UNESCAPED_UNICODE),
            ':time'    => $timeSpent,
            ':cheat'   => $cheatScore,
        ]);

        $newStatus = ($cheatScore >= ANTICHEAT_CHEAT_THRESHOLD) ? 'flagged' : 'completed';
        $this->updateAttemptStatus($attemptId, $newStatus);

        return [
            'score'       => $score,
            'max_score'   => $maxScore,
            'percentage'  => $percentage,
            'passed'      => $passed,
            'cheat_score' => $cheatScore,
            'answers'     => $answersSnapshot,
        ];
    }

    
    private function calculateCheatScore(int $attemptId): int {
        $stmt = $this->db->prepare(
            'SELECT event_type, severity, COUNT(*) as cnt FROM logs WHERE attempt_id = ? GROUP BY event_type, severity'
        );
        $stmt->execute([$attemptId]);
        $events = $stmt->fetchAll();

        
        $weights = [
            'high'   => 15,
            'medium' => 7,
            'low'    => 3,
        ];

        $score = 0;
        foreach ($events as $e) {
            $weight = $weights[$e['severity']] ?? 3;
            
            $score += min((int)$e['cnt'] * $weight, 30);
        }
        
        
        return min($score, 100);
    }

    public function getUserResults(int $userId): array {
        $stmt = $this->db->prepare(
            'SELECT r.*, t.title as test_title, a.attempt_number, a.started_at as attempt_started
             FROM results r
             JOIN tests t ON r.test_id = t.id
             JOIN attempts a ON r.attempt_id = a.id
             WHERE r.user_id = ?
             ORDER BY r.created_at DESC'
        );
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public function findResult(int $attemptId): ?array {
        $stmt = $this->db->prepare('SELECT * FROM results WHERE attempt_id = ? LIMIT 1');
        $stmt->execute([$attemptId]);
        return $stmt->fetch() ?: null;
    }

    
    public function findResultWithDetails(int $attemptId): ?array {
        $stmt = $this->db->prepare(
            'SELECT r.*, u.username, u.email, t.title as test_title, t.pass_score, a.attempt_number
             FROM results r
             JOIN users u ON r.user_id = u.id
             JOIN tests t ON r.test_id = t.id
             JOIN attempts a ON r.attempt_id = a.id
             WHERE r.attempt_id = ?
             LIMIT 1'
        );
        $stmt->execute([$attemptId]);
        return $stmt->fetch() ?: null;
    }

    

    public function logEvent(?int $attemptId, int $userId, string $eventType, array $data = [], string $severity = 'low'): void {
        
        $cleanData = $this->sanitizeData($data);

        $stmt = $this->db->prepare(
            'INSERT INTO logs (attempt_id, user_id, event_type, event_data, severity)
             VALUES (:aid, :uid, :type, :data, :sev)'
        );
        $stmt->execute([
            ':aid'  => $attemptId,
            ':uid'  => $userId,
            ':type' => $eventType,
            ':data' => json_encode($cleanData, JSON_UNESCAPED_UNICODE),
            ':sev'  => $severity,
        ]);
    }

    
    private function sanitizeData(array $data): array {
        $result = [];
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $result[$key] = $this->sanitizeData($value);
            } elseif (is_string($value)) {
                $result[$key] = htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
            } else {
                $result[$key] = $value;
            }
        }
        return $result;
    }

    public function getLogs(int $attemptId): array {
        $stmt = $this->db->prepare(
            'SELECT l.*, u.username FROM logs l JOIN users u ON l.user_id = u.id
             WHERE l.attempt_id = ? ORDER BY l.created_at'
        );
        $stmt->execute([$attemptId]);
        return $stmt->fetchAll();
    }

    public function getAllLogs(int $limit = 200): array {
        $stmt = $this->db->prepare(
            'SELECT l.*, u.username, t.title as test_title
             FROM logs l
             JOIN users u ON l.user_id = u.id
             JOIN attempts a ON l.attempt_id = a.id
             JOIN tests t ON a.test_id = t.id
             ORDER BY l.created_at DESC
             LIMIT ?'
        );
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getEyeTrackingLogs(int $limit = 500, ?int $testId = null, ?int $attemptId = null): array {
        $sql = 'SELECT l.*, u.username, t.title as test_title
                FROM logs l
                JOIN users u ON l.user_id = u.id
                JOIN attempts a ON l.attempt_id = a.id
                JOIN tests t ON a.test_id = t.id
                WHERE l.event_type = :eye_type';
        
        $params = [':eye_type' => 'eye_fixations'];
        
        if ($testId !== null) {
            $sql .= ' AND t.id = :test_id';
            $params[':test_id'] = $testId;
        }
        
        if ($attemptId !== null) {
            $sql .= ' AND l.attempt_id = :attempt_id';
            $params[':attempt_id'] = $attemptId;
        }
        
        $sql .= ' ORDER BY l.created_at DESC LIMIT :limit';
        $params[':limit'] = $limit;
        
        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            if ($key === ':limit') {
                $stmt->bindValue($key, $value, PDO::PARAM_INT);
            } else {
                $stmt->bindValue($key, $value);
            }
        }
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getAllResults(int $limit = 200): array {
        $stmt = $this->db->prepare(
            'SELECT r.*, u.username, u.email, t.title as test_title, a.attempt_number
             FROM results r
             JOIN users u ON r.user_id = u.id
             JOIN tests t ON r.test_id = t.id
             JOIN attempts a ON r.attempt_id = a.id
             ORDER BY r.created_at DESC LIMIT ?'
        );
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
