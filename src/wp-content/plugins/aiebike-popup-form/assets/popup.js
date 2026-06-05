/* global aepfConfig */
(function ($) {
    'use strict';

    var COOKIE_KEY  = 'aepf_shown';
    var SESSION_KEY = 'aepf_session_shown';
    var $overlay    = null;
    var isOpen      = false;

    // ── Helpers ──────────────────────────────────────────────────────────────
    function setCookie(name, value, days) {
        var d = new Date();
        d.setTime(d.getTime() + days * 864e5);
        document.cookie = name + '=' + value + ';expires=' + d.toUTCString() + ';path=/';
    }
    function getCookie(name) {
        var v = document.cookie.match('(^|;) ?' + name + '=([^;]*)(;|$)');
        return v ? v[2] : null;
    }
    function shouldShow() {
        var freq = aepfConfig.frequency;
        if (freq === 'always')  return true;
        if (freq === 'session') return !sessionStorage.getItem(SESSION_KEY);
        if (freq === 'once')    return !getCookie(COOKIE_KEY);
        return true;
    }
    function markShown() {
        var freq = aepfConfig.frequency;
        if (freq === 'session') sessionStorage.setItem(SESSION_KEY, '1');
        if (freq === 'once')    setCookie(COOKIE_KEY, '1', 365);
    }

    // ── Open / Close ─────────────────────────────────────────────────────────
    // Dùng opacity+visibility — KHÔNG toggle display để tránh conflict theme
    window.aepfOpenPopup = function () {
        if (!$overlay || !$overlay.length) $overlay = $('#aepf-overlay');
        if (isOpen) return;
        isOpen = true;
        $overlay.addClass('aepf-open');
        $('body').css('overflow', 'hidden');
    };

    window.aepfClosePopup = function () {
        if (!$overlay || !$overlay.length) $overlay = $('#aepf-overlay');
        if (!isOpen) return;
        isOpen = false;
        $overlay.removeClass('aepf-open');
        $('body').css('overflow', '');
    };

    // ── Init ─────────────────────────────────────────────────────────────────
    $(function () {
        $overlay = $('#aepf-overlay');

        if (!$overlay.length) return; // không có overlay thì dừng

        // Đóng khi click backdrop (vùng ngoài popup box)
        $overlay.on('click', function (e) {
            if ($(e.target).is($overlay)) aepfClosePopup();
        });

        // Đóng bằng phím Escape
        $(document).on('keydown.aepf', function (e) {
            if (e.key === 'Escape' && isOpen) aepfClosePopup();
        });

        // Auto-show sau delay
        if (aepfConfig.autoShow && shouldShow()) {
            setTimeout(function () {
                aepfOpenPopup();
                markShown();
            }, aepfConfig.delay || 0);
        }

        // ── Form AJAX Submit ──────────────────────────────────────────────
        $(document).on('submit', '#aepf-form', function (e) {
            e.preventDefault();
            var $form = $(this);
            var $btn  = $form.find('.aepf-submit');
            var $msg  = $form.find('.aepf-msg');

            $btn.prop('disabled', true).text('Đang gửi...');
            $msg.hide().removeClass('success error');

            var data = $form.serializeArray();
            data.push({ name: 'action',     value: 'aepf_submit' });
            data.push({ name: 'aepf_nonce', value: aepfConfig.nonce });

            $.post(aepfConfig.ajaxurl, $.param(data), function (res) {
                if (res.success) {
                    $msg.addClass('success').text(res.data.msg).show();
                    $form.find('input:not([type=hidden]), textarea').val('');
                    setTimeout(aepfClosePopup, 3200);
                } else {
                    $msg.addClass('error').text(res.data.msg || 'Có lỗi xảy ra, vui lòng thử lại.').show();
                }
            }).fail(function () {
                $msg.addClass('error').text('Lỗi kết nối. Vui lòng thử lại.').show();
            }).always(function () {
                var lbl = aepfConfig.btnLabel || 'GỬI';
                $btn.prop('disabled', false).text(lbl);
            });
        });
    });

})(jQuery);
