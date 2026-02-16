<?php

use Illuminate\Support\Facades\File;

beforeEach(function () {
    $this->outputDir = storage_path('code_names_resized');

    File::ensureDirectoryExists($this->outputDir);
    File::cleanDirectory($this->outputDir);
});

afterEach(function () {
    File::cleanDirectory($this->outputDir);
});

test('resize command resizes images to selected size', function () {
    $this->artisan('app:resize-code-names-images')
        ->expectsChoice('Select target size:', 'Minimum (354×354 px, 150 DPI)', [
            'Optimal (709×709 px, 300 DPI)',
            'Recommended (512×512 px, 216 DPI)',
            'Minimum (354×354 px, 150 DPI)',
        ])
        ->assertSuccessful();

    $resizedFiles = collect(File::files($this->outputDir))
        ->filter(fn ($file) => strtolower($file->getExtension()) === 'png');

    expect($resizedFiles)->not->toBeEmpty();

    $firstImage = imagecreatefrompng($resizedFiles->first()->getPathname());
    expect(imagesx($firstImage))->toBe(354);
    expect(imagesy($firstImage))->toBe(354);
    imagedestroy($firstImage);
});

test('resize command outputs to storage/code_names_resized directory', function () {
    $this->artisan('app:resize-code-names-images')
        ->expectsChoice('Select target size:', 'Recommended (512×512 px, 216 DPI)', [
            'Optimal (709×709 px, 300 DPI)',
            'Recommended (512×512 px, 216 DPI)',
            'Minimum (354×354 px, 150 DPI)',
        ])
        ->assertSuccessful();

    $resizedFiles = collect(File::files($this->outputDir))
        ->filter(fn ($file) => strtolower($file->getExtension()) === 'png');

    expect($resizedFiles)->not->toBeEmpty();

    $firstImage = imagecreatefrompng($resizedFiles->first()->getPathname());
    expect(imagesx($firstImage))->toBe(512);
    expect(imagesy($firstImage))->toBe(512);
    imagedestroy($firstImage);
});
