(function ($) {
    $(function () {
        $('.manage-inactive-subsites-notice button').click(function (e) {
            $.post(ajaxurl, {
                action: 'mis_hide_admin_notification',
                nonce: $.trim($('#manage_inactive_subsites_nonce').val()),
                uid: userSettings.uid
            });
        });
    });
}(jQuery));
