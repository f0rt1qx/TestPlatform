/**
 * Screenshot Capture Module for Testing Platform
 * Automatically captures screenshots during testing sessions
 * Supports manual and automatic capture modes
 */

var ScreenshotCapture = (function() {
  'use strict';

  function ScreenshotCapture(options) {
    this.attemptId = options.attemptId || null;
    this.onScreenshotCaptured = options.onScreenshotCaptured || null;
    this.isActive = false;
    this.captureInterval = null;
    this.screenshots = [];
    this.autoCaptureEnabled = false;
    this.captureFrequency = options.captureFrequency || 30000; // Default: 30 seconds
    this.maxScreenshots = options.maxScreenshots || 50;
    this.quality = options.quality || 0.8; // JPEG quality 0.0 - 1.0
    this.includeMetadata = options.includeMetadata !== false;
    this._canvas = null;
    this._ctx = null;
  }

  /**
   * Initialize screenshot capture system
   */
  ScreenshotCapture.prototype.start = function() {
    var self = this;

    try {
      // Create offscreen canvas for processing
      this._canvas = document.createElement('canvas');
      this._ctx = this._canvas.getContext('2d');

      this.isActive = true;

      // Start auto-capture if enabled
      if (this.autoCaptureEnabled) {
        this.startAutoCapture();
      }

      // Listen for visibility changes to capture when user returns
      document.addEventListener('visibilitychange', function() {
        if (document.visibilityState === 'visible' && self.isActive) {
          self.capture('visibility_change');
        }
      });

      // Listen for focus events
      window.addEventListener('focus', function() {
        if (self.isActive) {
          self.capture('window_focus');
        }
      });

      console.log('[ScreenshotCapture] Started successfully');
      return true;

    } catch (err) {
      console.error('[ScreenshotCapture] Initialization failed:', err);
      this._showError('Не удалось активировать систему скриншотов');
      return false;
    }
  };

  /**
   * Enable automatic screenshot capture
   * @param {number} frequencyMs - Capture frequency in milliseconds
   */
  ScreenshotCapture.prototype.enableAutoCapture = function(frequencyMs) {
    this.autoCaptureEnabled = true;
    if (frequencyMs) {
      this.captureFrequency = frequencyMs;
    }
    
    if (this.isActive) {
      this.startAutoCapture();
    }
    
    console.log('[ScreenshotCapture] Auto-capture enabled:', this.captureFrequency + 'ms');
  };

  /**
   * Disable automatic screenshot capture
   */
  ScreenshotCapture.prototype.disableAutoCapture = function() {
    this.autoCaptureEnabled = false;
    this.stopAutoCapture();
    console.log('[ScreenshotCapture] Auto-capture disabled');
  };

  /**
   * Start automatic capture interval
   */
  ScreenshotCapture.prototype.startAutoCapture = function() {
    var self = this;
    
    this.stopAutoCapture(); // Clear any existing interval
    
    this.captureInterval = setInterval(function() {
      if (self.isActive && document.visibilityState === 'visible') {
        self.capture('auto');
      }
    }, this.captureFrequency);

    console.log('[ScreenshotCapture] Auto-capture interval started');
  };

  /**
   * Stop automatic capture interval
   */
  ScreenshotCapture.prototype.stopAutoCapture = function() {
    if (this.captureInterval) {
      clearInterval(this.captureInterval);
      this.captureInterval = null;
    }
  };

  /**
   * Capture a screenshot
   * @param {string} trigger - What triggered the capture (manual, auto, event, etc.)
   * @param {object} metadata - Additional metadata to include
   * @returns {Promise<object>} - Screenshot data
   */
  ScreenshotCapture.prototype.capture = function(trigger, metadata) {
    var self = this;
    trigger = trigger || 'manual';

    return new Promise(function(resolve, reject) {
      try {
        if (!self.isActive) {
          reject(new Error('Screenshot capture is not active'));
          return;
        }

        // Check screenshot limit
        if (self.screenshots.length >= self.maxScreenshots) {
          console.warn('[ScreenshotCapture] Max screenshots reached, removing oldest');
          self.screenshots.shift(); // Remove oldest
        }

        // Set canvas size to match viewport
        self._canvas.width = window.innerWidth || document.documentElement.clientWidth;
        self._canvas.height = window.innerHeight || document.documentElement.clientHeight;

        // Use html2canvas if available, otherwise use basic approach
        if (typeof html2canvas !== 'undefined') {
          html2canvas(document.body, {
            width: self._canvas.width,
            height: self._canvas.height,
            useCORS: true,
            allowTaint: true,
            backgroundColor: '#ffffff'
          }).then(function(canvas) {
            self._processCanvas(canvas, trigger, metadata, resolve);
          }).catch(function(err) {
            console.error('[ScreenshotCapture] html2canvas error:', err);
            self._fallbackCapture(trigger, metadata, resolve);
          });
        } else {
          // Fallback: basic canvas capture (limited browser support)
          self._fallbackCapture(trigger, metadata, resolve);
        }

      } catch (err) {
        console.error('[ScreenshotCapture] Capture failed:', err);
        reject(err);
      }
    });
  };

  /**
   * Process captured canvas
   */
  ScreenshotCapture.prototype._processCanvas = function(canvas, trigger, metadata, resolve) {
    var self = this;

    try {
      // Convert to blob
      canvas.toBlob(function(blob) {
        var screenshotData = {
          id: 'screenshot_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9),
          attemptId: self.attemptId,
          trigger: trigger,
          timestamp: Date.now(),
          datetime: new Date().toISOString(),
          blob: blob,
          url: URL.createObjectURL(blob),
          width: canvas.width,
          height: canvas.height,
          size: blob.size,
          metadata: self.includeMetadata ? self._collectMetadata(metadata) : {}
        };

        self.screenshots.push(screenshotData);

        // Notify callback
        if (self.onScreenshotCaptured) {
          self.onScreenshotCaptured(screenshotData);
        }

        // Auto-send to API if available
        if (self.attemptId && typeof API !== 'undefined') {
          self._sendToServer(screenshotData);
        }

        console.log('[ScreenshotCapture] Captured:', trigger, '| Size:', (blob.size / 1024).toFixed(2) + 'KB');
        resolve(screenshotData);

      }, 'image/jpeg', this.quality);

    } catch (err) {
      console.error('[ScreenshotCapture] Processing error:', err);
      throw err;
    }
  };

  /**
   * Fallback capture method for browsers without full support
   */
  ScreenshotCapture.prototype._fallbackCapture = function(trigger, metadata, resolve) {
    var self = this;

    try {
      // Draw current state to canvas (basic approach)
      this._ctx.fillStyle = '#f0f0f0';
      this._ctx.fillRect(0, 0, this._canvas.width, this._canvas.height);
      
      // Add text information
      this._ctx.fillStyle = '#333';
      this._ctx.font = '16px Arial';
      this._ctx.fillText('Screenshot captured at: ' + new Date().toLocaleString(), 20, 40);
      this._ctx.fillText('Trigger: ' + trigger, 20, 70);
      this._ctx.fillText('Viewport: ' + this._canvas.width + 'x' + this._canvas.height, 20, 100);

      // Convert to blob
      this._canvas.toBlob(function(blob) {
        var screenshotData = {
          id: 'screenshot_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9),
          attemptId: self.attemptId,
          trigger: trigger,
          timestamp: Date.now(),
          datetime: new Date().toISOString(),
          blob: blob,
          url: URL.createObjectURL(blob),
          width: self._canvas.width,
          height: self._canvas.height,
          size: blob.size,
          metadata: self.includeMetadata ? self._collectMetadata(metadata) : {},
          isFallback: true
        };

        self.screenshots.push(screenshotData);

        if (self.onScreenshotCaptured) {
          self.onScreenshotCaptured(screenshotData);
        }

        console.log('[ScreenshotCapture] Captured (fallback):', trigger);
        resolve(screenshotData);

      }, 'image/jpeg', this.quality);

    } catch (err) {
      console.error('[ScreenshotCapture] Fallback capture failed:', err);
      resolve(null);
    }
  };

  /**
   * Collect metadata about the current state
   */
  ScreenshotCapture.prototype._collectMetadata = function(customMetadata) {
    var metadata = {
      userAgent: navigator.userAgent,
      language: navigator.language,
      platform: navigator.platform,
      screenResolution: screen.width + 'x' + screen.height,
      viewportSize: {
        width: window.innerWidth,
        height: window.innerHeight
      },
      scrollPosition: {
        x: window.scrollX || window.pageXOffset,
        y: window.scrollY || window.pageYOffset
      },
      url: window.location.href,
      title: document.title,
      activeElement: document.activeElement ? document.activeElement.tagName : null,
      timestamp: Date.now()
    };

    if (customMetadata) {
      Object.assign(metadata, customMetadata);
    }

    return metadata;
  };

  /**
   * Send screenshot to server
   */
  ScreenshotCapture.prototype._sendToServer = function(screenshotData) {
    var self = this;

    // Create form data for upload
    var formData = new FormData();
    formData.append('attempt_id', this.attemptId);
    formData.append('screenshot_id', screenshotData.id);
    formData.append('trigger', screenshotData.trigger);
    formData.append('timestamp', screenshotData.timestamp);
    formData.append('metadata', JSON.stringify(screenshotData.metadata));
    formData.append('image', screenshotData.blob, screenshotData.id + '.jpg');

    // Log event first
    if (typeof API !== 'undefined') {
      API.logEvent({
        attempt_id: this.attemptId,
        event_type: 'screenshot_captured',
        data: {
          screenshot_id: screenshotData.id,
          trigger: screenshotData.trigger,
          timestamp: screenshotData.timestamp,
          metadata: screenshotData.metadata
        }
      }).catch(function(err) {
        console.error('[ScreenshotCapture] Failed to log screenshot event:', err);
      });
    }

    // Note: Actual file upload would require a backend endpoint
    // This is a placeholder for the upload logic
    console.log('[ScreenshotCapture] Ready to upload:', screenshotData.id);
  };

  /**
   * Get all captured screenshots
   */
  ScreenshotCapture.prototype.getScreenshots = function() {
    return this.screenshots.slice();
  };

  /**
   * Get screenshot by ID
   */
  ScreenshotCapture.prototype.getScreenshotById = function(id) {
    return this.screenshots.find(function(s) {
      return s.id === id;
    });
  };

  /**
   * Download a screenshot
   */
  ScreenshotCapture.prototype.downloadScreenshot = function(screenshotId) {
    var screenshot = this.getScreenshotById(screenshotId);
    
    if (!screenshot) {
      console.error('[ScreenshotCapture] Screenshot not found:', screenshotId);
      return false;
    }

    var link = document.createElement('a');
    link.href = screenshot.url;
    link.download = 'screenshot_' + screenshot.timestamp + '.jpg';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);

    console.log('[ScreenshotCapture] Downloaded:', screenshotId);
    return true;
  };

  /**
   * Download all screenshots as a zip (requires JSZip library)
   */
  ScreenshotCapture.prototype.downloadAllScreenshots = function() {
    var self = this;

    if (this.screenshots.length === 0) {
      console.warn('[ScreenshotCapture] No screenshots to download');
      return false;
    }

    if (typeof JSZip === 'undefined') {
      console.error('[ScreenshotCapture] JSZip library not loaded');
      this._showError('Для скачивания архива необходима библиотека JSZip');
      return false;
    }

    var zip = new JSZip();
    var folder = zip.folder('screenshots_' + this.attemptId);

    this.screenshots.forEach(function(screenshot) {
      folder.file(screenshot.id + '.jpg', screenshot.blob);
    });

    // Add metadata file
    var metadataContent = JSON.stringify({
      attemptId: this.attemptId,
      totalScreenshots: this.screenshots.length,
      screenshots: this.screenshots.map(function(s) {
        return {
          id: s.id,
          trigger: s.trigger,
          timestamp: s.timestamp,
          datetime: s.datetime,
          metadata: s.metadata
        };
      })
    }, null, 2);

    folder.file('metadata.json', metadataContent);

    zip.generateAsync({ type: 'blob' }).then(function(content) {
      var link = document.createElement('a');
      link.href = URL.createObjectURL(content);
      link.download = 'screenshots_' + self.attemptId + '_' + Date.now() + '.zip';
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
      
      console.log('[ScreenshotCapture] Downloaded all screenshots as ZIP');
    });

    return true;
  };

  /**
   * Clear all stored screenshots
   */
  ScreenshotCapture.prototype.clearScreenshots = function() {
    // Revoke object URLs to free memory
    this.screenshots.forEach(function(s) {
      if (s.url) {
        URL.revokeObjectURL(s.url);
      }
    });
    
    this.screenshots = [];
    console.log('[ScreenshotCapture] All screenshots cleared');
  };

  /**
   * Stop screenshot capture
   */
  ScreenshotCapture.prototype.stop = function() {
    this.isActive = false;
    this.stopAutoCapture();
    
    // Remove event listeners
    document.removeEventListener('visibilitychange', this._visibilityHandler);
    window.removeEventListener('focus', this._focusHandler);

    console.log('[ScreenshotCapture] Stopped');
  };

  /**
   * Get statistics
   */
  ScreenshotCapture.prototype.getStats = function() {
    var now = Date.now();
    var recentScreenshots = this.screenshots.filter(function(s) {
      return now - s.timestamp < 60000; // Last minute
    });

    return {
      totalScreenshots: this.screenshots.length,
      isActive: this.isActive,
      autoCaptureEnabled: this.autoCaptureEnabled,
      captureFrequency: this.captureFrequency,
      maxScreenshots: this.maxScreenshots,
      recentCount: recentScreenshots.length,
      lastCaptureTime: this.screenshots.length > 0 ? 
        this.screenshots[this.screenshots.length - 1].timestamp : null
    };
  };

  /**
   * Show error notification
   */
  ScreenshotCapture.prototype._showError = function(message) {
    var toast = document.createElement('div');
    toast.style.cssText =
      'position:fixed;top:80px;right:24px;z-index:99999;' +
      'background:#fef2f2;border:2px solid #fecaca;border-radius:12px;' +
      'padding:16px 20px;max-width:400px;' +
      'color:#dc2626;font-size:0.9rem;font-weight:600;' +
      'box-shadow:0 10px 30px rgba(0,0,0,0.15);' +
      'animation:slideInRight 0.3s ease;';
    toast.innerHTML = 
      '<div style="display:flex;align-items:center;gap:10px;">' +
        '<span style="font-size:1.5rem;">⚠️</span>' +
        '<div>' +
          '<div style="margin-bottom:4px;">Скриншоты недоступны</div>' +
          '<div style="font-size:0.8rem;font-weight:500;color:#991b1b;">' + message + '</div>' +
        '</div>' +
      '</div>';

    document.body.appendChild(toast);

    setTimeout(function() {
      toast.style.opacity = '0';
      toast.style.transition = 'opacity 0.3s';
      setTimeout(function() { if (toast.parentNode) toast.remove(); }, 300);
    }, 5000);
  };

  return ScreenshotCapture;
})();

// Add CSS animation for error toasts
(function() {
  var style = document.createElement('style');
  style.textContent =
    '@keyframes slideInRight {' +
      'from { transform: translateX(100%); opacity: 0; }' +
      'to { transform: translateX(0); opacity: 1; }' +
    '}';
  document.head.appendChild(style);
})();
