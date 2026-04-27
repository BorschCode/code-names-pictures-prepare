<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use TCPDF;

class GenerateCodeNamesWordsPdf extends Command
{
    protected $signature = 'app:generate-code-names-words-pdf {file}';
    protected $description = 'Generate CodeNames PDF: 2 words per card (Large + Mirrored Small with Icon)';

    private const COLS = 4;
    private const ROWS = 4;
    private const PAGE_W = 297;
    private const PAGE_H = 210;
    private const START_X = 18;
    private const START_Y = 15;
    private const CELL_W = 65;
    private const CELL_H = 45;
    private const ICON_SIZE = 6;

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

        $perPage = self::COLS * self::ROWS;

        foreach (array_chunk($words, $perPage) as $pageIndex => $chunk) {
            $pdf->AddPage();

            // Grid тільки на непарних сторінках
            $isBackside = ($pageIndex % 2 === 1);
            if (!$isBackside) {
                $this->drawGrid($pdf);
            }

            foreach ($chunk as $i => $word) {
                $col = $i % self::COLS;
                $row = intdiv($i, self::COLS);

                // Дзеркальне відображення колонок для backside (дуплекс)
                if ($isBackside) {
                    $x = self::PAGE_W - self::START_X - ($col + 1) * self::CELL_W;
                } else {
                    $x = self::START_X + $col * self::CELL_W;
                }
                $y = self::START_Y + $row * self::CELL_H;

                $this->drawSingleCardLayout($pdf, $x, $y, $word);
            }
        }

        $output = storage_path('code_names_pdf/code_names_v3.pdf');
        File::ensureDirectoryExists(dirname($output));
        $pdf->Output($output, 'F');

        $this->info("Saved to: {$output}");
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

    private function drawSingleCardLayout(TCPDF $pdf, float $x, float $y, string $word): void
    {
        $word = mb_strtoupper($word);
        $iconPath = storage_path('code_names/icon.png');

        // 1. ВЕЛИКЕ СЛОВО (зверху, прямо)
        $pdf->SetTextColor(30, 30, 30); // Grayscale майже чорний
        $pdf->SetFont('dejavusans', 'B', 18);
        $pdf->SetXY($x, $y + 8);
        $pdf->Cell(self::CELL_W, 10, $word, 0, 0, 'C');

        // 2. Divider line (рівно по центру картки)
        $pdf->SetDrawColor(160, 160, 160);
        $pdf->SetLineWidth(0.2);
        $pdf->Line($x + 6, $y + (self::CELL_H / 2), $x + self::CELL_W - 6, $y + (self::CELL_H / 2));

        // 3. маленьке слово + іконка (ЗНИЗУ, ПЕРЕВЕРНУТЕ)
        // Повертаємо тільки нижню частину
        $pdf->StartTransform();
        // Робимо поворот навколо центру картки
        $pdf->Rotate(180, $x + self::CELL_W / 2, $y + self::CELL_H / 2);

        // Малюємо мале слово так само, як велике, але в "своїй" половині
        $pdf->SetFont('dejavusans', '', 11);
        $pdf->SetTextColor(100, 100, 100); // Grayscale сірий

        // Координата Y для малого слова після повороту
        // (воно опиниться внизу картки, якщо дивитись прямо)
        $pdf->SetXY($x + 5, $y + 8);
        $pdf->Cell(self::CELL_W - 15, 10, $word, 0, 0, 'L');

        // Іконка на рівні малого слова
        if (file_exists($iconPath)) {
            $pdf->Image(
                $iconPath,
                $x + self::CELL_W - self::ICON_SIZE - 6,
                $y + 10,
                self::ICON_SIZE,
                self::ICON_SIZE
            );
        }

        $pdf->StopTransform();
    }
}
