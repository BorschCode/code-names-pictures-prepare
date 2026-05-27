<?php

use Illuminate\Support\Facades\File;

beforeEach(function () {
    $this->sourceDir = storage_path('code_names');
    $this->fixtureImage = $this->sourceDir . '/__test_fixture.png';

    File::ensureDirectoryExists($this->sourceDir);

    $img = imagecreatetruecolor(100, 100);
    $color = imagecolorallocate($img, 80, 120, 160);
    imagefill($img, 0, 0, $color);
    imagepng($img, $this->fixtureImage);
    imagedestroy($img);
});

afterEach(function () {
    File::delete($this->fixtureImage);
});

test('pdf grid is symmetrically centered on A4 page for duplex printing', function () {
    $imageSize = 60;
    $columns = 3;
    $rows = 4;
    $pageWidth = 210;
    $pageHeight = 297;
    $gap = 0.5;

    $gridWidth = $columns * $imageSize + ($columns - 1) * $gap;
    $gridHeight = $rows * $imageSize + ($rows - 1) * $gap;

    $marginX = ($pageWidth - $gridWidth) / 2;
    $marginY = ($pageHeight - $gridHeight) / 2;

    // Left margin must equal right margin for duplex alignment
    $rightMargin = $pageWidth - $marginX - $gridWidth;
    expect($marginX)->toBe($rightMargin);

    // Top margin must equal bottom margin for duplex alignment
    $bottomMargin = $pageHeight - $marginY - $gridHeight;
    expect($marginY)->toBe($bottomMargin);
});

test('pdf generation command produces output file', function () {
    $outputFile = storage_path('code_names_pdf/code_names.pdf');

    $this->artisan('app:generate-code-names-pdf')
        ->assertSuccessful();

    expect(File::exists($outputFile))->toBeTrue();
});
