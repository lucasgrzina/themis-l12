<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

/**
 * Informe de tramites archivados automaticamente por el proceso
 * de archivado de "Beneficio Acordado". Sirve de hoja de trabajo
 * para la posterior destruccion de la carpeta fisica.
 */
class TramitesArchivadosExport implements FromArray, WithHeadings, ShouldAutoSize, WithTitle
{
    /** @var array<int,array<string,mixed>> */
    protected $rows;

    /** @param array<int,array<string,mixed>> $rows */
    public function __construct(array $rows)
    {
        $this->rows = $rows;
    }

    public function array(): array
    {
        return $this->rows;
    }

    public function headings(): array
    {
        return [
            'ID Tramite',
            'Expediente',
            'Cliente',
            'CUIT/CUIL',
            'Ubicacion carpeta',
            'Estado',
            'Fecha inicio',
            'Nro beneficio',
            'Fecha beneficio',
            'Fecha archivo',
        ];
    }

    public function title(): string
    {
        return 'Tramites archivados';
    }
}
