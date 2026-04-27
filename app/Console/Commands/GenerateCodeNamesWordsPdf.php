<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use TCPDF;

class GenerateCodeNamesWordsPdf extends Command
{
    protected $signature = 'app:generate-code-names-words-pdf {file}';
    protected $description = 'Generate CodeNames PDF: Strict layout orientation';

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

        $output = storage_path('code_names_pdf/code_names_fixed.pdf');
        File::ensureDirectoryExists(dirname($output));
        $pdf->Output($output, 'F');

        $this->info("Готово: {$output}");
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
        $iconPath = storage_path('code_names/icon.png');

        // --- ВЕРХНЯ ЧАСТИНА (ПРЯМА) ---

        // 1. Circle (строго центр верх)
        $pdf->SetFillColor(60, 60, 60);
        $pdf->Circle($x + (self::CELL_W / 2), $y + 4, self::COMPASS_CIRCLE_RADIUS, 0, 360, 'F');

        // 2. Велике слово
        $pdf->SetFont('dejavusans', 'B', 20);
        $pdf->SetTextColor(20, 20, 20);
        $pdf->SetXY($x, $y + 8);
        $pdf->Cell(self::CELL_W, 10, $word, 0, 0, 'C');

        // 3. Divider (під великим словом)
        $pdf->SetDrawColor(140, 140, 140);
        $pdf->SetLineWidth(0.4);
        $pdf->Line($x + 6, $y + 20, $x + self::CELL_W - 6, $y + 20);


        // --- НИЖНЯ ЧАСТИНА (ПЕРЕВЕРНУТА НА 180) ---

        $pdf->StartTransform();
        // Поворот навколо центру картки
        $pdf->Rotate(180, $x + (self::CELL_W / 2), $y + (self::CELL_H / 2));

        /* * Оскільки ми перевернули весь простір, "верх" став "низом".
         * Щоб текст опинився внизу фізичної картки, ми малюємо його в "новому верху".
         */

        // Мале слово (центроване)
        $pdf->SetFont('dejavusans', '', 11);
        $pdf->SetTextColor(80, 80, 80);
        // Ставимо Y так, щоб воно було симетрично нижній частині
        $pdf->SetXY($x, $y + 8);
        $pdf->Cell(self::CELL_W, 8, $word, 0, 0, 'C');

        // Іконка праворуч від малого слова
        if (file_exists($iconPath)) {
            $pdf->Image(
                $iconPath,
                $x + self::CELL_W - self::ICON_SIZE - 6,
                $y + 8.5, // Центруємо відносно тексту
                self::ICON_SIZE,
                self::ICON_SIZE
            );
        }

        $pdf->StopTransform();
    }
}
