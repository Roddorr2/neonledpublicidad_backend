# Ejemplos de migraciones para Plantillas WhatsApp

Este documento muestra ejemplos y pasos para aplicar las migraciones necesarias si se decide habilitar la persistencia de plantillas (incluyendo soporte híbrido de imágenes: Cloudinary + almacenamiento local).

> Nota: en este repo usaremos Cloudinary para pruebas; las migraciones quedan como ejemplo para aplicar cuando sea necesario.

---

## 1) Crear tabla `plantillas_whatsapp` (ejemplo)

Archivo sugerido: `2026_02_20_000001_create_plantillas_whatsapp_table.php`

Contenido de ejemplo (Laravel migration):

```php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plantillas_whatsapp', function (Blueprint $table) {
            $table->id('id_plantilla_whatsapp');
            $table->foreignId('id_producto')->constrained('productos','id_producto')->onDelete('cascade');
            $table->tinyInteger('numero_plantilla'); // 1..3
            $table->string('nombre')->nullable();
            $table->text('mensaje');
            $table->string('imagen_url', 500)->nullable(); // URL absoluta (Cloudinary o asset)
            $table->string('imagen_public_id')->nullable(); // public_id en Cloudinary (opcional)
            $table->foreignId('created_by')->nullable()->constrained('users','id')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users','id')->nullOnDelete();
            $table->timestamps();
            $table->unique(['id_producto','numero_plantilla']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plantillas_whatsapp');
    }
};
```

---

## 2) Añadir `imagen_public_id` a una tabla ya existente (ejemplo)

Si la tabla ya existe y quieres añadir solo la columna `imagen_public_id`:

```php
Schema::table('plantillas_whatsapp', function (Blueprint $table) {
    $table->string('imagen_public_id')->nullable()->after('imagen_url');
});
```

---

## 3) Ampliar `number_message` en `modal_wats` para incluir `3`

En MySQL/MariaDB el `enum` no es trivial de cambiar desde Blueprint sin `doctrine/dbal`. Dos opciones:

Opción A — usando `DB::statement` (directo SQL):

```php
use Illuminate\Support\Facades\DB;

public function up(): void
{
    DB::statement("ALTER TABLE modal_wats MODIFY COLUMN number_message ENUM('1','2','3') NOT NULL DEFAULT '1'");
}

public function down(): void
{
    DB::statement("ALTER TABLE modal_wats MODIFY COLUMN number_message ENUM('1','2') NOT NULL DEFAULT '1'");
}
```

Opción B — usando `doctrine/dbal` y `change()` (recomendado si prefieres Blueprint):

1. Instala la dependencia: `composer require doctrine/dbal`
2. En la migración:

```php
Schema::table('modal_wats', function (Blueprint $table) {
    $table->enum('number_message', [1,2,3])->default(1)->change();
});
```

> Atención: probar en entorno de staging antes de aplicar en producción.

---

## 4) Seeder ejemplo (catalogo 4 productos × 3 plantillas)

```php
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PlantillasWhatsappSeeder extends Seeder
{
    public function run()
    {
        $plantillas = [];
        for ($prod = 1; $prod <= 4; $prod++) {
            for ($n = 1; $n <= 3; $n++) {
                $plantillas[] = [
                    'id_producto' => $prod,
                    'numero_plantilla' => $n,
                    'mensaje' => "Hola {nombre}, mensaje plantilla $n para producto $prod",
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }
        DB::table('plantillas_whatsapp')->insert($plantillas);
    }
}
```

---

## 5) Lógica de subida híbrida (resumen para el controlador `actualizar`)

1. Comprobar si viene archivo `imagen` en el request.
2. Detectar si Cloudinary está configurado: `env('CLOUDINARY_URL')` o `config('services.cloudinary')`.
3. Si Cloudinary está disponible:
   - Usar Cloudinary SDK para subir:
     - `$result = $cloudinary->uploadApi()->upload($file->getRealPath(), ['folder' => 'plantillas_whatsapp']);`
     - Guardar `imagen_url = $result['secure_url']` y `imagen_public_id = $result['public_id']`.
4. Si Cloudinary NO está disponible:
   - Guardar en disco público: `$path = $request->file('imagen')->store('plantillas_whatsapp', 'public');`
   - Guardar `imagen_url = asset('storage/'.$path)`.
5. Al reemplazar imagen previa:
   - Si existe `imagen_public_id`, llamar a Cloudinary para borrar `uploadApi()->destroy($publicId)`.
   - Si la imagen previa era local (p. ej. URL contiene `storage/`), eliminar el archivo del disco.
6. Guardar siempre `imagen_url` en DB para que el `whatsapp-service` consuma una URL absoluta.

---

## 6) Comandos útiles

- Ejecutar migraciones:

```bash
php artisan migrate
```

- Ejecutar seeder específico:

```bash
php artisan db:seed --class=PlantillasWhatsappSeeder
```

- Instalar `doctrine/dbal` si vas a usar `change()`:

```bash
composer require doctrine/dbal
```

---

## 7) Variables de entorno necesarias para pruebas con Cloudinary

- `CLOUDINARY_URL`
- `CLOUDINARY_CLOUD_NAME`
- `CLOUDINARY_KEY`
- `CLOUDINARY_SECRET`

Estas ya aparecen en el repo (revisar `.env`). Para pruebas locales, configura `CLOUDINARY_*` y usa el flujo Cloudinary.

---

## 8) Recomendación operativa

- Para pruebas inmediatas usa Cloudinary (no requiere subir assets locales).
- Mantén el campo `imagen_public_id` para facilitar borrados y rastreo cuando uses Cloudinary.
- Si en el futuro se habilitan imágenes locales, sigue el mismo campo `imagen_url` con URLs públicas (`asset('storage/...')`) para que el `whatsapp-service` no necesite cambios.

---

Archivo creado por el equipo técnico — si quieres que implemente la migración y el controlador de subida en este repo, indícalo y preparo el PR/patch correspondiente.
