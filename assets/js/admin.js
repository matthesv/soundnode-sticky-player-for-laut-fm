/**
 * SoundNode Sticky Player for laut.fm - Admin JavaScript
 */
(function ($) {
    'use strict';

    $(document).ready(function () {
        $('.lfsp-color-picker').wpColorPicker();

        var $modeSelect = $('#lfsp-playback-mode');
        if ($modeSelect.length) {
            function updateModeInfo() {
                var mode = $modeSelect.val();
                $('.lfsp-mode-info').hide();
                $('.lfsp-mode-info[data-mode="' + mode + '"]').show();
            }
            $modeSelect.on('change', updateModeInfo);
            updateModeInfo();
        }
    });
})(jQuery);
