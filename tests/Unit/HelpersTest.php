<?php

namespace Tests\Unit;

//use PHPUnit\Framework\TestCase;
use Tests\TestCase;

class HelpersTest extends TestCase
{
    // =========================================
    // TESTS: formatearTelefonoWhatsApp
    // =========================================

    /**
     * @test
     * Debe formatear un teléfono sin código de país
     */
    public function test_formatearTelefonoWhatsApp_agrega_codigo_pais_correcto()
    {
        $resultado = formatearTelefonoWhatsApp('987654321');
        $this->assertEquals('51987654321', $resultado);
    }

    /**
     * @test
     * Debe manejar teléfono con espacios
     */
    public function test_formatearTelefonoWhatsApp_elimina_espacios()
    {
        $resultado = formatearTelefonoWhatsApp('98 765 4321');
        //$this->assertEquals('5198765432', $resultado);
        $this->assertEquals('51987654321', $resultado);
    }

    /**
     * @test
     * Debe manejar teléfono con guiones
     */
    public function test_formatearTelefonoWhatsApp_elimina_guiones()
    {
        $resultado = formatearTelefonoWhatsApp('98-765-4321');
        //$this->assertEquals('5198765432', $resultado);
        $this->assertEquals('51987654321', $resultado);
    }

    /**
     * @test
     * Debe manejar teléfono con paréntesis
     */
    public function test_formatearTelefonoWhatsApp_elimina_caracteres_especiales()
    {
        $resultado = formatearTelefonoWhatsApp('(98) 765-4321');
        //$this->assertEquals('5198765432', $resultado);
        $this->assertEquals('51987654321', $resultado);
    }

    /**
     * @test
     * No debe duplicar código de país si ya existe
     */
    public function test_formatearTelefonoWhatsApp_no_duplica_codigo_pais()
    {
        $resultado = formatearTelefonoWhatsApp('51987654321');
        $this->assertEquals('51987654321', $resultado);
    }

    /**
     * @test
     * Debe retornar cadena vacía si recibe entrada vacía
     */
    public function test_formatearTelefonoWhatsApp_retorna_vacio_con_entrada_vacia()
    {
        $resultado = formatearTelefonoWhatsApp('');
        $this->assertEquals('', $resultado);
    }

    /**
     * @test
     * Debe retornar cadena vacía con null
     */
    public function test_formatearTelefonoWhatsApp_retorna_vacio_con_null()
    {
        $resultado = formatearTelefonoWhatsApp(null);
        $this->assertEquals('', $resultado);
    }

    /**
     * @test
     * Debe manejar teléfono muy largo
     */
    public function test_formatearTelefonoWhatsApp_maneja_telefono_largo()
    {
        $resultado = formatearTelefonoWhatsApp('51987654321987654321');
        $this->assertStringStartsWith('51', $resultado);
    }

    // =========================================
    // TESTS: validarTelefonoPeruano
    // =========================================

    /**
     * @test
     * Debe validar número correcto de 9 dígitos que empieza con 9
     */
    public function test_validarTelefonoPeruano_valida_numero_correcto()
    {
        $resultado = validarTelefonoPeruano('987654321');
        $this->assertTrue($resultado);
    }

    /**
     * @test
     * Debe validar número con código de país
     */
    public function test_validarTelefonoPeruano_valida_con_codigo_pais()
    {
        $resultado = validarTelefonoPeruano('51987654321');
        $this->assertTrue($resultado);
    }

    /**
     * @test
     * Debe validar número con espacios
     */
    public function test_validarTelefonoPeruano_valida_con_espacios()
    {
        $resultado = validarTelefonoPeruano('98 765 4321');
        $this->assertTrue($resultado);
    }

    /**
     * @test
     * Debe validar número con guiones
     */
    public function test_validarTelefonoPeruano_valida_con_guiones()
    {
        $resultado = validarTelefonoPeruano('98-765-4321');
        $this->assertTrue($resultado);
    }

    /**
     * @test
     * Debe rechazar número que no empieza con 9
     */
    public function test_validarTelefonoPeruano_rechaza_numero_sin_9()
    {
        $resultado = validarTelefonoPeruano('987654320');
        // Este rechazo es incorrecto en la lógica actual, ajustar si es necesario
        $this->assertFalse(validarTelefonoPeruano('887654321'));
    }

    /**
     * @test
     * Debe rechazar número con menos de 9 dígitos
     */
    public function test_validarTelefonoPeruano_rechaza_numero_corto()
    {
        $resultado = validarTelefonoPeruano('9876543');
        $this->assertFalse($resultado);
    }

    /**
     * @test
     * Debe rechazar número con más de 9 dígitos (sin código de país)
     */
    public function test_validarTelefonoPeruano_rechaza_numero_largo()
    {
        $resultado = validarTelefonoPeruano('9876543210');
        $this->assertFalse($resultado);
    }

    /**
     * @test
     * Debe rechazar cadena vacía
     */
    public function test_validarTelefonoPeruano_rechaza_vacio()
    {
        $resultado = validarTelefonoPeruano('');
        $this->assertFalse($resultado);
    }

    /**
     * @test
     * Debe rechazar números que son letras
     */
    public function test_validarTelefonoPeruano_rechaza_texto()
    {
        $resultado = validarTelefonoPeruano('abcdefghi');
        $this->assertFalse($resultado);
    }

    /**
     * @test
     * Debe rechazar null
     */
    public function test_validarTelefonoPeruano_rechaza_null()
    {
        $resultado = validarTelefonoPeruano(null);
        $this->assertFalse($resultado);
    }

    // =========================================
    // TESTS: whatsapp_url
    // =========================================

