<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use TCPDF;

class GenerateCodeNamesWordsPdf extends Command
{
    protected $signature = 'app:generate-code-names-words-pdf {file}';
    protected $description = 'Generate CodeNames PDF: Ring style, random icons from storage/icons with rotation';

    private const COLS = 4;
    private const ROWS = 4;
    private const PAGE_W = 297;
    private const PAGE_H = 210;
    private const START_X = 18;
    private const START_Y = 15;
    private const CELL_W = 65;
    private const CELL_H = 45;
    private const ICON_SIZE = 7;
    private const COMPASS_CIRCLE_RADIUS = 2.5;

    public function handle(): int
    {
        $file = $this->argument('file');

        if (!File::exists($file)) {
            $this->error('File not found');
            return self::FAILURE;
        }

        $words = $this->loadWords($file);
        if (!$words) {
            $this->error('No words found');
            return self::FAILURE;
        }

        $pdf = new TCPDF('L', 'mm', 'A4', true, 'UTF-8', false);
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetMargins(0, 0, 0);
        $pdf->SetAutoPageBreak(false);

        foreach (array_chunk($words, self::COLS * self::ROWS) as $pageIndex => $chunk) {
            $pdf->AddPage();
            $isBackside = ($pageIndex % 2 === 1);

            if (!$isBackside) {
                $this->drawGrid($pdf);
            }

            foreach ($chunk as $i => $word) {
                $col = $i % self::COLS;
                $row = intdiv($i, self::COLS);

                if ($isBackside) {
                    $x = self::PAGE_W - self::START_X - ($col + 1) * self::CELL_W;
                } else {
                    $x = self::START_X + $col * self::CELL_W;
                }
                $y = self::START_Y + $row * self::CELL_H;

                $this->drawSingleCard($pdf, $x, $y, $word);
            }
        }

        $output = storage_path('code_names_pdf/code_names_final.pdf');
        File::ensureDirectoryExists(dirname($output));
        $pdf->Output($output, 'F');

        $this->info("Готово! Файл збережено: {$output}");
        return self::SUCCESS;
    }

    private function loadWords(string $file): array
    {
        return array_filter(array_map('trim', file($file)));
    }

    private function drawGrid(TCPDF $pdf): void
    {
        $pdf->SetDrawColor(180, 180, 180);
        $pdf->SetLineWidth(0.1);
        for ($c = 0; $c <= self::COLS; $c++) {
            $x = self::START_X + $c * self::CELL_W;
            $pdf->Line($x, self::START_Y, $x, self::START_Y + self::ROWS * self::CELL_H);
        }
        for ($r = 0; $r <= self::ROWS; $r++) {
            $y = self::START_Y + $r * self::CELL_H;
            $pdf->Line(self::START_X, $y, self::START_X + self::COLS * self::CELL_W, $y);
        }
    }

    private function drawSingleCard(TCPDF $pdf, float $x, float $y, string $word): void
    {
        $word = mb_strtoupper($word);

        // --- ЛОГІКА ІКОНОК ---
        $iconsDir = storage_path('icons');
        $iconFiles = glob($iconsDir . '/*.png');
        $randomIcon = !empty($iconFiles) ? $iconFiles[array_rand($iconFiles)] : null;

        // --- ВЕРХНЯ ЧАСТИНА (ПРЯМА) ---

        // 1. Circle "Колесо"
        $pdf->SetDrawColor(40, 40, 40);
        $pdf->SetLineWidth(0.6);
        $pdf->Circle($x + (self::CELL_W / 2), $y + 5, self::COMPASS_CIRCLE_RADIUS, 0, 360, 'D');

        // 2. Велике слово
        $pdf->SetFont('dejavusans', 'B', 20);
        $pdf->SetTextColor(20, 20, 20);
        $pdf->SetXY($x, $y + 10);
        $pdf->Cell(self::CELL_W, 10, $word, 0, 0, 'C');

        // 3. Divider
        $pdf->SetDrawColor(150, 150, 150);
        $pdf->SetLineWidth(0.4);
        $pdf->Line($x + 8, $y + 21, $x + self::CELL_W - 8, $y + 21);


        // --- НИЖНЯ ЧАСТИНА (ПЕРЕВЕРНУТА НА 180) ---

        $pdf->StartTransform();
        $pdf->Rotate(180, $x + (self::CELL_W / 2), $y + (self::CELL_H / 2));

        // Мале слово
        $pdf->SetFont('dejavusans', '', 11);
        $pdf->SetTextColor(80, 80, 80);
        $pdf->SetXY($x + 5, $y + 7);
        $pdf->Cell(self::CELL_W - 15, 8, $word, 0, 0, 'L');

        // Іконка з випадковим поворотом
        if ($randomIcon && file_exists($randomIcon)) {
            $randomRotation = rand(-20, 20);

            // Координати центру іконки для повороту
            $iconX = $x + self::CELL_W - self::ICON_SIZE - 6;
            $iconY = $y + 7.5;
            $centerX = $iconX + (self::ICON_SIZE / 2);
            $centerY = $iconY + (self::ICON_SIZE / 2);

            $pdf->StartTransform();
            // Повертаємо саму іконку навколо її власного центру
            $pdf->Rotate($randomRotation, $centerX, $centerY);

            $pdf->Image(
                $randomIcon,
                $iconX,
                $iconY,
                self::ICON_SIZE,
                self::ICON_SIZE
            );
            $pdf->StopTransform();
        }

        $pdf->StopTransform();
    }
}
