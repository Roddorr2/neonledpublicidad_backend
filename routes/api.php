<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\RolController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PermisoController;
use App\Http\Controllers\Api\BlogController;
use App\Http\Controllers\Api\CardController;
use App\Http\Controllers\Api\ModalesController;
use App\Http\Controllers\Api\TarjetaController;
use App\Http\Controllers\Api\BlogBodyController;
use App\Http\Controllers\Api\BlogHeadController;
use App\Http\Controllers\Api\EmpleadoController;
use App\Http\Controllers\Api\ModalWatController;

use App\Http\Controllers\Api\ModalMailController;
use App\Http\Controllers\Api\BlogFooterController;
use App\Http\Controllers\API\ClienteController;
use App\Http\Controllers\Api\ContactanosController;
use App\Http\Controllers\Api\ReclamacionesController;
use App\Http\Controllers\Api\CloudinaryController;
use App\Http\Controllers\Api\CommendTarjetaController;
use App\Http\Controllers\Api\PropuestaController;
use App\Http\Controllers\Api\productosController;
use App\Http\Controllers\Api\BlogAuditoriaController;
use App\Http\Controllers\Api\MetricasController;
use App\Http\Controllers\Api\WhatsAppCampaignController;
use App\Http\Controllers\Api\WhatsappWebhookController;
use App\Http\Controllers\Api\PlantillasWhatsappController;
use App\Http\Controllers\Api\PlantillasEmailController;

// rutas públicas
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/reset_password', [AuthController::class, "forgotPassword"]);
Route::post('/update_password', [AuthController::class, "updatePassword"]);

Route::post('/contactanos', [ContactanosController::class, "create"]);
Route::post('/reclamaciones', [ReclamacionesController::class, "create"]);
Route::post('/modales', [ModalesController::class, "create"]);

// Cloudinary webhook (public - Cloudinary will call this URL)
Route::post('/cloudinary/webhook', [CloudinaryController::class, 'webhook']);

// Webhook endpoint (protected by X-API-Key header in controller)
Route::post('/whatsapp/webhook/status', [WhatsappWebhookController::class, 'status'])
    ->middleware('throttle:120,1');

// Endpoints consumidos por servicios (X-API-Key header validated in controller)
Route::get('/plantillas/whatsapp/{id_producto}/{numero_plantilla}', [PlantillasWhatsappController::class, 'showByProductoNumero'])
    ->middleware('throttle:240,1')
    ->whereNumber('id_producto')
    ->whereNumber('numero_plantilla');
Route::get('/plantillas/email/{id_producto}/{numero_plantilla}', [PlantillasEmailController::class, 'showByProductoNumero'])
    ->middleware('throttle:240,1')
    ->whereNumber('id_producto')
    ->whereNumber('numero_plantilla');

// blogs públicos para ver los clientes
Route::get('/cards_public', [CardController::class, "index_public"]);
// Route::get('/cards', [CardController::class, "index"]);

Route::get('/blogs/{id}', [BlogController::class, "show"]);
Route::get('/blogs/links/{link}', [BlogController::class, "showByLink"]);
Route::get('/blogs', [BlogController::class, "index"]);
Route::get('/blog_head/{id}', [BlogHeadController::class, "show"]);
Route::get('/blog_footer/{id}', [BlogFooterController::class, "show"]);
Route::get('/blog_body/{id}', [BlogBodyController::class, "show"]);// blogs públicos

Route::get('/modales/send_wat/{id}', [ModalWatController::class, "sendWat"]);

// Route::get('/productos', [ProductosController::class, 'get']);
Route::get('/productos/{id}', [ProductosController::class, 'getById']);
Route::get('/productos_compacto', [ProductosController::class, 'getCompact']);

/**
 * Endpoints de propuestas sin middleware (temporal)
 */
    // Route::get('/propuestas',[PropuestaController::class, "getAll"]);
    // Route::get('/propuesta/{id}',[PropuestaController::class, "load"]);
    // Route::post('/propuesta',[PropuestaController::class, "create"]);
    // Route::put('/propuesta/{id}',[PropuestaController::class, "update"]);
    // Route::delete('/propuesta/{id}',[PropuestaController::class, "delete"]);
    // Route::post('/imagen_propuesta/{id}',[PropuestaController::class, "uploadimage"]);
    // Route::post('/video_propuesta/{id}',[PropuestaController::class, "uploadvideo"]);
    // Route::delete('/video_propuesta/{id}',[PropuestaController::class, "erasevideo"]);
    // Route::delete('/imagen_propuesta/{id}',[PropuestaController::class, "eraseimage"]);

