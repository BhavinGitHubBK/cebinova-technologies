<?php

$src = imagecreatefrompng(__DIR__ . '/../public/images/branding/logo-s.png');
$size = 64;
$pad = 6;
$fav = imagecreatetruecolor($size, $size);
imagealphablending($fav, false);
imagesavealpha($fav, true);
imagefilledrectangle($fav, 0, 0, $size, $size, imagecolorallocatealpha($fav, 11, 31, 58, 0));
imagealphablending($fav, true);
$mw = imagesx($src);
$mh = imagesy($src);
$box = $size - $pad * 2;
$scale = min($box / $mw, $box / $mh);
$dw = (int) round($mw * $scale);
$dh = (int) round($mh * $scale);
$dx = (int) floor(($size - $dw) / 2);
$dy = (int) floor(($size - $dh) / 2);
imagecopyresampled($fav, $src, $dx, $dy, 0, 0, $dw, $dh, $mw, $mh);
imagealphablending($fav, false);
imagesavealpha($fav, true);
imagepng($fav, __DIR__ . '/../public/favicon.png', 8);
copy(__DIR__ . '/../public/images/branding/logo-s.png', __DIR__ . '/../public/images/branding/logo-mark.png');
echo "favicon {$dw}x{$dh}\n";
