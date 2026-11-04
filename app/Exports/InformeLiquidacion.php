<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\Exportable;

/**
 * Export XLSX del informe de liquidacion de honorarios del Time.
 * Recibe el arreglo calculado por App\Services\LiquidacionService.
 */
class InformeLiquidacion implements FromView, ShouldAutoSize
{
    use Exportable;

    public $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function view(): View
    {
        return view('time.liquidacion-export', ['data' => $this->data]);
    }
}
