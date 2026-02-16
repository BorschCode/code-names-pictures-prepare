<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

use function Laravel\Prompts\progress;
use function Laravel\Prompts\select;

class ResizeCodeNamesImages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:resize-code-names-images';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Resize Code Names images to a selected target size for print quality';

    /** @var array<string, int> */
    private const SIZES = [
        'Optimal (709×709 px, 300 DPI)' => 709,
        'Recommended (512×512 px, 216 DPI)' => 512,
        'Minimum (354×354 px, 150 DPI)' => 354,
    ];

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $sourceDir = storage_path('code_names');
        $outputDir = storage_path('code_names_resized');

        $images = collect(File::files($sourceDir))
            ->filter(fn ($file) => strtolower($file->getExtension()) === 'png')
            ->sortBy(fn ($file) => $file->getFilename())
            ->values();

        if ($images->isEmpty()) {
            $this->error('No PNG images found in storage/code_names/');

            return self::FAILURE;
        }

        $this->info("Found {$images->count()} images.");

        $label = select(
            label: 'Select target size:',
            options: array_keys(self::SIZES),
            default: 'Recommended (512×512 px, 216 DPI)',
        );

        $targetSize = self::SIZES[$label];

        File::ensureDirectoryExists($outputDir);

        $resized = 0;
        $skipped = 0;

        progress(
            label: "Resizing to {$targetSize}×{$targetSize} px...",
            steps: $images,
            callback: function ($image) use ($targetSize, $outputDir, &$resized, &$skipped): void {
                $source = imagecreatefrompng($image->getPathname());

                if ($source === false) {
                    $skipped++;

                    return;
                }

                $width = imagesx($source);
                $height = imagesy($source);

                if ($width <= $targetSize && $height <= $targetSize) {
                    imagedestroy($source);
                    $skipped++;

                    return;
                }

                $destination = imagecreatetruecolor($targetSize, $targetSize);
                imagealphablending($destination, false);
                imagesavealpha($destination, true);

                imagecopyresampled($destination, $source, 0, 0, 0, 0, $targetSize, $targetSize, $width, $height);

                imagepng($destination, $outputDir.'/'.$image->getFilename(), 9);

                imagedestroy($source);
                imagedestroy($destination);

                $resized++;
            },
            hint: 'This may take a moment...',
        );

        $this->newLine();
        $this->info("Resized: {$resized}, Skipped: {$skipped}");
        $this->info('Output: storage/code_names_resized/');

        return self::SUCCESS;
    }
}
