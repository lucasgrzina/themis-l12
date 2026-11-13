<?php

namespace App\Services;

use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Arma el XLSX de liquidacion de honorarios del Time a partir del arreglo que
 * calcula LiquidacionService.
 *
 * Una solapa por cliente (replica "HOJA MODELO HS" / "HOJA MODELO PRESUP" de la planilla
 * mensual), mas las solapas SIRCREB (tasa referenciada por formula) y TOTALES.
 * Las celdas de horas/minutos, tarifas, tasas y % son editables (fondo amarillo claro) y
 * todos los importes que dependen de ellas son formulas.
 */
class LiquidacionXlsx
{
    const MONEY = '"$" #,##0.00';
    const USD = '"U$S" #,##0.00';
    const PCT = '0.00%';
    const INPUT = 'FFFFF2CC';
    const HEAD = 'FFFFFF00';
    const NETO = 'FF2EF274';

    /** Nombres de hoja ya usados (para evitar duplicados). */
    private $usados = [];

    /** Por cliente: ['sheet' => titulo, 'neto' => 'D18', 'parts' => [nombre => 'C60']] */
    private $resumen = [];

    public function build(array $data): Spreadsheet
    {
        $ss = new Spreadsheet();
        $sirc = $ss->getActiveSheet();
        $this->usados = [];
        $this->resumen = [];

        $sirc->setTitle($this->titulo('SIRCREB'));
        $sirc->setCellValue('A3', 'SIRCREB');
        $sirc->setCellValue('B3', (float) $data['retenciones']['sircreb']);
        $sirc->getStyle('B3')->getNumberFormat()->setFormatCode(self::PCT);
        $this->fill($sirc, 'B3', self::INPUT);
        $sirc->getStyle('A3')->getFont()->setBold(true);
        $sirc->getColumnDimension('A')->setWidth(14);
        $sirc->getColumnDimension('B')->setWidth(12);

        foreach ($data['clientes'] as $c) {
            $ws = $ss->createSheet();
            $ws->setTitle($this->titulo($c['cliente']));
            if ($c['modelo'] === 'presupuesto') {
                $this->hojaPresupuesto($ws, $c, $data);
            } else {
                $this->hojaHoras($ws, $c, $data);
            }
        }

        $this->hojaTotales($ss->createSheet());

        $ss->setActiveSheetIndex(0);
        return $ss;
    }

    // ------------------------------------------------------------------
    // Solapa por horas
    // ------------------------------------------------------------------

