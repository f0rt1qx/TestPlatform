/**
 * Screen Recording Module
 * Records the screen during test attempts using MediaRecorder API
 * Features: chunked upload, retry mechanism, error handling, progress tracking
 */
class ScreenRecorder {
  constructor(options = {}) {
    this.attemptId = options.attemptId;
    this.userId = options.userId;
    this.maxDuration = options.maxDuration || 1800000; // 30 minutes in ms
    this.chunkSize = options.chunkSize || 5 * 1024 * 1024; // 5MB chunks
    this.maxRetries = options.maxRetries || 3;
    this.retryDelay = options.retryDelay || 2000; // 2 seconds
    this.onRecordingStart = options.onRecordingStart || null;
    this.onRecordingStop = options.onRecordingStop || null;
    this.onChunkUpload = options.onChunkUpload || null;
    this.onError = options.onError || null;
    this.onProgress = options.onProgress || null;

    this.mediaRecorder = null;
    this.stream = null;
    this.isRecording = false;
    this.startTime = null;
    this.chunks = [];
    this.chunkIndex = 0;
    this.uploadQueue = [];
    this.isUploading = false;
    this.totalUploaded = 0;
    this.failedUploads = [];
    this.isStopping = false;
    this.stopPromise = null;
    this.stopPromiseResolve = null;
  }

  /**
   * Start screen recording
   * @returns {Promise<boolean>} Success status
   */
  async start() {
    try {
      // Request screen sharing permission
      this.stream = await navigator.mediaDevices.getDisplayMedia({
        video: {
          cursor: 'always',
          displaySurface: 'browser'
        },
        audio: false
      });

      // Create MediaRecorder
      const mimeType = this.getSupportedMimeType();
      this.mediaRecorder = new MediaRecorder(this.stream, {
        mimeType: mimeType,
        videoBitsPerSecond: 2500000 // 2.5 Mbps
      });

      // Handle data available
      this.mediaRecorder.ondataavailable = (event) => {
        if (event.data && event.data.size > 0) {
          this.chunks.push(event.data);
        }
      };

      // Handle recording stop
      this.mediaRecorder.onstop = async () => {
        try {
          await this.handleRecordingStop();
        } finally {
          this.isStopping = false;
          if (this.stopPromiseResolve) {
            this.stopPromiseResolve();
            this.stopPromiseResolve = null;
          }
          this.stopPromise = null;
        }
      };

      // Handle stream ended
      this.stream.getTracks().forEach(track => {
        track.onended = () => {
          if (this.isRecording) {
            console.log('[ScreenRecorder] Stream ended by user');
            this.stop();
          }
        };
      });

      // Start recording without timeslice to get a single finalized container on stop.
      this.mediaRecorder.start();
      this.isRecording = true;
      this.startTime = Date.now();

      console.log('[ScreenRecorder] Recording started');
      
      if (this.onRecordingStart) {
        this.onRecordingStart();
      }

      // Set max duration timer
      setTimeout(() => {
        if (this.isRecording) {
          console.log('[ScreenRecorder] Max duration reached');
          this.stop();
        }
      }, this.maxDuration);

      return true;

    } catch (error) {
      console.error('[ScreenRecorder] Failed to start:', error);
      if (this.onError) this.onError(error);
      return false;
    }
  }

  /**
   * Stop recording
   */
  stop() {
    if (this.stopPromise) {
      return this.stopPromise;
    }

    if (!this.isRecording || !this.mediaRecorder) {
      return Promise.resolve();
    }

    this.isStopping = true;
    this.stopPromise = new Promise((resolve) => {
      this.stopPromiseResolve = resolve;
    });

    try {
      if (this.mediaRecorder.state === 'recording') {
        // Flush final buffered data before stop to avoid truncated output.
        this.mediaRecorder.requestData();
        this.mediaRecorder.stop();
      } else {
        this.isStopping = false;
        if (this.stopPromiseResolve) {
          this.stopPromiseResolve();
          this.stopPromiseResolve = null;
        }
        this.stopPromise = null;
      }

      // Stop all tracks after recorder stop is requested.
      if (this.stream) {
        this.stream.getTracks().forEach(track => track.stop());
      }

      this.isRecording = false;
      console.log('[ScreenRecorder] Recording stopped');

    } catch (error) {
      console.error('[ScreenRecorder] Error stopping:', error);
      this.isStopping = false;
      if (this.stopPromiseResolve) {
        this.stopPromiseResolve();
        this.stopPromiseResolve = null;
      }
      this.stopPromise = null;
    }

    return this.stopPromise || Promise.resolve();
  }

