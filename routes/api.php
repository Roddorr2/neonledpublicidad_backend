<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BlogAuditoriaController;
use App\Http\Controllers\Api\BlogBodyController;
use App\Http\Controllers\Api\BlogController;
use App\Http\Controllers\Api\BlogFooterController;
use App\Http\Controllers\Api\BlogHeadController;
use App\Http\Controllers\Api\CardController;
use App\Http\Controllers\Api\ClienteController;
use App\Http\Controllers\Api\CloudinaryController;
use App\Http\Controllers\Api\CommendTarjetaController;
use App\Http\Controllers\Api\ContactanosController;
use App\Http\Controllers\Api\EmpleadoController;
use App\Http\Controllers\Api\GoogleReviewsController;
use App\Http\Controllers\Api\TestimonioController;
use App\Http\Controllers\Api\MetricasController;
use App\Http\Controllers\Api\ModalesController;
use App\Http\Controllers\Api\ModalMailController;
use App\Http\Controllers\Api\ModalWatController;
use App\Http\Controllers\Api\PermisoController;
use App\Http\Controllers\Api\PlantillasEmailController;
use App\Http\Controllers\Api\PlantillasWhatsappController;
use App\Http\Controllers\Api\PopupConfigController;
use App\Http\Controllers\Api\ProductosController;
use App\Http\Controllers\Api\PropuestaController;
use App\Http\Controllers\Api\ReclamacionesController;
use App\Http\Controllers\Api\RolController;
use App\Http\Controllers\Api\TarjetaController;
use App\Http\Controllers\Api\WhatsAppCampaignController;
use App\Http\Controllers\Api\WhatsappWebhookController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// ==================== RUTAS PÚBLICAS ====================
Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:register');
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:login');
Route::post('/reset_password', [AuthController::class, 'forgotPassword'])->middleware('throttle:reset_password');
Route::post('/update_password', [AuthController::class, 'updatePassword'])->middleware('throttle:update_password');

Route::post('/contactanos', [ContactanosController::class, 'create']);
Route::post('/reclamaciones', [ReclamacionesController::class, 'create']);
Route::post('/modales', [ModalesController::class, 'create']);

// Cloudinary webhook (public)
Route::post('/cloudinary/webhook', [CloudinaryController::class, 'webhook']);

// Testimonios de Google Business (cacheados en backend) para el carrusel de /nosotros
Route::get('/testimonios-google', [GoogleReviewsController::class, 'index'])
    ->middleware('throttle:60,1');

// Testimonios propios (creados desde el dashboard) para el carrusel de /nosotros
Route::get('/testimonios', [TestimonioController::class, 'publico']);

// Webhook endpoint (protegido por X-API-Key en el controlador)
Route::post('/whatsapp/webhook/status', [WhatsappWebhookController::class, 'status'])
    ->middleware('throttle:120,1');

// Endpoints consumidos por servicios externos (X-API-Key)
Route::get('/plantillas/whatsapp/{id_producto}/{numero_plantilla}', [PlantillasWhatsappController::class, 'showByProductoNumero'])
    ->middleware('throttle:240,1')
    ->whereNumber('id_producto')
    ->whereNumber('numero_plantilla');

Route::get('/plantillas/email/{id_producto}/{numero_plantilla}', [PlantillasEmailController::class, 'showByProductoNumero'])
    ->middleware('throttle:240,1')
    ->whereNumber('id_producto')
    ->whereNumber('numero_plantilla');

// Blogs públicos
Route::get('/cards_public', [CardController::class, 'index_public']);
Route::get('/cards/search', [CardController::class, 'search']);
Route::get('/blogs/{id}', [BlogController::class, 'show']);
Route::get('/blogs/links/{link}', [BlogController::class, 'showByLink']);
Route::get('/blogs', [BlogController::class, 'index']);
Route::get('/blog_head/{id}', [BlogHeadController::class, 'show']);
Route::get('/blog_footer/{id}', [BlogFooterController::class, 'show']);
Route::get('/blog_body/{id}', [BlogBodyController::class, 'show']);

Route::get('/modales/send_wat/{id}', [ModalWatController::class, 'sendWat']);