    private function hojaHoras(Worksheet $ws, array $c, array $data)
    {
        $ret = $data['retenciones'];
        $sirc = $this->ref('SIRCREB', 'B3');

        $ws->setCellValue('A1', $c['cliente'] . ' — ' . $this->periodo($data));
        $ws->getStyle('A1')->getFont()->setBold(true)->setSize(12);

        // ---- Cuadro de valores de hora acordados (K:P) --------------------
        foreach (['L' => 'VALOR', 'M' => 'IMP CTO Y DTO', 'N' => 'SIRCREB', 'O' => 'RET GCIAS', 'P' => 'NETO'] as $col => $txt) {
            $ws->setCellValue($col . '1', $txt);
        }
        $this->bold($ws, 'K1:P1');
        $ws->setCellValue('M2', (float) $ret['imp_cto_dto']);
        $ws->setCellValue('N2', '=+' . $sirc);
        $ws->setCellValue('O2', (float) $ret['ret_gcias']);
        $ws->getStyle('M2:O2')->getNumberFormat()->setFormatCode(self::PCT);
        $this->fill($ws, 'M2:O2', self::INPUT);

        $tarifas = $c['tarifas'];
        if (!$this->tieneBase($tarifas)) {
            array_unshift($tarifas, ['clave' => '', 'categoria' => null, 'etiqueta' => 'HORA', 'valor' => 0.0]);
        }
        $tarRow = [];
        $r = 3;
        foreach ($tarifas as $t) {
            $tarRow[$t['clave']] = $r;
            $ws->setCellValue("K$r", $t['etiqueta']);
            $ws->setCellValue("L$r", (float) $t['valor']);
            $this->fill($ws, "L$r", self::INPUT);
            $this->filasImporte($ws, $r);
            $r++;
            $ws->setCellValue("K$r", str_replace('HORA', 'MINUTOS', $t['etiqueta']));
            $ws->setCellValue("L$r", "=+L" . ($r - 1) . "/60");
            $this->filasImporte($ws, $r);
            $r++;
        }
        $ws->getStyle('L3:P' . ($r - 1))->getNumberFormat()->setFormatCode(self::USD);
        $finTarifas = $r - 1;

        // ---- Horas reales cargadas en el Time (referencia), hs y min separados
        $r += 1;
        $ws->setCellValue("K$r", 'HORAS REALES (Time)');
        $ws->setCellValue("L$r", 'HS');
        $ws->setCellValue("M$r", 'MIN');
        $this->bold($ws, "K$r:M$r");
        $r++;
        $iniReales = $r;
        foreach ($c['abogados'] as $a) {
            if ($a['es_director']) {
                continue;
            }
            $ws->setCellValue("K$r", $a['abogado']);
            $ws->setCellValue("L$r", intdiv((int) $a['minutos'], 60));
            $ws->setCellValue("M$r", (int) $a['minutos'] % 60);
            $r++;
        }
        $ws->setCellValue("K$r", 'TOTAL');
        if ($r > $iniReales) {
            $fin = $r - 1;
            $ws->setCellValue("L$r", "=SUM(L$iniReales:L$fin)+INT(SUM(M$iniReales:M$fin)/60)");
            $ws->setCellValue("M$r", "=MOD(SUM(M$iniReales:M$fin),60)");
        } else {
            $ws->setCellValue("L$r", 0);
            $ws->setCellValue("M$r", 0);
        }
        $this->bold($ws, "K$r:M$r");
        $r++;
        if ($c['minutos_piso'] > 0) {
            $ws->setCellValue("K$r", 'PISO (abono)');
            $ws->setCellValue("L$r", intdiv((int) $c['minutos_piso'], 60));
            $ws->setCellValue("M$r", (int) $c['minutos_piso'] % 60);
            $r++;
            $ws->setCellValue("K$r", 'EXCEDENTE');
            $ws->setCellValue("L$r", intdiv((int) $c['minutos_excedente'], 60));
            $ws->setCellValue("M$r", (int) $c['minutos_excedente'] % 60);
            $r++;
        }
        if ($c['horas_director'] > 0) {
            $minDir = (int) round($c['horas_director'] * 60);
            $ws->setCellValue("K$r", 'DIRECTOR (parámetro)');
            $ws->setCellValue("L$r", intdiv($minDir, 60));
            $ws->setCellValue("M$r", $minDir % 60);
            $r++;
        }
        $finDerecha = $r - 1;

        // ---- Totales mensuales (A:E) --------------------------------------
        $ws->setCellValue('A2', 'TOTAL HORAS MENSUALES');
        $ws->setCellValue('B2', 'VALOR HORA');
        $ws->setCellValue('C2', 'HS');
        $ws->setCellValue('D2', 'MIN');
        $ws->setCellValue('E2', 'U$S');
        $this->fill($ws, 'A2:E2', self::HEAD);
        $this->bold($ws, 'A2:E2');
        $ws->getStyle('B2:E2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $ws->setCellValue('G2', 'TC MEP');
        $ws->setCellValue('H2', (float) $data['tc']);
        $ws->getStyle('H2')->getNumberFormat()->setFormatCode('0.00');
        $this->fill($ws, 'G2:H2', self::HEAD);
        $this->bold($ws, 'G2:H2');

        $r = 3;
        foreach ($c['lineas'] as $l) {
            $tr = $tarRow[$l['tarifa_clave']] ?? $tarRow[''];
            $min = (int) $l['minutos'];
            $ws->setCellValue("A$r", $l['etiqueta']);
            $ws->setCellValue("B$r", "=+L$tr");
            $ws->setCellValue("C$r", intdiv($min, 60));
            $ws->setCellValue("D$r", $min % 60);
            $ws->setCellValue("E$r", "=+B$r*(C$r+D$r/60)");
            $this->fill($ws, "C$r:D$r", self::INPUT);
            $r++;
        }
        $ws->getStyle('B3:B' . ($r - 1))->getNumberFormat()->setFormatCode(self::USD);
        $ws->getStyle('E3:E' . ($r - 1))->getNumberFormat()->setFormatCode(self::USD);
        $finLineas = $r - 1;

        $tot = $r + 1;
        $ws->setCellValue("D$tot", 'TOTAL U$S');
        $ws->setCellValue("E$tot", "=SUM(E3:E$finLineas)");
        $ws->getStyle("E$tot")->getNumberFormat()->setFormatCode(self::USD);
        $ws->setCellValue('D' . ($tot + 1), 'TOTAL $');
        $ws->setCellValue('E' . ($tot + 1), "=+E$tot*H2");
        $filasRet = [['IMP CTO Y DTO', 'M2'], ['SIRCREB', 'N2'], ['RET GCIAS', 'O2']];
        $rr = $tot + 2;
        foreach ($filasRet as [$txt, $cell]) {
            $ws->setCellValue("A$rr", $txt);
            $ws->setCellValue("D$rr", "=+$cell");
            $ws->getStyle("D$rr")->getNumberFormat()->setFormatCode(self::PCT);
            $ws->setCellValue("E$rr", '=-E' . ($tot + 1) . "*D$rr");
            $rr++;
        }
        $rowPart = $rr;
        $rowNeto = $rr + 1;
        $ws->setCellValue("A$rowPart", 'PARTICIPACIONES');
        $ws->setCellValue("A$rowNeto", 'NETO CVA');
        $ws->setCellValue("E$rowNeto", '=SUM(E' . ($tot + 1) . ":E$rowPart)");
        $ws->getStyle('E' . ($tot + 1) . ":E$rowNeto")->getNumberFormat()->setFormatCode(self::MONEY);
        $this->bold($ws, "A$rowNeto:E$rowNeto");
        $this->fill($ws, "E$rowNeto", self::NETO);
        $this->bold($ws, "D$tot:D" . ($tot + 1));

        // ---- Bloques de participacion por abogado (5 columnas c/u) --------
        $inicio = max($rowNeto, $finDerecha, $finTarifas) + 3;
        $grupos = [1, 7, 13, 19]; // A, G, M, S
        $abogados = array_values($c['abogados']);
        $celdaPart = [];
        foreach ($abogados as $i => $a) {
            $c0 = $grupos[$i % 4];
            $t = $inicio + intdiv($i, 4) * 8;
            $L = [];
            for ($k = 0; $k < 5; $k++) {
                $L[$k] = Coordinate::stringFromColumnIndex($c0 + $k);
            }
            $tr = $tarRow[$a['tarifa_clave']] ?? $tarRow[''];
            $min = (int) $a['minutos'];

            $ws->mergeCells("{$L[0]}$t:{$L[4]}$t");
            $ws->setCellValue("{$L[0]}$t", 'TOTAL HORAS NETAS DE IMP ' . mb_strtoupper($a['abogado']));
            $this->bold($ws, "{$L[0]}$t");
            $this->fill($ws, "{$L[0]}$t:{$L[4]}$t", 'FFD9E1F2');

            $h = $t + 1;
            $ws->setCellValue("{$L[1]}$h", 'VALOR NETO');
            $ws->setCellValue("{$L[2]}$h", 'HS');
            $ws->setCellValue("{$L[3]}$h", 'MIN');
            $ws->setCellValue("{$L[4]}$h", 'U$S');
            $this->bold($ws, "{$L[0]}$h:{$L[4]}$h");
            $ws->getStyle("{$L[1]}$h:{$L[4]}$h")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

            $d = $t + 2;
            $ws->setCellValue("{$L[0]}$d", 'HORAS TRABAJADAS');
            $ws->setCellValue("{$L[1]}$d", "=+P$tr");
            $ws->setCellValue("{$L[2]}$d", intdiv($min, 60));
            $ws->setCellValue("{$L[3]}$d", $min % 60);
            $ws->setCellValue("{$L[4]}$d", "=+{$L[1]}$d*({$L[2]}$d+{$L[3]}$d/60)");
            $this->fill($ws, "{$L[2]}$d:{$L[3]}$d", self::INPUT);

            $ws->setCellValue("{$L[3]}" . ($t + 3), 'TOTAL $');
            $ws->setCellValue("{$L[4]}" . ($t + 3), "=+{$L[4]}$d*\$H\$2");

            $p = $t + 4;
            $ws->setCellValue("{$L[0]}$p", 'PARTICIPACION');
            $ws->setCellValue("{$L[3]}$p", (float) $a['pct']);
            $ws->getStyle("{$L[3]}$p")->getNumberFormat()->setFormatCode(self::PCT);
            $this->fill($ws, "{$L[3]}$p", self::INPUT);
            $ws->setCellValue("{$L[4]}$p", "=+{$L[4]}" . ($t + 3) . "*{$L[3]}$p");
            $this->bold($ws, "{$L[0]}$p:{$L[4]}$p");

            $ws->getStyle("{$L[1]}$d")->getNumberFormat()->setFormatCode(self::USD);
            $ws->getStyle("{$L[4]}$d")->getNumberFormat()->setFormatCode(self::USD);
            $ws->getStyle("{$L[4]}" . ($t + 3) . ":{$L[4]}$p")->getNumberFormat()->setFormatCode(self::MONEY);

            $celdaPart[$a['abogado']] = "{$L[4]}$p";
        }

        // ---- Resumen de participaciones -----------------------------------
        $s = $inicio + (int) ceil(max(1, count($abogados)) / 4) * 8 + 1;
        $iniResumen = $s;
        $resumen = [];
        foreach ($abogados as $a) {
            $ws->setCellValue("A$s", 'PARTICIPACION ' . mb_strtoupper($a['abogado']));
            $ws->setCellValue("E$s", '=+' . $celdaPart[$a['abogado']]);
            $ws->getStyle("E$s")->getNumberFormat()->setFormatCode(self::MONEY);
            $resumen[$a['abogado']] = "E$s";
            $s++;
        }
        $ws->setCellValue("A$s", 'TOTAL PARTICIPACIONES');
        $ws->setCellValue("E$s", count($abogados) ? "=SUM(E$iniResumen:E" . ($s - 1) . ")" : 0);
        $ws->getStyle("E$s")->getNumberFormat()->setFormatCode(self::MONEY);
        $this->bold($ws, "A$s:E$s");
        $ws->setCellValue("E$rowPart", "=-E$s");

        // Anchos
        foreach (['A' => 30, 'B' => 14, 'C' => 8, 'D' => 12, 'E' => 18, 'F' => 4, 'G' => 22, 'H' => 14, 'I' => 8, 'J' => 8, 'K' => 26,
                  'L' => 14, 'M' => 14, 'N' => 14, 'O' => 14, 'P' => 18, 'Q' => 10, 'R' => 4, 'S' => 22, 'T' => 14, 'U' => 8, 'V' => 8, 'W' => 18] as $col => $w) {
            $ws->getColumnDimension($col)->setWidth($w);
        }

        $this->resumen[] = [
            'sheet' => $ws->getTitle(),
            'cliente' => $c['cliente'],
            'neto' => "E$rowNeto",
            'parts' => $resumen,
        ];
    }

    /** Columnas M:P de una fila del cuadro de valores: retenciones y neto por hora/minuto. */
    private function filasImporte(Worksheet $ws, $r)
    {
        $ws->setCellValue("M$r", "=-L$r*\$M\$2");
        $ws->setCellValue("N$r", "=-L$r*\$N\$2");
        $ws->setCellValue("O$r", "=-L$r*\$O\$2");
        $ws->setCellValue("P$r", "=SUM(L$r:O$r)");
    }

    private function tieneBase(array $tarifas)
    {
        foreach ($tarifas as $t) {
            if ($t['clave'] === '') {
                return true;
            }
        }
        return false;
    }

    // ------------------------------------------------------------------
    // Solapa por presupuesto
    // ------------------------------------------------------------------

    private function hojaPresupuesto(Worksheet $ws, array $c, array $data)
    {
        $ret = $data['retenciones'];
        $items = $c['items'];

        $desc = implode(' / ', array_filter(array_map(fn ($i) => $i['descripcion'], $items)));
        $ws->mergeCells('B3:C3');
        $ws->setCellValue('B3', $desc !== '' ? $desc : $c['cliente']);
        $ws->setCellValue('A1', $c['cliente'] . ' — ' . $this->periodo($data));
        $ws->getStyle('A1')->getFont()->setBold(true)->setSize(12);

        $num = fn ($v) => rtrim(rtrim(number_format((float) $v, 2, '.', ''), '0'), '.');
        $todos = implode('+', array_map(fn ($i) => $num($i['subtotal']), $items)) ?: '0';
        $conIva = array_filter($items, fn ($i) => $i['aplica_iva']);
        $ivaExpr = $conIva ? '(' . implode('+', array_map(fn ($i) => $num($i['subtotal']), $conIva)) . ')' : '0';

        $ws->setCellValue('D3', 'SUBTOTAL $');
        $ws->setCellValue('E3', "=$todos");
        $this->fill($ws, 'E3', self::INPUT);
        $ws->mergeCells('B4:C4');
        $ws->setCellValue('B4', 'SUBTOTAL CON IVA');
        $ws->setCellValue('D7', (float) $ret['iva']);
        $ws->setCellValue('E4', count($conIva) === count($items) ? '=+(E3*D7)+E3' : "=+E3+$ivaExpr*D7");

        $filas = [
            5 => ['IMP. AL CTO Y DTO (S/TOTAL TRANSF)', (float) $ret['imp_cto_dto'], '=-E4*D5'],
            6 => ['SIRCREB (S/TOTAL TRANSFERIDO)', '=+' . $this->ref('SIRCREB', 'B3'), '=-E4*D6'],
            7 => ['IVA', (float) $ret['iva'], count($conIva) === count($items) ? '=-E3*D7' : "=-$ivaExpr*D7"],
            8 => ['RETENCION DE GANANCIAS', (float) $ret['ret_gcias'], '=-E3*D8'],
        ];
        foreach ($filas as $r => [$txt, $tasa, $f]) {
            $ws->mergeCells("B$r:C$r");
            $ws->setCellValue("B$r", $txt);
            $ws->setCellValue("D$r", $tasa);
            $ws->setCellValue("E$r", $f);
            $ws->getStyle("D$r")->getNumberFormat()->setFormatCode(self::PCT);
            if ($r !== 6) {
                $this->fill($ws, "D$r", self::INPUT);
            }
        }

        $ext = $c['participaciones_externas'] ?? [];
        $r = 9;
        foreach ($ext as $p) {
            if ($p['base'] !== 'subtotal') {
                continue;
            }
            $ws->mergeCells("B$r:C$r");
            $ws->setCellValue("B$r", 'PARTICIPACION ' . mb_strtoupper($p['nombre']));
            $ws->setCellValue("D$r", (float) $p['porcentaje']);
            $ws->getStyle("D$r")->getNumberFormat()->setFormatCode(self::PCT);
            $this->fill($ws, "D$r", self::INPUT);
            $ws->setCellValue("E$r", "=-E3*D$r");
            $r++;
        }

        $sn = $r;
        $ws->mergeCells("B$sn:C$sn");
        $ws->setCellValue("B$sn", 'SUBTOTAL NETO');
        $ws->setCellValue("E$sn", '=SUM(E4:E' . ($sn - 1) . ')');
        $this->bold($ws, "B$sn:E$sn");
        $r++;
        foreach ($ext as $p) {
            if ($p['base'] !== 'subtotal_neto') {
                continue;
            }
            $ws->mergeCells("B$r:C$r");
            $ws->setCellValue("B$r", 'PARTICIPACION ' . mb_strtoupper($p['nombre']));
            $ws->setCellValue("D$r", (float) $p['porcentaje']);
            $ws->getStyle("D$r")->getNumberFormat()->setFormatCode(self::PCT);
            $this->fill($ws, "D$r", self::INPUT);
            $ws->setCellValue("E$r", "=-E$sn*D$r");
            $r++;
        }
        $neto = $r + 1;
        $ws->mergeCells("B$neto:D$neto");
        $ws->setCellValue("B$neto", 'NETO CVA');
        $ws->setCellValue("E$neto", "=SUM(E$sn:E" . ($r - 1) . ')');
        $this->bold($ws, "B$neto:E$neto");
        $this->fill($ws, "E$neto", self::NETO);

        $ws->getStyle("E3:E$neto")->getNumberFormat()->setFormatCode(self::MONEY);
        $ws->getColumnDimension('A')->setWidth(4);
        $ws->getColumnDimension('B')->setWidth(34);
        $ws->getColumnDimension('C')->setWidth(22);
        $ws->getColumnDimension('D')->setWidth(14);
        $ws->getColumnDimension('E')->setWidth(20);

        $this->resumen[] = [
            'sheet' => $ws->getTitle(),
            'cliente' => $c['cliente'],
            'neto' => "E$neto",
            'parts' => [],
        ];
    }

    // ------------------------------------------------------------------
    // Solapa TOTALES
    // ------------------------------------------------------------------

    private function hojaTotales(Worksheet $ws)
    {
        $ws->setTitle($this->titulo('TOTALES'));

        // Columnas por abogado: union de nombres, Director al final
        $nombres = [];
        foreach ($this->resumen as $r) {
            foreach (array_keys($r['parts']) as $n) {
                $nombres[$n] = true;
            }
        }
        $nombres = array_keys($nombres);
        usort($nombres, function ($a, $b) {
            return [$a === 'Director' ? 1 : 0, $a] <=> [$b === 'Director' ? 1 : 0, $b];
        });

        $ws->setCellValue('B3', 'CLIENTE');
        $ws->setCellValue('C3', 'NETO CVA');
        foreach ($nombres as $i => $n) {
            $ws->setCellValue(Coordinate::stringFromColumnIndex(4 + $i) . '3', $n === 'Director' ? 'PARTICIP DIRECTOR' : mb_strtoupper($n));
        }
        $colTot = Coordinate::stringFromColumnIndex(4 + count($nombres));
        $ws->setCellValue($colTot . '3', 'TOTALES PARTICIP');
        $this->bold($ws, "B3:{$colTot}3");
        $this->fill($ws, "B3:{$colTot}3", self::HEAD);

        $row = 4;
        foreach ($this->resumen as $r) {
            $ws->setCellValue("B$row", $r['cliente']);
            $ws->setCellValue("C$row", '=+' . $this->ref($r['sheet'], $r['neto']));
            foreach ($nombres as $i => $n) {
                $col = Coordinate::stringFromColumnIndex(4 + $i);
                $ws->setCellValue("$col$row", isset($r['parts'][$n]) ? '=+' . $this->ref($r['sheet'], $r['parts'][$n]) : 0);
            }
            $ws->setCellValue("$colTot$row", $nombres ? "=SUM(D$row:" . Coordinate::stringFromColumnIndex(3 + count($nombres)) . "$row)" : 0);
            $row++;
        }
        $fin = $row - 1;
        $ws->setCellValue("B$row", 'TOTALES');
        for ($ci = 3; $ci <= 4 + count($nombres); $ci++) {
            $col = Coordinate::stringFromColumnIndex($ci);
            $ws->setCellValue("$col$row", $fin >= 4 ? "=SUM({$col}4:{$col}$fin)" : 0);
        }
        $this->bold($ws, "B$row:{$colTot}$row");
        $ws->getStyle("C4:{$colTot}$row")->getNumberFormat()->setFormatCode(self::MONEY);
        $ws->getColumnDimension('A')->setWidth(3);
        $ws->getColumnDimension('B')->setWidth(34);
        for ($ci = 3; $ci <= 4 + count($nombres); $ci++) {
            $ws->getColumnDimension(Coordinate::stringFromColumnIndex($ci))->setWidth(19);
        }
    }

    // ------------------------------------------------------------------
    // Utilidades
    // ------------------------------------------------------------------

    private function periodo(array $data)
    {
        return Carbon::parse($data['desde'])->format('d/m/Y') . ' al ' . Carbon::parse($data['hasta'])->format('d/m/Y');
    }

    /** Titulo de hoja valido (<=31, sin []:*?/\) y unico. */
    private function titulo($nombre)
    {
        $t = trim(preg_replace('/[\[\]\:\*\?\/\\\\]/u', ' ', (string) $nombre));
        $t = mb_substr($t === '' ? 'Hoja' : $t, 0, 31);
        $base = $t;
        $n = 2;
        while (isset($this->usados[mb_strtolower($t)])) {
            $suf = ' ' . $n++;
            $t = mb_substr($base, 0, 31 - mb_strlen($suf)) . $suf;
        }
        $this->usados[mb_strtolower($t)] = true;
        return $t;
    }

    private function ref($sheet, $cell)
    {
        return "'" . str_replace("'", "''", $sheet) . "'!" . $cell;
    }

    private function fill(Worksheet $ws, $range, $argb)
    {
        $ws->getStyle($range)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB($argb);
    }

    private function bold(Worksheet $ws, $range)
    {
        $ws->getStyle($range)->getFont()->setBold(true);
    }
}
