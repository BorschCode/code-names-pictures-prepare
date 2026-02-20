<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

use function Laravel\Prompts\progress;

class GrayscaleCodeNamesImages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:grayscale-code-names-images';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Convert Code Names images from color to grayscale';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $sourceDir = storage_path('code_names');
        $outputDir = storage_path('code_names_grayscale');

        $images = collect(File::files($sourceDir))
            ->filter(fn ($file) => strtolower($file->getExtension()) === 'png')
            ->sortBy(fn ($file) => $file->getFilename())
            ->values();

        if ($images->isEmpty()) {
            $this->error('No PNG images found in storage/code_names/');

            return self::FAILURE;
        }

        $this->info("Found {$images->count()} images.");

        File::ensureDirectoryExists($outputDir);

        $converted = 0;
        $skipped = 0;

        progress(
            label: 'Converting to grayscale...',
            steps: $images,
            callback: function ($image) use ($outputDir, &$converted, &$skipped): void {
                $source = imagecreatefrompng($image->getPathname());

                if ($source === false) {
                    $skipped++;

                    return;
                }

                imagealphablending($source, false);
                imagesavealpha($source, true);

                imagefilter($source, IMG_FILTER_GRAYSCALE);

                imagepng($source, $outputDir.'/'.$image->getFilename(), 9);

                imagedestroy($source);

                $converted++;
            },
            hint: 'This may take a moment...',
        );

        $this->newLine();
        $this->info("Converted: {$converted}, Skipped: {$skipped}");
        $this->info('Output: storage/code_names_grayscale/');

        return self::SUCCESS;
    }
}
