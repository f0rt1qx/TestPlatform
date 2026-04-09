var EyeTracker = (function() {

  function EyeTracker(options) {
    this.attemptId       = options.attemptId || null;
    this.onGazeData      = options.onGazeData || null;
    this.onCalibrationComplete = options.onCalibrationComplete || null;
    this.isActive        = false;
    this.isCalibrated      = false;
    this._gazePoints     = [];
    this._fixations      = [];
    this._currentFixation = null;
    this._logInterval    = null;
    this._webgazer       = null;
    this._videoElement   = null;
    this._overlayCanvas   = null;
    this._lastGazeTime   = 0;
    this.FIXATION_DURATION = 100; 
    this.SAMPLE_RATE     = 100; 
  }

  EyeTracker.prototype.start = async function() {
    var self = this;

    try {
      
      var stream = await navigator.mediaDevices.getUserMedia({ video: { width: 320, height: 240 } });
      stream.getTracks().forEach(track => track.stop()); 

      
      if (typeof webgazer === 'undefined') {
        await this._loadWebGazer();
      }

      this._webgazer = webgazer;

      
      webgazer
        .setGazeListener(function(data, elapsedTime) {
          if (!self.isActive || !data) return;
          
          var gazePoint = {
            x: data.x,
            y: data.y,
            timestamp: Date.now(),
            attemptId: self.attemptId
          };

          self._gazePoints.push(gazePoint);
          self._processFixation(gazePoint);

          if (self.onGazeData) {
            self.onGazeData(gazePoint);
          }

          self._lastGazeTime = Date.now();
        })
        .setRegression('ridge')
        .begin();

      
      webgazer.showVideoPreview(false);
      webgazer.showPredictionPoints(false);

      this.isActive = true;

      
      await this._startCalibration();

      
      this._startLogFlush();

      return true;

    } catch (err) {
      console.error('[EyeTracker] Initialization failed:', err);
      this._showError('Не удалось активировать отслеживание взгляда. Проверьте камеру.');
      return false;
    }
  };

  EyeTracker.prototype._loadWebGazer = function() {
    return new Promise(function(resolve, reject) {
      var script = document.createElement('script');
      script.src = 'https:
      script.onload = resolve;
      script.onerror = function() {
        reject(new Error('Failed to load WebGazer.js'));
      };
      document.head.appendChild(script);
    });
  };

  EyeTracker.prototype._startCalibration = function() {
    var self = this;

    
    return new Promise(function(resolve) {
      self._showCalibrationUI(function() {
        self.isCalibrated = true;
        
        
        self._hideCalibrationPoints();
        
        
        self._showGazeOverlay();

        if (self.onCalibrationComplete) {
          self.onCalibrationComplete();
        }

        resolve();
      });
    });
  };

  EyeTracker.prototype._showCalibrationUI = function(onComplete) {
    var self = this;
    var points = [];
    var currentIndex = 0;
    var clickCount = 0;
    var requiredClicks = 5;

    
    var container = document.createElement('div');
    container.id = 'eye-calibration-overlay';
    container.style.cssText = 
      'position:fixed;inset:0;z-index:99998;background:rgba(15,23,42,0.95);' +
      'display:flex;align-items:center;justify-content:center;flex-direction:column;' +
      'font-family:Inter,system-ui,sans-serif;backdrop-filter:blur(8px);';

    container.innerHTML =
      '<div style="text-align:center;max-width:600px;padding:40px;">' +
        '<div style="font-size:4rem;margin-bottom:20px;">👁️</div>' +
        '<h2 style="color:#fff;font-size:1.8rem;font-weight:800;margin-bottom:12px;">' +
          'Калибровка eye-tracking' +
        '</h2>' +
        '<p style="color:#94a3b8;font-size:1rem;line-height:1.6;margin-bottom:8px;">' +
          'Сейчас на экране появятся <strong style="color:#fff;">9 точек</strong>.' +
        '</p>' +
        '<p style="color:#94a3b8;font-size:1rem;line-height:1.6;margin-bottom:32px;">' +
          'Нажмите на каждую точку <strong style="color:#fff;">5 раз</strong>, глядя на неё.' +
        '</p>' +
        '<div id="calibration-progress" style="background:#1e293b;border-radius:12px;height:8px;margin-bottom:20px;overflow:hidden;">' +
          '<div id="calibration-progress-fill" style="background:linear-gradient(90deg,#6366f1,#8b5cf6);height:100%;width:0%;transition:width 0.3s;"></div>' +
        '</div>' +
        '<p id="calibration-hint" style="color:#64748b;font-size:0.9rem;">Точка 1 из 9</p>' +
      '</div>';

    document.body.appendChild(container);

    
    var positions = [
      { x: '15%', y: '15%' }, { x: '50%', y: '15%' }, { x: '85%', y: '15%' },
      { x: '15%', y: '50%' }, { x: '50%', y: '50%' }, { x: '85%', y: '50%' },
      { x: '15%', y: '85%' }, { x: '50%', y: '85%' }, { x: '85%', y: '85%' }
    ];

    positions.forEach(function(pos, idx) {
      var point = document.createElement('div');
      point.id = 'cal-point-' + idx;
      point.style.cssText =
        'position:absolute;width:24px;height:24px;border-radius:50%;' +
        'background:#ef4444;border:3px solid #fff;cursor:pointer;' +
        'left:' + pos.x + ';top:' + pos.y + ';' +
        'transform:translate(-50%,-50%);display:none;' +
        'box-shadow:0 0 20px rgba(239,68,68,0.6);' +
        'transition:transform 0.2s,background 0.2s;';
      
      point.addEventListener('click', function() {
        clickCount++;
        
        
        this.style.transform = 'translate(-50%,-50%) scale(1.3)';
        this.style.background = '#10b981';
        var self = this;
        setTimeout(function() {
          self.style.transform = 'translate(-50%,-50%) scale(1)';
          self.style.background = '#ef4444';
        }, 150);

        if (clickCount >= requiredClicks) {
          clickCount = 0;
          currentIndex++;
          
          
          var progress = (currentIndex / positions.length) * 100;
          var fill = document.getElementById('calibration-progress-fill');
          if (fill) fill.style.width = progress + '%';
          
          var hint = document.getElementById('calibration-hint');
          if (hint) hint.textContent = currentIndex < positions.length ? 
            'Точка ' + (currentIndex + 1) + ' из ' + positions.length : 'Калибровка завершена...';

          
          document.getElementById('cal-point-' + (currentIndex - 1)).style.display = 'none';
          
          if (currentIndex < positions.length) {
            document.getElementById('cal-point-' + currentIndex).style.display = 'block';
          } else {
            
            setTimeout(function() {
              onComplete();
            }, 500);
          }
        }
      });

      container.appendChild(point);
    });

    
    document.getElementById('cal-point-0').style.display = 'block';

    
    this._calibrationContainer = container;
  };

  EyeTracker.prototype._hideCalibrationPoints = function() {
    if (this._calibrationContainer) {
      this._calibrationContainer.style.opacity = '0';
      this._calibrationContainer.style.transition = 'opacity 0.3s';
      setTimeout(() => {
        if (this._calibrationContainer && this._calibrationContainer.parentNode) {
          this._calibrationContainer.remove();
        }
      }, 300);
    }
  };

  EyeTracker.prototype._showGazeOverlay = function() {
    
    var overlay = document.createElement('div');
    overlay.id = 'eye-gaze-indicator';
    overlay.style.cssText =
      'position:fixed;bottom:80px;right:24px;z-index:90;' +
      'background:rgba(15,23,42,0.9);border-radius:12px;padding:12px 16px;' +
      'color:#fff;font-size:0.8rem;font-weight:600;' +
      'backdrop-filter:blur(8px);border:1px solid rgba(255,255,255,0.1);' +
      'display:flex;align-items:center;gap:8px;' +
      'opacity:0;transition:opacity 0.3s;';
    overlay.innerHTML =
      '<div style="width:8px;height:8px;border-radius:50%;background:#10b981;animation:pulse 1.5s infinite;"></div>' +
      '<span>Eye-tracking активен</span>';

    document.body.appendChild(overlay);

    setTimeout(() => {
      overlay.style.opacity = '1';
    }, 100);

    this._gazeOverlay = overlay;
  };

  EyeTracker.prototype._processFixation = function(gazePoint) {
    var self = this;

    if (!this._currentFixation) {
      this._currentFixation = {
        startX: gazePoint.x,
        startY: gazePoint.y,
        startTime: gazePoint.timestamp,
        points: [gazePoint]
      };
    } else {
      var dx = gazePoint.x - this._currentFixation.startX;
      var dy = gazePoint.y - this._currentFixation.startY;
      var distance = Math.sqrt(dx * dx + dy * dy);

      
      if (distance < 50) {
        this._currentFixation.points.push(gazePoint);
        this._currentFixation.endX = gazePoint.x;
        this._currentFixation.endY = gazePoint.y;

        var duration = gazePoint.timestamp - this._currentFixation.startTime;

        if (duration >= this.FIXATION_DURATION) {
          this._currentFixation.duration = duration;
          this._currentFixation.endTime = gazePoint.timestamp;
          this._fixations.push(Object.assign({}, this._currentFixation));
          this._currentFixation = null; 
        }
      } else {
        
        this._currentFixation = null;
      }
    }
  };

  EyeTracker.prototype._startLogFlush = function() {
    var self = this;
    this._logInterval = setInterval(function() {
      self._flushData();
    }, 5000);
  };

  EyeTracker.prototype._flushData = function() {
    if (this._gazePoints.length === 0 && this._fixations.length === 0) return;

    var fixationsToFlush = this._fixations.slice();
    var pointsCount = this._gazePoints.length;

    
    this._fixations = [];

    
    if (fixationsToFlush.length > 0 && this.attemptId) {
      if (typeof API !== 'undefined') {
        API.logEvent({
          attempt_id: this.attemptId,
          event_type: 'eye_fixations',
          data: {
            fixations: fixationsToFlush,
            count: fixationsToFlush.length
          }
        }).catch(function(err) {
          console.error('[EyeTracker] Failed to log fixations:', err);
        });
      }
    }

    
    console.log('[EyeTracker] Flushed:', pointsCount, 'points,', fixationsToFlush.length, 'fixations');
  };

  EyeTracker.prototype.stop = function() {
    this.isActive = false;

    
    this._flushData();

    
    if (this._logInterval) {
      clearInterval(this._logInterval);
    }

    
    if (this._webgazer) {
      try {
        this._webgazer.end();
      } catch (e) {
        console.error('[EyeTracker] Error stopping WebGazer:', e);
      }
    }

    
    if (this._gazeOverlay && this._gazeOverlay.parentNode) {
      this._gazeOverlay.remove();
    }

    console.log('[EyeTracker] Stopped');
  };

  EyeTracker.prototype.getStats = function() {
    var now = Date.now();
    var timeSinceLastGaze = now - this._lastGazeTime;
    var isActive = timeSinceLastGaze < 5000; 

    return {
      totalPoints: this._gazePoints.length,
      totalFixations: this._fixations.length,
      isActive: this.isActive && isActive,
      isCalibrated: this.isCalibrated,
      lastGazeTime: this._lastGazeTime
    };
  };

  EyeTracker.prototype._showError = function(message) {
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
          '<div style="margin-bottom:4px;">Eye-tracking недоступен</div>' +
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

  return EyeTracker;
})();

(function() {
  var style = document.createElement('style');
  style.textContent =
    '@keyframes slideInRight {' +
      'from { transform: translateX(100%); opacity: 0; }' +
      'to { transform: translateX(0); opacity: 1; }' +
    '}' +
    '@keyframes pulse {' +
      '0%, 100% { opacity: 1; transform: scale(1); }' +
      '50% { opacity: 0.5; transform: scale(1.2); }' +
    '}';
  document.head.appendChild(style);
})();