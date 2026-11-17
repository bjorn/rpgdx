/* Remote avatars can fail after the server has rendered the page. */
(function () {
    'use strict';
    var fallback = new URL('images/no_avatar.gif', document.currentScript.src).href;
    document.querySelectorAll('.postprofile .avatar img').forEach(function (image) {
        function showFallbackAvatar() {
            if (image.src !== fallback) {
                image.width = 80;
                image.height = 80;
                image.src = fallback;
            }
        }
        image.addEventListener('error', showFallbackAvatar, { once: true });
        if (image.complete && image.naturalWidth === 0) {
            showFallbackAvatar();
        }
    });
}());