  /**
   * Handle recording stop and upload final data
   */
  async handleRecordingStop() {
    const duration = this.startTime ? Date.now() - this.startTime : 0;

    console.log(`[ScreenRecorder] Processing ${this.chunks.length} chunks, duration: ${duration}ms`);

    // Upload all remaining chunks
    if (this.chunks.length > 0) {
      await this.uploadAllChunks();
    }

    this.chunks = [];
    this.chunkIndex = 0;

    if (this.onRecordingStop) {
      this.onRecordingStop({ duration });
    }
  }

  /**
   * Upload a single chunk with retry mechanism
   * @param {Blob} blob - The chunk data
   * @param {number} retryCount - Current retry attempt (internal use)
   */
  async uploadChunk(blob, retryCount = 0) {
    try {
      const formData = new FormData();
      formData.append('attempt_id', this.attemptId);
      formData.append('user_id', this.userId);
      formData.append('chunk_index', this.chunkIndex);
      formData.append('video_chunk', blob, `recording_${this.attemptId}_${this.chunkIndex}.webm`);
      formData.append('is_final', 'false');

      const controller = new AbortController();
      const timeoutId = setTimeout(() => controller.abort(), 30000); // 30s timeout

      const response = await fetch('api/recording.php', {
        method: 'POST',
        body: formData,
        signal: controller.signal
      });

      clearTimeout(timeoutId);

      if (!response.ok) {
        throw new Error(`HTTP ${response.status}: ${response.statusText}`);
      }

      const result = await response.json();

      if (result.success) {
        console.log(`[ScreenRecorder] Chunk ${this.chunkIndex} uploaded (${(blob.size / 1024).toFixed(1)}KB)`);
        this.totalUploaded += blob.size;
        this.chunkIndex++;

        if (this.onChunkUpload) {
          this.onChunkUpload(this.chunkIndex);
        }

        if (this.onProgress) {
          this.onProgress({
            chunksUploaded: this.chunkIndex,
            totalBytes: this.totalUploaded,
            duration: Date.now() - this.startTime
          });
        }

        return true;
      } else {
        throw new Error(result.message || 'Upload failed');
      }

    } catch (error) {
      console.error(`[ScreenRecorder] Upload failed (attempt ${retryCount + 1}/${this.maxRetries + 1}):`, error.message);

      // Retry if we haven't exceeded max retries
      if (retryCount < this.maxRetries) {
        console.log(`[ScreenRecorder] Retrying in ${this.retryDelay / 1000}s...`);
        await this.sleep(this.retryDelay);
        return this.uploadChunk(blob, retryCount + 1);
      }

      // Store failed chunk for later retry
      this.failedUploads.push({
        blob,
        chunkIndex: this.chunkIndex,
        error: error.message,
        timestamp: Date.now()
      });

      console.error(`[ScreenRecorder] Chunk ${this.chunkIndex} failed after ${this.maxRetries + 1} attempts`);

      if (this.onError) {
        this.onError(new Error(`Chunk upload failed: ${error.message}`));
      }

      return false;
    }
  }

  /**
   * Sleep utility for retry delays
   */
  sleep(ms) {
    return new Promise(resolve => setTimeout(resolve, ms));
  }

  /**
   * Upload all remaining chunks with progress tracking and retry support
   */
  async uploadAllChunks() {
    if (this.chunks.length === 0 && this.failedUploads.length === 0) return;

    console.log(`[ScreenRecorder] Finalizing upload: ${this.chunks.length} chunks pending, ${this.failedUploads.length} failed`);

    // Combine all chunks into single blob
    const combinedBlob = new Blob(this.chunks, { type: 'video/webm' });
    const duration = this.startTime ? Date.now() - this.startTime : 0;

    console.log(`[ScreenRecorder] Combined blob size: ${(combinedBlob.size / 1024 / 1024).toFixed(2)}MB, duration: ${duration}ms`);

    const formData = new FormData();
    formData.append('attempt_id', this.attemptId);
    formData.append('user_id', this.userId);
    formData.append('chunk_index', this.chunkIndex);
    formData.append('video_chunk', combinedBlob, `recording_${this.attemptId}_final.webm`);
    formData.append('is_final', 'true');
    formData.append('duration', duration);

    try {
      const controller = new AbortController();
      const timeoutId = setTimeout(() => controller.abort(), 60000); // 60s timeout for final upload

      const response = await fetch('api/recording.php', {
        method: 'POST',
        body: formData,
        signal: controller.signal
      });

      clearTimeout(timeoutId);

      if (!response.ok) {
        throw new Error(`HTTP ${response.status}: ${response.statusText}`);
      }

      const result = await response.json();

      if (result.success) {
        console.log('[ScreenRecorder] Final recording uploaded successfully');
        this.totalUploaded += combinedBlob.size;

        // Retry failed uploads if any
        if (this.failedUploads.length > 0) {
          console.log(`[ScreenRecorder] Retrying ${this.failedUploads.length} failed chunks...`);
          await this.retryFailedUploads();
        }

        if (this.onProgress) {
          this.onProgress({
            chunksUploaded: this.chunkIndex + 1,
            totalBytes: this.totalUploaded,
            duration: duration,
            isFinal: true
          });
        }

        return true;
      } else {
        throw new Error(result.message || 'Final upload failed');
      }

    } catch (error) {
      console.error('[ScreenRecorder] Final upload error:', error);

      // Store for retry
      this.failedUploads.push({
        blob: combinedBlob,
        chunkIndex: this.chunkIndex,
        isFinal: true,
        error: error.message,
        timestamp: Date.now()
      });

      if (this.onError) {
        this.onError(new Error(`Final upload failed: ${error.message}`));
      }

      return false;
    }
  }

