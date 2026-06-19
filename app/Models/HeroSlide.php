<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HeroSlide extends Model
{
    protected $fillable = [
        'badge',
        'title',
        'highlight',
        'description',
        'cta_text',
        'cta_link',
        'image_path',
        'sort_order',
        'is_active',
    ];
}
