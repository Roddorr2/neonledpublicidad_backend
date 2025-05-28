<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


/**
 * @OA\Schema(
 *     schema="Tarjeta",
 *     type="object",
 *     title="Tarjeta",
 *     description="Elemento representativo vinculado a un BlogBody, que contiene un título y descripción breves.",
 *     required={"titulo", "descripcion", "id_blog_body"},
 *     @OA\Property(property="id_tarjeta", type="integer", readOnly=true, example=10),
 *     @OA\Property(property="titulo", type="string", example="Innovación en Energía Renovable"),
 *     @OA\Property(property="descripcion", type="string", example="Resumen de avances en paneles solares en 2025."),
 *     @OA\Property(property="id_blog_body", type="integer", example=3),
 *     @OA\Property(property="blog_body", ref="#/components/schemas/BlogBody")
 * )
 */
class Tarjeta extends Model
{
    use HasFactory;
    protected $table = 'tarjetas';
    protected $primaryKey = 'id_tarjeta';
    public $timestamps = false;
    protected $fillable = [
        'titulo',
        'descripcion',
        'id_blog_body',
    ];

    public function blog_body(){
        return $this->belongsTo(BlogBody::class, 'id_blog_body', 'id_blog_body');
    }
}
