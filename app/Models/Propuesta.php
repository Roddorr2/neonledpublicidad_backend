<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Propuesta extends Model
{
  protected $fillable = [
    'id_cliente','titulo','descripcion1','descripcion2','descripcion3','descripcion4','descripcion5','descripcion6','descripcion7','descripcion8','descripcion9','descripcion10',
  ];
 
  public function cliente() :BelongsTo
  {
    return $this->belongsTo(Cliente::class, 'id_cliente', 'id');
  }
  
}
