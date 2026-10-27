<?php

namespace App\Console\Commands;

use App\Exports\TramitesArchivadosExport;
use App\Models\TramiteCliente;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Archiva automaticamente los tramites con estado "Beneficio Acordado"
 * (AFJP + ANSES) cuya fecha_inicio supera la antiguedad configurada y que
 * aun no estan archivados, y genera un informe (Excel) de los tramites
 * afectados para la posterior destruccion de la carpeta fisica.
 *
 * Pensado para correr una vez por dia (00:00) desde el scheduler.
 */
class ArchivarTramitesBeneficioAcordado extends Command
{
    protected $signature = 'tramites:archivar-beneficio-acordado
        {--dry-run : No modifica la base; solo genera el informe de candidatos}
        {--anios= : Sobrescribe la antiguedad (en anios) definida en config}
        {--fecha= : Fecha de corte explicita (YYYY-MM-DD); tiene prioridad sobre --anios}';

    protected $description = 'Archiva tramites con Beneficio Acordado antiguos y genera el informe para destruccion de carpeta fisica';

    public function handle(): int
    {
        $cfg      = config('themis.archivado');
        $estados  = $cfg['estados_beneficio_acordado'];
        $usuarioId = $cfg['usuario_sistema_id'];
        $chunk    = max(1, (int) $cfg['chunk']);
        $dryRun   = (bool) $this->option('dry-run');

        if (empty($estados)) {
            $this->error('No hay estados de "Beneficio Acordado" configurados (themis.archivado.estados_beneficio_acordado).');
            return self::FAILURE;
        }

        // Fecha de corte: --fecha explicita o bien hoy - antiguedad_anios.
        $cutoff = $this->resolverFechaCorte($cfg);
        if ($cutoff === null) {
            $this->error('La opcion --fecha debe tener formato YYYY-MM-DD.');
            return self::FAILURE;
        }

        $fechaArchivoStore = Carbon::now()->toDateString();

        $this->info(sprintf(
            '%sArchivando tramites con estado %s, fecha_inicio <= %s (no archivados).',
            $dryRun ? '[DRY-RUN] ' : '',
            implode(',', $estados),
            $cutoff->toDateString()
        ));

        // Query base: estados objetivo + antiguedad + no archivados.
        // whereDate opera sobre la columna real (Y-m-d), no sobre el accessor d/m/Y.
        $baseQuery = TramiteCliente::query()
            ->whereIn('estado_tramite_id', $estados)
            ->whereDate('fecha_inicio', '<=', $cutoff->toDateString())
            ->where(function ($q) {
                $q->whereNull('archivar')->orWhere('archivar', false);
            });

        $ids = (clone $baseQuery)->orderBy('id')->pluck('id');
        $total = $ids->count();

        if ($total === 0) {
            $this->info('No hay tramites que archivar. No se genera informe.');
            Log::info('[archivado] Sin tramites candidatos.', ['cutoff' => $cutoff->toDateString()]);
            return self::SUCCESS;
        }

        $this->info("Tramites candidatos: {$total}");

        // 1) Armar el informe (estado previo al archivado).
        $rows = $this->construirFilas($ids, $chunk, $fechaArchivoStore, $dryRun);

        // 2) Aplicar el archivado (salvo dry-run).
        $afectados = 0;
        if (! $dryRun) {
            $bar = $this->output->createProgressBar((int) ceil($total / $chunk));
            foreach ($ids->chunk($chunk) as $lote) {
                $afectados += DB::transaction(function () use ($lote, $usuarioId, $fechaArchivoStore) {
                    return TramiteCliente::whereIn('id', $lote->all())->update([
                        'archivar'          => true,
                        'fecha_archivo'     => $fechaArchivoStore,
                        'usuario_archivo_id' => $usuarioId,
                        'updated_at'        => Carbon::now(),
                    ]);
                });
                $bar->advance();
            }
            $bar->finish();
            $this->newLine();
        }

        // 3) Guardar el informe.
        $rutaRelativa = $this->guardarInforme($rows, $cfg['informe_dir'], $dryRun);
        $rutaAbsoluta = Storage::disk('local')->path($rutaRelativa);
        $this->info("Informe generado: {$rutaAbsoluta}");

        // 4) Email opcional.
        $this->enviarInforme($cfg['informe_emails'], $rutaRelativa, $total, $dryRun);

        Log::info('[archivado] Proceso finalizado.', [
            'dry_run'    => $dryRun,
            'candidatos' => $total,
            'archivados' => $dryRun ? 0 : $afectados,
            'cutoff'     => $cutoff->toDateString(),
            'informe'    => $rutaRelativa,
        ]);

        $this->info($dryRun
            ? "[DRY-RUN] {$total} candidatos (no se modifico la base)."
            : "Listo: {$afectados} tramites archivados.");

        return self::SUCCESS;
    }

