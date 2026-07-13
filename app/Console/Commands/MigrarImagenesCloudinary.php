<?php

namespace App\Console\Commands;

use App\Models\Campania;
use App\Models\Cliente;
use App\Models\Empleado;
use App\Models\PopupConfig;
use Cloudinary\Cloudinary;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Migra fisicamente las imagenes que ya estan alojadas en Cloudinary (empleados,
 * clientes, campanias de WhatsApp, popups) hacia la cuenta configurada actualmente
 * en .env (CLOUDINARY_*), descargando cada imagen desde su secure_url publica y
 * resubiendola. No requiere acceso a la cuenta de origen: solo usa las URLs ya
 * guardadas en la base de datos. Ignora cualquier URL que no sea de
 * res.cloudinary.com (por ejemplo assets estaticos servidos por el propio backend).
 */
class MigrarImagenesCloudinary extends Command
{
    protected $signature = 'cloudinary:migrar-imagenes
        {--modelo=todos : empleados|clientes|campanias|popups|todos}
        {--dry-run : Solo muestra que se migraria, sin subir ni guardar nada}
        {--limit=0 : Limite de registros a procesar por modelo (0 = sin limite)}';

    protected $description = 'Migra las imagenes ya alojadas en Cloudinary a la cuenta configurada en .env';

    private array $resumen = [];

    /** @var array<string, array{clase: class-string, tipo: string, llavePrimaria: string, campos: array<int, array{url: string, publicId: ?string}>}> */
    private array $configuraciones;

    public function __construct()
    {
        parent::__construct();

        $this->configuraciones = [
            'empleados' => [
                'clase'         => Empleado::class,
                'tipo'          => 'empleados',
                'llavePrimaria' => 'id_empleado',
                'campos'        => [
                    ['url' => 'imagen_perfil_url', 'publicId' => 'imagen_perfil'],
                ],
            ],
            'clientes' => [
                'clase'         => Cliente::class,
                'tipo'          => 'clientes',
                'llavePrimaria' => 'id',
                'campos'        => [
                    ['url' => 'imagen_perfil_url', 'publicId' => 'imagen_perfil'],
                ],
            ],
            'campanias' => [
                'clase'         => Campania::class,
                'tipo'          => 'campanias_whatsapp',
                'llavePrimaria' => 'id_campania',
                'campos'        => [
                    ['url' => 'imagen_url', 'publicId' => null],
                ],
            ],
            'popups' => [
                'clase'         => PopupConfig::class,
                'tipo'          => 'popup_configs',
                'llavePrimaria' => 'id_popup_config',
                'campos'        => [
                    ['url' => 'left_image_url', 'publicId' => 'left_image_public_id'],
                    ['url' => 'right_image_url', 'publicId' => 'right_image_public_id'],
                    ['url' => 'mobile_image_url', 'publicId' => 'mobile_image_public_id'],
                ],
            ],
        ];
    }

