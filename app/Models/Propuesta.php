<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Propuesta extends Model
{
  protected $fillable = [
    'id_user','titulo','descripcion1','descripcion2','descripcion3','descripcion4','descripcion5','descripcion6','descripcion7','descripcion8','descripcion9','descripcion10',
  ];
 public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id');
    }
  
}
