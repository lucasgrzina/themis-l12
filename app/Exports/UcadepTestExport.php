<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\RegistersEventListeners;

class UcadepTestExport implements FromView, ShouldAutoSize, WithEvents
{
	use Exportable, RegistersEventListeners;
	public function __construct($data)
	{
	    $this->data = $data;
	}	

    public function view(): View
    {
        return view('exports.ucadep', [
            'data' => $this->data
        ]);
    }
}