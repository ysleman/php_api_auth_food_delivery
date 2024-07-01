<?php
namespace App\Http\Controllers;

use App\Models\deliverydriver_orders;
use App\Models\favorites;
use App\Models\item_ingredients;
use App\Models\menu_items;
use App\Models\order_items;
use App\Models\User;
use App\Models\temp_orders;
use App\Models\orders;
use App\Models\resturant_orders;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function userdetails()
    {
        return response()->json(['status'=>'success','message'=>auth($guard='api')->user()]);
    }
    public function addorder(Request $request){
        $user_id=auth($guard='api')->user()['id'];
        $order=new orders();
        $order->orderDate=date('Y/m/d');
        $order->user_id=$user_id;
        $order->driver_id=$request->driver_id;
        $order->totalPrice=$request->totalprice;
        if($order->save()){
            //ask if menu_item and item have that item
            $howmany=$request->howmany;
            for($i=1;$i<$howmany;$i++){
                $item_id_x="item_id".$i;
                $quantity_x="quantity".$i;
                $resturant_id_x="resturant_id".$i;
                try{
                    if(item_ingredients::where('itemid','=',$request->$item_id_x)->exists() && menu_items::find($request->$resturant_id_x)->exists())
                        continue;
                }catch (\Throwable $e) {
                    return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
                }
            }
            $i=0;
            $item_id_x="item_id".$i;
            $quantity_x="quantity".$i;
            $resturant_id_x="resturant_id".$i;
            $order_item=new order_items();
            if($request->driver_id!=0){
                $delivery_driver_order=new deliverydriver_orders();
                $delivery_driver_order->order_id=$order['id'];
                $delivery_driver_order->driver_id=$request->driver_id;
                $delivery_driver_order->delivered=0;
                $delivery_driver_order->save();
            }
            $resturant_order=new resturant_orders();
            $resturant_order->order_id=$order['id'];
            $resturant_order->resturant_id=$request->$resturant_id_x;
            $resturant_order->finished=0;
            $resturant_order->save();
            $order_item->order_id=$order['id'];
            $order_item->item_id=$request->$item_id_x;
            $order_item->quanity=$request->$quantity_x;
            $order_item->resturant_id=$request->$resturant_id_x;
            $order_item->save();
            for($i=1;$i<$howmany;$i++){
                $order_item=new order_items();
                $order_item->order_id=$order['id'];
                $item_id_x="item_id".$i;
                $quantity_x="quantity".$i;
                $resturant_id_x="resturant_id".$i;
                $order_item->item_id=$request->$item_id_x;
                //ask if menu_item have that item
                $order_item->quanity=$request->$quantity_x;
                $order_item->resturant_id=$request->$resturant_id_x;
                $order_item->save();
                $resturant_order=new resturant_orders();
                $resturant_order->order_id=$order['id'];
                $resturant_order->resturant_id=$request->$resturant_id_x;
                $resturant_order->finished=0;
                $resturant_order->save();
            }
            $temp_orders=temp_orders::where('user_id',$user_id)::get()->delete();
            
        }
        return response()->json(['status'=>'success','message'=>'success']);
    }

    public function orders_list()
    {
        $user_id=auth($guard='api')->user()['id'];
        $orders_list_unfiltered=orders::all();
        $orders_list=array();
        for($i=0;$i<count($orders_list_unfiltered);$i++){
            if($orders_list_unfiltered[$i]['user_id']==$user_id)array_push($orders_list,$orders_list_unfiltered[$i]);
        }
        return response()->json(['status'=>'success','message'=>$orders_list]);
    }


    public function temp_order_add(Request $request){
        $user_id=auth($guard='api')->user()['id'];
        $howmany=$request->howmany;
        for($i=1;$i<$howmany;$i++){
            $item_id_x="item_id".$i;
            try{
                if(item_ingredients::where('itemid','=',$request->$item_id_x)->exists())
                    continue;
            }catch (\Throwable $e) {
                return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
            }
        }
        $i=0;
        $order=new temp_orders();
        $order->user_id=$user_id;
        $i=0;
        $item_id_x="item_id".$i;
        $quantity_x="quantity".$i;
        $order->item_id=$request->$item_id_x;
        $order->quantity=$request->$quantity_x;
        $order->save();
        for($i=1;$i<$howmany;$i++){
            $order=new temp_orders();
            $order->user_id=$user_id;
            $item_id_x="item_id".$i;
            $quantity_x="quantity".$i;
            $order->item_id=$request->$item_id_x;
            $order->quantity=$request->$quantity_x;
            $order->save();
        }
        return response()->json(['status'=>'success','message'=>'success']);
    }
    public function temp_order()
    {
        $user_id=auth($guard='api')->user()['id'];
        $temp_orders = temp_orders::where('user_id','=',$user_id);
        return response()->json($temp_orders);
    }
    public function updateProfile(Request $request){
        $user_id=auth($guard='api')->user()['id'];
        $user=User::find($user_id);
        $user->username = $request->username;
        $user->email = $request->email;
        $user->firstname=$request->firstname;
        $user->lastname=$request->lastname;
        $user->phone=$request->phone;
        $user->birthDate=$request->birthdate;
        $user->img=$request->img;
        if($user->save())
        return response()->json(['status'=>'success','message'=>'success']);
        else 
        return response()->json(['status'=>'error','message'=>'error']);
    }


    public function favoriteremove(Request $request){
        $user_id=auth($guard='api')->user()['id'];
        $fav=favorites::find($user_id)
            ->where('resturant_id',$request->resturant_id);
        if($fav!=NULL){
            $fav->first()->delete();
            return response()->json(['status'=>'success','message'=>'success']);
        }
        else 
        return response()->json(['status'=>'error','message'=>'error']);
    }
    public function favoriteAdd(Request $request){
        $user_id=auth($guard='api')->user()['id'];
        $fav=new favorites();
        $fav->user_id = $user_id;
        $fav->resturant_id = $request->resturant_id;     
        if($fav->save())
            return response()->json(['status'=>'success','message'=>'success']);
        else 
            return response()->json(['status'=>'error','message'=>'error']);
    }
    public function favorite(){
        $user_id=auth($guard='api')->user()['id'];
        $favorites=favorites::find($user_id);
        if ($favorites==NULL) {
            // Handle the case where no favorites are found
            // $favorites will be an empty collection
            return response()->json(['status'=>'error','message'=>'error']);
        } else {
            return response()->json(['status'=>'success','message'=>$favorites->get()]);
        }
    }

}