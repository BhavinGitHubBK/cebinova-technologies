<?php

declare(strict_types=1);

$src = $argv[1] ?? '';
$out = __DIR__ . '/../public/images/branding/logo-s.png';

if ($src === '' || ! is_file($src)) {
    fwrite(STDERR, "Usage: php extract-s-icon.php <source>\n");
    exit(1);
}

$info = getimagesize($src);
echo "Source: {$info[0]}x{$info[1]} {$info['mime']}\n";

$im = match ($info['mime']) {
    'image/jpeg' => imagecreatefromjpeg($src),
    'image/png' => imagecreatefrompng($src),
    default => false,
};
if ($im === false) {
    fwrite(STDERR, "Cannot decode image\n");
    exit(1);
}

$w = imagesx($im);
$h = imagesy($im);
$work = imagecreatetruecolor($w, $h);
imagealphablending($work, false);
imagesavealpha($work, true);

$colHits = array_fill(0, $w, 0);
$minX = $w;
$minY = $h;
$maxX = 0;
$maxY = 0;

for ($y = 0; $y < $h; $y++) {
    for ($x = 0; $x < $w; $x++) {
        $rgb = imagecolorat($im, $x, $y);
        $r = ($rgb >> 16) & 0xFF;
        $g = ($rgb >> 8) & 0xFF;
        $b = $rgb & 0xFF;
        $luma = 0.2126 * $r + 0.7152 * $g + 0.0722 * $b;
        $sat = max($r, $g, $b) - min($r, $g, $b);

        if ($luma > 242 && $sat < 22) {
            imagesetpixel($work, $x, $y, imagecolorallocatealpha($work, 0, 0, 0, 127));
            continue;
        }

        imagesetpixel($work, $x, $y, imagecolorallocatealpha($work, $r, $g, $b, 0));
        $colHits[$x]++;
        $minX = min($minX, $x);
        $minY = min($minY, $y);
        $maxX = max($maxX, $x);
        $maxY = max($maxY, $y);
    }
}

$started = false;
$gap = 0;
$cutX = (int) round($w * 0.28);
for ($x = $minX; $x <= $maxX; $x++) {
    $hits = $colHits[$x];
    $ratio = $hits / $h;
    if ($ratio > 0.08) {
        $started = true;
        $gap = 0;
        continue;
    }
    if (! $started) {
        continue;
    }
    $gap++;
    if ($gap >= 6 && $x < (int) ($w * 0.42)) {
        $cutX = $x - 2;
        break;
    }
}

$pad = 8;
$sx = max(0, $minX - $pad);
$sy = max(0, $minY - $pad);
$ex = min($w - 1, $cutX + $pad);
$ey = min($h - 1, $maxY + $pad);
$cw = $ex - $sx + 1;
$ch = $ey - $sy + 1;

$mark = imagecreatetruecolor($cw, $ch);
imagealphablending($mark, false);
imagesavealpha($mark, true);
imagefilledrectangle($mark, 0, 0, $cw, $ch, imagecolorallocatealpha($mark, 0, 0, 0, 127));
imagealphablending($mark, true);
imagecopy($mark, $work, 0, 0, $sx, $sy, $cw, $ch);
imagealphablending($mark, false);
imagesavealpha($mark, true);
imagepng($mark, $out, 8);

echo "Wrote logo-s.png {$cw}x{$ch} cutX={$cutX}\n";

imagedestroy($im);
imagedestroy($work);
imagedestroy($mark);
