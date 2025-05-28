<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
/**
 * @OA\Schema(
 *     schema="Productos",
 *     type="object",
 *     title="Producto",
 *     required={"nombre", "descripcion"},
 *     @OA\Property(
 *         property="id_producto",
 *         type="integer",
 *         description="ID único del producto",
 *         readOnly=true
 *     ),
 *     @OA\Property(
 *         property="nombre",
 *         type="string",
 *         description="Nombre del producto"
 *     ),
 *     @OA\Property(
 *         property="descripcion",
 *         type="string",
 *         description="Descripción del producto"
 *     )
 * )
 */
class Productos extends Model
{
    protected $table = 'productos';

    protected $primaryKey = 'id_producto';

    protected $fillable = [
        'nombre',
        'descripcion'
    ];

    public $timestamps = false;
}
