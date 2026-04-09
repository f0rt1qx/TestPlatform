<?php


class TestModel {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getAll(bool $activeOnly = true): array {
        $where = $activeOnly ? 'WHERE t.is_active = 1' : '';
        $stmt = $this->db->query(
            "SELECT t.*, u.username as creator,
                    (SELECT COUNT(*) FROM questions WHERE test_id = t.id) as question_count
             FROM tests t
             JOIN users u ON t.created_by = u.id
             $where
             ORDER BY t.created_at DESC"
        );
        return $stmt->fetchAll();
    }

    public function findById(int $id): ?array {
        $stmt = $this->db->prepare('SELECT * FROM tests WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    
    public function loadForAttempt(int $testId): ?array {
        $test = $this->findById($testId);
        if (!$test) return null;

        $shuffleQuestions = (bool)$test['shuffle_questions'];
        $shuffleAnswers = (bool)$test['shuffle_answers'];
        
        
        $orderClause = $shuffleQuestions ? 'RAND()' : 'q.order_num ASC';
        $answerOrderClause = $shuffleAnswers ? 'RAND()' : 'a.order_num ASC';
        
        $stmt = $this->db->prepare(
            "SELECT q.id as question_id, q.question_text, q.question_type, q.points, q.order_num as q_order,
                    a.id as answer_id, a.answer_text, a.is_correct, a.order_num as a_order
             FROM questions q
             LEFT JOIN answers a ON q.id = a.question_id
             WHERE q.test_id = ?
             ORDER BY $orderClause, $answerOrderClause"
        );
        $stmt->execute([$testId]);
        $rows = $stmt->fetchAll();

        
        $questions = [];
        $questionMap = [];
        
        foreach ($rows as $row) {
            $qId = $row['question_id'];
            
            if (!isset($questionMap[$qId])) {
                $question = [
                    'id' => $qId,
                    'question_text' => $row['question_text'],
                    'question_type' => $row['question_type'],
                    'points' => $row['points'],
                    'order_num' => $row['q_order'],
                    'answers' => [],
                ];
                $questions[] = $question;
                $questionMap[$qId] = count($questions) - 1;
            }
            
            if ($row['answer_id']) {
                $questions[$questionMap[$qId]]['answers'][] = [
                    'id' => $row['answer_id'],
                    'answer_text' => $row['answer_text'],
                    'order_num' => $row['a_order'],
                ];
            }
        }

        return array_merge($test, ['questions' => $questions]);
    }

    public function getQuestions(int $testId, bool $shuffle = false): array {
        $allowedOrders = ['order_num ASC', 'RAND()'];
        $order = $shuffle ? 'RAND()' : 'order_num ASC';
        
        
        if (!in_array($order, $allowedOrders)) {
            $order = 'order_num ASC';
        }
        
        $stmt = $this->db->prepare(
            "SELECT * FROM questions WHERE test_id = ? ORDER BY $order"
        );
        $stmt->execute([$testId]);
        return $stmt->fetchAll();
    }

    
    public function getQuestionsWithAnswers(int $testId): array {
        $stmt = $this->db->prepare(
            "SELECT q.id as question_id, q.question_text, q.question_type, q.points, q.order_num,
                    a.id as answer_id, a.answer_text, a.is_correct, a.order_num as a_order
             FROM questions q
             LEFT JOIN answers a ON q.id = a.question_id
             WHERE q.test_id = ?
             ORDER BY q.order_num, a.order_num"
        );
        $stmt->execute([$testId]);
        $rows = $stmt->fetchAll();

        $questions = [];
        $questionMap = [];
        
        foreach ($rows as $row) {
            $qId = $row['question_id'];
            
            if (!isset($questionMap[$qId])) {
                $question = [
                    'id' => $qId,
                    'question_text' => $row['question_text'],
                    'question_type' => $row['question_type'],
                    'points' => $row['points'],
                    'order_num' => $row['order_num'],
                    'answers' => [],
                ];
                $questions[] = $question;
                $questionMap[$qId] = count($questions) - 1;
            }
            
            if ($row['answer_id']) {
                $questions[$questionMap[$qId]]['answers'][] = [
                    'id' => $row['answer_id'],
                    'answer_text' => $row['answer_text'],
                    'is_correct' => (bool)$row['is_correct'],
                ];
            }
        }

        return $questions;
    }

    public function getAnswers(int $questionId, bool $shuffle = false): array {
        $order = $shuffle ? 'RAND()' : 'order_num ASC';
        $stmt = $this->db->prepare(
            "SELECT * FROM answers WHERE question_id = ? ORDER BY $order"
        );
        $stmt->execute([$questionId]);
        return $stmt->fetchAll();
    }

    public function create(array $data): int {
        $stmt = $this->db->prepare(
            'INSERT INTO tests (title, description, time_limit, max_attempts, pass_score, shuffle_questions, shuffle_answers, created_by)
             VALUES (:title, :description, :time_limit, :max_attempts, :pass_score, :shuffle_q, :shuffle_a, :created_by)'
        );
        $stmt->execute([
            ':title'       => $data['title'],
            ':description' => $data['description'] ?? '',
            ':time_limit'  => (int)($data['time_limit'] ?? 30),
            ':max_attempts'=> (int)($data['max_attempts'] ?? 1),
            ':pass_score'  => (int)($data['pass_score'] ?? 60),
            ':shuffle_q'   => (int)($data['shuffle_questions'] ?? 1),
            ':shuffle_a'   => (int)($data['shuffle_answers'] ?? 1),
            ':created_by'  => $data['created_by'],
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function addQuestion(int $testId, array $data): int {
        $stmt = $this->db->prepare(
            'INSERT INTO questions (test_id, question_text, question_type, points, order_num)
             VALUES (:test_id, :text, :type, :points, :order_num)'
        );
        $stmt->execute([
            ':test_id'   => $testId,
            ':text'      => $data['question_text'],
            ':type'      => $data['question_type'] ?? 'single',
            ':points'    => (int)($data['points'] ?? 1),
            ':order_num' => (int)($data['order_num'] ?? 0),
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function addAnswer(int $questionId, array $data): void {
        $stmt = $this->db->prepare(
            'INSERT INTO answers (question_id, answer_text, is_correct, order_num)
             VALUES (:qid, :text, :correct, :order_num)'
        );
        $stmt->execute([
            ':qid'      => $questionId,
            ':text'     => $data['answer_text'],
            ':correct'  => (int)($data['is_correct'] ?? 0),
            ':order_num'=> (int)($data['order_num'] ?? 0),
        ]);
    }

    public function getUserAttemptCount(int $userId, int $testId): int {
        $stmt = $this->db->prepare(
            'SELECT COUNT(*) FROM attempts WHERE user_id = ? AND test_id = ? AND status != "in_progress"'
        );
        $stmt->execute([$userId, $testId]);
        return (int)$stmt->fetchColumn();
    }

    public function deleteTest(int $testId): void {
        $stmt = $this->db->prepare('DELETE FROM tests WHERE id = ?');
        $stmt->execute([$testId]);
    }

    public function toggleActive(int $testId, bool $active): void {
        $stmt = $this->db->prepare('UPDATE tests SET is_active = ? WHERE id = ?');
        $stmt->execute([(int)$active, $testId]);
    }
}
