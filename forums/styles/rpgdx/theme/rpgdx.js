/* Flex wrapping replaces prosilver's float-based navigation overflow calculation. */
(function () {
    'use strict';
    var navigation = document.getElementById('nav-main');
    if (navigation) {
        navigation.setAttribute('data-skip-responsive', 'true');
    }
    document.querySelectorAll('.breadcrumbs').forEach(function (breadcrumbs) {
        breadcrumbs.setAttribute('data-skip-responsive', 'true');
    });
    // Historical remote avatars may no longer exist. Keep the author details visible.
    document.querySelectorAll('.postprofile .avatar img').forEach(function (image) {
        function hideMissingAvatar() {
            image.closest('.avatar-container').hidden = true;
        }
        image.addEventListener('error', hideMissingAvatar);
        if (image.complete && image.naturalWidth === 0) {
            hideMissingAvatar();
        }
    });
}());
