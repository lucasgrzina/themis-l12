<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\TimeGestion;
use App\Models\TimeHora;
use Tests\TestCase;

/**
 * Caracterización de la generación de documentos (Fase 00 del plan de
 * migración a Laravel 12): PDF vía barryvdh/laravel-dompdf y Excel vía
 * maatwebsite/excel. Ambos paquetes tienen saltos de versión obligados
 * en el plan (dompdf a ^3.1.1, excel un salto directo 3.0 -> 4.x
 * confirmado incompatible en el medio) -- este test es la referencia
 * de comportamiento contra la que comparar después de esos bumps.
 *
 * Nota aparte, no cubierta acá: la generación de .docx vía PhpWord
 * (GenerarDocumentacionTrait / ImprimirController::documentacionReq)
 * está efectivamente MUERTA en el flujo real -- el bloque que arma el
 * Word está comentado y ese endpoint devuelve PDF igual que los demás.
 * El único lugar donde PhpWord corre de verdad es TestController::
 * printWord, una ruta de scaffolding que arma un documento genérico sin
 * datos de negocio. No se escribe un test de caracterización para eso
 * porque no protegería ningún comportamiento real -- si se quiere bajar
 * la prioridad de phpoffice/phpword en la Fase 06 del plan, este es el
 * motivo.
 */
class DocumentGenerationTest extends TestCase
{
    public function test_pdf_time_abogado_cliente_con_datos_devuelve_pdf_valido()
    {
        $cliente = Cliente::create([
            'personeria' => 'H',
            'nombre_completo' => 'Cliente Para PDF',
            'sexo' => 'M',
            'tipo_doc_id' => 38,
            'nro_doc' => '12345678',
        ]);

        $gestion = TimeGestion::create(['nombre' => 'Consulta', 'vigente' => true]);

        TimeHora::create([
            'user_id' => 1,
            'cliente_id' => $cliente->id,
            'gestion_id' => $gestion->id,
            'referencia' => 'Ref. de test',
            'facturar' => true,
            'descripcion' => 'Trabajo de prueba',
            'fecha' => now()->format('Y-m-d'),
            'minutos' => 60,
        ]);

        $response = $this->get('/imprimir/time/imp-abogado-cliente?'.http_build_query([
            'desde' => now()->subDay()->format('d/m/Y'),
            'hasta' => now()->addDay()->format('d/m/Y'),
        ]));

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
        $this->assertGreaterThan(1000, strlen($response->getContent()), 'El PDF generado debería tener contenido real, no estar vacío');
        $this->assertStringStartsWith('%PDF', $response->getContent());
    }

    public function test_pdf_time_abogado_cliente_sin_resultados_no_rompe()
    {
        // Rango de fechas sin ningún registro -- el reporte debe seguir
        // devolviendo un PDF válido (aunque vacío de filas), no un 500.
        $response = $this->get('/imprimir/time/imp-abogado-cliente?'.http_build_query([
            'desde' => '01/01/1990',
            'hasta' => '02/01/1990',
        ]));

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
    }

    // NO HAY TEST para "GET /imprimir/time/imp-abogado-cliente sin
    // desde/hasta" -- se intentó y se dejó fuera a propósito. Hallazgo
    // real, verificado a mano: como 'desde'/'hasta' nunca se validan
    // antes de Carbon::createFromFormat(), pedir la ruta sin esos
    // parámetros dispara una excepción que ImprimirController atrapa
    // con `catch (\Exception $e) { die($e->getMessage()); }` en vez de
    // sendError(). El problema no es solo la falta de un mensaje
    // prolijo: die()/exit() en código de aplicación mata el proceso de
    // PHP entero. Bajo PHPUnit (que corre toda la app en el mismo
    // proceso, sin un límite real de request/response) esto aborta la
    // suite completa sin imprimir ningún resultado y con exit code 0 --
    // se verificó así, de forma aislada, fuera de esta clase. En
    // php-fpm real el radio de daño es menor (se termina esa conexión,
    // el worker sigue vivo), pero sigue siendo un error sin manejar que
    // devuelve texto plano crudo con el mensaje de la excepción. No se
    // corrige acá -- es una decisión de ImprimirController completo
    // (usa die() en varios métodos), no algo puntual de esta ruta.

    public function test_excel_informe_beneficios_devuelve_xlsx_valido()
    {
        // user_id es obligatorio en la práctica, aunque nada lo exija
        // explícitamente: InformesRepository::beneficios() arma
        // "user_id IN (".$userId.")" con whereRaw() y SIN casteo a
        // entero. Sin user_id (ni responsable_area=true) el string
        // queda vacío -> "IN ()" -> error de sintaxis SQL (ver el test
        // de abajo). El patrón se repite igual en tramites(),
        // tramitesHistoricos() y requerimientosEmpresas() -- y al ser
        // concatenación cruda de un parámetro de request en whereRaw(),
        // sin bindings ni cast, es candidato a SQL injection si algún
        // día un valor no numérico llega hasta acá sin pasar por un
        // formulario que ya lo restrinja a un <select>. Reportado por
        // separado, no corregido en este commit.
        $response = $this->get('/imprimir/informes/beneficios?'.http_build_query([
            'desde' => now()->subYear()->format('d/m/Y'),
            'hasta' => now()->format('d/m/Y'),
            'area_id' => 1,
            'user_id' => 1,
        ]));

        $response->assertStatus(200);
        $response->assertHeader(
            'content-type',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        );

        // Excel::download() devuelve un BinaryFileResponse -- el archivo
        // vive en disco/stream, TestResponse::getContent() no lo expone
        // (limitación del harness de test, no de la app real: un
        // browser/curl reales sí reciben el contenido). Se captura igual
        // que lo haría el servidor, con buffer de salida sobre
        // sendContent().
        ob_start();
        $response->baseResponse->sendContent();
        $content = ob_get_clean();

        $this->assertGreaterThan(1000, strlen($content), 'El .xlsx generado debería tener contenido real');
        $this->assertStringStartsWith('PK', $content); // firma de archivo zip/xlsx
    }

    public function test_excel_informe_beneficios_sin_area_id_rompe_con_500()
    {
        // Hallazgo: ImprimirController::getFiltersInfo() hace
        // Area::find($request->get('area_id'))->nombre sin comprobar
        // que exista -- si falta area_id, es un error fatal (llamar a
        // ->nombre sobre null), no una validación con mensaje claro.
        $response = $this->get('/imprimir/informes/beneficios?'.http_build_query([
            'desde' => now()->subYear()->format('d/m/Y'),
            'hasta' => now()->format('d/m/Y'),
        ]));

        $response->assertStatus(500);
    }
}