    private function resolverFechaCorte(array $cfg): ?Carbon
    {
        if ($this->option('fecha')) {
            try {
                return Carbon::createFromFormat('Y-m-d', $this->option('fecha'))->startOfDay();
            } catch (\Throwable $e) {
                return null;
            }
        }

        $anios = $this->option('anios') !== null
            ? (int) $this->option('anios')
            : (int) $cfg['antiguedad_anios'];

        return Carbon::today()->subYears($anios);
    }

    /**
     * @param  \Illuminate\Support\Collection<int,int>  $ids
     * @return array<int,array<int,mixed>>
     */
    private function construirFilas($ids, int $chunk, string $fechaArchivoStore, bool $dryRun): array
    {
        $rows = [];
        $fechaArchivoShow = Carbon::parse($fechaArchivoStore)->format('d/m/Y');

        foreach ($ids->chunk($chunk) as $lote) {
            $tramites = TramiteCliente::with(['cliente', 'estado'])
                ->whereIn('id', $lote->all())
                ->orderBy('id')
                ->get();

            foreach ($tramites as $t) {
                $rows[] = [
                    $t->id,
                    $t->expediente,
                    optional($t->cliente)->nombre_completo,
                    optional($t->cliente)->cuit,
                    optional($t->cliente)->ubicacion_carpeta,
                    optional($t->estado)->nombre,
                    $t->fecha_inicio,     // accessor -> d/m/Y
                    $t->nro_beneficio,
                    $t->fecha_beneficio,  // accessor -> d/m/Y
                    $dryRun ? '(dry-run)' : $fechaArchivoShow,
                ];
            }
        }

        return $rows;
    }

    /** @param array<int,array<int,mixed>> $rows */
    private function guardarInforme(array $rows, string $dir, bool $dryRun): string
    {
        $dir = trim($dir, '/');
        $nombre = sprintf(
            'informe_archivado%s_%s.xlsx',
            $dryRun ? '_dryrun' : '',
            Carbon::now()->format('Ymd_His')
        );
        $ruta = "{$dir}/{$nombre}";

        Storage::disk('local')->makeDirectory($dir);
        Excel::store(new TramitesArchivadosExport($rows), $ruta, 'local');

        return $ruta;
    }

    /** @param array<int,string> $emails */
    private function enviarInforme(array $emails, string $rutaRelativa, int $total, bool $dryRun): void
    {
        if (empty($emails)) {
            return;
        }

        $rutaAbsoluta = Storage::disk('local')->path($rutaRelativa);
        $asunto = sprintf('%sInforme archivado Beneficio Acordado - %s tramites', $dryRun ? '[DRY-RUN] ' : '', $total);

        try {
            \Mail::raw(
                "Se adjunta el informe de tramites archivados automaticamente.\nTotal: {$total}\nArchivo: {$rutaRelativa}",
                function ($message) use ($emails, $asunto, $rutaAbsoluta) {
                    $message->to($emails)
                        ->subject($asunto)
                        ->attach($rutaAbsoluta);
                }
            );
            $this->info('Informe enviado por email a: ' . implode(', ', $emails));
        } catch (\Throwable $e) {
            $this->warn('No se pudo enviar el informe por email: ' . $e->getMessage());
            Log::warning('[archivado] Error enviando informe por email.', ['error' => $e->getMessage()]);
        }
    }
}
