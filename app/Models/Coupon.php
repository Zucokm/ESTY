<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['code', 'type', 'value', 'valid_until', 'usage_limit', 'times_used', 'is_active'])]
class Coupon extends Model
{
    use SoftDeletes;

    protected function casts(): array
    {
        return [
            'valid_until' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function isValid(): bool
    {
        if (!$this->is_active) return false;
        if ($this->usage_limit !== null && $this->times_used >= $this->usage_limit) return false;
        if ($this->valid_until !== null && $this->valid_until->isPast()) return false;
        return true;
    }
}
