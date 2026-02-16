<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use TCPDF;

class GenerateCodeNamesPdf extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:generate-code-names-pdf';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a printable A4 PDF with Code Names pictures arranged in a 3x4 grid';

    private const IMAGE_SIZE = 60;

    private const COLUMNS = 3;

    private const ROWS = 4;

    private const IMAGES_PER_PAGE = self::COLUMNS * self::ROWS;

    private const PAGE_WIDTH = 210;

    private const PAGE_HEIGHT = 297;

    private const GAP = 0.5;

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $sourceDir = storage_path('code_names');
        $outputDir = storage_path('code_names_pdf');
        $outputFile = $outputDir.'/code_names.pdf';

        $images = collect(File::files($sourceDir))
            ->filter(fn ($file) => strtolower($file->getExtension()) === 'png')
            ->sortBy(fn ($file) => $file->getFilename())
            ->values();

        if ($images->isEmpty()) {
            $this->error('No PNG images found in storage/code_names/');

            return self::FAILURE;
        }

        $this->info("Found {$images->count()} images. Generating PDF...");

        File::ensureDirectoryExists($outputDir);

        $marginX = (self::PAGE_WIDTH - (self::COLUMNS * self::IMAGE_SIZE) - ((self::COLUMNS - 1) * self::GAP)) / 2;
        $marginY = (self::PAGE_HEIGHT - (self::ROWS * self::IMAGE_SIZE) - ((self::ROWS - 1) * self::GAP)) / 2;

        $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetAutoPageBreak(false);
        $pdf->SetMargins(0, 0, 0);
        $pdf->SetHeaderMargin(0);
        $pdf->SetFooterMargin(0);
        $pdf->SetCellPadding(0);

        $pages = $images->chunk(self::IMAGES_PER_PAGE);

        foreach ($pages as $pageIndex => $pageImages) {
            $pdf->AddPage();

            $pageNumber = $pageIndex + 1;
            if ($pageNumber % 2 !== 0) {
                $this->drawCutLines($pdf, $marginX, $marginY);
            }

            foreach ($pageImages->values() as $index => $image) {
                $col = $index % self::COLUMNS;
                $row = intdiv($index, self::COLUMNS);

                $x = $marginX + $col * (self::IMAGE_SIZE + self::GAP);
                $y = $marginY + $row * (self::IMAGE_SIZE + self::GAP);

                $pdf->Image(
                    $image->getPathname(),
                    $x,
                    $y,
                    self::IMAGE_SIZE,
                    self::IMAGE_SIZE,
                    'PNG',
                );

                $this->drawTopMarker($pdf, $x, $y);
            }
        }

        $pdf->Output($outputFile, 'F');

        $this->info("PDF saved to: {$outputFile}");
        $this->info("Total pages: {$pages->count()}");

        return self::SUCCESS;
    }

    /**
     * Draw a small filled triangle in the top-left corner of an image to indicate orientation.
     */
    private function drawTopMarker(TCPDF $pdf, float $x, float $y): void
    {
        $offset = 3.5;
        $leg = 4.8;
        $r = 1.2;

        $cornerX = $x + $offset;
        $cornerY = $y + $offset;

        $pdf->SetFillColor(180, 180, 180);

        // Triangle body with the top-left corner cut off
        $pdf->Polygon(
            [
                $cornerX + $r, $cornerY,
                $cornerX + $leg, $cornerY,
                $cornerX, $cornerY + $leg,
                $cornerX, $cornerY + $r,
            ],
            'F',
        );

        // Rounded arc filling the cut corner
        $pdf->PieSector($cornerX + $r, $cornerY + $r, $r, 270, 360, 'F');
    }

    /**
     * Draw black cut lines between images as cutting guides.
     */
    private function drawCutLines(TCPDF $pdf, float $marginX, float $marginY): void
    {
        $pdf->SetDrawColor(0, 0, 0);
        $pdf->SetLineWidth(0.2);

        $gridWidth = self::COLUMNS * self::IMAGE_SIZE + (self::COLUMNS - 1) * self::GAP;
        $gridHeight = self::ROWS * self::IMAGE_SIZE + (self::ROWS - 1) * self::GAP;

        // Outer border
        $pdf->Rect($marginX, $marginY, $gridWidth, $gridHeight);

        // Vertical lines between columns
        for ($col = 1; $col < self::COLUMNS; $col++) {
            $x = $marginX + $col * (self::IMAGE_SIZE + self::GAP) - self::GAP / 2;
            $pdf->Line($x, $marginY, $x, $marginY + $gridHeight);
        }

        // Horizontal lines between rows
        for ($row = 1; $row < self::ROWS; $row++) {
            $y = $marginY + $row * (self::IMAGE_SIZE + self::GAP) - self::GAP / 2;
            $pdf->Line($marginX, $y, $marginX + $gridWidth, $y);
        }
    }
}
