<?php

use Illuminate\Support\Facades\File;

beforeEach(function () {
    $this->outputDir = storage_path('code_names_grayscale');

    File::ensureDirectoryExists($this->outputDir);
    File::cleanDirectory($this->outputDir);
});

afterEach(function () {
    File::cleanDirectory($this->outputDir);
});

test('grayscale command converts images to grayscale', function () {
    $this->artisan('app:grayscale-code-names-images')
        ->assertSuccessful();

    $outputFiles = collect(File::files($this->outputDir))
        ->filter(fn ($file) => strtolower($file->getExtension()) === 'png');

    expect($outputFiles)->not->toBeEmpty();

    $image = imagecreatefrompng($outputFiles->first()->getPathname());
    $width = imagesx($image);
    $height = imagesy($image);

    // Sample a pixel and verify R == G == B (grayscale property)
    $centerColor = imagecolorat($image, intdiv($width, 2), intdiv($height, 2));
    $rgb = imagecolorsforindex($image, $centerColor);

    expect($rgb['red'])->toBe($rgb['green']);
    expect($rgb['red'])->toBe($rgb['blue']);

    imagedestroy($image);
});

test('grayscale command outputs to storage/code_names_grayscale directory', function () {
    $this->artisan('app:grayscale-code-names-images')
        ->assertSuccessful();

    $outputFiles = collect(File::files($this->outputDir))
        ->filter(fn ($file) => strtolower($file->getExtension()) === 'png');

    expect($outputFiles)->not->toBeEmpty();
});
