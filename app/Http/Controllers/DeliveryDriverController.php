<?php

namespace App\Http\Controllers;

use App\Models\delivery_drivers;
use App\Models\deliverydriver_orders;
use App\Models\orders;
use Illuminate\Http\Request;

class DeliveryDriverController extends Controller
{
    public function orders_list()
    {
        $driver_id=auth($guard='delivery_drivers')->user()['id'];
        $orders_list=deliverydriver_orders::where('driver_id',$driver_id)->get();        
        return response()->json(['status'=>'success','message'=>$orders_list]);
    }
    public function index(){
        return response()->json(['status'=>'success','message'=>auth($guard='delivery_drivers')->user()]);
    }
    public function orders_id(Request $request){
        $idk=deliverydriver_orders::where('order_id',$request->id)->first();
        return response()->json(['status'=>'success','message'=>$idk]);
    }
    public function update_track(Request $request){
        $driver_id=auth($guard='delivery_drivers')->user()['id'];
        $order_id=$request->order_id;
        $long=$request->long;
        $lat=$request->lat;
        if(empty($lat) || empty($long)) return response()->json(['status'=>'error','message'=>'you must enter long and lat']);
        $deliverydriver_order=deliverydriver_orders::where('driver_id','=', $driver_id)->where('order_id',$order_id)->first();
        $deliverydriver_order->long=$long;
        $deliverydriver_order->lat=$lat;
        $deliverydriver_order->save();
        return response()->json(['status'=>'success','message'=>'success']);
    }
    public function tracking(Request $request){
        $order_id=$request->order_id;
        $dr=deliverydriver_orders::where('driver_id',auth($guard='delivery_drivers')->user()['id'])->where('order_id',$order_id)->first();
        if($dr['lat'] == NULL || $dr['long'] == NULL) return response()->json(['status'=>'success','messages'=>'havent_arrived']);
        return response()->json(['status'=>'success','message'=>$dr]);
    }
    public function removeorder(Request $request){
        $driver_id=auth($guard='delivery_drivers')->user()['id'];
        $order_id=$request->order_id;
        try{
            $order = deliverydriver_orders::where('order_id','=', $order_id)
                                    ->where('driver_id', '=',$driver_id)
                                    ->first();
    
            if ($order) {
                $order->delete();
            }

        }catch(\Exception $e){
            return response()->json(['status'=>'error','message'=>$e->getMessage()]);
        }
        return response()->json(['status'=>'success','message'=>'order removed']);
    }
    public function check_auth(){
        return response()->json(['status'=>'success','message'=>'You are authenticated']);
    }
    public function edit_order(Request $request){
        $order_id=$request->order_id;
        $order=deliverydriver_orders::where('order_id',$order_id)->first();
        $order->delivered=$request->delivered;
        $order->save();
        return response()->json(['status'=>'success','message'=>'success']);
    }
   
}
