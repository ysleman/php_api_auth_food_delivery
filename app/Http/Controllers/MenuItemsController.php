<?php

namespace App\Http\Controllers;

use App\Models\ingredients;
use App\Models\item_ingredients;
use App\Models\menu_items;
use Illuminate\Http\Request;

class MenuItemsController extends Controller
{
    public function index()
    {
        $menu_items_unfiltered = menu_items::all();
        $menu_items=array();
        $item_ingds=item_ingredients::all();
        $ingds=ingredients::all();
        $count=0;
        for($y=0;$y<count($menu_items_unfiltered);$y++){
            for($i=0;$i<count($item_ingds);$i++){
                if($item_ingds[$i]["itemid"]==$menu_items_unfiltered[$y]["id"]){
                    if(empty($menu_items[$y])){
                        array_push($menu_items,$menu_items_unfiltered[$y]);
                        $menu_items[$count]["item".$i]=$item_ingds[$i];
                        
                    }else {
                        $menu_items[$count]["item".$i]=$item_ingds[$i];
                    }
                    $ingd_id=$menu_items[$count]["item".$i]["IngredientID"];
                        for($x=0;$x<count($ingds);$x++){
                            if($ingds[$x]["id"]==$ingd_id)$menu_items[$count]["item".$i]["IngredientID"]=$ingds[$x];
                        }
                } 
            }
            $count++;
        }

        return response()->json($menu_items);
    }
    public function item_id(Request $request)
    {   
        $id=$request->id;
        $menu_items_unfiltered = menu_items::where('id',$id)->first();
        $menu_items=array();
        $item_ingds=item_ingredients::where("itemid",$id)->get();
        $ingds=ingredients::all();
        $count=0;
        for($i=0;$i<count($item_ingds);$i++){
                    if($i==0){
                        array_push($menu_items,$menu_items_unfiltered);
                        $menu_items[0]["item".$i]=$item_ingds[$i];
                    }else {
                        $menu_items[0]["item".$i]=$item_ingds[$i];
                    }
                    $menu_items[0]["item".$i]["IngredientID"]=ingredients::where('id',$item_ingds[$i]["IngredientID"])->first();
        }
        return $menu_items;
    }
    public function index_id(Request $request)
    {   
        $id=$request->id;
        $menu_items_unfiltered = menu_items::where('resturant_id',$id)->get();
        $menu_items=array();
        $item_ingds=item_ingredients::all();
        $ingds=ingredients::all();
        $count=0;
        for($y=0;$y<count($menu_items_unfiltered);$y++){
            for($i=0;$i<count($item_ingds);$i++){
                if($item_ingds[$i]["itemid"]==$menu_items_unfiltered[$y]["id"]){
                    if(empty($menu_items[$y])){
                        array_push($menu_items,$menu_items_unfiltered[$y]);
                        $menu_items[$count]["item".$i]=$item_ingds[$i];
                        
                    }else {
                        $menu_items[$count]["item".$i]=$item_ingds[$i];
                    }
                    $ingd_id=$menu_items[$count]["item".$i]["IngredientID"];
                        for($x=0;$x<count($ingds);$x++){
                            if($ingds[$x]["id"]==$ingd_id)$menu_items[$count]["item".$i]["IngredientID"]=$ingds[$x];
                        }
                } 
            }
            $count++;
        }

        return $menu_items;
    }
    public function resturant_id()
    {   
        $id=auth($guard='resturants')->user()['id'];
        $menu_items_unfiltered = menu_items::where('resturant_id',$id)->get();
        $menu_items=array();
        $item_ingds=item_ingredients::all();
        $ingds=ingredients::all();
        $count=0;
        for($y=0;$y<count($menu_items_unfiltered);$y++){
            for($i=0;$i<count($item_ingds);$i++){
                if($item_ingds[$i]["itemid"]==$menu_items_unfiltered[$y]["id"]){
                    if(empty($menu_items[$y])){
                        array_push($menu_items,$menu_items_unfiltered[$y]);
                        $menu_items[$count]["item".$i]=$item_ingds[$i];
                        
                    }else {
                        $menu_items[$count]["item".$i]=$item_ingds[$i];
                    }
                    $ingd_id=$menu_items[$count]["item".$i]["IngredientID"];
                        for($x=0;$x<count($ingds);$x++){
                            if($ingds[$x]["id"]==$ingd_id)$menu_items[$count]["item".$i]["IngredientID"]=$ingds[$x];
                        }
                } 
            }
            $count++;
        }

        return $menu_items;
    }
}
