<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$books = App\Models\Book::where('status', 'active')->get();

$extensions = [];
foreach ($books as $b) {
    $img = $b->cover_image;
    $ext = strtolower(pathinfo($img, PATHINFO_EXTENSION));
    $extensions[$ext] = ($extensions[$ext] ?? 0) + 1;
    $cleanPath = ltrim(str_replace(['/storage/', 'storage/'], '', str_replace('\\', '/', $img)), '/');
    $fullPath = public_path('storage/' . $cleanPath);
    $size = file_exists($fullPath) ? filesize($fullPath) : -1;
    echo sprintf("[%2d] %-40s | %-5s | %7d bytes | %s\n", $b->id, substr($b->title, 0, 40), $ext, $size, $img);
}

echo "\nExtension distribution:\n";
print_r($extensions);
