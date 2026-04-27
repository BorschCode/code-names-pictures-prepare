<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use TCPDF;

class GenerateCodeNamesWordsPdf extends Command
{
    protected $signature = 'app:generate-code-names-words-pdf {file}';
    protected $description = 'Generate CodeNames PDF: Larger mirrored font, large rotated icons, wheel circle style';

    private const COLS = 4;
    private const ROWS = 4;
    private const PAGE_W = 297;
    private const PAGE_H = 210;
    private const START_X = 18;
    private const START_Y = 15;
    private const CELL_W = 65;
    private const CELL_H = 45;

    private const ICON_SIZE = 12;
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

        $output = storage_path('code_names_pdf/code_names_words.pdf');
        File::ensureDirectoryExists(dirname($output));
        $pdf->Output($output, 'F');

        $this->info("PDF згенеровано (Emoji видалено): {$output}");
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
        // 1. Очищення тексту від Emoji (вони не підтримуються шрифтами PDF)
        // Регулярний вираз видаляє 4-байтні символи Unicode (емодзі)
        $cleanWord = preg_replace('/[\x{1F600}-\x{1F64F}\x{1F300}-\x{1F5FF}\x{1F680}-\x{1F6FF}\x{1F1E6}-\x{1F1FF}\x{2600}-\x{26FF}\x{2700}-\x{27BF}\x{1F900}-\x{1F9FF}\x{1F018}-\x{1F093}\x{1F100}-\x{1F1FF}\x{1F170}-\x{1F171}\x{1F17E}-\x{1F17F}\x{1F18E}\x{1F300}-\x{1F5FF}\x{1F600}-\x{1F64F}\x{1F680}-\x{1F6FF}\x{1F900}-\x{1F9FF}\x{2049}\x{203C}\x{2139}\x{2122}\x{2194}-\x{2199}\x{21A9}-\x{21AA}\x{231A}-\x{231B}\x{23E9}-\x{23EC}\x{23F0}\x{23F3}\x{24C2}\x{25AA}-\x{25AB}\x{25B6}\x{25C0}\x{25FB}-\x{25FE}\x{2600}-\x{2604}\x{260E}\x{2611}\x{2614}-\x{2615}\x{2618}\x{261D}\x{2620}\x{2622}-\x{2623}\x{2626}\x{262A}\x{262E}-\x{262F}\x{2638}-\x{263A}\x{2640}\x{2642}\x{2648}-\x{2653}\x{2660}\x{2663}\x{2665}-\x{2666}\x{2668}\x{267B}\x{267F}\x{2692}-\x{2697}\x{2699}\x{269B}-\x{269C}\x{26A0}-\x{26A1}\x{26AA}-\x{26AB}\x{26B0}-\x{26B1}\x{26BD}-\x{26BE}\x{26C4}-\x{26C5}\x{26C8}\x{26CE}-\x{26CF}\x{26D1}\x{26D3}-\x{26D4}\x{26E9}-\x{26EA}\x{26F0}-\x{26F5}\x{26F7}-\x{26FA}\x{26FD}\x{2702}\x{2705}\x{2708}-\x{270D}\x{270F}\x{2712}\x{2714}\x{2716}\x{271D}\x{2721}\x{2728}\x{2733}-\x{2734}\x{2744}\x{2747}\x{274C}\x{274E}\x{2753}-\x{2755}\x{2757}\x{2763}-\x{2764}\x{2795}-\x{2797}\x{27A1}\x{27B0}\x{27BF}\x{2934}-\x{2935}\x{2B05}-\x{2B07}\x{2B1B}-\x{2B1C}\x{2B50}\x{2B55}\x{3030}\x{303D}\x{3297}\x{3299}]/u', '', $word);

        $cleanWord = trim($cleanWord);
        $cleanWord = mb_strtoupper($cleanWord);

        // Вибір випадкової іконки
        $iconsDir = storage_path('icons');
        $iconFiles = glob($iconsDir . '/*.png');
        $randomIcon = !empty($iconFiles) ? $iconFiles[array_rand($iconFiles)] : null;

        // --- ВЕРХНЯ ЧАСТИНА ---
        $pdf->SetDrawColor(40, 40, 40);
        $pdf->SetLineWidth(0.6);
        $pdf->Circle($x + (self::CELL_W / 2), $y + 5, self::COMPASS_CIRCLE_RADIUS, 0, 360, 'D');

        $pdf->SetFont('dejavusans', 'B', 20);
        $pdf->SetTextColor(20, 20, 20);
        $pdf->SetXY($x, $y + 10);
        $pdf->Cell(self::CELL_W, 10, $cleanWord, 0, 0, 'C');

        $pdf->SetDrawColor(150, 150, 150);
        $pdf->SetLineWidth(0.4);
        $pdf->Line($x + 8, $y + 21, $x + self::CELL_W - 8, $y + 21);

        // --- НИЖНЯ ЧАСТИНА (ПЕРЕВЕРНУТА) ---
        $pdf->StartTransform();
        $pdf->Rotate(180, $x + (self::CELL_W / 2), $y + (self::CELL_H / 2));

        $pdf->SetFont('dejavusans', '', 14);
        $pdf->SetTextColor(80, 80, 80);
        $pdf->SetXY($x + 5, $y + 5);
        $pdf->Cell(self::CELL_W - 22, 14, $cleanWord, 0, 0, 'L');

        if ($randomIcon && file_exists($randomIcon)) {
            $iconX = $x + self::CELL_W - self::ICON_SIZE - 5;
            $iconY = $y + 6;
            $pdf->StartTransform();
            $pdf->Rotate(180, $iconX + (self::ICON_SIZE / 2), $iconY + (self::ICON_SIZE / 2));
            $pdf->Image($randomIcon, $iconX, $iconY, self::ICON_SIZE, self::ICON_SIZE);
            $pdf->StopTransform();
        }

        $pdf->StopTransform();
    }
}
