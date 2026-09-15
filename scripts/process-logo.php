<?php

declare(strict_types=1);

$src = __DIR__ . '/../public/images/branding/logo-original.png';
$out = __DIR__ . '/../public/images/branding/logo.png';
$outNav = __DIR__ . '/../public/images/branding/logo-nav.png';
$outIcon = __DIR__ . '/../public/images/branding/logo-mark.png';
$outFav = __DIR__ . '/../public/favicon.png';
$outSvg = __DIR__ . '/../public/favicon.svg';

$info = getimagesize($src);
if ($info === false) {
    fwrite(STDERR, "Cannot read logo\n");
    exit(1);
}

echo "Source: {$info[0]}x{$info[1]} {$info['mime']}\n";

$im = imagecreatefrompng($src);
if ($im === false) {
    fwrite(STDERR, "Cannot decode PNG\n");
    exit(1);
}

$w = imagesx($im);
$h = imagesy($im);
imagealphablending($im, false);
imagesavealpha($im, true);

$minX = $w;
$minY = $h;
$maxX = 0;
$maxY = 0;
$colHits = array_fill(0, $w, 0);
$rowHits = array_fill(0, $h, 0);

for ($y = 0; $y < $h; $y++) {
    for ($x = 0; $x < $w; $x++) {
        $rgb = imagecolorat($im, $x, $y);
        $a = ($rgb >> 24) & 0x7F;
        $r = ($rgb >> 16) & 0xFF;
        $g = ($rgb >> 8) & 0xFF;
        $b = $rgb & 0xFF;
        $luma = 0.2126 * $r + 0.7152 * $g + 0.0722 * $b;
        $sat = max($r, $g, $b) - min($r, $g, $b);

        if ($a > 110 || ($luma < 22 && $sat < 18)) {
            imagesetpixel($im, $x, $y, imagecolorallocatealpha($im, 0, 0, 0, 127));
            continue;
        }

        $minX = min($minX, $x);
        $minY = min($minY, $y);
        $maxX = max($maxX, $x);
        $maxY = max($maxY, $y);
        $colHits[$x]++;
        $rowHits[$y]++;
    }
}

$pad = 18;
$minX = max(0, $minX - $pad);
$minY = max(0, $minY - $pad);
$maxX = min($w - 1, $maxX + $pad);
$maxY = min($h - 1, $maxY + $pad);

$cw = $maxX - $minX + 1;
$ch = $maxY - $minY + 1;

$cropped = canvas($cw, $ch);
imagecopy($cropped, $im, 0, 0, $minX, $minY, $cw, $ch);
savePng($cropped, $out);
echo "Wrote logo.png {$cw}x{$ch}\n";

$navCropH = navCropHeight($rowHits, $minY, $maxY, $ch);
$navH = 160;
$navW = max(1, (int) round($cw * ($navH / $navCropH)));
$nav = canvas($navW, $navH);
imagecopyresampled($nav, $cropped, 0, 0, 0, 0, $navW, $navH, $cw, $navCropH);
savePng($nav, $outNav);
echo "Wrote logo-nav.png {$navW}x{$navH} (from {$cw}x{$navCropH})\n";

$markW = markWidth($colHits, $minX, $maxX, $cw);
$mark = canvas($markW, $ch);
imagecopy($mark, $cropped, 0, 0, 0, 0, $markW, $ch);
savePng($mark, $outIcon);
echo "Wrote logo-mark.png {$markW}x{$ch}\n";

writeFavicon($mark, $outFav, $outSvg);

imagedestroy($im);
imagedestroy($cropped);
imagedestroy($nav);
imagedestroy($mark);

function savePng($img, string $path): void
{
    imagealphablending($img, false);
    imagesavealpha($img, true);
    imagepng($img, $path, 8);
}

function canvas(int $width, int $height)
{
    $img = imagecreatetruecolor($width, $height);
    imagealphablending($img, false);
    imagesavealpha($img, true);
    imagefilledrectangle($img, 0, 0, $width, $height, imagecolorallocatealpha($img, 0, 0, 0, 127));
    imagealphablending($img, true);

    return $img;
}

function navCropHeight(array $rowHits, int $minY, int $maxY, int $ch): int
{
    $gap = 0;
    for ($y = $maxY; $y >= $minY; $y--) {
        if (($rowHits[$y] ?? 0) < 8) {
            $gap++;
            if ($gap >= 10) {
                $cut = $y - $minY;
                if ($cut > (int) ($ch * 0.62)) {
                    return min($ch, $cut + 8);
                }
            }
            continue;
        }
        $gap = 0;
    }

    return (int) round($ch * 0.86);
}

function markWidth(array $colHits, int $minX, int $maxX, int $cw): int
{
    $started = false;
    $gap = 0;
    for ($x = $minX; $x <= $maxX; $x++) {
        $hits = $colHits[$x] ?? 0;
        if ($hits > 12) {
            $started = true;
            $gap = 0;
            continue;
        }
        if (! $started) {
            continue;
        }
        $gap++;
        if ($gap >= 14) {
            $cut = $x - $minX - (int) floor($gap / 2);

            return max((int) round($cw * 0.18), min($cw, $cut + 12));
        }
    }

    return (int) round($cw * 0.26);
}

function writeFavicon($mark, string $pngPath, string $svgPath): void
{
    $size = 64;
    $fav = imagecreatetruecolor($size, $size);
    imagealphablending($fav, false);
    imagesavealpha($fav, true);
    imagefilledrectangle($fav, 0, 0, $size, $size, imagecolorallocatealpha($fav, 11, 31, 58, 0));
    imagealphablending($fav, true);

    $mw = imagesx($mark);
    $mh = imagesy($mark);
    $pad = 7;
    $box = $size - ($pad * 2);
    $scale = min($box / $mw, $box / $mh);
    $dw = (int) round($mw * $scale);
    $dh = (int) round($mh * $scale);
    $dx = (int) floor(($size - $dw) / 2);
    $dy = (int) floor(($size - $dh) / 2);
    imagecopyresampled($fav, $mark, $dx, $dy, 0, 0, $dw, $dh, $mw, $mh);
    savePng($fav, $pngPath);
    imagedestroy($fav);
    echo "Wrote favicon.png {$size}x{$size}\n";

    $svg = <<<'SVG'
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64" role="img" aria-label="SARVIX">
  <rect width="64" height="64" rx="14" fill="#0B1F3A"/>
  <path d="M18 46c7.5-1.8 12.5-6.2 14.2-13.2 1.5-6.2-1.8-10.4-8.8-12.4 9.4-1.6 16.8-5.8 15.2-14.2C37 1.8 28.2-0.4 18 4.2" fill="none" stroke="#3B9BFF" stroke-width="5" stroke-linecap="round"/>
  <path d="M14 28c8 1.6 16.5 4.2 18 12 1.2 6.4-3.4 11.6-12 14" fill="none" stroke="#7EC8FF" stroke-width="4.2" stroke-linecap="round"/>
  <rect x="11" y="22" width="5" height="5" rx="1.2" fill="#2F7FE0"/>
  <rect x="8" y="29" width="4" height="4" rx="1" fill="#5AA8F5"/>
  <rect x="13" y="34" width="3.2" height="3.2" rx=".8" fill="#1E4E9C"/>
</svg>
SVG;
    file_put_contents($svgPath, $svg);
    echo "Wrote favicon.svg\n";
}
