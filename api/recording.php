<?php
/**
 * API endpoint for screen recording upload and management
 * Features: chunk upload, validation, security checks, error handling
 */

require_once __DIR__ . '/../src/bootstrap.php';

header('Content-Type: application/json; charset=utf-8');

// Handle DELETE requests for deleting recordings
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    try {
        AuthMiddleware::requireAdmin();

        // Get JSON input
        $input = json_decode(file_get_contents('php://input'), true);
        $recordingId = isset($input['recording_id']) ? (int)$input['recording_id'] : 0;

        if ($recordingId <= 0) {
            throw new Exception('Invalid recording ID');
        }

        // Delete recording
        $recordingModel = new RecordingModel();
        $deleted = $recordingModel->delete($recordingId);

        if ($deleted) {
            echo json_encode([
                'success' => true,
                'message' => 'Recording deleted successfully'
            ]);
        } else {
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'message' => 'Recording not found'
            ]);
        }

    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
    exit;
}

// Only allow POST requests for uploads
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    $authPayload = AuthMiddleware::require();
    $userId = (int)($authPayload['sub'] ?? 0);
    $userRole = $authPayload['role'] ?? 'student';
    $db = Database::getInstance();

    // Get POST data with validation
    $attemptId = isset($_POST['attempt_id']) ? (int)$_POST['attempt_id'] : 0;
    $chunkIndex = isset($_POST['chunk_index']) ? (int)$_POST['chunk_index'] : 0;
    $isFinal = isset($_POST['is_final']) && $_POST['is_final'] === 'true';
    $duration = isset($_POST['duration']) ? (int)$_POST['duration'] : 0;

    // Validate required fields
    if ($attemptId <= 0) {
        throw new Exception('Invalid attempt_id');
    }

    if ($chunkIndex < 0) {
        throw new Exception('Invalid chunk_index');
    }

    // Verify attempt exists and user owns it (or is admin)
    $stmt = $db->prepare(
        "SELECT a.id, a.user_id, a.status, t.id as test_id
         FROM attempts a
         JOIN tests t ON a.test_id = t.id
         WHERE a.id = ?"
    );
    $stmt->execute([$attemptId]);
    $attempt = $stmt->fetch();

    if (!$attempt) {
        throw new Exception('Attempt not found');
    }

    if ((int)$attempt['user_id'] !== $userId && $userRole !== 'admin') {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Access denied']);
        exit;
    }

    // Validate attempt status (allow in_progress and completed)
    if (!in_array($attempt['status'], ['in_progress', 'completed'])) {
        throw new Exception('Invalid attempt status: ' . $attempt['status']);
    }

    // Validate file upload
    if (!isset($_FILES['video_chunk'])) {
        throw new Exception('No video_chunk file provided');
    }

    $file = $_FILES['video_chunk'];

    // Check for upload errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errorMessages = [
            UPLOAD_ERR_INI_SIZE => 'File exceeds server upload_max_filesize',
            UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE directive',
            UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
            UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the file upload'
        ];
        $errorMsg = $errorMessages[$file['error']] ?? 'Unknown upload error';
        throw new Exception($errorMsg);
    }

    // Validate file size
    $maxChunkSize = 10 * 1024 * 1024; // 10MB for chunks
    $maxFinalSize = 100 * 1024 * 1024; // 100MB for final
    $maxSize = $isFinal ? $maxFinalSize : $maxChunkSize;

    if ($file['size'] > $maxSize) {
        $maxMB = $maxSize / 1024 / 1024;
        throw new Exception("File size exceeds {$maxMB}MB limit");
    }

    if ($file['size'] === 0) {
        throw new Exception('Empty file uploaded');
    }

    // Validate file type by MIME type and extension
    $allowedMimes = ['video/webm', 'video/mp4', 'application/octet-stream'];
    $fileMime = mime_content_type($file['tmp_name']);

    if (!in_array($fileMime, $allowedMimes)) {
        throw new Exception("Invalid file type: {$fileMime}. Only WebM and MP4 allowed");
    }

    // Validate file extension matches MIME type
    $pathInfo = pathinfo($file['name']);
    $extension = strtolower($pathInfo['extension'] ?? '');
    $allowedExtensions = ['webm', 'mp4'];

    if (!in_array($extension, $allowedExtensions)) {
        throw new Exception("Invalid file extension: {$extension}");
    }

    // Save recording using RecordingModel
    $recordingModel = new RecordingModel();
    $result = $recordingModel->save([
        'attempt_id' => $attemptId,
        'user_id' => $userId,
        'chunk_index' => $chunkIndex,
        'is_final' => $isFinal,
        'duration' => $duration
    ]);

    // Log recording upload for audit trail
    try {
        $stmt = $db->prepare(
            "INSERT INTO logs (attempt_id, user_id, event_type, event_data, severity, created_at)
             VALUES (?, ?, 'recording_uploaded', ?, 'low', NOW())"
        );
        $stmt->execute([
            $attemptId,
            $userId,
            json_encode([
                'recording_id' => $result['recording_id'],
                'is_final' => $isFinal,
                'chunk_index' => $chunkIndex,
                'file_size' => $result['file_size'],
                'file_size_mb' => round($result['file_size'] / 1024 / 1024, 2),
                'duration' => $duration,
                'duration_sec' => round($duration / 1000, 1)
            ])
        ]);
    } catch (Exception $logError) {
        error_log('Recording log failed: ' . $logError->getMessage());
        // Don't fail the whole upload if logging fails
    }

    // Return success response
    echo json_encode([
        'success' => true,
        'recording_id' => $result['recording_id'],
        'url' => $result['url'],
        'file_size' => $result['file_size'],
        'file_size_mb' => round($result['file_size'] / 1024 / 1024, 2),
        'chunk_index' => $chunkIndex,
        'is_final' => $isFinal,
        'message' => $isFinal ? 'Recording finalized successfully' : 'Chunk uploaded successfully'
    ]);

} catch (Exception $e) {
    error_log('Recording upload error: ' . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'error_type' => 'upload_error'
    ]);
}
