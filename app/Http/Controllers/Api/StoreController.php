<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Store;
use Illuminate\Http\Request;
use App\Http\Resources\StoreResource;
use Validator;
use App\Http\Traits\ApiHelpers;

class StoreController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        $user = auth('sanctum')->user();
        $stores = $user->stores;

        return $this->sendResponse(StoreResource::collection($stores), 'stores retrieved successfully.');
    }



    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $user = auth('sanctum')->user();

        $input = $request->all();


        $validator = Validator::make($input, [
            'name' => 'required|unique:stores',
            'is_vat_included'=> 'nullable|boolean',
            'vat_percentage'=> 'required_if:is_vat_included,true',
            'shipping_cost'=> 'nullable|numeric',

        ]);

        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors(),404);
        }


        $store = $user->orders()->create(['quantity'=>$input['quantity'] , '']);


        return $this->sendResponse(new StoreResource($store), 'Store created successfully.');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Store  $store
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $user = auth('sanctum')->user();
        $store = Store::where('id',$id)->where('merchant_id',$user->id)->first();
        if (is_null($store)) {
            return $this->sendError('Store not found.');
        }

        return $this->sendResponse(new StoreResource($store), 'Store retrieved successfully.');
    }



    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Store  $store
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request,$id)
    {
        $user = auth('sanctum')->user();
        $store = Store::where('id',$id)->where('merchant_id',$user->id)->first();
        if (is_null($store)) {
            return $this->sendError('Store not found.');
        }
        $input = $request->all();

        $validator = Validator::make($input, [
            'name' => 'unique:stores',
            'is_vat_included'=> 'nullable|boolean',
            'vat_percentage'=> 'required_if:is_vat_included,true',
            'shipping_cost'=> 'nullable|numeric',
        ]);

        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors(),404);
        }

        $store->name = $input['name']??$store->name;
        $store->is_vat_included = $input['is_vat_included']??$store->is_vat_included;
        $store->vat_percentage = $input['vat_percentage']??$store->vat_percentage;
        $store->shipping_cost = $input['shipping_cost']??$store->shipping_cost;
        $store->save();

        return $this->sendResponse(new StoreResource($store), 'Store updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Store  $store
     * @return \Illuminate\Http\Response
     */
    public function destroy(Store $store)
    {
        $store->delete();

        return $this->sendResponse([], 'Product deleted successfully.');
    }
}