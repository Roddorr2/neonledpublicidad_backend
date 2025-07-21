<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Propuesta extends Model
{
  protected $fillable = [
    'id_cliente','nombre','descripcion'
  ];
 
  public function cliente() :BelongsTo
  {
    return $this->belongsTo(Cliente::class, 'id_cliente', 'id');
  }
  
}
