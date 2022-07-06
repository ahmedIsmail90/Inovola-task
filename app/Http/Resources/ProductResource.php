<?php

namespace App\Http\Resources;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use JsonSerializable;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array|Arrayable|JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'sku' => $this->sku,
            'price'=>$this->price,
            'translation' => array(
                'en'=>array(
                    'name'=>$this->translate('en')->name,
                    'description'=> $this->translate('en')->description
                ),
                'ar'=>array(
                    'name'=>$this->translate('en')->name,
                    'description'=> $this->translate('en')->description
                ),
            ),
        ];
    }
}
