<?php

declare(strict_types=1);

$src = $argv[1] ?? '';
$root = dirname(__DIR__);

if ($src === '' || ! is_file($src)) {
    fwrite(STDERR, "Usage: php apply-cebinova-logo.php <source-png>\n");
    exit(1);
}

$brandDir = $root.'/public/assets/brand';
$brandingDir = $root.'/public/images/branding';
@mkdir($brandDir, 0777, true);
@mkdir($brandingDir, 0777, true);

$info = getimagesize($src);
if ($info === false) {
    fwrite(STDERR, "Cannot read image\n");
    exit(1);
}

$ext = match ($info['mime']) {
    'image/jpeg' => 'jpg',
    'image/webp' => 'webp',
    default => 'png',
};
copy($src, $brandingDir.'/logo-original.'.$ext);

$im = match ($info['mime']) {
    'image/jpeg' => imagecreatefromjpeg($src),
    'image/webp' => imagecreatefromwebp($src),
    default => imagecreatefrompng($src),
};
if ($im === false) {
    fwrite(STDERR, "Cannot decode image\n");
    exit(1);
}

imagepng($im, $brandingDir.'/logo-original.png', 6);

$w = imagesx($im);
$h = imagesy($im);
$work = canvas($w, $h);
imagecopy($work, $im, 0, 0, 0, 0, $w, $h);
imagedestroy($im);
$im = $work;
imagealphablending($im, false);
imagesavealpha($im, true);
echo "Source: {$w}x{$h} {$info['mime']}\n";

$blue = [];
$gold = [];
$grey = [];

for ($y = 0; $y < $h; $y += 2) {
    for ($x = 0; $x < $w; $x += 2) {
        $rgb = imagecolorat($im, $x, $y);
        $r = ($rgb >> 16) & 0xFF;
        $g = ($rgb >> 8) & 0xFF;
        $b = $rgb & 0xFF;
        $luma = 0.2126 * $r + 0.7152 * $g + 0.0722 * $b;
        $sat = max($r, $g, $b) - min($r, $g, $b);
        if ($luma < 28) {
            continue;
        }
        if ($b > $r + 40 && $b > $g + 20 && $sat > 50) {
            $blue[] = [$r, $g, $b];
        } elseif ($r > 180 && $g > 130 && $b < 90 && $r > $b + 80) {
            $gold[] = [$r, $g, $b];
        } elseif ($sat < 28 && $luma > 70 && $luma < 170) {
            $grey[] = [$r, $g, $b];
        }
    }
}

function medianChannel(array $samples, int $i): int
{
    $vals = array_column($samples, $i);
    sort($vals);
    $n = count($vals);

    return $n ? $vals[(int) floor($n / 2)] : 0;
}

function hexOf(array $samples): string
{
    if ($samples === []) {
        return '#000000';
    }

    return sprintf(
        '#%02x%02x%02x',
        medianChannel($samples, 0),
        medianChannel($samples, 1),
        medianChannel($samples, 2)
    );
}

echo 'Blue samples: '.count($blue).' '.hexOf($blue).PHP_EOL;
echo 'Gold samples: '.count($gold).' '.hexOf($gold).PHP_EOL;
echo 'Grey samples: '.count($grey).' '.hexOf($grey).PHP_EOL;

imagealphablending($im, false);
imagesavealpha($im, true);

$clear = imagecolorallocatealpha($im, 0, 0, 0, 127);
$queue = [];
$seen = [];
$qHead = 0;

$enqueue = function (int $x, int $y) use (&$queue, &$seen, $w, $h): void {
    if ($x < 0 || $y < 0 || $x >= $w || $y >= $h) {
        return;
    }
    $key = $y * $w + $x;
    if (isset($seen[$key])) {
        return;
    }
    $seen[$key] = true;
    $queue[] = [$x, $y];
};

for ($x = 0; $x < $w; $x++) {
    $enqueue($x, 0);
    $enqueue($x, $h - 1);
}
for ($y = 0; $y < $h; $y++) {
    $enqueue(0, $y);
    $enqueue($w - 1, $y);
}

