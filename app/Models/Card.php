<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @OA\Schema(
 *     schema="Card",
 *     type="object",
 *     title="Card",
 *     description="Modelo que representa una tarjeta de presentación enlazada a un blog",
 *     required={"titulo", "descripcion", "public_image", "id_plantilla", "id_blog", "id_empleado"},
 *     @OA\Property(property="id_card", type="integer", readOnly=true, example=1),
 *     @OA\Property(property="titulo", type="string", example="Tarjeta de presentación A"),
 *     @OA\Property(property="descripcion", type="string", example="Descripción breve de la tarjeta"),
 *     @OA\Property(property="public_image", type="string", format="url", example="http://localhost:8000/storage/images/templates/plantilla1/blog-title1/head/imagenPrincipal.webp"),
 *     @OA\Property(property="url_image", type="string", format="url", example="/storage/images/templates/plantilla1/blog-title1/head/imagenPrincipal.webp"),
 *     @OA\Property(property="id_plantilla", type="integer", example=1),
 *     @OA\Property(property="id_blog", type="integer", example=10),
 *     @OA\Property(property="id_empleado", type="integer", example=5),
 *     @OA\Property(
 *         property="blog",
 *         ref="#/components/schemas/Blog"
 *     ),
 *     @OA\Property(
 *         property="empleado",
 *         ref="#/components/schemas/Empleado"
 *     )
 * )
 */

class Card extends Model
{
    use HasFactory;
    protected $table = 'cards';
    protected $primaryKey = 'id_card';
    public $timestamps = false;

    protected $fillable = [
        'titulo',
        'descripcion',
        'public_image',
        'url_image',
        'id_plantilla',
        'id_blog',
        'id_empleado'
    ];

    public function blog(){
        return $this->hasOne(Blog::class, 'id_blog', 'id_blog');
    }

    public function empleado(){
        return $this->hasOne(Empleado::class, 'id_empleado', 'id_empleado');
    }
}
