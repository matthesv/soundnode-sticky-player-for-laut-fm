/**
 * Laut.fm Sticky Player - Frontend JavaScript
 *
 * @package LFSP
 */
(function () {
    'use strict';

    // State
    let audio      = null;
    let isPlaying  = false;
    let isMuted    = false;
    let songTimer  = null;
    let clockTimer = null;

    // DOM Elements
    const els = {};

    /**
     * Initialisierung
     */
    function init() {
        // DOM-Referenzen cachen
        els.wrapper      = document.getElementById('lfsp-sticky-wrapper');
        els.playBtn      = document.getElementById('lfsp-play-btn');
        els.toggleBtn    = document.getElementById('lfsp-toggle-btn');
        els.songTitle    = document.getElementById('lfsp-song-title');
        els.clock        = document.getElementById('lfsp-clock');
        els.volumeSlider = document.getElementById('lfsp-volume-slider');
        els.muteBtn      = document.getElementById('lfsp-mute-btn');
        els.iconPlay     = els.playBtn ? els.playBtn.querySelector('.lfsp-icon-play') : null;
        els.iconPause    = els.playBtn ? els.playBtn.querySelector('.lfsp-icon-pause') : null;

        if (!els.wrapper) return;

        // Audio Element erstellen
        audio = new Audio();
        audio.preload = 'none';
        audio.volume  = 0.8;

        // Event Listeners (kein inline onclick!)
        if (els.playBtn) {
            els.playBtn.addEventListener('click', togglePlay);
        }

        if (els.toggleBtn) {
            els.toggleBtn.addEventListener('click', togglePlayer);
        }

        if (els.volumeSlider) {
            els.volumeSlider.addEventListener('input', handleVolume);
        }

        if (els.muteBtn) {
            els.muteBtn.addEventListener('click', toggleMute);
        }

        // Gespeicherter Zustand (localStorage für Player-State)
        restorePlayerState();

        // Uhr starten
        updateClock();
        clockTimer = setInterval(updateClock, 1000);

        // Song-Daten laden
        fetchSongData();
        songTimer = setInterval(fetchSongData, lfspConfig.updateInterval || 30000);

        // Autoplay (wird von Browsern blockiert ohne User-Interaktion)
        if (lfspConfig.autoplay) {
            play();
        }

        // Keyboard Support
        document.addEventListener('keydown', handleKeyboard);
    }

    /**
     * Play
     */
    function play() {
        if (!audio || !lfspConfig.streamUrl) return;

        audio.src = lfspConfig.streamUrl;
        audio.load();

        audio.play()
            .then(function () {
                isPlaying = true;
                updatePlayButton(true);
            })
            .catch(function (err) {
                console.warn('LFSP: Playback blocked -', err.message);
            });
    }

    /**
     * Pause
     */
    function pause() {
        if (!audio) return;

        audio.pause();
        audio.src = ''; // Stream stoppen
        isPlaying = false;
        updatePlayButton(false);
    }

    /**
     * Toggle Play/Pause
     */
    function togglePlay() {
        if (isPlaying) {
            pause();
        } else {
            play();
        }
    }

    /**
     * Play-Button UI aktualisieren
     */
    function updatePlayButton(playing) {
        if (els.iconPlay) {
            els.iconPlay.style.display = playing ? 'none' : 'block';
        }
        if (els.iconPause) {
            els.iconPause.style.display = playing ? 'block' : 'none';
        }
        if (els.playBtn) {
            els.playBtn.setAttribute('aria-label',
                playing ? lfspConfig.i18n.pause : lfspConfig.i18n.play
            );
            els.playBtn.classList.toggle('lfsp-playing', playing);
        }
    }

    /**
     * Volume Handler
     */
    function handleVolume() {
        if (!audio || !els.volumeSlider) return;
        audio.volume = els.volumeSlider.value / 100;

        // Unmute wenn Volume geändert wird
        if (isMuted && audio.volume > 0) {
            isMuted = false;
            audio.muted = false;
            updateMuteIcon();
        }
    }

    /**
     * Toggle Mute
     */
    function toggleMute() {
        if (!audio) return;
        isMuted = !isMuted;
        audio.muted = isMuted;
        updateMuteIcon();
    }

    /**
     * Mute-Icon aktualisieren
     */
    function updateMuteIcon() {
        var iconEl = els.muteBtn ? els.muteBtn.querySelector('.lfsp-volume-icon') : null;
        if (iconEl) {
            iconEl.textContent = isMuted ? '🔇' : '🔊';
        }
        if (els.muteBtn) {
            els.muteBtn.setAttribute('aria-label', isMuted ? 'Unmute' : 'Mute');
        }
    }

    /**
     * Player Toggle (Auf/Zu)
     */
    function togglePlayer() {
        if (!els.wrapper) return;

        var isClosed = els.wrapper.classList.contains('lfsp-closed');

        if (isClosed) {
            els.wrapper.classList.remove('lfsp-closed');
            savePlayerState('open');
        } else {
            els.wrapper.classList.add('lfsp-closed');
            savePlayerState('closed');
        }

        // ARIA aktualisieren
        if (els.toggleBtn) {
            els.toggleBtn.setAttribute('aria-expanded', isClosed ? 'true' : 'false');
        }
    }

    /**
     * Player-Zustand speichern
     */
    function savePlayerState(state) {
        try {
            localStorage.setItem('lfsp_player_state', state);
        } catch (e) {
            // localStorage nicht verfügbar - ignorieren
        }
    }

    /**
     * Player-Zustand wiederherstellen
     */
    function restorePlayerState() {
        try {
            var saved = localStorage.getItem('lfsp_player_state');
            if (saved === 'closed') {
                els.wrapper.classList.add('lfsp-closed');
                if (els.toggleBtn) {
                    els.toggleBtn.setAttribute('aria-expanded', 'false');
                }
            } else if (lfspConfig.defaultClosed) {
                els.wrapper.classList.add('lfsp-closed');
                if (els.toggleBtn) {
                    els.toggleBtn.setAttribute('aria-expanded', 'false');
                }
            }
        } catch (e) {
            // Ignorieren
        }
    }

    /**
     * Uhr aktualisieren
     */
    function updateClock() {
        if (!els.clock) return;
        var now = new Date();
        var hh  = String(now.getHours()).padStart(2, '0');
        var mm  = String(now.getMinutes()).padStart(2, '0');
        els.clock.textContent = hh + ':' + mm;
    }

    /**
     * Song-Daten per AJAX laden
     */
    function fetchSongData() {
        if (!lfspConfig.ajaxUrl || !lfspConfig.stationName) return;

        var url = new URL(lfspConfig.ajaxUrl);
        url.searchParams.set('action', 'lfsp_get_song_data');
        url.searchParams.set('station', lfspConfig.stationName);
        url.searchParams.set('nonce', lfspConfig.nonce);

        fetch(url.toString())
            .then(function (res) { return res.json(); })
            .then(function (response) {
                if (!response.success || !response.data) return;

                var data = response.data;
                var display = '';

                if (data.type === 'song' && data.artist && data.title) {
                    display = data.artist + ' – ' + data.title;
                } else if (data.title) {
                    display = data.title;
                } else {
                    display = lfspConfig.stationName + ' – ' + lfspConfig.i18n.liveNow;
                }

                if (els.songTitle) {
                    els.songTitle.textContent = display;
                }
            })
            .catch(function (err) {
                console.warn('LFSP: Song fetch error -', err.message);
            });
    }

    /**
     * Keyboard Support
     */
    function handleKeyboard(e) {
        // Nur wenn kein Input fokussiert
        if (['INPUT', 'TEXTAREA', 'SELECT'].indexOf(e.target.tagName) !== -1) return;

        switch (e.key) {
            case ' ':
                // Space: Play/Pause (nur wenn Player sichtbar)
                if (els.wrapper && !els.wrapper.classList.contains('lfsp-closed')) {
                    e.preventDefault();
                    togglePlay();
                }
                break;
            case 'm':
            case 'M':
                toggleMute();
                break;
        }
    }

    // === INIT ===
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

})();
