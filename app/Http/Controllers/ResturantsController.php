<?php

namespace App\Http\Controllers;

use App\Models\ingredients;
use App\Models\item_ingredients;
use App\Models\menu_items;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\resturants;
use App\Models\order_items;
use App\Models\orders;
use App\Models\resturant_orders;
use Illuminate\Support\Facades\DB;
use function PHPUnit\Framework\isEmpty;

class ResturantsController extends Controller
{

    public function index()
    {
        $resturants = resturants::all();
        for($i=0;$i<count($resturants);$i++)
            unset($resturants[$i]["order_id"]);
        return response()->json($resturants);
    }
    public function res_id(Request $request){
        $id=$request->id;
        $resturants = resturants::find($id)->first();
        return response()->json($resturants);
    }
    public function order_id(Request $request){
        $resturant_id=auth($guard='resturants')->user()['id'];
        $order_id=$request->id;
        $menu_list_unordered=menu_items::where('resturant_id',$resturant_id)->get();
        $restaurant_order=resturant_orders::where('order_id',$order_id)->first();
        $orders_list=order_items::where('order_id',$order_id)->get();
        $pricex=orders::find($order_id)->first();
        $x=0;
        for($y=0;$y<count($orders_list);$y++){
            for($l=0;$l<$menu_list_unordered->count();$l++){
                if($orders_list[$y]['item_id']==$menu_list_unordered[$l]['id']){
                    unset($menu_list_unordered[$l]["quantity"]);
                    unset($menu_list_unordered[$l]["resturant_id"]);
                    unset($orders_list[$y]["resturant_id"]);
                    unset($orders_list[$y]["id"]);
                    $restaurant_order["item".$x]=$menu_list_unordered[$l];
                    $x++;   
                }
            }
        }
        $restaurant_order["price"]=$pricex["totalPrice"];
        return response()->json(["res"=>$restaurant_order]);
    }
    public function editorder(Request $request){
        $order_id=$request->id;
        $finished=$request->finished;
        $price=$request->price;
        $resturant_id=auth($guard='resturants')->user()['id'];
        //can only edit finished , price for now later maybe do if he can remove item from order or not 
        $restaurant_order=resturant_orders::where('order_id',$order_id)->first();
        $pricex=orders::find($order_id)->first();
        if($restaurant_order && $pricex){
            $restaurant_order["finished"]=$finished;
            $pricex["totalPrice"]=$price;
            $pricex->save();
            $restaurant_order->save();
        }
        return response()->json(["status"=>"success"]);
    }
    public function order_list(){
        $resturant_id=auth($guard='resturants')->user()['id'];
        //multi orders
        $menu_list_unordered=menu_items::where('resturant_id',$resturant_id)->get();
        $current_restaurant_orders_unordered=resturant_orders::where('resturant_id',$resturant_id)->get();
        $orders_list=array();
        for($i=0;$i<count($current_restaurant_orders_unordered);$i++){
            array_push($orders_list,order_items::where('order_id',$current_restaurant_orders_unordered[$i]['order_id'])->get());
        }
        for($i=0;$i<count($orders_list);$i++)
            for($y=0;$y<count($orders_list[$i]);$y++){
                $x=0;
                for($l=0;$l<$menu_list_unordered->count();$l++){
                    if($orders_list[$i][$y]['item_id']==$menu_list_unordered[$l]['id']){
                        unset($menu_list_unordered[$l]["quantity"]);
                        unset($menu_list_unordered[$l]["resturant_id"]);
                        unset($orders_list[$i][$y]["item_id"]);
                        unset($orders_list[$i][$y]["resturant_id"]);
                        unset($orders_list[$i][$y]["id"]);
                        $orders_list[$i][$y]["item".$x]=$menu_list_unordered[$l];
                        $orders_list[$i][$y]["finished"]=$current_restaurant_orders_unordered[$i]["finished"];
                        $x++;   
                    }
                }
            }
        return response()->json($orders_list);
    }
    public function removeorder(Request $request){
        $resturant_id=auth($guard='resturants')->user()['id'];
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
        $menu_item=menu_items::where('id',$request->menu_items_id)->first();
        $item_ingredients=item_ingredients::where('itemid',$request->menu_items_id)->get();
        $idk=array();
        if($menu_item){
            $menu_item->name=$request->mainname;
            $menu_item->price=$request->mainprice;
            $menu_item->description=$request->description;
            $menu_item->quantity=$request->quantity;
            $menu_item->save();
            $howmany=$request->howmany;
            $i=0;
            if (count($item_ingredients) === $howmany) {
                foreach ($item_ingredients as $index => $item) {
                    $ingredient = ingredients::find($item['IngredientID']);
                    
                    if ($ingredient) {
                        $name_x = "name" . $index;
                        $price_x = "price" . $index;
                        // Update ingredient data
                        $ingredient->name = $request->$name_x;
                        $ingredient->price = $request->$price_x;
                        $ingredient->save();
                        
                        // Store updated names in $idk
                        array_push($idk, $request->$name_x);
                    }
                }
            }
        }   
        return response()->json(['status' => 'success', "help"=>$idk,'message' => 'Menu item edited successfully']);
    }

