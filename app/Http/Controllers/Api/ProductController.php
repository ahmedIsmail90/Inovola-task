<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Http\Request;
use Validator;
use Illuminate\Validation\Rule;

class ProductController extends BaseController
{


    /**
     * get store products paginated
     * @param    $storeId
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

    public function index($storeId,Request $request){
        $page = $request->get('page') ?? 1;
        $limit =$request->get('limit') ?? 5;
        $store = Store::find($storeId);
        if (is_null($store)) {
            return $this->sendError('Store not found.');
        }
        $products = $store->products()->paginate($limit, ['*'], 'page', $page);
        return $this->sendResponse($products->toArray(), 'List of products of store'.$store->name);
    }
    /**
     * Store a newly created store in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param    $storeId
     * @return \Illuminate\Http\Response
     */
    /*
     * @todo make user create bulk of products
     */
    public function store(Request $request,$storeId)
    {

        $store = Store::find($storeId);
        if (is_null($store)) {
            return $this->sendError('Store not found.');
        }
        $input = $request->all();
        $validator = Validator::make($input, [
            'price'    =>'numeric|required',
            'sku'      =>Rule::unique('products')->where(function ($query) use ($store) {
                    return $query->where('store_id', $store->id);
                }), // sku is unique per store
            'translations.en.name' => 'string|required',
            'translations.en.description'=> 'string|required',
            'translations.ar.name' => 'string|required',
            'translations.ar.description'=> 'string|required',


        ]);

        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors(),400);
        }

        $product = $store->products()->create($input);

        foreach ($input['translations'] as $key=>$value){
            $translation = $product->getTranslationOrNew($key);
            $translation->name = $value['name'];
            $translation->description = $value['description'];

        }

        $product->save();


        return $this->sendResponse(new ProductResource($product), 'Product created successfully.');
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param    $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request,$id)
    {
        $product =Product::find($id);
        if (is_null($product)) {
            return $this->sendError('Product not found.');
        }
        $input = $request->all();
        $validator = Validator::make($input, [
            'price'    =>'numeric',

            'sku'      =>Rule::unique('products')->where(function ($query) use ($product,$id) {
                return $query->where('store_id', $product->store_id)->where('id','!=',$id);
            }), // sku is unique per store
            'translations.en.name' => 'string',
            'translations.en.description'=> 'string',
            'translations.ar.name' => 'string',
            'translations.ar.description'=> 'string',

        ]);

        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors(),400);
        }

        $product->price = $input['price']??$product->price;
        $product->sku = $input['sku'] ?? $product->sku;
        if(isset($input['translations'])){
            foreach ($input['translations'] as $key=>$value){
                $translation = $product->getTranslationOrNew($key);
                $translation->name = $value['name'];
                $translation->description = $value['description'];

            }
        }

        $product->save();

        return $this->sendResponse(new ProductResource($product), 'Product updated successfully.');
    }


}
