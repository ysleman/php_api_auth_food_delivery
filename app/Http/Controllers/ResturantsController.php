<?php

namespace App\Http\Controllers;

use App\Models\ingredients;
use App\Models\item_ingredients;
use App\Models\menu_items;
use Illuminate\Http\Request;
use App\Models\resturants;
use App\Models\order_items;
use App\Models\resturant_orders;

class ResturantsController extends Controller
{
    public function index()
    {
        $resturants = resturants::all();
        for($i=0;$i<count($resturants);$i++)
            unset($resturants[$i]["order_id"]);
        return response()->json($resturants);
    }
    public function order_list(){
        $resturant_id=auth($guard='resturants')->user()['id'];
        //multi orders
        $order_list_unordered=order_items::all();
        $menu_list_unordered=menu_items::all();
        $orders_list=array();
        for($i=0;$i<count($order_list_unordered);$i++){
            if($order_list_unordered[$i]['resturant_id']==$resturant_id)
               array_push($orders_list,$order_list_unordered[$i]);
        }
        for($i=0;$i<count($orders_list);$i++)
            for($y=0;$y<count($menu_list_unordered);$y++)
                if($orders_list[$i]["item_id"]==$menu_list_unordered[$y]["id"] && $orders_list[$i]["resturant_id"]==$menu_list_unordered[$y]["resturant_id"]){
                    unset($menu_list_unordered[$i]["quantity"]);
                    unset($menu_list_unordered[$i]["resturant_id"]);
                    $orders_list[$i]["item_id"]=$menu_list_unordered[$y];
                }
            
        for($i=0;$i<count($orders_list);$i++)
            unset($orders_list[$i]["resturant_id"]);


        return response()->json($orders_list);
    }
    public function removeorder(Request $request){
        $resturant_id=auth($guard='resturant')->user()['id'];
        $order_id=$request->order_id;
        try{
            $order = resturant_orders::where('order_id','=', $order_id)
                                    ->where('resturant_id', '=',$resturant_id)
                                    ->first();
    
            if ($order) {
                $order->delete();
            }

        }catch(\Exception $e){
            return response()->json(['status'=>'error','message'=>$e->getMessage()]);
        }
        return response()->json(['status'=>'success','message'=>'order removed']);
    }
    public function menu_items_add(Request $request)
    {
        $resturant_id=auth($guard='resturants')->user()['id'];
        $menu_item=new menu_items();
        $menu_item->resturant_id=$resturant_id;
        $menu_item->name=$request->name;
        $menu_item->description=$request->description;
        $menu_item->price=$request->price;
        $menu_item->img=$request->img;
        $menu_item->quantity=$request->quantity;
        if($menu_item->save()){
            $menu_item_id=$menu_item['id'];
            $request->request->add(['menu_item_id' => $menu_item_id]); //add request
            return $this->menu_items_ingredients_add($request);
        }else{
            return response()->json(['status'=>'error','message'=>'error']);
        }
        
    }

    public function menu_items_edit(Request $request)
    {
        $resturant_id=auth($guard='resturants')->user()['id'];
        $menu_item=menu_items::where('id',$request->menu_item_id)->first();
        if($menu_item){
            
        }
        return response()->json(['status' => 'success', 'message' => 'Menu item edited successfully']);
    }

    public function menu_items_remove(Request $request)
    {
        // Your logic to remove menu items
        // Example:
        // DB::table('menu_items')->where('id', $request->id)->delete();
        return response()->json(['status' => 'success', 'message' => 'Menu item removed successfully']);
    }

    public function menu_items_ingredients_add(Request $request)
    {

        $howmany=$request->howmany;
        $i=0;
        $name_x="name".$i;
        $price_x="price".$i;
            $ingredient=new ingredients();
            $ingredient->name=$request->$name_x;
            $ingredient->price=$request->$price_x;
            if($ingredient->save()){
                $ingredient_id=$ingredient['id'];
                $item_ingredients=new item_ingredients();
                $item_ingredients->IngredientID=$ingredient_id;
                $item_ingredients->itemid=$request->menu_item_id;
                $item_ingredients->save();
            }

            if($howmany>1)
                for($i=1;$i<$howmany;$i++){
                    $name_x="name".$i;
                    $price_x="price".$i;
                    $ingredient=new ingredients();
                    $ingredient->name=$request->$name_x;
                    $ingredient->price=$request->$price_x;
                    if($ingredient->save()){
                        $ingredient_id=$ingredient['id'];
                        $item_ingredients=new item_ingredients();
                        $item_ingredients->IngredientID=$ingredient_id;
                        $item_ingredients->itemid=$request->menu_item_id;
                        $item_ingredients->save();
                    }
                }
        
        // Your logic to add menu item ingredients
        // Example:
        // DB::table('menu_item_ingredients')->insert(['menu_item_id' => $request->menu_item_id, ...]);
        return response()->json(['status' => 'success', 'message' => 'Menu item ingredient added successfully']);
    }

    public function menu_items_ingredients_edit(Request $request)
    {
        // Your logic to edit menu item ingredients
        // Example:
        // DB::table('menu_item_ingredients')->where('id', $request->id)->update(['name' => $request->name, ...]);
        return response()->json(['status' => 'success', 'message' => 'Menu item ingredient edited successfully']);
    }

    public function menu_items_ingredients_remove(Request $request)
    {
        // Your logic to remove menu item ingredients
        // Example:
        // DB::table('menu_item_ingredients')->where('id', $request->id)->delete();
        return response()->json(['status' => 'success', 'message' => 'Menu item ingredient removed successfully']);
    }

  
}


   

    