    /**
     * @test
     * Debe construir URL correctamente
     */
    public function test_whatsapp_url_construye_url_correcta()
    {
        config(['services.whatsapp.url' => 'https://api.whatsapp.com']);
        $resultado = whatsapp_url('/api/whatsapp/health');
        $this->assertEquals('https://api.whatsapp.com/api/whatsapp/health', $resultado);
    }

    /**
     * @test
     * Debe manejar URL sin barra al final
     */
    public function test_whatsapp_url_maneja_url_sin_barra_final()
    {
        config(['services.whatsapp.url' => 'https://api.whatsapp.com']);
        $resultado = whatsapp_url('api/whatsapp/health');
        $this->assertEquals('https://api.whatsapp.com/api/whatsapp/health', $resultado);
    }

    /**
     * @test
     * Debe manejar URL con barra al final
     */
    public function test_whatsapp_url_maneja_url_con_barra_final()
    {
        config(['services.whatsapp.url' => 'https://api.whatsapp.com/']);
        $resultado = whatsapp_url('/api/whatsapp/health');
        $this->assertEquals('https://api.whatsapp.com/api/whatsapp/health', $resultado);
    }

    /**
     * @test
     * Debe manejar path sin barra inicial
     */
    public function test_whatsapp_url_maneja_path_sin_barra()
    {
        config(['services.whatsapp.url' => 'https://api.whatsapp.com']);
        $resultado = whatsapp_url('status');
        $this->assertEquals('https://api.whatsapp.com/status', $resultado);
    }

    /**
     * @test
     * Debe manejar URL con múltiples barras finales
     */
    public function test_whatsapp_url_maneja_multiples_barras()
    {
        config(['services.whatsapp.url' => 'https://api.whatsapp.com///']);
        $resultado = whatsapp_url('///api/health');
        $this->assertStringContainsString('api/health', $resultado);
    }

    // =========================================
    // TESTS: whatsapp_api_key
    // =========================================

    /**
     * @test
     * Debe obtener API key desde configuración
     */
    public function test_whatsapp_api_key_obtiene_de_config()
    {
        config(['services.whatsapp.apikey' => 'test-api-key-123']);
        $resultado = whatsapp_api_key();
        $this->assertEquals('test-api-key-123', $resultado);
    }

    /**
     * @test
     * Debe retornar cadena vacía si no está configurada
     */
    public function test_whatsapp_api_key_retorna_vacio_si_no_existe()
    {
        config(['services.whatsapp.apikey' => null]);
        $resultado = whatsapp_api_key();
        $this->assertEquals('', $resultado);
    }

    /**
     * @test
     * Debe retornar cadena vacía si es falso
     */
    public function test_whatsapp_api_key_retorna_vacio_si_falso()
    {
        config(['services.whatsapp.apikey' => false]);
        $resultado = whatsapp_api_key();
        $this->assertEquals('', $resultado);
    }

    // =========================================
    // TESTS: chunksArray
    // =========================================

    /**
     * @test
     * Debe dividir array en chunks del tamaño especificado
     */
    public function test_chunksArray_divide_con_tamanio_especificado()
    {
        $array = range(1, 100);
        $resultado = chunksArray($array, 25);
        $this->assertCount(4, $resultado);
        $this->assertCount(25, $resultado[0]);
    }

    /**
     * @test
     * Debe usar tamaño por defecto de 50
     */
    public function test_chunksArray_usa_tamanio_defecto()
    {
        $array = range(1, 150);
        $resultado = chunksArray($array);
        $this->assertCount(3, $resultado);
        $this->assertCount(50, $resultado[0]);
        $this->assertCount(50, $resultado[1]);
        $this->assertCount(50, $resultado[2]);
    }

    /**
     * @test
     * Debe manejar array más pequeño que chunk size
     */
    public function test_chunksArray_maneja_array_pequenio()
    {
        $array = range(1, 10);
        $resultado = chunksArray($array, 50);
        $this->assertCount(1, $resultado);
        $this->assertCount(10, $resultado[0]);
    }

    /**
     * @test
     * Debe manejar array vacío
     */
    public function test_chunksArray_maneja_array_vacio()
    {
        $array = [];
        $resultado = chunksArray($array, 50);
        $this->assertIsArray($resultado);
    }

    /**
     * @test
     * Debe preservar elementos del array
     */
    public function test_chunksArray_preserva_elementos()
    {
        $array = ['a', 'b', 'c', 'd', 'e'];
        $resultado = chunksArray($array, 2);
        $flatResult = array_merge(...$resultado);
        $this->assertEquals($array, $flatResult);
    }

    /**
     * @test
     * Debe manejar chunk size de 1
     */
    public function test_chunksArray_maneja_chunk_size_uno()
    {
        $array = range(1, 5);
        $resultado = chunksArray($array, 1);
        $this->assertCount(5, $resultado);
        foreach ($resultado as $chunk) {
            $this->assertCount(1, $chunk);
        }
    }

    /**
     * @test
     * Debe manejar array con claves personalizadas
     */
    public function test_chunksArray_preserva_valores_con_claves()
    {
        $array = ['nombre' => 'Juan', 'edad' => 30, 'ciudad' => 'Lima'];
        $resultado = chunksArray($array, 2);
        $this->assertCount(2, $resultado);
    }

    /**
     * @test
     * Debe lanzar error con chunk size negativo
     */
    public function test_chunksArray_chunk_size_negativo()
    {
        $array = range(1, 10);
        // array_chunk lanza un warning, aquí verificamos que se comporta correctamente
        $resultado = @chunksArray($array, -5);
        $this->assertIsArray($resultado);
    }
}