Route::get('/productos/{id}', [ProductosController::class, 'getById']);
Route::get('/productos_compacto', [ProductosController::class, 'getCompact']);

// Pop-Up público
Route::get('/public/popup-configs/producto/{id_producto}', [PopupConfigController::class, 'showPublic'])
    ->whereNumber('id_producto');

// ==================== RUTAS AUTENTICADAS ====================
Route::middleware('auth:sanctum')->group(function () {

    // -------------------- AUTENTICACIÓN --------------------
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/change-password', [AuthController::class, 'changePassword']);

    // -------------------- PERFIL PROPIO (todos los autenticados) --------------------
    // Cada usuario puede actualizar su propio perfil
    Route::put('/mi-perfil/empleado', [EmpleadoController::class, 'updateOwnProfile']);
    Route::post('/mi-perfil/empleado/image', [EmpleadoController::class, 'updateOwnProfileImage']);
    Route::delete('/mi-perfil/empleado/image', [EmpleadoController::class, 'deleteOwnProfileImage']);

    // Cliente: actualizar su propio perfil
    Route::put('/mi-perfil/cliente', [ClienteController::class, 'updateOwnProfile']);
    Route::post('/mi-perfil/cliente/image', [ClienteController::class, 'updateOwnProfileImage']);
    Route::delete('/mi-perfil/cliente/image', [ClienteController::class, 'deleteOwnProfileImage']);

    // -------------------- CLOUDINARY --------------------
    Route::get('/cloudinary/signature', [CloudinaryController::class, 'signature']);

    // -------------------- TESTIMONIOS (dashboard) --------------------
    // Ver: administrador y marketing (ver permisos en PermisosSeeder)
    Route::middleware('permission:ver-testimonios')->group(function () {
        Route::get('/testimonios/admin', [TestimonioController::class, 'index']);
    });

    Route::middleware('permission:crear-testimonios')->group(function () {
        Route::post('/testimonios', [TestimonioController::class, 'store']);
    });

    Route::middleware('permission:editar-testimonios')->group(function () {
        Route::post('/testimonios/reordenar', [TestimonioController::class, 'reordenar']);
        Route::patch('/testimonios/{id}/toggle-activo', [TestimonioController::class, 'toggleActivo']);
        Route::post('/testimonios/{id}', [TestimonioController::class, 'update']); // POST + _method=PUT (por el archivo)
    });

    Route::middleware('permission:eliminar-testimonios')->group(function () {
        Route::delete('/testimonios/{id}', [TestimonioController::class, 'destroy']);
    });

    // -------------------- BLOGS Y TARJETAS --------------------
    Route::middleware('permission:ver-blogs')->group(function () {
        Route::get('/cards', [CardController::class, 'index']);
        Route::get('/tarjetas', [TarjetaController::class, 'index']);
        Route::get('/cards/blog/{id?}', [CardController::class, 'get']);
        Route::get('/blogs_auditoria', [BlogAuditoriaController::class, 'show']);
    });

    Route::middleware('permission:crear-blogs')->group(function () {
        Route::post('/card', [CardController::class, 'create']);
        Route::post('/blog', [BlogController::class, 'create']);
        Route::post('/blog_head', [BlogHeadController::class, 'create']);
        Route::post('/blog_body', [BlogBodyController::class, 'create']);
        Route::post('/blog_footer', [BlogFooterController::class, 'create']);
        Route::post('/commend_tarjeta', [CommendTarjetaController::class, 'create']);
        Route::post('/tarjeta', [TarjetaController::class, 'create']);
        Route::post('/card/blog/image_head/{id}', [CardController::class, 'imageHeader']);
        Route::post('/card/blog/images_body/{id}', [CardController::class, 'imagesBody']);
        Route::post('/card/blog/images_footer/{id}', [CardController::class, 'imagesFooter']);
    });

    Route::middleware('permission:editar-blogs')->group(function () {
        Route::put('/card/{id}', [CardController::class, 'update']);
        Route::put('/blog/{id}', [BlogController::class, 'update']);
        Route::put('/blog_head/{id}', [BlogHeadController::class, 'update']);
        Route::put('/blog_body/{id}', [BlogBodyController::class, 'update']);
        Route::put('/blog_footer/{id}', [BlogFooterController::class, 'update']);
        Route::put('/commend_tarjeta/{id}', [CommendTarjetaController::class, 'update']);
        Route::put('/tarjeta/{id}', [TarjetaController::class, 'update']);
        Route::delete('/delete_carpet/{id}', [CardController::class, 'deleteCarpetaImages']);
    });

    Route::middleware('permission:eliminar-blogs')->group(function () {
        Route::delete('/cards/{id}', [CardController::class, 'destroy']);
        Route::delete('/blogs/{id}', [BlogController::class, 'destroy']);
        Route::delete('/blog_head/{id}', [BlogHeadController::class, 'destroy']);
        Route::delete('/blog_body/{id}', [BlogBodyController::class, 'destroy']);
        Route::delete('/blog_footer/{id}', [BlogFooterController::class, 'destroy']);
    });

    Route::middleware('permission:eliminar-tarjetas')->group(function () {
        Route::delete('/commend_tarjeta/{id}', [CommendTarjetaController::class, 'destroy']);
        Route::delete('/tarjetas_delete/{id}', [TarjetaController::class, 'destroyAll']);
    });

    // -------------------- CONTACTOS, RECLAMACIONES, MODALES (solo lectura/escritura) --------------------
    Route::middleware('permission:ver-contactos')->get('/contactanos', [ContactanosController::class, 'get']);
    Route::middleware('permission:ver-contactos')->get('/contactanos/{id}', [ContactanosController::class, 'getById']);

    Route::middleware('permission:ver-reclamaciones')->group(function () {
        Route::get('/reclamaciones', [ReclamacionesController::class, 'get']);
        Route::get('/reclamaciones/{id}', [ReclamacionesController::class, 'getById']);
    });

    Route::middleware('permission:ver-modales')->group(function () {
        Route::get('/modales', [ModalesController::class, 'get']);
        Route::get('/modales/{id}', [ModalesController::class, 'getById']);
        Route::get('/modales/modals_emails_wats/{id}', [ModalesController::class, 'getSendModales']);
    });

    Route::middleware('permission:enviar-mensajes')->group(function () {
        Route::get('/modales/send_mail/{id}', [ModalMailController::class, 'sendMail']);
        Route::put('/modales/reportar_error/{id}', [ModalMailController::class, 'reportarError']);
        Route::put('/modales/estado_wat/{id}', [ModalWatController::class, 'cambiarEstado']);
    });

    Route::middleware('permission:editar-contactos')->put('/contactanos/{id}', [ContactanosController::class, 'update']);
    Route::middleware('permission:editar-reclamaciones')->put('/reclamaciones/{id}', [ReclamacionesController::class, 'update']);
    Route::middleware('permission:editar-modales')->put('/modales/{id}', [ModalesController::class, 'update']);

    Route::middleware('permission:eliminar-contactos')->delete('/contactanos/{id}', [ContactanosController::class, 'delete']);
    Route::middleware('permission:eliminar-reclamaciones')->delete('/reclamaciones/{id}', [ReclamacionesController::class, 'delete']);
    Route::middleware('permission:eliminar-modales')->delete('/modales/{id}', [ModalesController::class, 'delete']);

    // -------------------- CLIENTES --------------------
    Route::middleware('permission:crear-cliente')->post('/cliente', [ClienteController::class, 'create']);
    Route::middleware('permission:ver-cliente')->get('/cliente', [ClienteController::class, 'getAllByPage']);
    Route::middleware('permission:ver-cliente')->get('/cliente/{id}', [ClienteController::class, 'getById']);
    Route::middleware('permission:editar-cliente')->put('/cliente/{id}', [ClienteController::class, 'update']);
    Route::middleware('permission:eliminar-cliente')->delete('/cliente/{id}', [ClienteController::class, 'delete']);
    Route::post('/cliente/{id}/image', [ClienteController::class, 'updateProfileImage']);
    Route::delete('/cliente/{id}/image', [ClienteController::class, 'deleteProfileImage']);

    // -------------------- EMPLEADOS (SOLO ADMINISTRADOR) --------------------
    // Gestión completa de empleados solo para administradores
    Route::middleware('role:administrador')->group(function () {
        Route::get('/empleados', [EmpleadoController::class, 'getAllByPage']);
        Route::get('/empleados/{id}', [EmpleadoController::class, 'getById']);
        Route::post('/empleados', [EmpleadoController::class, 'create']);
        Route::put('/empleados/{id}', [EmpleadoController::class, 'update']);
        Route::put('/empleados/pass/{id}', [EmpleadoController::class, 'updatePass']);
        Route::delete('/empleados/{id}', [EmpleadoController::class, 'delete']);
        // Eliminar foto de CUALQUIER empleado (solo admin)
        Route::delete('/empleados/{id}/image', [EmpleadoController::class, 'deleteProfileImage']);
        Route::post('/empleados/{id}/image', [EmpleadoController::class, 'updateProfileImage']);
    });

    // -------------------- ROLES Y PERMISOS (SOLO ADMIN) --------------------
    Route::middleware('role:administrador')->group(function () {
        Route::get('/roles', [RolController::class, 'index']);
        Route::post('/roles', [RolController::class, 'store']);
        Route::get('/roles/{id}', [RolController::class, 'show']);
        Route::put('/roles/{id}', [RolController::class, 'update']);
        Route::delete('/roles/{id}', [RolController::class, 'destroy']);
        Route::get('/roles/{id}/permisos', [RolController::class, 'getPermisos']);
        Route::post('/roles/{id}/permisos', [RolController::class, 'syncPermisos']);

        Route::get('/permisos', [PermisoController::class, 'index']);
        Route::get('/permisos/{id}', [PermisoController::class, 'show']);
        Route::post('/permisos', [PermisoController::class, 'store']);
        Route::put('/permisos/{id}', [PermisoController::class, 'update']);
        Route::delete('/permisos/{id}', [PermisoController::class, 'destroy']);
    });

    // -------------------- PRODUCTOS --------------------
    Route::middleware('permission:ver-productos')->get('/productos', [ProductosController::class, 'get']);
    Route::middleware('permission:crear-productos')->post('/productos', [ProductosController::class, 'create']);
    Route::middleware('permission:editar-productos')->put('/productos/{id}', [ProductosController::class, 'update']);
    Route::middleware('permission:eliminar-productos')->delete('/productos/{id}', [ProductosController::class, 'destroy']);

    // -------------------- PROPUESTAS --------------------
    Route::middleware('permission:ver-propuestas-cliente')->get('/cliente/{id}/propuestas', [PropuestaController::class, 'getAll_Cliente']);
    Route::middleware('permission:ver-propuestas-cliente')->get('/cliente/{id_cliente}/propuesta/{id_propuesta}', [PropuestaController::class, 'load_cliente']);
    Route::middleware('permission:ver-propuestas-cliente')->get('/cliente/{id_cliente}/propuesta/{id_propuesta}/descargar-imagenes', [PropuestaController::class, 'descargarImagenes']);
    Route::middleware('permission:ver-propuestas-cliente')->get('/cliente/{id_cliente}/propuesta/{id_propuesta}/descargar-videos', [PropuestaController::class, 'descargarVideos']);

    Route::middleware('permission:ver-propuestas')->get('/propuestas', [PropuestaController::class, 'getAll']);
    Route::middleware('permission:ver-propuestas')->get('/propuesta/{id}', [PropuestaController::class, 'load']);

    Route::middleware('permission:crear-propuestas')->group(function () {
        Route::post('/propuesta', [PropuestaController::class, 'create']);
        Route::post('/imagen_propuesta/{id}', [PropuestaController::class, 'uploadimage']);
        Route::post('/video_propuesta/{id}', [PropuestaController::class, 'uploadvideo']);
    });

    Route::middleware('permission:editar-propuestas')->patch('/propuesta/{id}', [PropuestaController::class, 'update']);

    Route::middleware('permission:eliminar-propuestas')->group(function () {
        Route::delete('/propuesta/{id}', [PropuestaController::class, 'delete']);
        Route::delete('/video_propuesta/{id}', [PropuestaController::class, 'erasevideo']);
        Route::delete('/imagen_propuesta/{id}', [PropuestaController::class, 'eraseimage']);
    });

    // -------------------- MÉTRICAS --------------------
    Route::middleware('permission:ver-blogs')->group(function () {
        Route::get('/metrics/count_blogs_by_month', [MetricasController::class, "countBlogsByMonth"]);
        Route::get('/metrics/list_blogs_by_months_12', [MetricasController::class, "listBlogsByMonths12"]);
        Route::get('/metrics/top5_months_with_more_blogs', [MetricasController::class, "top5MothsWithMoreBlogs"]);
        Route::get('/metrics/cards_by_plantilla', [MetricasController::class, "listOfCardsByPlantilla"]);
        Route::get('/metrics/count_cards_by_plantilla', [MetricasController::class, "countListOfCardsByPlantilla"]);
        Route::get('/metrics/count_total_cards', [MetricasController::class, "tableCardsByIdPlantilla"]);
        Route::get('/metrics/list_empleado_cards', [MetricasController::class, "listEmpleadoWithCards"]);
        Route::get('/metrics/count_cards_by_empleado', [MetricasController::class, "countListOfCardsByEmpleado"]);
        Route::get('/metrics/count_total_cards_by_empleado', [MetricasController::class, "tableCardsByEmpleado"]);
        Route::get('/metrics/tiempo_creacion_edicion_publicacion_card', [MetricasController::class, "tiempoCreacionEdicionPublicacionCard"]);
    });

    // -------------------- WHATSAPP Y PLANTILLAS (SOLO MARKETING Y ADMIN) --------------------
    Route::middleware('role:marketing,administrador')->group(function () {
        // WhatsApp Campaigns
        Route::post('/whatsapp/campaign/activate', [WhatsAppCampaignController::class, 'activate']);
        Route::get('/whatsapp/campaign/preview/{service}', [WhatsAppCampaignController::class, 'previewCampaign'])
            ->where('service', '^p(1[0-5]|[1-9])$');
        Route::post('/whatsapp/campaign/create', [WhatsAppCampaignController::class, 'createCampaign']);
        Route::post('/whatsapp/campaign/{id}/start', [WhatsAppCampaignController::class, 'startCampaign'])
            ->whereNumber('id');
        Route::post('/whatsapp/campaign/estimate', [WhatsAppCampaignController::class, 'estimate']);
        Route::get('/whatsapp/campaign/{id}/status', [WhatsAppCampaignController::class, 'status'])
            ->whereNumber('id');
        Route::get('/whatsapp/campaign/{id}/progress-flag', [WhatsAppCampaignController::class, 'progressFlag'])
            ->whereNumber('id');
        Route::get('/whatsapp/campaigns', [WhatsAppCampaignController::class, 'index']);

        // Plantillas WhatsApp
        Route::get('/plantillas/whatsapp', [PlantillasWhatsappController::class, 'index']);
        Route::get('/plantillas/whatsapp/{id}', [PlantillasWhatsappController::class, 'show'])
            ->whereNumber('id');
        Route::post('/plantillas/whatsapp/{id}/actualizar', [PlantillasWhatsappController::class, 'actualizar'])
            ->whereNumber('id');

        // Plantillas Email
        Route::get('/plantillas/email', [PlantillasEmailController::class, 'index']);
        Route::get('/plantillas/email/{id}', [PlantillasEmailController::class, 'show'])
            ->whereNumber('id');
        Route::post('/plantillas/email/{id}/actualizar', [PlantillasEmailController::class, 'actualizar'])
            ->whereNumber('id');

        // Pop-Up Configs
        Route::get('/popup-configs', [PopupConfigController::class, 'index']);
        Route::get('/popup-configs/producto/{id_producto}', [PopupConfigController::class, 'showByProducto'])
            ->whereNumber('id_producto');
        Route::get('/popup-configs/{id}', [PopupConfigController::class, 'show'])
            ->whereNumber('id');
        Route::post('/popup-configs', [PopupConfigController::class, 'store']);
        Route::post('/popup-configs/{id}/actualizar', [PopupConfigController::class, 'update'])
            ->whereNumber('id');
        Route::delete('/popup-configs/{id}', [PopupConfigController::class, 'destroy'])
            ->whereNumber('id');
    });
});