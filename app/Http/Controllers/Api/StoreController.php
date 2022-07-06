<?php

namespace App\Http\Controllers\Api;

use App\Models\Store;
use Illuminate\Http\Request;
use App\Http\Resources\StoreResource;
use Illuminate\Http\Response;
use Validator;

class StoreController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
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
     * @param Request $request
     * @return Response
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

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors(), 404);
        }

        $store = $user->stores()->create($input);
        return $this->sendResponse(new StoreResource($store), 'Store created successfully.');
    }

    /**
     * Display the specified resource.
     *
     * @param Store $store
     * @return Response
     */
    public function show($id)
    {
        $user = auth('sanctum')->user();
        $store = Store::where('id', $id)->where('merchant_id', $user->id)->first();
        if (is_null($store)) {
            return $this->sendError('Store not found.');
        }

        return $this->sendResponse(new StoreResource($store), 'Store retrieved successfully.');
    }



    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param Store $store
     * @return Response
     */
    public function update(Request $request, $id)
    {
        $user = auth('sanctum')->user();
        $store = Store::where('id', $id)->where('merchant_id', $user->id)->first();
        if (is_null($store)) {
            return $this->sendError('Store not found.');
        }
        $input = $request->all();

        $validator = Validator::make($input, [
            'name' => 'unique:stores,name,'.$store->id, //unique name ignoring itself
            'is_vat_included'=> 'nullable|boolean',
            'vat_percentage'=> 'required_if:is_vat_included,true',
            'shipping_cost'=> 'nullable|numeric',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors(), 404);
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
     * @param Store $store
     * @return Response
     */
    public function destroy(Store $store)
    {
        $store->delete();

        return $this->sendResponse([], 'Product deleted successfully.');
    }
}
