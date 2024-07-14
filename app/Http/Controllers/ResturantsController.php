<?php

namespace App\Http\Controllers;

use App\Models\ingredients;
use App\Models\item_ingredients;
use App\Models\menu_items;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\resturants;
use App\Models\order_items;
use App\Models\resturant_orders;

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
            $menu_item->name=$request->name;
            $menu_item->price=$request->price;
            $menu_item->description=$request->description;
            $menu_item->quantity=$request->quantity;
            $menu_item->save();
        }
        return response()->json(['status' => 'success', 'message' => 'Menu item edited successfully']);
    }

    public function menu_items_remove(Request $request)
    {
        // Your logic to remove menu items
        // Example:
        // DB::table('menu_items')->where('id', $request->id)->delete();
        $menu_item=menu_items::where('id',$request->menu_item_id)->delete();
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
        
        $locationdetails= Http::get('https://geocode.maps.co/search?city='.$address.'&country=IL&api_key=6684fb0511fd4967575382teo9d69dc');
        if($locationdetails!=null){
            $lat = $locationdetails[0]['lat'];
            $lon = $locationdetails[0]['lon'];
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

}


   

    