<?php

namespace Tests\Feature;

use App\Models\EstadoRequerimiento;
use Tests\TestCase;

/**
 * Caracterización del ciclo de vida completo de un trámite (Fase 00 del
 * plan de migración a Laravel 12): cliente -> requerimiento -> trámite ->
 * archivado, incluyendo el camino PENSION + conviviente que crea
 * automáticamente un requerimiento de acreditación de convivencia
 * (ACRED_CONV, ver TramitesTrait::storeTramite / ClienteAPIController).
 *
 * Estos endpoints pasan por TramitesTrait, que corre dentro de
 * DB::beginTransaction()/commit(), y por los repositorios que en la
 * Fase 04 van a heredar de una nueva versión de prettus/l5-repository --
 * este test es la referencia de comportamiento contra la que comparar.
 */
class TramiteLifecycleTest extends TestCase
{
    /** @var string */
    protected $token;

    /** @var int */
    protected $estadoConcluidoId;

    protected function setUp(): void
    {
        parent::setUp();

        $login = $this->postJson('/api/authenticate', [
            'username' => 'wavecom',
            'password' => 'wavecom',
        ]);
        $this->token = $login->json()['token'];

        // estado_requerimientos: bug preexistente (ver TestingSeeder) --
        // el unique() global en `nombre` no convive con area_id, así que
        // un seed limpio deja esta tabla sin datos utilizables. Se crea
        // acá, dentro de la transacción del test, solo el estado que
        // TramitesTrait::archivar() busca por nombre para el área
        // Previsional (id 1). El id NO es fillable en el modelo (por
        // diseño), así que se captura el que MySQL asigna en vez de
        // asumir uno fijo -- y como no hay FK real hacia esta tabla
        // (ver más abajo), 'estado_req_id' => 51 en los payloads de
        // creación de requerimiento es solo un entero libre, no
        // necesita una fila real detrás.
        $this->estadoConcluidoId = EstadoRequerimiento::create([
            'nombre' => 'Concluido/Archivado',
            'area_id' => 1,
            'vigente' => true,
        ])->id;
    }

    protected function auth(array $headers = [])
    {
        return array_merge(['Authorization' => "Bearer {$this->token}"], $headers);
    }

    protected function crearCliente()
    {
        $response = $this->postJson('/api/clientes', [
            'personeria' => 'H',
            'nombre_completo' => 'Test Cliente '.uniqid(),
            'sexo' => 'M',
            'tipo_doc_id' => 38,
            'nro_doc' => (string) random_int(10000000, 99999999),
        ], $this->auth());

        $response->assertStatus(200);

        return $response->json()['data'];
    }

    public function test_ciclo_de_vida_completo_crear_y_archivar_tramite()
    {
        $cliente = $this->crearCliente();

        $requerimiento = $this->postJson("/api/clientes/{$cliente['id']}/requerimientos", [
            'area_id' => 1,
            'cliente_id' => $cliente['id'],
            'estado_req_id' => 51,
            'tipo_tramite_id' => 42, // Jubilacion Ordinaria Ley 18037/8 (no PENSION)
            'tipo_tramite' => ['tratamiento' => null],
            'responsables' => [['user_id' => 1]],
        ], $this->auth());

        $requerimiento->assertStatus(200);
        $requerimientoData = $requerimiento->json()['data'];
        // toArray() en un modelo recién creado (sin refresh) omite las
        // claves que nunca se asignaron -- no vienen como null, faltan.
        $this->assertArrayNotHasKey('req_nec_id', $requerimientoData);

        $tramite = $this->postJson("/api/clientes/{$cliente['id']}/tramites", [
            'area_id' => 1,
            'cliente_id' => $cliente['id'],
            'requerimiento_id' => $requerimientoData['id'],
            'estado_tramite_id' => 521, // Analisis de Especialista (Legales)
            'archivar' => false,
        ], $this->auth());

        $tramite->assertStatus(200);
        $tramiteData = $tramite->json()['data'];
        // 'archivar' se excluye explícitamente de $datos_basicos en
        // TramitesTrait::storeTramite -- al no archivar, el atributo
        // nunca se asigna y falta del array, no viene como false.
        $this->assertArrayNotHasKey('archivar', $tramiteData);
        $this->assertArrayNotHasKey('fecha_archivo', $tramiteData);

        // TramitesTrait::storeTramite fuerza estado_req_id = 54 (número
        // mágico hardcodeado en el código, no configurable) cada vez que
        // se crea un trámite sin archivar -- pisa el estado que tuviera
        // antes el requerimiento. No hay FK real entre estado_req_id y
        // estado_requerimientos.id a nivel de base (la migración no la
        // declara), así que ni siquiera exige que exista la fila 54.
        $this->assertDatabaseHas('requerimiento_clientes', [
            'id' => $requerimientoData['id'],
            'estado_req_id' => 54,
        ]);

        $archivado = $this->putJson("/api/clientes/{$cliente['id']}/tramites/{$tramiteData['id']}", [
            'area_id' => 1,
            'cliente_id' => $cliente['id'],
            'requerimiento_id' => $requerimientoData['id'],
            'estado_tramite_id' => 521,
            'archivar' => true,
        ], $this->auth());

        $archivado->assertStatus(200);
        $archivadoData = $archivado->json()['data'];
        $this->assertTrue((bool) $archivadoData['archivar']);
        $this->assertNotNull($archivadoData['fecha_archivo']);
        $this->assertEquals(1, $archivadoData['usuario_archivo_id']);

        // archivar() debe haber empujado el requerimiento a Concluido/Archivado.
        $this->assertDatabaseHas('requerimiento_clientes', [
            'id' => $requerimientoData['id'],
            'estado_req_id' => $this->estadoConcluidoId,
        ]);
    }

    public function test_pension_con_conviviente_crea_requerimiento_de_acreditacion_de_convivencia()
    {
        $cliente = $this->crearCliente();

        $requerimiento = $this->postJson("/api/clientes/{$cliente['id']}/requerimientos", [
            'area_id' => 1,
            'cliente_id' => $cliente['id'],
            'estado_req_id' => 51,
            'tipo_tramite_id' => 45, // Pension Directa (Reparto)
            'tipo_tramite' => ['tratamiento' => 'PENSION'],
            'estado_civil' => 'CO',
            'responsables' => [['user_id' => 1]],
        ], $this->auth());

        $requerimiento->assertStatus(200);
        $data = $requerimiento->json()['data'];

        $this->assertNotNull($data['req_nec_id'], 'Debería haberse creado y linkeado el requerimiento de ACRED_CONV');

        $this->assertDatabaseHas('requerimiento_clientes', [
            'id' => $data['req_nec_id'],
            'tipo_tramite_id' => 7224, // Acreditacion de convivencia
            'cliente_id' => $cliente['id'],
        ]);
    }

    public function test_pension_sin_convivencia_no_crea_requerimiento_de_acreditacion()
    {
        $cliente = $this->crearCliente();

        $requerimiento = $this->postJson("/api/clientes/{$cliente['id']}/requerimientos", [
            'area_id' => 1,
            'cliente_id' => $cliente['id'],
            'estado_req_id' => 51,
            'tipo_tramite_id' => 45,
            'tipo_tramite' => ['tratamiento' => 'PENSION'],
            'estado_civil' => 'SO', // soltero/a, no conviviente
            'responsables' => [['user_id' => 1]],
        ], $this->auth());

        $requerimiento->assertStatus(200);
        $data = $requerimiento->json()['data'];

        $this->assertArrayNotHasKey('req_nec_id', $data);
    }
}