    public function handle(): int
    {
        $modelo = $this->option('modelo');
        $dryRun = (bool) $this->option('dry-run');
        $limit  = (int) $this->option('limit');

        if (! in_array($modelo, array_merge(['todos'], array_keys($this->configuraciones)), true)) {
            $this->error("Modelo invalido: {$modelo}. Usa: todos|" . implode('|', array_keys($this->configuraciones)));

            return 1;
        }

        if ($dryRun) {
            $this->warn('Modo dry-run: no se subira ni guardara nada, solo se listará lo que se migraria.');
        }

        $totalMigrados = 0;
        $totalErrores  = 0;
        $totalSaltados = 0;

        foreach ($this->configuraciones as $clave => $config) {
            if ($modelo !== 'todos' && $modelo !== $clave) {
                continue;
            }

            [$m, $e, $s] = $this->migrarModelo($config, $dryRun, $limit);
            $totalMigrados += $m;
            $totalErrores  += $e;
            $totalSaltados += $s;
        }

        if (! $dryRun && ! empty($this->resumen)) {
            $archivo = 'migraciones_cloudinary/migracion_' . now()->format('Y-m-d_His') . '.json';
            Storage::disk('local')->put($archivo, json_encode($this->resumen, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            $this->info("Respaldo de mapeo antiguo -> nuevo guardado en storage/app/{$archivo}");
        }

        $this->newLine();
        $this->info("Migrados: {$totalMigrados} | Saltados: {$totalSaltados} | Errores: {$totalErrores}");

        return $totalErrores > 0 ? 1 : 0;
    }

    /**
     * @return array{0:int,1:int,2:int} [migrados, errores, saltados]
     */
    private function migrarModelo(array $config, bool $dryRun, int $limit): array
    {
        $clase         = $config['clase'];
        $tipo          = $config['tipo'];
        $llavePrimaria = $config['llavePrimaria'];
        $campos        = $config['campos'];

        $query = $clase::query()->where(function ($q) use ($campos) {
            foreach ($campos as $campo) {
                $q->orWhere(function ($qq) use ($campo) {
                    $qq->whereNotNull($campo['url'])->where($campo['url'], '!=', '');
                });
            }
        });

        if ($limit > 0) {
            $query->limit($limit);
        }

        $registros = $query->get();

        $this->info("--- {$tipo}: " . $registros->count() . ' registro(s) con al menos una imagen ---');

        $migrados = 0;
        $errores  = 0;
        $saltados = 0;

        foreach ($registros as $registro) {
            $id = $registro->{$llavePrimaria};

            foreach ($campos as $campo) {
                $urlVieja = $registro->{$campo['url']};

                if (empty($urlVieja)) {
                    continue;
                }

                if (! str_contains($urlVieja, 'res.cloudinary.com')) {
                    $this->line("[{$tipo}#{$id}] Omitido, no es Cloudinary ({$campo['url']}): {$urlVieja}");
                    $saltados++;

                    continue;
                }

                $tempPath = null;

                try {
                    $respuesta = Http::timeout(30)->get($urlVieja);

                    if (! $respuesta->successful()) {
                        $this->warn("[{$tipo}#{$id}] No se pudo descargar ({$respuesta->status()}): {$urlVieja}");
                        $saltados++;

                        continue;
                    }

                    if ($dryRun) {
                        $this->line("[{$tipo}#{$id}] Se migraria ({$campo['url']}): {$urlVieja}");
                        $migrados++;

                        continue;
                    }

                    $tempPath = tempnam(sys_get_temp_dir(), 'cld_');
                    file_put_contents($tempPath, $respuesta->body());

                    $cloudinary = $this->obtenerCloudinary();
                    $resultado  = $cloudinary->uploadApi()->upload($tempPath, [
                        'folder' => "migrados/{$tipo}/{$id}",
                    ]);

                    if (empty($resultado['secure_url'])) {
                        $this->error("[{$tipo}#{$id}] Cloudinary no devolvio secure_url");
                        $errores++;

                        continue;
                    }

                    $publicIdViejo = $campo['publicId'] ? $registro->{$campo['publicId']} : null;

                    $registro->{$campo['url']} = $resultado['secure_url'];

                    if ($campo['publicId']) {
                        $registro->{$campo['publicId']} = $resultado['public_id'];
                    }

                    $registro->save();

                    $this->resumen[] = [
                        'tipo'               => $tipo,
                        'id'                 => $id,
                        'campo'              => $campo['url'],
                        'public_id_anterior' => $publicIdViejo,
                        'url_anterior'       => $urlVieja,
                        'public_id_nuevo'    => $resultado['public_id'],
                        'url_nueva'          => $resultado['secure_url'],
                    ];

                    $this->line("[{$tipo}#{$id}] Migrado ({$campo['url']}) -> {$resultado['secure_url']}");
                    $migrados++;
                } catch (\Throwable $e) {
                    Log::error("MigrarImagenesCloudinary: fallo migrando {$tipo}#{$id} ({$campo['url']})", ['error' => $e->getMessage()]);
                    $this->error("[{$tipo}#{$id}] Error: {$e->getMessage()}");
                    $errores++;
                } finally {
                    if ($tempPath && file_exists($tempPath)) {
                        unlink($tempPath);
                    }
                }
            }
        }

        return [$migrados, $errores, $saltados];
    }

    private function obtenerCloudinary(): Cloudinary
    {
        try {
            return app(Cloudinary::class);
        } catch (\Throwable $e) {
            return new Cloudinary([
                'cloud' => [
                    'cloud_name' => env('CLOUDINARY_CLOUD_NAME'),
                    'api_key'    => env('CLOUDINARY_KEY'),
                    'api_secret' => env('CLOUDINARY_SECRET'),
                ],
                'url' => ['secure' => true],
            ]);
        }
    }
}
