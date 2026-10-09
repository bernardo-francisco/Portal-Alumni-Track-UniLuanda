<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class BaseExport implements FromArray, WithHeadings, WithStyles, ShouldAutoSize, WithTitle
{
    protected array $headings;
    protected array $rows;
    protected string $title;

    public function __construct(array $headings, array $rows, string $title = 'Relatório')
    {
        $this->headings = $headings;
        $this->rows     = $rows;
        $this->title    = $title;
    }

    public function array(): array
    {
        return $this->rows;
    }

    public function headings(): array
    {
        return $this->headings;
    }

    public function title(): string
    {
        return mb_substr($this->title, 0, 30); // máximo 31 caracteres no Excel
    }

    public function styles(Worksheet $sheet)
    {
        // Cabeçalho
        $sheet->getStyle('A1:' . $sheet->getHighestColumn() . '1')->applyFromArray([
            'font' => [
                'bold'  => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size'  => 11,
            ],
            'fill' => [
                'fillType'   => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '1A56DB'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER,
            ],
        ]);

        // Linhas de dados
        $highestRow = $sheet->getHighestRow();
        $highestCol = $sheet->getHighestColumn();

        if ($highestRow > 1) {
            $sheet->getStyle("A2:{$highestCol}{$highestRow}")->applyFromArray([
                'alignment' => [
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ]);

            // Zebra striping
            for ($i = 2; $i <= $highestRow; $i++) {
                if ($i % 2 === 0) {
                    $sheet->getStyle("A{$i}:{$highestCol}{$i}")->applyFromArray([
                        'fill' => [
                            'fillType'   => Fill::FILL_SOLID,
                            'startColor' => ['rgb' => 'F1F5F9'],
                        ],
                    ]);
                }
            }
        }

        return [];
    }
}