  /**
   * Retry all failed uploads
   */
  async retryFailedUploads() {
    if (this.failedUploads.length === 0) return;

    console.log(`[ScreenRecorder] Retrying ${this.failedUploads.length} failed uploads...`);

    const successfulRetries = [];

    for (let i = 0; i < this.failedUploads.length; i++) {
      const failed = this.failedUploads[i];
      console.log(`[ScreenRecorder] Retrying chunk ${failed.chunkIndex} (${i + 1}/${this.failedUploads.length})...`);

      try {
        const formData = new FormData();
        formData.append('attempt_id', this.attemptId);
        formData.append('user_id', this.userId);
        formData.append('chunk_index', failed.chunkIndex);
        formData.append('video_chunk', failed.blob, `recording_${this.attemptId}_${failed.chunkIndex}${failed.isFinal ? '_final' : ''}.webm`);
        formData.append('is_final', failed.isFinal ? 'true' : 'false');
        if (failed.isFinal) {
          formData.append('duration', failed.timestamp - this.startTime);
        }

        const response = await fetch('api/recording.php', {
          method: 'POST',
          body: formData
        });

        const result = await response.json();

        if (result.success) {
          console.log(`[ScreenRecorder] Chunk ${failed.chunkIndex} retried successfully`);
          successfulRetries.push(i);
          this.totalUploaded += failed.blob.size;
        } else {
          console.error(`[ScreenRecorder] Retry failed for chunk ${failed.chunkIndex}:`, result.message);
        }
      } catch (error) {
        console.error(`[ScreenRecorder] Retry error for chunk ${failed.chunkIndex}:`, error);
      }

      // Small delay between retries to avoid overwhelming the server
      await this.sleep(1000);
    }

    // Remove successful retries from failed list
    for (let i = successfulRetries.length - 1; i >= 0; i--) {
      this.failedUploads.splice(successfulRetries[i], 1);
    }

    console.log(`[ScreenRecorder] Retry complete: ${successfulRetries.length}/${this.failedUploads.length + successfulRetries.length} recovered`);

    return {
      total: this.failedUploads.length + successfulRetries.length,
      recovered: successfulRetries.length,
      remaining: this.failedUploads.length
    };
  }

  /**
   * Get supported MIME type
   */
  getSupportedMimeType() {
    const types = [
      'video/webm;codecs=vp9',
      'video/webm;codecs=vp8',
      'video/webm',
      'video/mp4'
    ];

    for (const type of types) {
      if (MediaRecorder.isTypeSupported(type)) {
        console.log(`[ScreenRecorder] Using MIME type: ${type}`);
        return type;
      }
    }

    console.warn('[ScreenRecorder] No supported MIME type found');
    return '';
  }

  /**
   * Check if screen recording is supported
   */
  static isSupported() {
    return !!(
      navigator.mediaDevices &&
      navigator.mediaDevices.getDisplayMedia &&
      window.MediaRecorder
    );
  }

  /**
   * Get recording status and statistics
   */
  getStatus() {
    return {
      isRecording: this.isRecording,
      duration: this.startTime ? Date.now() - this.startTime : 0,
      chunks: this.chunks.length,
      chunkIndex: this.chunkIndex,
      totalUploaded: this.totalUploaded,
      failedUploads: this.failedUploads.length,
      uploadEfficiency: this.totalUploaded > 0
        ? ((this.totalUploaded - this.failedUploads.reduce((sum, f) => sum + f.blob.size, 0)) / this.totalUploaded * 100).toFixed(1)
        : 100
    };
  }

  /**
   * Destroy recorder and cleanup
   */
  async destroy() {
    if (this.isRecording || this.isStopping) {
      await this.stop();
    }
    this.chunks = [];
    this.stream = null;
    this.mediaRecorder = null;
    this.stopPromise = null;
    this.stopPromiseResolve = null;
    this.isStopping = false;
    console.log('[ScreenRecorder] Destroyed');
  }
}

// Export globally
window.ScreenRecorder = ScreenRecorder;