    public function menu_items_remove(Request $request)
    {
        // Your logic to remove menu items
        // Example:
        // DB::table('menu_items')->where('id', $request->id)->delete();
        $menu_item=menu_items::where('id',$request->menu_item_id)->delete();
        $items=item_ingredients::where('itemid',$request->menu_item_id)->get();
        foreach($items as $item){
            $ing=ingredients::find($item['IngredientID'])->delete();
            $item->delete();
        }
        return response()->json(['status' => 'success', 'message' => 'Menu item removed successfully']);
    }
    public function resturant_menu_items(Request $request){
        $id=$request->id;
        $menu_items_unfiltered = menu_items::find($id)->first();
        $menu_items=array();
        array_push($menu_items,$menu_items_unfiltered);
        $item_ingds=item_ingredients::where('itemid',$id)->get();
        $ingds=ingredients::all();
        $menu_items[0]["howmany"]=count($item_ingds);
        // return response()->json(["message1"=>$menu_items_unfiltered,"me2"=>$item_ingds]);
        $count=1;
            for($i=0;$i<count($item_ingds);$i++){
                if($item_ingds[$i]["itemid"]==$id){
                    array_push($menu_items,$item_ingds[$i]);
                    $menu_items[$count]=$item_ingds[$i];
                    $ingd_id=$menu_items[$count]["IngredientID"];
                        for($x=0;$x<count($ingds);$x++)
                            if($ingds[$x]["id"]==$ingd_id)
                                $menu_items[$count]["IngredientID"]=$ingds[$x];
                } 
                $count++;
            }

        return $menu_items;
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
        
        return response()->json(['status' => 'success', 'message' => 'Menu item ingredient added successfully']);
    }

    public function menu_items_ingredients_edit(Request $request)
    {
        $id=$request->id;
        $ingredient=ingredients::where('id',$id)->first();
        if($ingredient){
            $ingredient->name=$request->name;
            $ingredient->price=$request->price;
            $ingredient->save();
        }

        //fix for ingeredients edit same as menu items edit
        return response()->json(['status' => 'success', 'message' => 'Menu item ingredient edited successfully']);
    }

    public function menu_items_ingredients_remove(Request $request)
    {
        // Your logic to remove menu item ingredients
        // Example:
        // DB::table('menu_item_ingredients')->where('id', $request->id)->delete();
        
        $id=$request->id;
        $ingredient=ingredients::where('id',$id)->delete();
        return response()->json(['status' => 'success', 'message' => 'Menu item ingredient removed successfully']);
    }

