<?php
/**
 * API endpoint for listing recordings (admin only)
 */

require_once __DIR__ . '/../src/bootstrap.php';

header('Content-Type: application/json; charset=utf-8');

// Only allow GET requests
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    AuthMiddleware::requireAdmin();

    // Get filters
    $attemptId = isset($_GET['attempt_id']) ? (int)$_GET['attempt_id'] : null;
    $userIdFilter = isset($_GET['user_id']) ? (int)$_GET['user_id'] : null;
    $testId = isset($_GET['test_id']) ? (int)$_GET['test_id'] : null;
    $limit = isset($_GET['limit']) ? min((int)$_GET['limit'], 200) : 50;
    $offset = isset($_GET['offset']) ? max((int)$_GET['offset'], 0) : 0;

    // Build query
    $where = ['r.is_final = 1'];
    $params = [];

    if ($attemptId) {
        $where[] = 'r.attempt_id = ?';
        $params[] = $attemptId;
    }
    if ($userIdFilter) {
        $where[] = 'r.user_id = ?';
        $params[] = $userIdFilter;
    }
    if ($testId) {
        $where[] = 'a.test_id = ?';
        $params[] = $testId;
    }

    $whereClause = 'WHERE ' . implode(' AND ', $where);

    // Get total count
    $countStmt = Database::getInstance()->prepare(
        "SELECT COUNT(*) as total
         FROM recordings r
         LEFT JOIN attempts a ON r.attempt_id = a.id
         $whereClause"
    );
    $countStmt->execute($params);
    $total = $countStmt->fetch()['total'];

    // Get recordings
    $stmt = Database::getInstance()->prepare(
        "SELECT r.*, 
                u.username,
                u.email,
                t.title as test_title,
                t.id as test_id,
                a.id as attempt_id,
                a.started_at,
                a.finished_at AS completed_at
         FROM recordings r
         LEFT JOIN users u ON r.user_id = u.id
         LEFT JOIN attempts a ON r.attempt_id = a.id
         LEFT JOIN tests t ON a.test_id = t.id
         $whereClause
         ORDER BY r.created_at DESC
         LIMIT ? OFFSET ?"
    );
    
    $stmt->execute(array_merge($params, [$limit, $offset]));
    $recordings = $stmt->fetchAll();

    // Format data
    $formatted = array_map(function($r) {
        return [
            'id' => (int)$r['id'],
            'attempt_id' => (int)$r['attempt_id'],
            'user_id' => (int)$r['user_id'],
            'username' => $r['username'],
            'email' => $r['email'],
            'test_id' => $r['test_id'],
            'test_title' => $r['test_title'],
            'file_path' => $r['file_path'],
            'file_size' => (int)$r['file_size'],
            'file_size_formatted' => formatFileSize((int)$r['file_size']),
            'duration' => (int)$r['duration'],
            'duration_formatted' => formatDuration((int)$r['duration']),
            'created_at' => $r['created_at'],
            'video_url' => 'api/view-recording.php?id=' . $r['id'],
            'started_at' => $r['started_at'],
            'completed_at' => $r['completed_at']
        ];
    }, $recordings);

    // Get stats
    $recordingModel = new RecordingModel();
    $stats = $recordingModel->getStats();

    echo json_encode([
        'success' => true,
        'recordings' => $formatted,
        'total' => (int)$total,
        'limit' => $limit,
        'offset' => $offset,
        'stats' => $stats
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage()
    ]);
}

function formatFileSize($bytes) {
    if ($bytes === 0) return '0 B';
    $units = ['B', 'KB', 'MB', 'GB'];
    $i = 0;
    while ($bytes >= 1024 && $i < count($units) - 1) {
        $bytes /= 1024;
        $i++;
    }
    return round($bytes, 2) . ' ' . $units[$i];
}

function formatDuration($ms) {
    $seconds = floor($ms / 1000);
    $minutes = floor($seconds / 60);
    $hours = floor($minutes / 60);
    
    if ($hours > 0) {
        return sprintf('%dч %dм %dс', $hours, $minutes % 60, $seconds % 60);
    } elseif ($minutes > 0) {
        return sprintf('%dм %dс', $minutes, $seconds % 60);
    }
    return sprintf('%dс', $seconds);
}
