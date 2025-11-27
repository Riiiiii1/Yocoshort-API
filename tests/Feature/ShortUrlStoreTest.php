<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Url;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ShortUrlStoreTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test básico para crear una nueva URL corta
     */
    public function test_crea_nueva_url_corta()
    {
        $response = $this->postJson('/api/long-url', [
            'long_url' => 'https://google.com'
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'short_url',
                'expires_at'
            ]);

        // Verifica que se guardó en la base de datos
        $this->assertDatabaseHas('short_links', [
            'long_url' => 'https://google.com'
        ]);
    }

    /**
     * Test que retorna URL existente cuando ya existe
     */
    public function test_retorna_url_existente_cuando_ya_existe()
    {
        // Primero creamos una URL
        $existingUrl = Url::create([
            'long_url' => 'https://youtube.com',
            'short_code' => 'test123',
            'clicks' => 0,
            'expires_at' => now()->addDay()
        ]);

        // Intentamos crear la misma URL otra vez
        $response = $this->postJson('/api/long-url', [
            'long_url' => 'https://youtube.com'
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'short_url' => url('test123')
            ]);

        // Verifica que no se creó un duplicado
        $this->assertCount(1, Url::where('long_url', 'https://youtube.com')->get());
    }

    /**
     * Test de validación - URL requerida
     */
    public function test_valida_url_requerida()
    {
        $response = $this->postJson('/api/long-url', [
            'long_url' => ''
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['long_url']);
    }

    /**
     * Test de validación - Formato de URL inválido
     */
    public function test_valida_formato_url_invalido()
    {
        $response = $this->postJson('/api/long-url', [
            'long_url' => 'esto-no-es-una-url'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['long_url']);
    }

    /**
     * Test que verifica que se genera un short_code único
     */
    public function test_genera_short_code_unico()
    {
        $response1 = $this->postJson('/api/long-url', [
            'long_url' => 'https://site1.com'
        ]);

        $response2 = $this->postJson('/api/long-url', [
            'long_url' => 'https://site2.com'
        ]);

        $response1->assertStatus(200);
        $response2->assertStatus(200);

        // Los short_url deben ser diferentes
        $this->assertNotEquals(
            $response1->json('short_url'),
            $response2->json('short_url')
        );
    }

    /**
     * Test que verifica que se elimina el slash final
     */
    public function test_elimina_slash_final_de_url()
    {
        $response = $this->postJson('/api/long-url', [
            'long_url' => 'https://example.com/'
        ]);

        $response->assertStatus(200);

        // Debe guardarse sin el slash final
        $this->assertDatabaseHas('short_links', [
            'long_url' => 'https://example.com'
        ]);
    }

    /**
     * Test que verifica la estructura de la respuesta
     */
    public function test_respuesta_tiene_estructura_correcta()
    {
        $response = $this->postJson('/api/long-url', [
            'long_url' => 'https://laravel.com'
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'short_url' => [
                    // La URL corta debe ser una string
                ],
                'expires_at' => [
                    // La fecha de expiración debe ser una string
                ]
            ]);

        // Verifica que los campos no estén vacíos
        $data = $response->json();
        $this->assertNotEmpty($data['short_url']);
        $this->assertNotEmpty($data['expires_at']);
    }

    /**
     * Test con diferentes tipos de URLs válidas
     */
    public function test_acepta_diferentes_urls_validas()
    {
        $urls = [
            'https://www.google.com',
            'http://example.com',
            'https://sub.dominio.com/path?query=param',
            'https://sitio.com:8080/path'
        ];

        foreach ($urls as $url) {
            $response = $this->postJson('/api/long-url', [
                'long_url' => $url
            ]);

            $response->assertStatus(200);
        }

        // Todas las URLs deben guardarse
        $this->assertCount(4, Url::all());
    }
}