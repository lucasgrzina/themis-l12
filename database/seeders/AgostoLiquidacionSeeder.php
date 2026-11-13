<?php

namespace Database\Seeders;

use App\Models\Cliente;
use App\Models\TimeGestion;
use App\Models\TimeHora;
use App\Models\TimeTarifa;
use App\Models\TimeParticipacion;
use App\Models\TimeParticipacionExterna;
use App\Models\TimePresupuesto;
use App\Models\TimeTipoCambio;
use App\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Carga la parametrizacion de liquidacion del Time para AGOSTO 2026,
 * tomada del Excel "8 AGOSTO 2026.xlsx". Idempotente: re-ejecutable.
 *
 * NO carga horas (esas ya estan en time_horas). Solo parametros economicos.
 *
 * Mapeo cliente->id confirmado contra time_horas de agosto 2026.
 * IRSA se omite: no existe como cliente en el sistema.
 * SOLUCIONES GRAFICAS (presupuesto): se carga monto/IVA y sus participaciones
 * a colaboradores externos (Rosenblat 20% s/subtotal, Colaborador CVA 10% s/neto)
 * via time_participaciones_externas.
 */
class AgostoLiquidacionSeeder extends Seeder
{
    const PERIODO = '2026-08-01';

    public function run()
    {
        // Abogados (user_id confirmados por horas cargadas en agosto)
        $ARIANA   = 616;
        $FELIX    = 584;
        $SANTIAGO = 607;
        $JUANA    = 581;
        $MARIANO  = 606;
        $DARIO    = 579;

        // Categorias de abogados
        User::where('id', $DARIO)->update(['categoria_abogado' => 'Sr']);

        // Tipo de cambio del periodo
        TimeTipoCambio::updateOrCreate(
            ['periodo' => self::PERIODO, 'nombre' => 'MEP'],
            ['valor' => 1525.67]
        );

        // Participaciones estandar (mismas en clientes por horas)
        $base4 = [
            [$ARIANA, 0.05], [$FELIX, 0.10], [$SANTIAGO, 0.05], [$JUANA, 0.10],
        ];

        // Definicion por cliente: [cliente_id, valor_hora_base, tarifas_extra, participaciones]
        $clientes = [
            // AGRO 2000 S.A.
            [9985, 190, [], [[$ARIANA, 0.05], [$FELIX, 0.10], [$SANTIAGO, 0.05]]],
            // CAMPOS ENTRERRIANOS SA
            [9784, 180, [], $base4],
            // UNTERFIN SRL
            [9230, 225, [], $base4],
            // BOTBOL NICOLAS
            [8703, 225, [], $base4],
            // NB GROUP (NB CARGO)
            [8960, 150, [], $base4],
            // SAVARY S.A.
            [8594, 180, ['Sr' => 200, 'Semi Sr' => 144],
                array_merge($base4, [[$MARIANO, 0.10]])],
            // REMATES ZARATE SA
            [8939, 180, ['Sr' => 200, 'Semi Sr' => 144],
                array_merge($base4, [[$MARIANO, 0.10]])],
        ];

        foreach ($clientes as [$cid, $valorBase, $extra, $parts]) {
            Cliente::where('id', $cid)->update([
                'modelo_facturacion' => 'horas',
                'aplica_iva' => false,
            ]);

            // Tarifas (idempotente: limpia y recrea)
            TimeTarifa::where('cliente_id', $cid)->forceDelete();
            TimeTarifa::create([
                'cliente_id' => $cid, 'categoria' => null,
                'valor_hora' => $valorBase, 'moneda' => 'USD',
                'vigencia_desde' => self::PERIODO,
            ]);
            foreach ($extra as $cat => $valor) {
                TimeTarifa::create([
                    'cliente_id' => $cid, 'categoria' => $cat,
                    'valor_hora' => $valor, 'moneda' => 'USD',
                    'vigencia_desde' => self::PERIODO,
                ]);
            }

            // Participaciones por defecto (periodo null)
            TimeParticipacion::where('cliente_id', $cid)->whereNull('periodo')->forceDelete();
            foreach ($parts as [$uid, $pct]) {
                TimeParticipacion::create([
                    'cliente_id' => $cid, 'user_id' => $uid,
                    'porcentaje' => $pct, 'periodo' => null,
                ]);
            }
        }

        // Horas de director y piso (ya no es un usuario): parametros del cliente con vigencia.
        // SAVARY: piso de 4 hs + 2 hs director; REMATES: 3 hs director. Participacion director 50%.
        \App\Models\TimeClienteParametro::whereIn('cliente_id', [8594, 8939])->forceDelete();
        \App\Models\TimeClienteParametro::create([
            'cliente_id' => 8594, 'vigencia_desde' => self::PERIODO,
            'horas_director' => 2, 'pct_director' => 0.50, 'horas_piso' => 4,
        ]);
        \App\Models\TimeClienteParametro::create([
            'cliente_id' => 8939, 'vigencia_desde' => self::PERIODO,
            'horas_director' => 3, 'pct_director' => 0.50, 'horas_piso' => 0,
        ]);

        // SOLUCIONES GRAFICAS S.A -> modelo presupuesto
        $sol = 8679;
        Cliente::where('id', $sol)->update([
            'modelo_facturacion' => 'presupuesto',
            'aplica_iva' => true,
        ]);
        TimePresupuesto::where('cliente_id', $sol)->where('periodo', self::PERIODO)->forceDelete();
        TimePresupuesto::create([
            'cliente_id' => $sol,
            'periodo' => self::PERIODO,
            'descripcion' => '50% PRESUPUESTO 2026 06 17 (resta particip. Albina)',
            'subtotal' => 470000,
            'aplica_iva' => true,
        ]);

        // Participaciones a colaboradores externos (no-usuarios) de SOLUCIONES GRAFICAS.
        // Rosenblat 20% sobre el subtotal (se descuenta antes del subtotal neto).
        // Colaborador CVA 10% sobre el subtotal neto (se descuenta despues).
        // periodo null = por defecto (aplica a cualquier mes). Idempotente.
        $partsExt = [
            ['nombre' => 'Rosenblat', 'porcentaje' => 0.20, 'base' => 'subtotal', 'orden' => 1],
            ['nombre' => 'Colaborador CVA', 'porcentaje' => 0.10, 'base' => 'subtotal_neto', 'orden' => 1],
        ];
        foreach ($partsExt as $pe) {
            TimeParticipacionExterna::where('cliente_id', $sol)
                ->where('nombre', $pe['nombre'])
                ->where('base', $pe['base'])
                ->whereNull('periodo')
                ->forceDelete();
            TimeParticipacionExterna::create([
                'cliente_id' => $sol,
                'nombre' => $pe['nombre'],
                'porcentaje' => $pe['porcentaje'],
                'base' => $pe['base'],
                'orden' => $pe['orden'],
                'periodo' => null,
            ]);
        }

        // Horas de SAVARY agosto 2026 (tomadas del Excel de ejemplo).
        // Solo este cliente: su informe quedo VALIDADO al centavo contra la planilla.
        // El resto de los clientes conservan sus horas reales de produccion.
        $this->seedHorasSavary([
            $ARIANA => 30, $SANTIAGO => 25, $FELIX => 220,
            $DARIO => 90, 617 /*cadeteria*/ => 25, $MARIANO => 20,
        ]);

        $this->command->info('AgostoLiquidacionSeeder: parametrizacion + horas SAVARY de agosto 2026 cargadas.');
    }

    /**
     * Normaliza las horas de SAVARY (cliente 8594) de agosto 2026 segun el Excel.
     * Idempotente: borra las horas de SAVARY del periodo y recrea el set exacto.
     * Afecta UNICAMENTE a SAVARY/agosto; no toca horas de otros clientes.
     *
     * @param array $horas    [user_id => minutos]
     */
    private function seedHorasSavary(array $horas)
    {
        $savary = 8594;
        $gestionId = TimeGestion::query()->orderBy('id')->value('id') ?: 1;

        TimeHora::where('cliente_id', $savary)
            ->whereBetween('fecha', ['2026-08-01', '2026-08-31'])
            ->forceDelete();

        foreach ($horas as $uid => $min) {
            if ($min <= 0) {
                continue;
            }
            TimeHora::create([
                'user_id' => $uid,
                'cliente_id' => $savary,
                'gestion_id' => $gestionId,
                'referencia' => 'Liquidacion agosto 2026',
                'facturar' => true,
                'descripcion' => 'Horas agosto 2026 (seed liquidacion)',
                'fecha' => '2026-08-31',
                'minutos' => $min,
                'estado' => 'vinculada',
            ]);
        }
    }
}
