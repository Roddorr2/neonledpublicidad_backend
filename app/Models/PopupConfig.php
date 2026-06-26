<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class PopupConfig extends Model
{
    protected $table = 'popup_configs';
    protected $primaryKey = 'id_popup_config';
    public $incrementing = true;

    protected $fillable = [
        'id_producto',

        // ── Compartidos (texto) ─────────────────────────────────────────────
        'title_text',
        'button_text',

        // ── Desktop ─────────────────────────────────────────────────────────
        'title_color',
        'button_color',
        'service_color',
        'service_color_2',
        'gradient_direction',
        'trigger_time',
        'left_image_url',
        'left_image_public_id',
        'left_opacity',
        'left_alt',
        'right_image_url',
        'right_image_public_id',
        'right_opacity',
        'right_alt',

        // ── Mobile ──────────────────────────────────────────────────────────
        'mobile_trigger_time',
        'mobile_title_color',
        'mobile_button_color',
        'mobile_service_color',
        'mobile_service_color_2',
        'mobile_gradient_direction',
        'mobile_image_url',
        'mobile_image_public_id',
        'mobile_opacity',
        'mobile_alt',

        'created_by',
        'updated_by',
    ];

    protected $casts = [
        // Desktop
        'trigger_time'   => 'integer',
        'left_opacity'   => 'integer',
        'right_opacity'  => 'integer',
        // Mobile
        'mobile_trigger_time' => 'integer',
        'mobile_opacity'      => 'integer',
    ];

    // Ocultar public_ids de Cloudinary en respuestas públicas
    protected $hidden = [
        'left_image_public_id',
        'right_image_public_id',
        'mobile_image_public_id',
    ];

    // ── Relaciones ───────────────────────────────────────────────────────────

    public function producto()
    {
        return $this->belongsTo(Productos::class, 'id_producto', 'id_producto');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by', 'id');
    }

    // ── Scopes ───────────────────────────────────────────────────────────────

    public function scopeByProducto(Builder $query, int $idProducto)
    {
        return $query->where('id_producto', $idProducto);
    }
}