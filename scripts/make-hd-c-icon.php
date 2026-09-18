<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$brandDir = $root.'/public/assets/brand';

$vbW = 270.0;
$vbH = 220.0;
$cx = 108.0;
$cy = 110.0;
$midR = 80.0;
$stroke = 48.0;
$cStart = 48.0;
$cEnd = 312.0;
$padX = 96.0;
$padY = 146.0;
$padR = 17.5;
$goldX = 220.0;
$goldY = 40.0;
$goldR = 22.5;
$lineW = 12.5;
$navyHex = '#013a9d';
$goldHex = '#fbb50b';

$pt = static function (float $ox, float $oy, float $r, float $deg): array {
    $rad = deg2rad($deg);

    return [
        round($ox + $r * cos($rad), 2),
        round($oy + $r * sin($rad), 2),
    ];
};

$cubic = static function (array $p0, array $p1, array $p2, array $p3, float $t): array {
    $u = 1 - $t;
    $uu = $u * $u;
    $tt = $t * $t;

    return [
        $uu * $u * $p0[0] + 3 * $uu * $t * $p1[0] + 3 * $u * $tt * $p2[0] + $tt * $t * $p3[0],
        $uu * $u * $p0[1] + 3 * $uu * $t * $p1[1] + 3 * $u * $tt * $p2[1] + $tt * $t * $p3[1],
    ];
};

[$sx, $sy] = $pt($cx, $cy, $midR, $cStart);
[$ex, $ey] = $pt($cx, $cy, $midR, $cEnd);

$bCurves = [
    [[166.0, 34.0], [200.0, 16.0], [246.0, 34.0], [244.0, 68.0]],
    [[244.0, 68.0], [242.0, 90.0], [226.0, 106.0], [202.0, 110.0]],
    [[202.0, 110.0], [226.0, 116.0], [248.0, 132.0], [240.0, 154.0]],
    [[240.0, 154.0], [232.0, 174.0], [198.0, 186.0], [166.0, 170.0]],
];

$bPath = 'M166 34 C200 16 246 34 244 68 C242 90 226 106 202 110 C226 116 248 132 240 154 C232 174 198 186 166 170 L166 34 Z';

$svg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 {$vbW} {$vbH}" fill="none" role="img" aria-hidden="true">
  <path d="M{$sx} {$sy} A{$midR} {$midR} 0 1 1 {$ex} {$ey}" stroke="{$navyHex}" stroke-width="{$stroke}" stroke-linecap="round"/>
  <path d="{$bPath}" fill="{$navyHex}"/>
  <circle cx="{$padX}" cy="{$padY}" r="{$padR}" fill="{$navyHex}"/>
  <path d="M{$padX} {$padY} L{$goldX} {$goldY}" stroke="{$navyHex}" stroke-width="{$lineW}" stroke-linecap="round"/>
  <circle cx="{$goldX}" cy="{$goldY}" r="{$goldR}" fill="{$goldHex}"/>
</svg>
SVG;

file_put_contents($brandDir.'/cebinova-c-icon.svg', $svg);

$scale = 8;
$w = (int) round($vbW * $scale);
$h = (int) round($vbH * $scale);
$hi = imagecreatetruecolor($w, $h);
imagealphablending($hi, false);
imagesavealpha($hi, true);
imagefilledrectangle($hi, 0, 0, $w, $h, imagecolorallocatealpha($hi, 0, 0, 0, 127));
imagealphablending($hi, true);

$navy = imagecolorallocate($hi, 0x01, 0x3a, 0x9d);
$gold = imagecolorallocate($hi, 0xfb, 0xb5, 0x0b);

$stamp = static function (float $x, float $y, float $r, int $color) use ($hi, $scale): void {
    $d = (int) max(1, round($r * 2 * $scale));
    imagefilledellipse($hi, (int) round($x * $scale), (int) round($y * $scale), $d, $d, $color);
};

$capR = $stroke / 2;
for ($a = $cStart; $a <= $cEnd; $a += 0.12) {
    $rad = deg2rad($a);
    $stamp($cx + $midR * cos($rad), $cy + $midR * sin($rad), $capR, $navy);
}

$poly = [];
foreach ($bCurves as $curve) {
    for ($i = 0; $i <= 48; $i++) {
        [$x, $y] = $cubic($curve[0], $curve[1], $curve[2], $curve[3], $i / 48);
        $poly[] = (int) round($x * $scale);
        $poly[] = (int) round($y * $scale);
    }
}
imagefilledpolygon($hi, $poly, $navy);

$stamp($padX, $padY, $padR, $navy);
$steps = 280;
for ($i = 0; $i <= $steps; $i++) {
    $t = $i / $steps;
    $stamp($padX + ($goldX - $padX) * $t, $padY + ($goldY - $padY) * $t, $lineW / 2, $navy);
}
$stamp($goldX, $goldY, $goldR, $gold);

$outW = (int) round($vbW * 4);
$outH = (int) round($vbH * 4);
$out = imagescale($hi, $outW, $outH, defined('IMG_BICUBIC') ? IMG_BICUBIC : IMG_BILINEAR_FIXED);
if (! $out instanceof GdImage) {
    fwrite(STDERR, "Failed to downsample HD icon\n");
    exit(1);
}

imagealphablending($out, false);
imagesavealpha($out, true);
imagepng($out, $brandDir.'/cebinova-c-icon.png', 9);
imagewebp($out, $brandDir.'/cebinova-c-icon.webp', 98);
echo "SVG, PNG {$outW}x{$outH}, WebP written\n";
imagedestroy($hi);
imagedestroy($out);
passthru('php '.escapeshellarg(__DIR__.'/generate-favicon.php'));
