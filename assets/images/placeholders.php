<?php
// Create directory if it doesn't exist
$placeholder_dir = __DIR__ . '/placeholders';
if (!file_exists($placeholder_dir)) {
    mkdir($placeholder_dir, 0777, true);
}

// Placeholder images to be included
$placeholder_images = [
    'room.jpg',
    'gallery1.jpg',
    'gallery2.jpg',
    'gallery3.jpg',
    'profile.jpg',
    'hero-bg.jpg'
];

// Download placeholder images if they don't exist
foreach ($placeholder_images as $image) {
    $filepath = $placeholder_dir . '/' . $image;
    if (!file_exists($filepath)) {
        file_put_contents($filepath, file_get_contents("https://via.placeholder.com/800x600.jpg?text=" . pathinfo($image, PATHINFO_FILENAME)));
    }
}
