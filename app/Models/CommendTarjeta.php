<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @OA\Schema(
 *     schema="CommendTarjeta",
 *     type="object",
 *     title="CommendTarjeta",
 *     description="Modelo de CommendTarjeta",
 *     @OA\Property(property="id_commend_tarjeta", type="integer", example=1),
 *     @OA\Property(property="titulo", type="string", maxLength=255, nullable=true, example="Título de ejemplo"),
 *     @OA\Property(property="texto1", type="string", maxLength=255, nullable=true, example="Texto 1"),
 *     @OA\Property(property="texto2", type="string", maxLength=255, nullable=true, example="Texto 2"),
 *     @OA\Property(property="texto3", type="string", maxLength=255, nullable=true, example="Texto 3"),
 *     @OA\Property(property="texto4", type="string", maxLength=255, nullable=true, example="Texto 4"),
 *     @OA\Property(property="texto5", type="string", maxLength=255, nullable=true, example="Texto 5")
 * )
 */

class CommendTarjeta extends Model
{

    
    use HasFactory;

    protected $table = 'commend_tarjetas';
    protected $primaryKey = 'id_commend_tarjeta';
    public $timestamps = false;

    protected $fillable = [
        'titulo',
        'texto1',
        'texto2',
        'texto3',
        'texto4',
        'texto5',
    ];

    public function blog_body(){
        return $this->belongsTo(BlogBody::class, 'id_commend_tarjeta', 'id_commend_tarjeta');
    }
}
