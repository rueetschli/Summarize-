/**
 * Multi-Lecture Audio-Player – Vanilla JS
 * Play/Pause, Seek, Volume, Mute, ±15 s, Prev/Next, Lecture switching
 */
(function () {
    'use strict';

    var cfg = window.PLAYER_CONFIG || {};
    var lectures = cfg.lectures || [];
    var enableDownload = cfg.enableDownload !== false;

    if (!lectures.length) return;

    // ── State ──────────────────────────────────────────────────
    var currentIndex = 0;
    var seeking = false;

    // ── DOM ────────────────────────────────────────────────────
    var nowPlaying  = document.getElementById('nowPlaying');
    var status      = document.getElementById('playerStatus');
    var progressWrap= document.getElementById('progressWrap');
    var progressBar = document.getElementById('progress');
    var bufferedBar = document.getElementById('buffered');
    var thumb       = document.getElementById('thumb');
    var curTimeEl   = document.getElementById('currentTime');
    var durationEl  = document.getElementById('duration');
    var btnPlay     = document.getElementById('btnPlay');
    var iconPlay    = document.getElementById('iconPlay');
    var iconPause   = document.getElementById('iconPause');
    var btnBack15   = document.getElementById('btnBack15');
    var btnFwd15    = document.getElementById('btnFwd15');
    var btnPrev     = document.getElementById('btnPrev');
    var btnNext     = document.getElementById('btnNext');
    var btnMute     = document.getElementById('btnMute');
    var iconVol     = document.getElementById('iconVol');
    var iconMute    = document.getElementById('iconMute');
    var volumeSlider= document.getElementById('volumeSlider');
    var actionsWrap = document.getElementById('playerActions');
    var lectureItems= document.querySelectorAll('.lecture-item');

    // ── Audio element ─────────────────────────────────────────
    var audio = new Audio();
    audio.preload = 'metadata';

    // ── Helpers ───────────────────────────────────────────────
    function fmt(sec) {
        if (!isFinite(sec) || sec < 0) return '0:00';
        var m = Math.floor(sec / 60);
        var s = Math.floor(sec % 60);
        return m + ':' + (s < 10 ? '0' : '') + s;
    }

    function pct(val, max) {
        if (!max) return 0;
        return Math.min(100, Math.max(0, (val / max) * 100));
    }

    function setProgress(percent) {
        progressBar.style.width = percent + '%';
        thumb.style.left = percent + '%';
        progressWrap.setAttribute('aria-valuenow', Math.round(percent));
    }

    function escHtml(str) {
        var d = document.createElement('div');
        d.textContent = str;
        return d.innerHTML;
    }

    // ── Status / Error ────────────────────────────────────────
    function showStatus(msg, isError) {
        status.textContent = msg;
        status.className = 'player__status' + (isError ? ' is-error' : '');
    }

    function hideStatus() {
        status.className = 'player__status is-ready';
    }

    // ── Lecture switching ─────────────────────────────────────
    function loadLecture(index, autoplay) {
        if (index < 0 || index >= lectures.length) return;

        currentIndex = index;
        var lec = lectures[index];

        // Update active state in list
        for (var i = 0; i < lectureItems.length; i++) {
            var item = lectureItems[i];
            if (i === index) {
                item.classList.add('is-active');
                item.setAttribute('aria-current', 'true');
            } else {
                item.classList.remove('is-active');
                item.setAttribute('aria-current', 'false');
            }
        }

        // Update now-playing label
        nowPlaying.textContent = lec.title;

        // Reset progress
        setProgress(0);
        bufferedBar.style.width = '0%';
        curTimeEl.textContent = '0:00';
        durationEl.textContent = '0:00';

        // Show loading
        showStatus('Audio wird geladen…');

        // Load audio
        audio.src = lec.mp3_path;
        audio.load();

        // Reset play icon
        iconPlay.classList.remove('hidden');
        iconPause.classList.add('hidden');
        btnPlay.setAttribute('aria-label', 'Abspielen');

        if (autoplay) {
            audio.play().then(function () {
                iconPlay.classList.add('hidden');
                iconPause.classList.remove('hidden');
                btnPlay.setAttribute('aria-label', 'Pause');
            }).catch(function () {});
        }

        // Update download buttons
        updateActions(lec);

        // Update prev/next states
        btnPrev.disabled = index === 0;
        btnNext.disabled = index === lectures.length - 1;
    }

    function updateActions(lec) {
        var html = '';
        if (enableDownload && lec.mp3_path) {
            html += '<a href="' + escHtml(lec.mp3_path) + '" download class="btn btn--action" aria-label="MP3 herunterladen">' +
                '<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">' +
                '<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/>' +
                '</svg> MP3 herunterladen</a>';
        }
        if (lec.pdf_path) {
            html += '<a href="' + escHtml(lec.pdf_path) + '" download class="btn btn--action btn--accent" aria-label="Handout herunterladen">' +
                '<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">' +
                '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/>' +
                '</svg> Handout herunterladen</a>';
        }
        actionsWrap.innerHTML = html;
    }

    // ── Events: Loading ───────────────────────────────────────
    audio.addEventListener('loadedmetadata', function () {
        durationEl.textContent = fmt(audio.duration);
        hideStatus();
    });

    audio.addEventListener('waiting', function () {
        showStatus('Puffern…');
    });

    audio.addEventListener('canplay', function () {
        hideStatus();
    });

    audio.addEventListener('error', function () {
        var msg = 'Audio konnte nicht geladen werden.';
        if (audio.error) {
            switch (audio.error.code) {
                case 1: msg = 'Wiedergabe abgebrochen.'; break;
                case 2: msg = 'Netzwerkfehler beim Laden.'; break;
                case 3: msg = 'Audio-Format nicht unterstützt.'; break;
                case 4: msg = 'Audio-Datei nicht gefunden.'; break;
            }
        }
        showStatus(msg, true);
    });

    // ── Events: Time / Progress ───────────────────────────────
    audio.addEventListener('timeupdate', function () {
        if (seeking) return;
        curTimeEl.textContent = fmt(audio.currentTime);
        setProgress(pct(audio.currentTime, audio.duration));
    });

    audio.addEventListener('progress', function () {
        if (audio.buffered.length > 0) {
            var end = audio.buffered.end(audio.buffered.length - 1);
            bufferedBar.style.width = pct(end, audio.duration) + '%';
        }
    });

    audio.addEventListener('ended', function () {
        // Auto-advance to next lecture
        if (currentIndex < lectures.length - 1) {
            loadLecture(currentIndex + 1, true);
        } else {
            iconPlay.classList.remove('hidden');
            iconPause.classList.add('hidden');
            btnPlay.setAttribute('aria-label', 'Abspielen');
        }
    });

    // ── Play / Pause ──────────────────────────────────────────
    function togglePlay() {
        if (audio.paused) {
            audio.play();
            iconPlay.classList.add('hidden');
            iconPause.classList.remove('hidden');
            btnPlay.setAttribute('aria-label', 'Pause');
        } else {
            audio.pause();
            iconPlay.classList.remove('hidden');
            iconPause.classList.add('hidden');
            btnPlay.setAttribute('aria-label', 'Abspielen');
        }
    }
    btnPlay.addEventListener('click', togglePlay);

    // ── Prev / Next ───────────────────────────────────────────
    btnPrev.addEventListener('click', function () {
        if (currentIndex > 0) loadLecture(currentIndex - 1, true);
    });
    btnNext.addEventListener('click', function () {
        if (currentIndex < lectures.length - 1) loadLecture(currentIndex + 1, true);
    });

    // ── Lecture list clicks ───────────────────────────────────
    for (var j = 0; j < lectureItems.length; j++) {
        lectureItems[j].addEventListener('click', function () {
            var idx = parseInt(this.getAttribute('data-index'), 10);
            if (idx !== currentIndex) {
                loadLecture(idx, true);
            } else {
                togglePlay();
            }
        });
    }

    // ── Skip ±15s ─────────────────────────────────────────────
    btnBack15.addEventListener('click', function () {
        audio.currentTime = Math.max(0, audio.currentTime - 15);
    });
    btnFwd15.addEventListener('click', function () {
        audio.currentTime = Math.min(audio.duration || 0, audio.currentTime + 15);
    });

    // ── Seek (mouse + touch) ──────────────────────────────────
    function seekTo(e) {
        var rect = progressWrap.getBoundingClientRect();
        var x = (e.touches ? e.touches[0].clientX : e.clientX) - rect.left;
        var ratio = Math.min(1, Math.max(0, x / rect.width));
        if (audio.duration) {
            audio.currentTime = ratio * audio.duration;
            curTimeEl.textContent = fmt(audio.currentTime);
            setProgress(ratio * 100);
        }
    }

    function onSeekStart(e) {
        seeking = true;
        seekTo(e);
        document.addEventListener('mousemove', seekTo);
        document.addEventListener('mouseup', onSeekEnd);
        document.addEventListener('touchmove', seekTo, { passive: false });
        document.addEventListener('touchend', onSeekEnd);
    }
    function onSeekEnd() {
        seeking = false;
        document.removeEventListener('mousemove', seekTo);
        document.removeEventListener('mouseup', onSeekEnd);
        document.removeEventListener('touchmove', seekTo);
        document.removeEventListener('touchend', onSeekEnd);
    }

    progressWrap.addEventListener('mousedown', onSeekStart);
    progressWrap.addEventListener('touchstart', onSeekStart, { passive: true });

    // Keyboard seek on progress bar
    progressWrap.addEventListener('keydown', function (e) {
        if (!audio.duration) return;
        var step = audio.duration * 0.02;
        if (e.key === 'ArrowRight' || e.key === 'ArrowUp') {
            audio.currentTime = Math.min(audio.duration, audio.currentTime + step);
            e.preventDefault();
        } else if (e.key === 'ArrowLeft' || e.key === 'ArrowDown') {
            audio.currentTime = Math.max(0, audio.currentTime - step);
            e.preventDefault();
        }
    });

    // ── Volume & Mute ─────────────────────────────────────────
    var lastVolume = 1;

    volumeSlider.addEventListener('input', function () {
        audio.volume = parseFloat(this.value);
        audio.muted = false;
        updateMuteIcon();
    });

    btnMute.addEventListener('click', function () {
        if (audio.muted || audio.volume === 0) {
            audio.muted = false;
            audio.volume = lastVolume || 0.5;
            volumeSlider.value = audio.volume;
        } else {
            lastVolume = audio.volume;
            audio.muted = true;
        }
        updateMuteIcon();
    });

    function updateMuteIcon() {
        var muted = audio.muted || audio.volume === 0;
        iconVol.classList.toggle('hidden', muted);
        iconMute.classList.toggle('hidden', !muted);
    }

    // ── Keyboard: Space to play/pause ─────────────────────────
    document.addEventListener('keydown', function (e) {
        if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') return;
        if (e.code === 'Space') {
            e.preventDefault();
            togglePlay();
        }
    });

    // ── Init: load first lecture ──────────────────────────────
    loadLecture(0, false);
})();
