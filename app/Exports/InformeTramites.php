<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\RegistersEventListeners;

class InformeTramites implements FromView, ShouldAutoSize, WithEvents
{
	use Exportable, RegistersEventListeners;
	public static $filtersCount = 0;
	public function __construct($data,$request=[],$filters=[])
	{
	    $this->data = $data;
	    $this->request = $request;
	    $this->filters = $filters;
	    static::$filtersCount = count($this->filters);
	}	

    public function view(): View
    {
        return view('informes.tramites', [
            'data' => $this->data,
            'request' => $this->request,
            'filters' => $this->filters
        ]);
    }

	public static function afterSheet(AfterSheet $event)
    {
    	$filtersCount   = static::$filtersCount;
    	
    	$headerRange 	= 'A'.($filtersCount+2).':'.$event->sheet->getHighestColumn().($filtersCount+2);
    	$tableRange  	= 'A'.($filtersCount+2).':'.$event->sheet->getHighestColumn().($event->sheet->getHighestRow()-2);
    	$filtersRange	= 'A1:A'.$filtersCount;
    	$totalsRange	= 'A'.$event->sheet->getHighestRow().':B'.$event->sheet->getHighestRow();

		$tableStyle = [
		  'borders' =>	[
		      'allBorders' => [
		          'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
		          'color' => ['argb' => '000000'],
		      ]
		  ]
		];

		$headerStyle = $filtersStyle = [
            'font' => [
                'bold' => true
            ]   
		];

		$event->sheet->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);

        $event->sheet->getStyle($filtersRange)->applyFromArray($filtersStyle);

        $event->sheet->getStyle($headerRange)->applyFromArray($headerStyle);
	    
        $event->sheet->getStyle($tableRange)->applyFromArray($tableStyle);

        $event->sheet->getStyle($totalsRange)->applyFromArray($tableStyle+$filtersStyle);

    	
    }
}