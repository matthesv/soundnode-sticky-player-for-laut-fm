(function () {
    'use strict';

    var audio      = null;
    var isPlaying  = false;
    var isMuted    = false;
    var songTimer  = null;
    var clockTimer = null;
    var popupRef   = null;
    var els        = {};

    function init() {
        if (typeof lfspConfig === 'undefined') {
            console.error('LFSP: lfspConfig not found.');
            return;
        }

        els.wrapper      = document.getElementById('lfsp-sticky-wrapper');
        els.playBtn      = document.getElementById('lfsp-play-btn');
        els.toggleBtn    = document.getElementById('lfsp-toggle-btn');
        els.songTitle    = document.getElementById('lfsp-song-title');
        els.clock        = document.getElementById('lfsp-clock');
        els.volumeSlider = document.getElementById('lfsp-volume-slider');
        els.muteBtn      = document.getElementById('lfsp-mute-btn');
        els.iconPlay     = els.playBtn ? els.playBtn.querySelector('.lfsp-icon-play') : null;
        els.iconPause    = els.playBtn ? els.playBtn.querySelector('.lfsp-icon-pause') : null;

        if (!els.wrapper) {
            console.error('LFSP: #lfsp-sticky-wrapper not found.');
            return;
        }

        if (lfspConfig.playbackMode === 'inline') {
            audio = new Audio();
            audio.preload = 'none';
            audio.volume  = 0.8;

            audio.addEventListener('waiting', function () {
                if (els.playBtn) els.playBtn.classList.add('lfsp-buffering');
            });

            audio.addEventListener('playing', function () {
                if (els.playBtn) els.playBtn.classList.remove('lfsp-buffering');
                isPlaying = true;
                updatePlayButton(true);
            });

            audio.addEventListener('error', function (e) {
                console.warn('LFSP: Audio error', e);
                if (els.playBtn) els.playBtn.classList.remove('lfsp-buffering');
                isPlaying = false;
                updatePlayButton(false);
            });
        } else {
            var popupLabel = lfspConfig.playbackMode === 'popup_stream'
                ? lfspConfig.i18n.openStreamPopup
                : lfspConfig.i18n.openPopup;
            if (els.playBtn) {
                els.playBtn.setAttribute('aria-label', popupLabel || lfspConfig.i18n.play);
            }
        }

        if (els.playBtn) els.playBtn.addEventListener('click', togglePlay);
        if (els.toggleBtn) {
            els.toggleBtn.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                togglePlayer();
            });
        }
        if (els.volumeSlider) els.volumeSlider.addEventListener('input', handleVolume);
        if (els.muteBtn) els.muteBtn.addEventListener('click', toggleMute);

        restorePlayerState();

        updateClock();
        clockTimer = setInterval(updateClock, 1000);

        fetchSongData();
        songTimer = setInterval(fetchSongData, parseInt(lfspConfig.updateInterval, 10) || 30000);

        if (lfspConfig.autoplay && lfspConfig.playbackMode === 'inline') play();

        document.addEventListener('keydown', handleKeyboard);
    }

    function openPopup() {
        if (popupRef && !popupRef.closed) {
            popupRef.focus();
            return;
        }

        var w = lfspConfig.popupWidth || 500;
        var h = lfspConfig.popupHeight || 600;
        var left = Math.round((screen.width - w) / 2);
        var top = Math.round((screen.height - h) / 2);
        var features = 'width=' + w + ',height=' + h + ',left=' + left + ',top=' + top +
            ',scrollbars=yes,resizable=yes,status=no,toolbar=no,menubar=no,location=no';

        popupRef = window.open(lfspConfig.popupUrl, 'lfsp_popup', features);
    }

    function play() {
        if (lfspConfig.playbackMode !== 'inline') {
            openPopup();
            return;
        }

        if (!audio || !lfspConfig.streamUrl) return;

        if (els.playBtn) els.playBtn.classList.add('lfsp-buffering');

        audio.src = lfspConfig.streamUrl;

        var playPromise = audio.play();
        if (playPromise !== undefined) {
            playPromise
                .then(function () {
                    isPlaying = true;
                    updatePlayButton(true);
                })
                .catch(function (err) {
                    console.warn('LFSP: Playback blocked -', err.message);
                    if (els.playBtn) els.playBtn.classList.remove('lfsp-buffering');
                });
        }
    }

    function pause() {
        if (!audio) return;
        audio.pause();
        audio.src = '';
        isPlaying = false;
        updatePlayButton(false);
        if (els.playBtn) els.playBtn.classList.remove('lfsp-buffering');
    }

    function togglePlay() {
        if (lfspConfig.playbackMode !== 'inline') {
            openPopup();
            return;
        }

        if (isPlaying) {
            pause();
        } else {
            play();
        }
    }

    function updatePlayButton(playing) {
        if (els.iconPlay) els.iconPlay.style.display = playing ? 'none' : 'block';
        if (els.iconPause) els.iconPause.style.display = playing ? 'block' : 'none';
        if (els.playBtn) {
            els.playBtn.setAttribute('aria-label',
                playing ? lfspConfig.i18n.pause : lfspConfig.i18n.play
            );
            els.playBtn.classList.toggle('lfsp-playing', playing);
        }
    }

    function handleVolume() {
        if (!audio || !els.volumeSlider) return;
        audio.volume = els.volumeSlider.value / 100;
        if (isMuted && audio.volume > 0) {
            isMuted = false;
            audio.muted = false;
            updateMuteIcon();
        }
    }

    function toggleMute() {
        if (!audio) return;
        isMuted = !isMuted;
        audio.muted = isMuted;
        updateMuteIcon();
    }

    function updateMuteIcon() {
        var iconEl = els.muteBtn ? els.muteBtn.querySelector('.lfsp-volume-icon') : null;
        if (iconEl) iconEl.textContent = isMuted ? '\uD83D\uDD07' : '\uD83D\uDD0A';
        if (els.muteBtn) els.muteBtn.setAttribute('aria-label', isMuted ? 'Unmute' : 'Mute');
    }

    function togglePlayer() {
        if (!els.wrapper) return;

        var isClosed = els.wrapper.classList.contains('lfsp-closed');

        if (isClosed) {
            els.wrapper.classList.remove('lfsp-closed');
            if (els.toggleBtn) els.toggleBtn.setAttribute('aria-expanded', 'true');
            savePlayerState('open');
        } else {
            els.wrapper.classList.add('lfsp-closed');
            if (els.toggleBtn) els.toggleBtn.setAttribute('aria-expanded', 'false');
            savePlayerState('closed');
        }
    }

    function savePlayerState(state) {
        try { localStorage.setItem('lfsp_player_state', state); } catch (e) {}
    }

    function restorePlayerState() {
        try {
            var saved = localStorage.getItem('lfsp_player_state');
            if (saved === 'closed' || (!saved && lfspConfig.defaultClosed)) {
                els.wrapper.classList.add('lfsp-closed');
                if (els.toggleBtn) els.toggleBtn.setAttribute('aria-expanded', 'false');
            } else {
                els.wrapper.classList.remove('lfsp-closed');
                if (els.toggleBtn) els.toggleBtn.setAttribute('aria-expanded', 'true');
            }
        } catch (e) {}
    }

    function updateClock() {
        if (!els.clock) return;
        var now = new Date();
        els.clock.textContent = String(now.getHours()).padStart(2, '0') + ':' + String(now.getMinutes()).padStart(2, '0');
    }

    function setFallbackTitle() {
        if (els.songTitle && els.songTitle.textContent.trim() === lfspConfig.i18n.loading) {
            els.songTitle.textContent = lfspConfig.stationName + ' \u2013 ' + lfspConfig.i18n.liveNow;
        }
    }

    function fetchSongData() {
        if (!lfspConfig.ajaxUrl || !lfspConfig.stationName) return;

        var url;
        try {
            url = new URL(lfspConfig.ajaxUrl);
        } catch (e) {
            console.warn('LFSP: Invalid ajaxUrl', lfspConfig.ajaxUrl);
            setFallbackTitle();
            return;
        }

        url.searchParams.set('action', 'lfsp_get_song_data');
        url.searchParams.set('station', lfspConfig.stationName);
        url.searchParams.set('nonce', lfspConfig.nonce);

        fetch(url.toString())
            .then(function (res) {
                if (!res.ok) throw new Error('HTTP ' + res.status);
                return res.json();
            })
            .then(function (response) {
                if (!response.success || !response.data) {
                    setFallbackTitle();
                    return;
                }
                var data    = response.data;
                var display = '';
                if (data.type === 'song' && data.artist && data.title) {
                    display = data.artist + ' \u2013 ' + data.title;
                } else if (data.title) {
                    display = data.title;
                } else {
                    display = lfspConfig.stationName + ' \u2013 ' + lfspConfig.i18n.liveNow;
                }
                if (els.songTitle) els.songTitle.textContent = display;
            })
            .catch(function (err) {
                console.warn('LFSP: Song fetch error -', err.message);
                setFallbackTitle();
            });
    }

    function handleKeyboard(e) {
        if (['INPUT', 'TEXTAREA', 'SELECT'].indexOf(e.target.tagName) !== -1) return;
        if (e.target.isContentEditable) return;

        switch (e.key) {
            case ' ':
                if (els.wrapper && els.wrapper.contains(document.activeElement)) {
                    e.preventDefault();
                    togglePlay();
                }
                break;
            case 'm':
            case 'M':
                if (els.wrapper && els.wrapper.contains(document.activeElement)) {
                    toggleMute();
                }
                break;
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
