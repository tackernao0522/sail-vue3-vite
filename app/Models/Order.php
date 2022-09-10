<?php

namespace App\Models;

use App\Models\Scopes\Subtotal;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected static function booted()
    {
        static::addGlobalScope(new Subtotal);
    }

    public function scopeBetweenDate($query, $startDate = null, $endDate = null)
    {
        if (is_null($startDate) && is_null($endDate)) {
            return $query;
        }

        if (!is_null($startDate) && is_null($endDate)) {
            return $query->where('created_at', ">=", $startDate);
        }

        if (is_null($startDate) && !is_null($endDate)) {
            return $query->where('created_at', '<=', $endDate);
        }

        if (!is_null($startDate) && !is_null($endDate)) {
            return $query->where('created_at', '>=', $startDate)
                ->where('created_at', '<=', $endDate);
        }
    }
}
