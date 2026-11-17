<?php
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}
define('IN_RPGDX', true);
require dirname(__DIR__) . '/includes/functions.php';
$path = tempnam(sys_get_temp_dir(), 'rpgdx-thumbnail-');
try {
    foreach ([[32, 32], [1600, 10], [10, 10]] as [$width, $height]) {
        $image = imagecreatetruecolor($width, $height);
        imagepng($image, $path);
        imagedestroy($image);
        create_thumbnail($path);
        $thumbnail = getimagesize($path . '.thumb.jpg');
        if ($thumbnail[0] !== 140 || $thumbnail[1] !== max(1, (int) ($height * 140 / $width)) || $thumbnail[2] !== IMAGETYPE_JPEG) {
            throw new RuntimeException('Unexpected screenshot thumbnail dimensions or type.');
        }
        unlink($path . '.thumb.jpg');
    }
    echo "PASS screenshot thumbnails: square, minimum size and extreme valid aspect ratio\n";
} finally {
    @unlink($path . '.thumb.jpg');
    unlink($path);
}
