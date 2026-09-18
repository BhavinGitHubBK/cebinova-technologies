<?php

declare(strict_types=1);

$srcPath = $argv[1] ?? '';
$root = dirname(__DIR__);
$brandDir = $root.'/public/assets/brand';

if ($srcPath === '' || ! is_file($srcPath)) {
    fwrite(STDERR, "Usage: php extract-original-mark.php <source-png>\n");
    exit(1);
}

$im = imagecreatefrompng($srcPath);
if ($im === false) {
    fwrite(STDERR, "Cannot read source\n");
    exit(1);
}

imagealphablending($im, false);
imagesavealpha($im, true);

$w = imagesx($im);
$h = imagesy($im);
echo "Source {$w}x{$h}\n";

$isLogo = static function (int $r, int $g, int $b): bool {
    $luma = 0.2126 * $r + 0.7152 * $g + 0.0722 * $b;
    $sat = max($r, $g, $b) - min($r, $g, $b);
    $isNavy = $b > $r + 25 && $b > $g + 10 && $sat > 40 && $luma > 18;
    $isGold = $r > 170 && $g > 110 && $b < 100 && $r > $b + 70;

    return $isNavy || $isGold;
};

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
        $a = ($rgb >> 24) & 0x7F;
        if ($a > 100) {
            continue;
        }
        if (! $isLogo($r, $g, $b)) {
            imagealphablending($im, false);
            imagesetpixel($im, $x, $y, imagecolorallocatealpha($im, 0, 0, 0, 127));
            continue;
        }
        $luma = 0.2126 * $r + 0.7152 * $g + 0.0722 * $b;
        $sat = max($r, $g, $b) - min($r, $g, $b);
        $strength = min(1.0, max($sat / 90, ($luma - 22) / 40));
        $alpha = (int) round((1 - $strength) * 80);
        if ($alpha > 8) {
            imagealphablending($im, false);
            imagesetpixel($im, $x, $y, imagecolorallocatealpha($im, $r, $g, $b, $alpha));
        }
        $minX = min($minX, $x);
        $minY = min($minY, $y);
        $maxX = max($maxX, $x);
        $maxY = max($maxY, $y);
    }
}

$pad = 8;
$minX = max(0, $minX - $pad);
$minY = max(0, $minY - $pad);
$maxX = min($w - 1, $maxX + $pad);
$maxY = min($h - 1, $maxY + $pad);
$cw = $maxX - $minX + 1;
$ch = $maxY - $minY + 1;

$crop = imagecreatetruecolor($cw, $ch);
imagealphablending($crop, false);
imagesavealpha($crop, true);
imagefilledrectangle($crop, 0, 0, $cw, $ch, imagecolorallocatealpha($crop, 0, 0, 0, 127));
imagealphablending($crop, true);
imagecopy($crop, $im, 0, 0, $minX, $minY, $cw, $ch);
imagealphablending($crop, false);
imagesavealpha($crop, true);

imagepng($crop, $brandDir.'/cebinova-c-icon.png', 9);
imagewebp($crop, $brandDir.'/cebinova-c-icon.webp', 98);
echo "Wrote mark {$cw}x{$ch}\n";

$data = base64_encode((string) file_get_contents($brandDir.'/cebinova-c-icon.png'));
$svg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 {$cw} {$ch}" width="{$cw}" height="{$ch}" role="img" aria-hidden="true">
  <image href="data:image/png;base64,{$data}" width="{$cw}" height="{$ch}" preserveAspectRatio="xMidYMid meet"/>
</svg>
SVG;
file_put_contents($brandDir.'/cebinova-c-icon.svg', $svg);
echo "Wrote SVG wrapper\n";

imagedestroy($im);
imagedestroy($crop);

passthru('php '.escapeshellarg(__DIR__.'/generate-favicon.php'));
