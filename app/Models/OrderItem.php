<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;
    protected $fillable =
        ['total','vat_total','vat_percentage',
            'sub_total','price','quantity','sku','product_id','order_id','store_id'
        ];

    public function order()
    {
        return $this->hasOne(Order::class);
    }

    public function store()
    {
        return $this->hasOne(Store::class);
    }

    public function product()
    {
        return $this->hasOne(Product::class);
    }
}
