<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReturnMessage extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'is_admin' => 'boolean',
    ];

    public function returnRequest()
    {
        return $this->belongsTo(ReturnRequest::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
