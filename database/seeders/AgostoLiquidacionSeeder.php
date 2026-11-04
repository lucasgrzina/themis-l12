<?php

namespace Database\Seeders;

use App\Models\Cliente;
use App\Models\TimeTarifa;
use App\Models\TimeParticipacion;
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
 * SOLUCIONES GRAFICAS (presupuesto): se carga monto/IVA; sus participaciones
 * (Rosenblat 20%, Colaborador CVA 10%) son a NO-usuarios y quedan pendientes.
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

        // Usuario Director (se crea si no existe)
        $director = User::firstOrCreate(
            ['username' => 'director'],
            [
                'name' => 'Director',
                'email' => 'director@nomail.com',
                'password' => Hash::make(\Illuminate\Support\Str::random(16)),
                'responsable' => true,
                'visible' => true,
                'time_level' => 1,
            ]
        );
        $director->categoria_abogado = 'Director';
        $director->visible = true;
        $director->responsable = true;
        if (!$director->time_level) {
            $director->time_level = 1;
        }
        $director->save();
        $DIRECTOR = $director->id;

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
                array_merge($base4, [[$MARIANO, 0.10], [$DIRECTOR, 0.50]])],
            // REMATES ZARATE SA
            [8939, 180, ['Sr' => 200, 'Semi Sr' => 144],
                array_merge($base4, [[$MARIANO, 0.10], [$DIRECTOR, 0.50]])],
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

        $this->command->info('AgostoLiquidacionSeeder: parametrizacion de agosto 2026 cargada. Director id='.$DIRECTOR);
    }
}
