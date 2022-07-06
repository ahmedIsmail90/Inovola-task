<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Astrotomic\Translatable\Translatable;






class Product extends Model
{
    use HasFactory;
    use Translatable;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'price','sku'
    ];
    public $translatedAttributes = ['name', 'description'];

    public function store()
    {
        return $this->hasOne(Store::class);
    }
}