/**
 * Enpoints de gestion de clientes
 */
    // Route::post('/cliente', [ClienteController::class, "create"]);
    // Route::get('/cliente/{id}', [ClienteController::class, "getById"]);
    // Route::get('/cliente', [ClienteController::class, "getAllByPage"]);
    // Route::put('/cliente/{id}', [ClienteController::class, "update"]);
    // Route::delete('/cliente/{id}', [ClienteController::class, "delete"]);
    // Route::post('/cliente/{id}/image', [ClienteController::class, 'updateProfileImage']);
    // Route::delete('/cliente/{id}/image', [ClienteController::class, 'deleteProfileImage']);

// rutas autenticadas
Route::middleware('auth:sanctum')->group(function () {
    // autenticación
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    // Route::post('/empleados/verify-password', [EmpleadoController::class, 'verifyPassword']);
    //  Route::post('/cliente/verify-password', [ClienteController::class, 'verifyPassword']);
    Route::post('/change-password', [AuthController::class, 'changePassword']);

    // imágenes
    Route::post('/empleados/{id}/image', [EmpleadoController::class, 'updateProfileImage']);
    Route::delete('/empleados/{id}/image', [EmpleadoController::class, 'deleteProfileImage']);
    // Cloudinary signature endpoint (authenticated)
    Route::get('/cloudinary/signature', [CloudinaryController::class, 'signature']);
    // Route::post('/cliente/{id}/image', [ClienteController::class, 'updateProfileImage']);
    // Route::delete('/cliente/{id}/image', [ClienteController::class, 'deleteProfileImage']);

    Route::middleware('permission:ver-blogs')->get('/cards', [CardController::class, "index"]);

    Route::middleware('permission:ver-contactos')->get('/contactanos', [ContactanosController::class, "get"]);
    Route::middleware('permission:ver-reclamaciones')->get('/reclamaciones', [ReclamacionesController::class, "get"]);
    Route::middleware('permission:ver-modales')->get('/modales', [ModalesController::class, "get"]);
    Route::middleware('permission:ver-blogs')->get('/cards/blog/{id?}', [CardController::class, "get"]);
    Route::middleware('permission:ver-blogs')->get('/blogs_auditoria', [BlogAuditoriaController::class, 'show']);
    Route::middleware('permission:ver-contactos')->get('/contactanos/{id}', [ContactanosController::class, "getById"]);
    Route::middleware('permission:ver-reclamaciones')->get('/reclamaciones/{id}', [ReclamacionesController::class, "getById"]);
    Route::middleware('permission:ver-modales')->get('/modales/{id}', [ModalesController::class, "getById"]);

    //revisar emails y messages
    Route::middleware('permission:ver-modales')->get('/modales/modals_emails_wats/{id}', [ModalesController::class, "getSendModales"]);
    //enviar emails y messages
    Route::middleware('permission:enviar-mensajes')->get('/modales/send_mail/{id}',[ModalMailController::class, "sendMail"]);
    Route::middleware('permission:enviar-mensajes')->put('/modales/reportar_error/{id}', [ModalMailController::class, "reportarError"]);
    Route::middleware('permission:enviar-mensajes')->put('/modales/estado_wat/{id}', [ModalWatController::class, "cambiarEstado"]);

    // WhatsApp Campaigns protegidas
    Route::middleware('role:marketing,administrador')->group(function () {
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
        // Plantillas WhatsApp (dashboard)
        Route::get('/plantillas/whatsapp', [PlantillasWhatsappController::class, 'index']);
        Route::get('/plantillas/whatsapp/{id}', [PlantillasWhatsappController::class, 'show'])
            ->whereNumber('id');
        Route::post('/plantillas/whatsapp/{id}/actualizar', [PlantillasWhatsappController::class, 'actualizar'])
            ->whereNumber('id');

        // Plantillas Email (dashboard)
        Route::get('/plantillas/email', [PlantillasEmailController::class, 'index']);
        Route::get('/plantillas/email/{id}', [PlantillasEmailController::class, 'show'])
            ->whereNumber('id');
        Route::post('/plantillas/email/{id}/actualizar', [PlantillasEmailController::class, 'actualizar'])
            ->whereNumber('id');
    });

    //rutas create blog
    Route::middleware('permission:crear-blogs')->post('/card', [CardController::class, "create"]);
    Route::middleware('permission:crear-blogs')->post('/blog', [BlogController::class, "create"]);
    Route::middleware('permission:crear-blogs')->post('/blog_head', [BlogHeadController::class, "create"]);
    Route::middleware('permission:crear-blogs')->post('/blog_body', [BlogBodyController::class, "create"]);
    Route::middleware('permission:crear-blogs')->post('/blog_footer', [BlogFooterController::class, "create"]);
    Route::middleware('permission:crear-tarjetas')->post('/commend_tarjeta', [CommendTarjetaController::class, "create"]);
    Route::middleware('permission:crear-tarjetas')->post('/tarjeta', [TarjetaController::class, "create"]);
    Route::middleware('permission:crear-tarjetas')->post('/card/blog/image_head/{id}', [CardController::class, "imageHeader"]);
    Route::middleware('permission:crear-tarjetas')->post('/card/blog/images_body/{id}', [CardController::class, "imagesBody"]);
    Route::middleware('permission:crear-tarjetas')->post('/card/blog/images_footer/{id}', [CardController::class, "imagesFooter"]);

    //rutas update blog
    Route::middleware('permission:editar-blogs')->put('/card/{id}', [CardController::class, "update"]);
    Route::middleware('permission:editar-blogs')->put('/blog/{id}', [BlogController::class, "update"]);
    Route::middleware('permission:editar-blogs')->put('/blog_head/{id}', [BlogHeadController::class, "update"]);
    Route::middleware('permission:editar-blogs')->put('/blog_body/{id}', [BlogBodyController::class, "update"]);
    Route::middleware('permission:editar-blogs')->put('/blog_footer/{id}', [BlogFooterController::class, "update"]);
    Route::middleware('permission:editar-blogs')->put('/commend_tarjeta/{id}', [CommendTarjetaController::class, "update"]);
    Route::middleware('permission:editar-blogs')->put('/tarjeta/{id}', [TarjetaController::class, "update"]);

    //rutas delete blog
    Route::middleware('permission:eliminar-blogs')->delete('/cards/{id}', [CardController::class, "destroy"]);
    Route::middleware('permission:eliminar-blogs')->delete('/blogs/{id}', [BlogController::class, "destroy"]);
    Route::middleware('permission:eliminar-blogs')->delete('/blog_head/{id}', [BlogHeadController::class, "destroy"]);
    Route::middleware('permission:eliminar-blogs')->delete('/blog_body/{id}', [BlogBodyController::class, "destroy"]);
    Route::middleware('permission:eliminar-blogs')->delete('/blog_footer/{id}', [BlogFooterController::class, "destroy"]);
    Route::middleware('permission:eliminar-tarjetas')->delete('/commend_tarjeta/{id}', [CommendTarjetaController::class, "destroy"]);
    Route::middleware('permission:eliminar-tarjetas')->delete('/tarjetas_delete/{id}', [TarjetaController::class, "destroyAll"]);
    Route::middleware('permission:editar-blogs')->delete('/delete_carpet/{id}', [CardController::class, "deleteCarpetaImages"]);

    //rutas propuesta
    Route::middleware('permission:ver-propuestas-cliente')->get('/cliente/{id}/propuestas',[PropuestaController::class, "getAll_Cliente"]);//retorna propuestas por id del cliente
    Route::middleware('permission:ver-propuestas')->get('/propuestas',[PropuestaController::class, "getAll"]);//todas las propuestas en general
    Route::middleware('permission:ver-propuestas')->get('/propuesta/{id}',[PropuestaController::class, "load"]);//busca una propuesta por id_propuesta
    Route::middleware('permission:ver-propuestas-cliente')->get('/cliente/{id_cliente}/propuesta/{id_propuesta}',[PropuestaController::class, "load_cliente"]);//propuestas por cliente
    Route::middleware('permission:crear-propuestas')->post('/propuesta',[PropuestaController::class, "create"]);//ya
    Route::middleware('permission:editar-propuestas')->patch('/propuesta/{id}',[PropuestaController::class, "update"]);//ya
    Route::middleware('permission:eliminar-propuestas')->delete('/propuesta/{id}',[PropuestaController::class, "delete"]);//
    Route::middleware('permission:crear-propuestas')->post('/imagen_propuesta/{id}',[PropuestaController::class, "uploadimage"]);
    Route::middleware('permission:crear-propuestas')->post('/video_propuesta/{id}',[PropuestaController::class, "uploadvideo"]);
    Route::middleware('permission:eliminar-propuestas')->delete('/video_propuesta/{id}',[PropuestaController::class, "erasevideo"]);
    Route::middleware('permission:eliminar-propuestas')->delete('/imagen_propuesta/{id}',[PropuestaController::class, "eraseimage"]);
    Route::middleware('permission:ver-propuestas-cliente')->get('/cliente/{id_cliente}/propuesta/{id_propuesta}/descargar-imagenes',[PropuestaController::class, 'descargarImagenes']);
    Route::middleware('permission:ver-propuestas-cliente')->get('/cliente/{id_cliente}/propuesta/{id_propuesta}/descargar-videos',[PropuestaController::class, 'descargarVideos']);

    //rutas clientes
    Route::middleware('permission:crear-cliente')->post('/cliente', [ClienteController::class, "create"]);
    Route::middleware('permission:ver-cliente')->get('/cliente/{id}', [ClienteController::class, "getById"]);
    Route::middleware('permission:ver-cliente')->get('/cliente', [ClienteController::class, "getAllByPage"]);
    Route::middleware('permission:editar-cliente')->put('/cliente/{id}', [ClienteController::class, "update"]);
    Route::put('/mi-perfil', [ClienteController::class, "updateProfile"]);
    Route::middleware('permission:eliminar-cliente')->delete('/cliente/{id}', [ClienteController::class, "delete"]);
    Route::post('/cliente/{id}/image', [ClienteController::class, 'updateProfileImage']);
    Route::delete('/cliente/{id}/image', [ClienteController::class, 'deleteProfileImage']);

    // rutas update
    Route::middleware('permission:editar-contactos')->put('/contactanos/{id}', [ContactanosController::class, "update"]);
    Route::middleware('permission:editar-reclamaciones')->put('/reclamaciones/{id}', [ReclamacionesController::class, "update"]);
    Route::middleware('permission:editar-modales')->put('/modales/{id}', [ModalesController::class, "update"]);

    // rutas delete/destroy
    Route::middleware('permission:eliminar-contactos')->delete('/contactanos/{id}', [ContactanosController::class, "delete"]);
    Route::middleware('permission:eliminar-reclamaciones')->delete('/reclamaciones/{id}', [ReclamacionesController::class, "delete"]);
    Route::middleware('permission:eliminar-modales')->delete('/modales/{id}', [ModalesController::class, "delete"]);

    //rutas empleados
    Route::middleware('permission:ver-empleados')->get('/empleados', [EmpleadoController::class, "getAllByPage"]);
    Route::middleware('permission:ver-empleados')->get('/empleados/{id}', [EmpleadoController::class, "getById"]);
    Route::middleware('permission:crear-empleados')->post('/empleados', [EmpleadoController::class, "create"]);
    Route::middleware('permission:permisos-generales')->put('/empleados/{id}', [EmpleadoController::class, "update"]);
    Route::middleware('permission:permisos-generales')->put('/empleados/pass/{id}', [EmpleadoController::class, "updatePass"]);
    Route::middleware('permission:eliminar-empleados')->delete('/empleados/{id}', [EmpleadoController::class, "delete"]);

    // roles
    Route::middleware('permission:ver-roles')->get('/roles', [RolController::class, "index"]);
    Route::middleware('permission:crear-roles')->post('/roles', [RolController::class, "store"]);
    Route::middleware('permission:ver-roles')->get('/roles/{id}', [RolController::class, "show"]);
    Route::middleware('permission:editar-roles')->put('/roles/{id}', [RolController::class, "update"]);
    Route::middleware('permission:eliminar-roles')->delete('/roles/{id}', [RolController::class, "destroy"]);
    Route::middleware('permission:ver-permisos')->get('/roles/{id}/permisos', [RolController::class, "getPermisos"]);
    Route::middleware('permission:ver-permisos')->post('/roles/{id}/permisos', [RolController::class, "syncPermisos"]);

    // permisos
    Route::middleware('permission:ver-permisos')->get('/permisos', [PermisoController::class, "index"]);
    Route::middleware('permission:ver-permisos')->get('/permisos/{id}', [PermisoController::class, "show"]);
    Route::middleware('permission:crear-permisos')->post('/permisos', [PermisoController::class, "store"]);
    Route::middleware('permission:editar-permisos')->put('/permisos/{id}', [PermisoController::class, "update"]);
    Route::middleware('permission:eliminar-permisos')->delete('/permisos/{id}', [PermisoController::class, "destroy"]);

    // productos
    Route::middleware('permission:ver-productos')->get('/productos', [ProductosController::class, 'get']);
    // Route::middleware('permission:ver-productos')->get('/productos/{id}', [ProductosController::class, 'getById']);
    // Route::middleware('permission:ver-productos')->get('/productos_compacto', [ProductosController::class, 'getCompact']);
    Route::middleware('permission:crear-productos')->post('/productos', [ProductosController::class, 'create']);
    Route::middleware('permission:editar-productos')->put('/productos/{id}', [ProductosController::class, 'update']);
    Route::middleware('permission:eliminar-productos')->delete('/productos/{id}', [ProductosController::class, 'destroy']);

    // metricas
    Route::middleware('permission:ver-blogs')->get('/metrics/count_blogs_by_month', [MetricasController::class, "countBlogsByMonth"]);//1.1 Cantidad de blogs creados en un mes específico
    Route::middleware('permission:ver-blogs')->get('/metrics/list_blogs_by_months_12', [MetricasController::class, "listBlogsByMonths12"]);//1.2 Lista de cantidad de blogs creados en los últimos 12 meses
    Route::middleware('permission:ver-blogs')->get('/metrics/top5_months_with_more_blogs', [MetricasController::class, "top5MothsWithMoreBlogs"]);//1.3 Top 5 meses con más blogs creados
    Route::middleware('permission:ver-blogs')->get('/metrics/cards_by_plantilla', [MetricasController::class, "listOfCardsByPlantilla"]);//2.1 Lista de cards creadas por plantilla
    Route::middleware('permission:ver-blogs')->get('/metrics/count_cards_by_plantilla', [MetricasController::class, "countListOfCardsByPlantilla"]);//2.2 Cantidad de cards creadas por plantilla
    Route::middleware('permission:ver-blogs')->get('/metrics/count_total_cards', [MetricasController::class, "tableCardsByIdPlantilla"]);//2.3 Cantidad total de cards creadas por plantilla
    Route::middleware('permission:ver-blogs')->get('/metrics/list_empleado_cards', [MetricasController::class, "listEmpleadoWithCards"]);//3.1 Lista de empleados con cantidad de cards creadas
    Route::middleware('permission:ver-blogs')->get('/metrics/count_cards_by_empleado', [MetricasController::class, "countListOfCardsByEmpleado"]);//3.2 Cantidad de cards creadas por empleado
    Route::middleware('permission:ver-blogs')->get('/metrics/count_total_cards_by_empleado', [MetricasController::class, "tableCardsByEmpleado"]);//3.3 Top 5 empleados con más cards creadas
    //Route::middleware('permission:ver-blogs')->get('/metrics/frecuencia_publicacion_cards_todos_empleados', [MetricasController::class, "frecuenciaPublicacionCardsTodosEmpleados"]);//4.1 Frecuencia de publicación de cards por empleado Desacarteable
    Route::middleware('permission:ver-blogs')->get('/metrics/tiempo_creacion_edicion_publicacion_card', [MetricasController::class, "tiempoCreacionEdicionPublicacionCard"]);//4.2 Tiempo promedio de creación, edición y publicación de una card por empleado
});
