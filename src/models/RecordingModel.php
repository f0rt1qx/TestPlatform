<?php

/**
 * RecordingModel - handles screen recording storage and retrieval
 */
class RecordingModel {
    private PDO $db;
    private string $uploadDir;

    public function __construct() {
        $this->db = Database::getInstance();
        $this->uploadDir = __DIR__ . '/../../uploads/recordings';
        
        // Create upload directory if it doesn't exist
        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0755, true);
        }
    }

    /**
     * Save recording chunk or finalize recording
     */
    public function save(array $data): array {
        $attemptId = $data['attempt_id'] ?? null;
        $userId = $data['user_id'] ?? null;
        $chunkIndex = $data['chunk_index'] ?? 0;
        $isFinal = $data['is_final'] ?? false;
        $duration = $data['duration'] ?? 0;

        if (!$attemptId || !$userId) {
            throw new Exception('attempt_id and user_id are required');
        }

        // Generate unique filename
        $filename = 'recording_' . $attemptId . '_' . time() . '.webm';
        $filepath = $this->uploadDir . '/' . $filename;
        $relativePath = 'uploads/recordings/' . $filename;

        // Handle file upload
        if (isset($_FILES['video_chunk']) && $_FILES['video_chunk']['error'] === UPLOAD_ERR_OK) {
            $tmpFile = $_FILES['video_chunk']['tmp_name'];
            
            // Validate file type (allow webm and mp4)
            $allowedTypes = ['video/webm', 'video/mp4', 'application/octet-stream'];
            $fileType = mime_content_type($tmpFile);
            
            if (!in_array($fileType, $allowedTypes)) {
                throw new Exception('Invalid file type. Only WebM and MP4 allowed');
            }

            // Validate file size (max 50MB for final, 10MB for chunks)
            $maxSize = $isFinal ? 50 * 1024 * 1024 : 10 * 1024 * 1024;
            if ($_FILES['video_chunk']['size'] > $maxSize) {
                throw new Exception('File size exceeds limit');
            }

            // Basic container signature validation to avoid storing corrupted files.
            $isTrustedMime = in_array($fileType, ['video/webm', 'video/mp4'], true);
            if (!$isTrustedMime && !$this->isValidVideoContainer($tmpFile)) {
                throw new Exception('Uploaded file is not a valid WebM/MP4 container');
            }

            // Move uploaded file
            if (!move_uploaded_file($tmpFile, $filepath)) {
                throw new Exception('Failed to save recording');
            }

            // Get file size
            $fileSize = filesize($filepath);
        } else {
            throw new Exception('No recording file uploaded');
        }

        // Insert into database
        $stmt = $this->db->prepare(
            "INSERT INTO recordings (attempt_id, user_id, file_path, file_size, chunk_index, is_final, duration, created_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, NOW())"
        );

        $stmt->execute([
            $attemptId,
            $userId,
            $relativePath,
            $fileSize,
            $chunkIndex,
            $isFinal ? 1 : 0,
            $duration
        ]);

        $recordingId = (int)$this->db->lastInsertId();

        return [
            'success' => true,
            'recording_id' => $recordingId,
            'file_path' => $relativePath,
            'file_size' => $fileSize,
            'url' => '/' . $relativePath
        ];
    }

    /**
     * Get all recordings for an attempt
     */
    public function getByAttempt(int $attemptId): array {
        $stmt = $this->db->prepare(
            "SELECT * FROM recordings WHERE attempt_id = ? ORDER BY created_at ASC"
        );
        $stmt->execute([$attemptId]);
        return $stmt->fetchAll();
    }

    /**
     * Get final recording for an attempt
     */
    public function getFinalByAttempt(int $attemptId): ?array {
        $stmt = $this->db->prepare(
            "SELECT * FROM recordings WHERE attempt_id = ? AND is_final = 1 ORDER BY created_at DESC LIMIT 1"
        );
        $stmt->execute([$attemptId]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Get recordings by user
     */
    public function getByUser(int $userId, int $limit = 50): array {
        $stmt = $this->db->prepare(
            "SELECT r.*, t.title as test_title, u.username
             FROM recordings r
             LEFT JOIN attempts a ON r.attempt_id = a.id
             LEFT JOIN tests t ON a.test_id = t.id
             LEFT JOIN users u ON r.user_id = u.id
             WHERE r.user_id = ? AND r.is_final = 1
             ORDER BY r.created_at DESC
             LIMIT ?"
        );
        $stmt->execute([$userId, $limit]);
        return $stmt->fetchAll();
    }

    /**
     * Get all final recordings (admin)
     */
    public function getAllFinal(int $limit = 100, int $offset = 0): array {
        $stmt = $this->db->prepare(
            "SELECT r.*, t.title as test_title, u.username, u.email
             FROM recordings r
             LEFT JOIN attempts a ON r.attempt_id = a.id
             LEFT JOIN tests t ON a.test_id = t.id
             LEFT JOIN users u ON r.user_id = u.id
             WHERE r.is_final = 1
             ORDER BY r.created_at DESC
             LIMIT ? OFFSET ?"
        );
        $stmt->execute([$limit, $offset]);
        return $stmt->fetchAll();
    }

    /**
     * Count final recordings
     */
    public function countFinal(): int {
        $stmt = $this->db->query("SELECT COUNT(*) as cnt FROM recordings WHERE is_final = 1");
        return (int)$stmt->fetch()['cnt'];
    }

    /**
     * Get recording by ID
     */
    public function findById(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM recordings WHERE id = ? LIMIT 1");
        $stmt->execute([$id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Delete a recording
     */
    public function delete(int $id): bool {
        $recording = $this->findById($id);
        if (!$recording) {
            return false;
        }

        // Delete file
        $fullPath = __DIR__ . '/../../' . $recording['file_path'];
        if (file_exists($fullPath)) {
            unlink($fullPath);
        }

        // Delete from database
        $stmt = $this->db->prepare("DELETE FROM recordings WHERE id = ?");
        $stmt->execute([$id]);

        return true;
    }

    /**
     * Delete all recordings for an attempt
     */
    public function deleteByAttempt(int $attemptId): int {
        $recordings = $this->getByAttempt($attemptId);
        $count = 0;

        foreach ($recordings as $recording) {
            // Delete file
            $fullPath = __DIR__ . '/../../' . $recording['file_path'];
            if (file_exists($fullPath)) {
                unlink($fullPath);
            }
            $count++;
        }

        // Delete from database
        $stmt = $this->db->prepare("DELETE FROM recordings WHERE attempt_id = ?");
        $stmt->execute([$attemptId]);

        return $count;
    }

    /**
     * Get statistics
     */
    public function getStats(): array {
        $stmt = $this->db->query(
            "SELECT 
                COUNT(*) as total_recordings,
                SUM(CASE WHEN is_final = 1 THEN 1 ELSE 0 END) as final_recordings,
                SUM(file_size) as total_size,
                AVG(duration) as avg_duration
             FROM recordings"
        );
        return $stmt->fetch() ?: [
            'total_recordings' => 0,
            'final_recordings' => 0,
            'total_size' => 0,
            'avg_duration' => 0
        ];
    }

    /**
     * Validate that uploaded file has WebM/MP4 container signature.
     */
    private function isValidVideoContainer(string $filePath): bool {
        $handle = @fopen($filePath, 'rb');
        if (!$handle) {
            return false;
        }

        $header = fread($handle, 32);
        fclose($handle);

        if ($header === false || strlen($header) < 12) {
            return false;
        }

        // WebM/Matroska EBML header: 1A 45 DF A3
        if (strncmp($header, "\x1A\x45\xDF\xA3", 4) === 0) {
            return true;
        }

        // MP4/ISO BMFF: bytes 4..7 should be "ftyp"
        if (substr($header, 4, 4) === 'ftyp') {
            return true;
        }

        return false;
    }
}
