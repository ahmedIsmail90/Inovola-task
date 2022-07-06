<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;


class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
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
