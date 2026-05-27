<?php

use Illuminate\Support\Facades\File;

beforeEach(function () {
    $this->sourceDir = storage_path('code_names');
    $this->outputDir = storage_path('code_names_resized');
    $this->fixtureImage = $this->sourceDir . '/__test_fixture.png';

    File::ensureDirectoryExists($this->sourceDir);
    File::ensureDirectoryExists($this->outputDir);
    File::cleanDirectory($this->outputDir);

    $img = imagecreatetruecolor(800, 800);
    $color = imagecolorallocate($img, 100, 150, 200);
    imagefill($img, 0, 0, $color);
    imagepng($img, $this->fixtureImage);
    imagedestroy($img);
});

afterEach(function () {
    File::delete($this->fixtureImage);
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
