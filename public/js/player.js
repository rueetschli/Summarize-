/**
 * Audio-Player – Vanilla JS
 * Play/Pause, Seek, Volume, Mute, ±15 s, Buffering-Anzeige
 */
(function () {
    'use strict';

    var mp3Src = (window.PLAYER_CONFIG && window.PLAYER_CONFIG.mp3) || '';
    if (!mp3Src) return;

    // DOM
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
    var btnMute     = document.getElementById('btnMute');
    var iconVol     = document.getElementById('iconVol');
    var iconMute    = document.getElementById('iconMute');
    var volumeSlider= document.getElementById('volumeSlider');

    // Audio element
    var audio = new Audio();
    audio.preload = 'metadata';
    audio.src = mp3Src;

    var seeking = false;

    // ── Helpers ─────────────────────────────────────────────
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

    // ── Status / Error ──────────────────────────────────────
    function showStatus(msg, isError) {
        status.textContent = msg;
        status.className = 'player__status' + (isError ? ' is-error' : '');
    }

    function hideStatus() {
        status.className = 'player__status is-ready';
    }

    // ── Events: Loading ─────────────────────────────────────
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

    // ── Events: Time / Progress ─────────────────────────────
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
        iconPlay.classList.remove('hidden');
        iconPause.classList.add('hidden');
        btnPlay.setAttribute('aria-label', 'Abspielen');
    });

    // ── Play / Pause ────────────────────────────────────────
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

    // ── Skip ±15s ───────────────────────────────────────────
    btnBack15.addEventListener('click', function () {
        audio.currentTime = Math.max(0, audio.currentTime - 15);
    });
    btnFwd15.addEventListener('click', function () {
        audio.currentTime = Math.min(audio.duration || 0, audio.currentTime + 15);
    });

    // ── Seek (mouse + touch) ────────────────────────────────
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
        var step = audio.duration * 0.02; // 2% per key press
        if (e.key === 'ArrowRight' || e.key === 'ArrowUp') {
            audio.currentTime = Math.min(audio.duration, audio.currentTime + step);
            e.preventDefault();
        } else if (e.key === 'ArrowLeft' || e.key === 'ArrowDown') {
            audio.currentTime = Math.max(0, audio.currentTime - step);
            e.preventDefault();
        }
    });

    // ── Volume & Mute ───────────────────────────────────────
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

    // ── Keyboard: Space to play/pause ───────────────────────
    document.addEventListener('keydown', function (e) {
        if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') return;
        if (e.code === 'Space') {
            e.preventDefault();
            togglePlay();
        }
    });
})();
