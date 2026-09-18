<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$srcPath = $root.'/public/assets/brand/cebinova-c-icon.png';
$im = imagecreatefrompng($srcPath);
if ($im === false) {
    fwrite(STDERR, "Cannot read C icon\n");
    exit(1);
}

imagealphablending($im, false);
imagesavealpha($im, true);

$w = imagesx($im);
$h = imagesy($im);
$minX = $w;
$minY = $h;
$maxX = 0;
$maxY = 0;

for ($y = 0; $y < $h; $y++) {
    for ($x = 0; $x < $w; $x++) {
        $a = (imagecolorat($im, $x, $y) >> 24) & 0x7F;
        if ($a > 110) {
            continue;
        }
        $minX = min($minX, $x);
        $minY = min($minY, $y);
        $maxX = max($maxX, $x);
        $maxY = max($maxY, $y);
    }
}

$cw = $maxX - $minX + 1;
$ch = $maxY - $minY + 1;
$cropped = canvas($cw, $ch);
imagecopy($cropped, $im, 0, 0, $minX, $minY, $cw, $ch);

function fitMark($mark, int $size, int $pad): GdImage
{
    $out = canvas($size, $size);
    $mw = imagesx($mark);
    $mh = imagesy($mark);
    $box = $size - ($pad * 2);
    $scale = min($box / $mw, $box / $mh);
    $dw = (int) round($mw * $scale);
    $dh = (int) round($mh * $scale);
    $dx = (int) floor(($size - $dw) / 2);
    $dy = (int) floor(($size - $dh) / 2);
    imagecopyresampled($out, $mark, $dx, $dy, 0, 0, $dw, $dh, $mw, $mh);

    return $out;
}

function canvas(int $width, int $height): GdImage
{
    $img = imagecreatetruecolor($width, $height);
    imagealphablending($img, false);
    imagesavealpha($img, true);
    imagefilledrectangle($img, 0, 0, $width, $height, imagecolorallocatealpha($img, 0, 0, 0, 127));
    imagealphablending($img, true);

    return $img;
}

function savePng(GdImage $img, string $path): void
{
    imagealphablending($img, false);
    imagesavealpha($img, true);
    imagepng($img, $path, 6);
}

$favicon = fitMark($cropped, 64, 2);
savePng($favicon, $root.'/public/favicon.png');

$apple = canvas(180, 180);
$white = imagecolorallocate($apple, 255, 255, 255);
imagefilledrectangle($apple, 0, 0, 180, 180, $white);
$fitted = fitMark($cropped, 180, 16);
imagecopy($apple, $fitted, 0, 0, 0, 0, 180, 180);
savePng($apple, $root.'/public/apple-touch-icon.png');

$small = fitMark($cropped, 32, 1);
$tmp = sys_get_temp_dir().'/cebinova-fav32.png';
savePng($small, $tmp);
$png = file_get_contents($tmp);
$header = pack('v3', 0, 1, 1);
$entry = pack('C4v2V2', 32, 32, 0, 0, 1, 32, strlen($png), 22);
file_put_contents($root.'/public/favicon.ico', $header.$entry.$png);
unlink($tmp);

$svgPng = $root.'/public/favicon.png';
$b64 = base64_encode(file_get_contents($svgPng));
$svg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64" role="img" aria-label="CEBINOVA">
  <image href="data:image/png;base64,{$b64}" width="64" height="64"/>
</svg>
SVG;
file_put_contents($root.'/public/favicon.svg', $svg);

echo "Wrote favicon.png, favicon.svg, favicon.ico, apple-touch-icon.png\n";
