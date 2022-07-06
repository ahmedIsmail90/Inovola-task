<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderItemResource;
use App\Http\Resources\ProductResource;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Http\Request;
use Validator;
use Illuminate\Support\Facades\Config;

class CartController extends BaseController
{
    /**
     * add products to cart
     * @param    $storeId
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
//supposed that the request contains the valid quantity each time and of user delete the item ,the request contains the item with 0 quantity
    public function addItems($storeId,Request $request){

        $store = Store::find($storeId);
        $user = auth('sanctum')->user();
        //check if store exist
        if (is_null($store)) {
            return $this->sendError('Store not found.');
        }
        $input=$request->all();

        $validator = Validator::make($input, [
            'items'=>'required|array',
            'items.*.sku'=>'required|string',
            'items.*.quantity'=>'required|integer',
        ]);
        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors(),400);
        }
        //get order status array from config
        $orderStatus = Config::get('enum.order_status');
        //check that customer has cart or not
        $cart = $this->checkMerchantCart($user);
        // if cart not found create it
        if(is_null($cart)) {
            $cart=  $store->orders()->create([
                'store_name'=>$store->name , 'customer_id'=> $user->id ,
                'vat_percentage'=>$store->vat_percentage , 'status'=>$orderStatus['CART'],'state'=>$orderStatus['CART'],
                'shipping_cost'=>$store->shipping_cost??0

                 ]);}

            //order attributes calculated from order item attributes
            $subtotalOrder = $totalOrder = $vatTotalOrder =$orderQuantity=0;
            foreach($input['items'] as $item){
                /*
                 * @App/Http/Models/Product
                 */
                $product = Product::where(['sku'=>$item['sku'] ])->first();
                //check that order item exist
                $orderItem = OrderItem::where('order_id',$cart->id)->where('product_id',$product->id)->first();
                if(is_null($orderItem)){ // if order item not found create it
                    $orderItem = $cart->orderItems()->create([
                        'price'=>$product->price , 'product_id'=> $product->id ,'store_id'=>$store->id,
                        'vat_percentage'=>$store->vat_percentage,'sku'=>$product->sku
                    ]);
                }
                //if quantity =0 it means that user delete item from his cart
                if($item['quantity']==0){
                    $orderItem->delete();
                    continue; //continue to next item
                }
                // calculate order item attributes and add it to order
                $quantity = $item['quantity'];
                $subtotal = $quantity*$orderItem->price;
                $total= $subtotal;
                $vatPercentage = null;
                $vatTotal = 0;
                if($store->is_vat_included){
                    $vatPercentage = $store->vat_percentage;
                    $vatTotal = ($subtotal * $vatPercentage)/100;
                    $total = $subtotal+$vatTotal;
                }
                $orderItem->sub_total = $subtotal;
                $orderItem->vat_percentage=$vatPercentage;
                $orderItem->vat_total=$vatTotal;
                $orderItem->total = $total;
                $orderItem->quantity = $quantity;
                $orderItem->save();
                $subtotalOrder+= $subtotal;
                $totalOrder += $total;
                $vatTotalOrder+= $vatTotal;
                $orderQuantity+=$quantity;


            }

            // add order attributes
            $cart->total= $totalOrder+$cart->shipping_cost;
            $cart->sub_total= $subtotalOrder;
            $cart->vat_total= $vatTotalOrder;
            $cart->save();

            $result=[
                'cart_id'=>$cart->id,
                'cart_sub_total'=>$cart->sub_total,
                'vat_total' =>$cart->vat_total??0,
                 'shipping_cost'=>$cart->shipping_cost,
                'cart_quantity' => $orderQuantity,
                'cart_total'   => round($cart->total,2)
            ];



        //dd($products);
        return $this->sendResponse($result, 'added to cart');
    }


    public function checkMerchantCart($user){
        //check cart exist for the user
        $cart= Order::where('state','cart')->where('customer_id',$user->id)->first();
        return $cart;

    }

    /**
     * add products to cart
     * @param    $cartId
     * @return \Illuminate\Http\Response
     */

    public function show($cartId){
        $user = auth('sanctum')->user();
        $cart = Order::where('id',$cartId)->where('state','cart')->where('customer_id',$user->id)->first();
        //check if store exist
        if (is_null($cart)) {
            return $this->sendError('cart not found.');
        }

        $result['cart'] = $cart;
        $items=[];
        foreach ($cart->orderItems()->get()->toArray() as $item){
            $product = Product::find($item['product_id']);
            $item['name']['en']=$product->translate('en')->name;
            $item['name']['ar']=$product->translate('ar')->name;
            $items[]=$item;
        }
        $result['cart_items']=$items;
        return $this->sendResponse($result,'cart details');
    }




}