while ($qHead < count($queue)) {
    [$x, $y] = $queue[$qHead++];
    $rgb = imagecolorat($im, $x, $y);
    $r = ($rgb >> 16) & 0xFF;
    $g = ($rgb >> 8) & 0xFF;
    $b = $rgb & 0xFF;
    $luma = 0.2126 * $r + 0.7152 * $g + 0.0722 * $b;
    $sat = max($r, $g, $b) - min($r, $g, $b);
    $isBg = $luma < 24 || ($luma < 38 && $sat < 52);
    if (! $isBg) {
        continue;
    }
    imagesetpixel($im, $x, $y, $clear);
    $enqueue($x + 1, $y);
    $enqueue($x - 1, $y);
    $enqueue($x, $y + 1);
    $enqueue($x, $y - 1);
}

for ($y = 0; $y < $h; $y++) {
    for ($x = 0; $x < $w; $x++) {
        $rgb = imagecolorat($im, $x, $y);
        $a = ($rgb >> 24) & 0x7F;
        if ($a > 110) {
            continue;
        }
        $r = ($rgb >> 16) & 0xFF;
        $g = ($rgb >> 8) & 0xFF;
        $b = $rgb & 0xFF;
        $luma = 0.2126 * $r + 0.7152 * $g + 0.0722 * $b;
        $sat = max($r, $g, $b) - min($r, $g, $b);
        if ($luma < 24 || ($luma < 38 && $sat < 52)) {
            imagesetpixel($im, $x, $y, $clear);
        }
    }
}

$minX = $w;
$minY = $h;
$maxX = 0;
$maxY = 0;
$colHits = array_fill(0, $w, 0);

for ($y = 0; $y < $h; $y++) {
    for ($x = 0; $x < $w; $x++) {
        $rgb = imagecolorat($im, $x, $y);
        $a = ($rgb >> 24) & 0x7F;
        if ($a > 110) {
            continue;
        }
        $minX = min($minX, $x);
        $minY = min($minY, $y);
        $maxX = max($maxX, $x);
        $maxY = max($maxY, $y);
        $colHits[$x]++;
    }
}

$pad = 12;
$minX = max(0, $minX - $pad);
$minY = max(0, $minY - $pad);
$maxX = min($w - 1, $maxX + $pad);
$maxY = min($h - 1, $maxY + $pad);
$cw = $maxX - $minX + 1;
$ch = $maxY - $minY + 1;

$cropped = canvas($cw, $ch);
imagecopy($cropped, $im, 0, 0, $minX, $minY, $cw, $ch);
savePng($cropped, $brandingDir.'/logo.png');
saveWebp($cropped, $brandingDir.'/logo.webp');
echo "Wrote logo.png {$cw}x{$ch}\n";

$started = false;
$gap = 0;
$markCut = (int) round($cw * 0.28);
for ($x = $minX; $x <= $maxX; $x++) {
    $hits = $colHits[$x] ?? 0;
    if ($hits > 18) {
        $started = true;
        $gap = 0;
        continue;
    }
    if (! $started) {
        continue;
    }
    $gap++;
    if ($gap >= 16) {
        $markCut = $x - $minX - (int) floor($gap / 2) + 10;
        break;
    }
}

$mark = canvas($markCut, $ch);
imagecopy($mark, $cropped, 0, 0, 0, 0, $markCut, $ch);
savePng($mark, $brandDir.'/cebinova-c-icon.png');
saveWebp($mark, $brandDir.'/cebinova-c-icon.webp');
echo "Wrote C icon {$markCut}x{$ch}\n";

passthru('php '.escapeshellarg(__DIR__.'/generate-favicon.php'));

function canvas(int $width, int $height)
{
    $img = imagecreatetruecolor($width, $height);
    imagealphablending($img, false);
    imagesavealpha($img, true);
    imagefilledrectangle($img, 0, 0, $width, $height, imagecolorallocatealpha($img, 0, 0, 0, 127));
    imagealphablending($img, true);

    return $img;
}

function savePng($img, string $path): void
{
    imagealphablending($img, false);
    imagesavealpha($img, true);
    imagepng($img, $path, 6);
}

function saveWebp($img, string $path): void
{
    imagealphablending($img, false);
    imagesavealpha($img, true);
    imagewebp($img, $path, 90);
}
