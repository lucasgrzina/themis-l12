<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Listado plano: una fila por hora cargada.
 * Columna "Horas" decimal (sumable en Excel) + "Horas (hh:mm)" legible.
 */
class InformeTimeAnalisis implements FromArray, WithHeadings, WithColumnFormatting, WithStyles, WithTitle, ShouldAutoSize
{
    use Exportable;

    private $rows;

    public function __construct($rows)
    {
        $this->rows = $rows;
    }

    public function title(): string
    {
        return 'Horas';
    }

    public function headings(): array
    {
        return ['Fecha', 'Abogado', 'Cliente', 'Gestión', 'Referencia', 'Descripción',
                'Horas', 'Horas (hh:mm)', 'Facturable', 'Estado'];
    }

    public function array(): array
    {
        $out = [];
        foreach ($this->rows as $r) {
            $min = (int) $r->minutos;
            $out[] = [
                \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel(substr($r->fecha, 0, 10)),
                $r->abogado,
                $r->cliente,
                $r->gestion,
                $r->referencia,
                $r->descripcion,
                round($min / 60, 2),
                sprintf('%d:%02d', intdiv($min, 60), $min % 60),
                $r->facturar ? 'Sí' : 'No',
                $r->estado === 'pendiente' ? 'Pendiente' : 'Confirmada',
            ];
        }
        return $out;
    }

    public function columnFormats(): array
    {
        return [
            'A' => NumberFormat::FORMAT_DATE_DDMMYYYY,
            'G' => '0.00',
        ];
    }

    public function styles(Worksheet $sheet): ?array
    {
        $sheet->freezePane('A2');
        $sheet->setAutoFilter($sheet->calculateWorksheetDimension());
        return [1 => ['font' => ['bold' => true]]];
    }
}
