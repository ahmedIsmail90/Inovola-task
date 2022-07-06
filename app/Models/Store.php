<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Store extends Model
{
    use HasFactory;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'detail','is_vat_included',
        'shipping_cost', 'vat_percentage'
    ];
    public function merchant()
    {
        return $this->hasOne(User::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class, 'store_id');
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'store_id');
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class, 'store_id');
    }
}