    public function sort_location(Request $request){
        $address=$request->address;
        $lat_sent=$request->lat;
        $lon_sent=$request->lon;
        
        $locationdetails= Http::get('https://geocode.maps.co/search?city='.$address.'&country=IL&api_key=6684fb0511fd4967575382teo9d69dc');
        if($locationdetails!=null || ($lat_sent!=null && $lon_sent!=null)){
           $lat='';
           $lon='';
            if($lat_sent!=null && $lon_sent!=null) {
                $lat=$lat_sent;
                $lon=$lon_sent;
            }else {
                $lat = $locationdetails[0]['lat'];
                $lon = $locationdetails[0]['lon'];
            }
            $url = "http://api.geonames.org/findNearbyPlaceNameJSON?lat=".$lat."&lng=".$lon."&style=full&maxRows=30&radius=5&cities=cities1000&username=xtoyx3";

            $closecities = Http::get($url);

            if ($closecities->successful()) {
                $restaurants=resturants::all();
                $cities = $closecities->json()['geonames'];

                function alternateNameMatchesAddress($city, $address) {
                    foreach ($city['alternateNames'] as $altName) {
                        if (strcasecmp($altName['name'], $address) === 0) {
                            return true;
                        }
                    }
                    return false;
                }
                
                function findCityByRestaurantAddress($cities, $restaurantAddress) {
                    foreach ($cities as $city) {
                        if (alternateNameMatchesAddress($city, $restaurantAddress)) {
                            return $city;
                        }
                    }
                    return null; // If no matching city is found
                }
                $sortedCities=array();
                $notmatchedresturants=array();
                foreach ($restaurants as $restaurant) {
                    $matchingCity = findCityByRestaurantAddress($cities, $restaurant['address']);
                    if ($matchingCity) {
                       array_push($sortedCities,$restaurant);
                    } else {
                        array_push($notmatchedresturants,$restaurant);
                    }
                } 
                function findClosestCityByDistance($cities, $restaurant) {
                    $closestCity = null;
                    $minDistance = PHP_INT_MAX;
                
                    foreach ($cities as $city) {
                        $distance = (float)$city['distance'];
                        if ($distance < $minDistance) {
                            $closestCity = $city;
                            $minDistance = $distance;
                        }
                    }
                
                    return $closestCity;
                }
                
                function sortRestaurantsByDistance($cities, $restaurants) {
                    $sortedByDistance = [];
                
                    foreach ($restaurants as $restaurant) {
                        $closestCity = findClosestCityByDistance($cities, $restaurant);
                        if ($closestCity) {
                            $sortedByDistance[] = $restaurant;
                        }
                    }
                
                    return $sortedByDistance;
                }
            
                $sortedByDistance = sortRestaurantsByDistance($cities, $notmatchedresturants);
                
                $finalSortedRestaurants = array_merge($sortedCities, $sortedByDistance);

                            
                
                
            // Prepare the response
            return $finalSortedRestaurants;

            } else {
                return response()->json(['status' => 'error', 'message' => 'Failed to fetch nearby cities', 'lat' => $lat, 'lon' => $lon]);
            }
        }else return response()->json(['status' => 'error', 'message' => 'not found']);
    }
    public function sort_location_food(Request $request){
        $food=$request->food;
        
        $data=$this->sort_location($request);
        $ids=array();
        foreach ($data as $restaurant) {
            $id = $restaurant['id'];
            array_push($ids,$id);
        }
        $food_array=array();
        $menu_items_list=new MenuItemsController();
        foreach ($ids as $id){
            $request->request->add(['id' => $id]); //add request
            $menu_items=$menu_items_list->index_id($request);
            array_push($food_array,$menu_items);
        }
            $food_array_filtered = [];
            
            foreach ($food_array as $items) {
                foreach($items as $item)
                    if ($item["name"] === $food) {
                        $food_array_filtered[] = $item;
                    }
            }

            // Return JSON response with filtered array
            return response()->json(["status"=>'success',"message" => $food_array_filtered]);
    }
    public function check_auth(){
        return response()->json(['status'=>'success','message'=>'You are authenticated']);
    }
    public function accept_order(Request $request){
        $id=$request->id;
        $order_resturant=resturant_orders::where('order_id',$id)->first();
        $order_resturant->accepted="yes";
        $order_resturant->save();
        return response()->json(['status'=>'success','message'=>'success']);
    }
}


   

    