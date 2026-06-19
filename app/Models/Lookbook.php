<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lookbook extends Model
{
    protected $fillable = [
        'badge',
        'title',
        'description',
        'cta_text',
        'cta_link',
        'image_path',
        'sort_order',
        'is_active',
    ];
}
