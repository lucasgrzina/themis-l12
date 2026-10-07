<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * Test de caracterización del login JWT (Fase 00 del plan de migración a
 * Laravel 12). Congela el contrato que el frontend Vue consume hoy:
 * POST /api/authenticate -> {"token": "..."} en éxito, 401/422 en error.
 * Sirve como referencia de comportamiento antes de tocar jwt-auth o
 * l5-repository en las próximas fases.
 */
class AuthenticationTest extends TestCase
{
    public function test_login_con_credenciales_validas_devuelve_token()
    {
        $response = $this->postJson('/api/authenticate', [
            'username' => 'wavecom',
            'password' => 'wavecom',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['token']);
        $this->assertNotEmpty($response->json()['token']);
    }

    public function test_login_con_password_incorrecta_devuelve_401()
    {
        $response = $this->postJson('/api/authenticate', [
            'username' => 'wavecom',
            'password' => 'password-incorrecta',
        ]);

        $response->assertStatus(401);
        $response->assertJson(['error' => 'Invalid Login Credential']);
    }

    public function test_login_con_usuario_inexistente_devuelve_401()
    {
        $response = $this->postJson('/api/authenticate', [
            'username' => 'usuario-que-no-existe',
            'password' => 'cualquiera',
        ]);

        $response->assertStatus(401);
    }

    public function test_login_sin_username_devuelve_422()
    {
        $response = $this->postJson('/api/authenticate', [
            'password' => 'wavecom',
        ]);

        $response->assertStatus(422);
    }

    public function test_login_sin_password_devuelve_422()
    {
        $response = $this->postJson('/api/authenticate', [
            'username' => 'wavecom',
        ]);

        $response->assertStatus(422);
    }

    public function test_endpoint_protegido_sin_token_devuelve_401()
    {
        $response = $this->getJson('/api/user');

        $response->assertStatus(401);
    }

    public function test_endpoint_protegido_con_token_valido_responde_ok()
    {
        $login = $this->postJson('/api/authenticate', [
            'username' => 'wavecom',
            'password' => 'wavecom',
        ]);
        $token = $login->json()['token'];

        $response = $this->getJson('/api/user', [
            'Authorization' => "Bearer {$token}",
        ]);

        $response->assertStatus(200);
        $this->assertSame('wavecom', $response->json()['username']);
    }

    public function test_endpoint_protegido_con_token_invalido_devuelve_401()
    {
        $response = $this->getJson('/api/user', [
            'Authorization' => 'Bearer un-token-invalido',
        ]);

        $response->assertStatus(401);
    }
}
