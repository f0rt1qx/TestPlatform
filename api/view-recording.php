<?php
/**
 * API endpoint for viewing/downloading recording files with authentication
 */

require_once __DIR__ . '/../src/bootstrap.php';

// Принудительно запускаем сессию ДО любых проверок

// Get recording ID from URL
$recordingId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($recordingId <= 0) {
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Invalid recording ID']);
    exit;
}

try {

    $payload = AuthMiddleware::require();
    $userId = (int)($payload['sub'] ?? 0);
    $userRole = $payload['role'] ?? 'student';

    // Get recording from database
    $recordingModel = new RecordingModel();
    $recording = $recordingModel->findById($recordingId);

    if (!$recording) {
        http_response_code(404);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Recording not found']);
        exit;
    }

    // Check permissions:
    // - Admin can view all recordings
    // - Users can only view their own recordings
    if ($userRole !== 'admin' && $recording['user_id'] != $userId) {
        http_response_code(403);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Access denied']);
        exit;
    }

    // Get full file path
    $fullPath = __DIR__ . '/../' . $recording['file_path'];

    if (!file_exists($fullPath)) {
        http_response_code(404);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'File not found on server']);
        exit;
    }

    $fh = @fopen($fullPath, 'rb');
    $headerBytes = $fh ? fread($fh, 32) : false;
    if ($fh) {
        fclose($fh);
    }

    $isWebm = $headerBytes !== false && strlen($headerBytes) >= 4 && strncmp($headerBytes, "\x1A\x45\xDF\xA3", 4) === 0;
    $isMp4 = $headerBytes !== false && strlen($headerBytes) >= 8 && substr($headerBytes, 4, 4) === 'ftyp';
    if (!$isWebm && !$isMp4) {
        http_response_code(422);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Recording file is corrupted or has unsupported format'
        ]);
        exit;
    }

    // Get file info
    $fileSize = filesize($fullPath);
    $fileType = $isMp4 ? 'video/mp4' : 'video/webm';

    // Set appropriate headers for video streaming
    header('Content-Type: ' . $fileType);
    header('Content-Length: ' . $fileSize);
    header('Accept-Ranges: bytes');
    header('Cache-Control: private, max-age=3600');
    header('Content-Disposition: inline; filename="recording_' . $recordingId . ($isMp4 ? '.mp4' : '.webm') . '"');

    // Handle range requests for video streaming
    if (isset($_SERVER['HTTP_RANGE'])) {
        $range = str_replace('bytes=', '', $_SERVER['HTTP_RANGE']);
        list($start, $end) = explode('-', $range);
        
        $start = intval($start);
        if (!$end) $end = $fileSize - 1;
        else $end = intval($end);
        
        $length = $end - $start + 1;
        
        header('HTTP/1.1 206 Partial Content');
        header('Content-Range: bytes ' . $start . '-' . $end . '/' . $fileSize);
        header('Content-Length: ' . $length);
        
        // Read and output the range
        $fp = fopen($fullPath, 'rb');
        fseek($fp, $start);
        $data = fread($fp, $length);
        fclose($fp);
        echo $data;
    } else {
        // Output entire file
        readfile($fullPath);
    }
    
    exit;

} catch (Exception $e) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage()
    ]);
    exit;
}
