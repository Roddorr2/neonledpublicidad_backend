<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PopupConfig extends Model
{
    protected $table = 'popup_configs';

    protected $primaryKey = 'id_popup_config';

    protected $fillable = [
        'id_producto',
        'title_text',
        'title_color',
        'button_text',
        'button_color',
        'service_color',
        'service_color_2',
        'gradient_direction',
        'trigger_time',
        'left_image_url',
        'left_public_id',
        'left_opacity',
        'left_alt',
        'right_image_url',
        'right_public_id',
        'right_opacity',
        'right_alt',
        'mobile_image_url',
        'mobile_public_id',
        'mobile_opacity',
        'mobile_alt',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'trigger_time' => 'integer',
        'left_opacity' => 'integer',
        'right_opacity' => 'integer',
        'mobile_opacity' => 'integer',
    ];

    public function producto(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Productos::class, 'id_producto', 'id_producto');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function scopeByProducto($query, $id)
    {
        return $query->where('id_producto', $id);
    }
}