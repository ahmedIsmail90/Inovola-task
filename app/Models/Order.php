<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'store_name','total','vat_total',
'shipping_cost','vat_percentage','sub_total','status','state','customer_id'
    ];


    public function orderItems()
    {
        return $this->hasMany(OrderItem::class,'order_id');
    }
}
