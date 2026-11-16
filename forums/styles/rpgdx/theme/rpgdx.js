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
    // Keep distinct result counts and section headings, but show the page title once.
    var pageTitle = document.querySelector('.rpgdx-page-title');
    if (pageTitle) {
        function titleText(element) {
            return element.textContent.replace(/\s+/g, ' ').trim();
        }
        document.querySelectorAll('#page-body > h2, #page-body > form > h2, #page-body h2.login-title, #page-body h2.message-title').forEach(function (heading) {
            if (titleText(heading) === titleText(pageTitle)) {
                heading.hidden = true;
            }
        });
    }
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
