<?php
namespace App\Http\Controllers;

use App\Models\delivery_drivers;
use App\Models\User;
use App\Models\orders;
use App\Models\resturants;
use App\Models\menu_items;
use App\Models\order_items;
use App\Models\item_ingredients;
use App\Models\deliverydriver_orders;
use App\Models\resturant_orders;
use App\Models\temp_orders;
use App\Models\ingredients;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use DateTime;


class AdminController extends Controller
{
    //SHOW
    public function orders_list()
    {
        $orders_list=orders::all();
        return response()->json(['status'=>'success','message'=>$orders_list]);
    }
    public function users_list()
    {
        $orders_list=User::all();
        return response()->json(['status'=>'success','message'=>$orders_list]);
    }
    public function resturants_list()
    {
        $orders_list=resturants::all();
        return response()->json(['status'=>'success','message'=>$orders_list]);
    }
    public function delivery_list()
    {
        $orders_list=delivery_drivers::all();
        return response()->json(['status'=>'success','message'=>$orders_list]);
    }
    //TODO:- ADD MONTHLY 
    public function resturants_money_all_tax(){
        $restaurants = resturants::all(); // Assuming you want to calculate for all restaurants
        $results = [];
    
        foreach ($restaurants as $restaurant) {
            $whattype = $restaurant->typeoftax;
            $totals = array_fill(0, 12, 0);  // Initialize an array with 12 zeros for each month
    
            switch ($whattype) {
                case 'monthly':
                    $monthly_amount = $restaurant->moneyorpercentage;
                    for ($month = 0; $month < 12; $month++) {
                        $totals[$month] = $monthly_amount;
                    }
                    break;
    
                case 'perorder':
                    $order_items = order_items::where('resturant_id', $restaurant->id)->get();
                    $order_list = [];
    
                    foreach ($order_items as $item) {
                        if (!in_array($item->order_id, $order_list)) {
                            array_push($order_list, $item->order_id);
                        }
                    }
    
                    foreach ($order_list as $order_id) {
                        $order = orders::find($order_id);
                        $order_date = new DateTime($order->orderDate);
                        $month = (int)$order_date->format('n') - 1;  // Get month (0-based index)
                        $totals[$month] += (int)$order->totalPrice;
                    }
    
                    $percentage = $restaurant->moneyorpercentage / 100;
                    for ($month = 0; $month < 12; $month++) {
                        $totals[$month] *= $percentage;
                    }
                    break;
            }
    
            $results[] = [
                'restaurant_id' => $restaurant->id,
                'restaurant_name' => $restaurant->name,
                'monthly_totals' => $totals
            ];
        }
    
        return response()->json(['status' => 'success', 'data' => $results]);
    }
    public function resturants_money_all_total(){
        $restaurants = resturants::all(); // Assuming you want to calculate for all restaurants
        $results = [];
    
        foreach ($restaurants as $restaurant) {
            $whattype = $restaurant->typeoftax;
            $totals = array_fill(0, 12, 0);  // Initialize an array with 12 zeros for each month
    
            switch ($whattype) {
                case 'monthly':
                    $monthly_amount = $restaurant->moneyorpercentage;
                    for ($month = 0; $month < 12; $month++) {
                        $totals[$month] = $monthly_amount;
                    }
                    break;
    
                case 'perorder':
                    $order_items = order_items::where('resturant_id', $restaurant->id)->get();
                    $order_list = [];
    
                    foreach ($order_items as $item) {
                        if (!in_array($item->order_id, $order_list)) {
                            array_push($order_list, $item->order_id);
                        }
                    }
    
                    foreach ($order_list as $order_id) {
                        $order = orders::find($order_id);
                        $order_date = new DateTime($order->orderDate);
                        $month = (int)$order_date->format('n') - 1;  // Get month (0-based index)
                        $totals[$month] += (int)$order->totalPrice;
                    }
    
                    $percentage = 1;
                    for ($month = 0; $month < 12; $month++) {
                        $totals[$month] *= $percentage;
                    }
                    break;
            }
    
            $results[] = [
                'restaurant_id' => $restaurant->id,
                'restaurant_name' => $restaurant->name,
                'monthly_totals' => $totals
            ];
        }
    
        return response()->json(['status' => 'success', 'data' => $results]);
    }
 
    
    public function resturants_money_tax(Request $request){
        $res_id = $request->id;
        $resturant = resturants::find($res_id);
        $whattype = $resturant['typeoftax'];
        $totals = array_fill(0, 12, 0);  // Initialize an array with 12 zeros for each month
    
        switch ($whattype) {
            case 'monthly':
                $monthly_amount = $resturant['moneyorpercentage'];
                for ($month = 0; $month < 12; $month++) {
                    $totals[$month] = $monthly_amount;
                }
                break;
            case 'perorder':
                $order_items = order_items::where('resturant_id', $resturant['id'])->get();
                $order_list = array();
                foreach ($order_items as $item) {
                    if (!in_array($item['order_id'], $order_list)) {
                        array_push($order_list, $item['order_id']);
                    }
                }
                foreach ($order_list as $order_id) {
                    $s1 = orders::find($order_id);
                    $order_date = new DateTime($s1['orderDate']);
                    $month = (int)$order_date->format('m') - 1;  // Get month (0-based index)
                    $totals[$month] += (int)$s1['totalPrice'];
                }
                $percentage = $resturant['moneyorpercentage'] / 100;
                for ($month = 0; $month < 12; $month++) {
                    $totals[$month] *= $percentage;
                }
                break;
        }
    
        return response()->json(['status' => 'success', 'monthly_totals' => $totals]);
    }
    public function resturants_money_total(Request $request){
        $res_id = $request->id;
        $resturant = resturants::find($res_id);
        $whattype = $resturant['typeoftax'];
        $totals = array_fill(0, 12, 0);  // Initialize an array with 12 zeros for each month
    
        switch ($whattype) {
            case 'monthly':
                $monthly_amount = $resturant['moneyorpercentage'];
                for ($month = 0; $month < 12; $month++) {
                    $totals[$month] = $monthly_amount;
                }
                break;
            case 'perorder':
                $order_items = order_items::where('resturant_id', $resturant['id'])->get();
                $order_list = array();
                foreach ($order_items as $item) {
                    if (!in_array($item['order_id'], $order_list)) {
                        array_push($order_list, $item['order_id']);
                    }
                }
                foreach ($order_list as $order_id) {
                    $s1 = orders::find($order_id);
                    $order_date = new DateTime($s1['orderDate']);
                    $month = (int)$order_date->format('m') - 1;  // Get month (0-based index)
                    $totals[$month] += (int)$s1['totalPrice'];
                }
                $percentage = 1;
                for ($month = 0; $month < 12; $month++) {
                    $totals[$month] *= $percentage;
                }
                break;
        }
    
        return response()->json(['status' => 'success', 'monthly_totals' => $totals]);
    }
    //ADD
    public function orders_add(Request $request)
    {
        $user_id=$request->user_id;
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

        }
        return response()->json(['status'=>'success','message'=>'success']);
    }
    public function users_add(Request $request)
    {
        $user = new User();
        $user->username = $request->username;
        $user->email = $request->email;
        $user->address=$request->address;
        $user->password = app('hash')->make($request->password);
        $user->firstname=$request->firstname;
        $user->lastname=$request->lastname;
        $user->phone=$request->phone;
        $user->birthDate=$request->birthdate;
        $user->img=$request->img;
        if($user->save())
            return response()->json(['status'=>'success','message'=>'success']);
        else return response()->json(['status'=>'error','message'=>'failed']);
       
    }
    public function resturants_add(Request $request)
    {
        $resturants = new resturants();
        $resturants->name = $request->name;
        $resturants->username = $request->username;
        $resturants->phone=$request->phone;
        $resturants->rating=$request->rating;
        $resturants->address=$request->address;
        $resturants->password = app('hash')->make($request->password);
        $resturants->typeoftax=$request->typeoftax;
        $resturants->moneyorpercentage=$request->moneyorpercentage;
        if($resturants->save())
            return response()->json(['status'=>'success','message'=>'success']);
        else return response()->json(['status'=>'error','message'=>'failed']);
    }
    public function delivery_add(Request $request)
    {
        $delivery_drivers = new delivery_drivers();
        $delivery_drivers->full_Name = $request->fullname;
        $delivery_drivers->username = $request->username;
        $delivery_drivers->phone=$request->phone;
        $delivery_drivers->password = app('hash')->make($request->password);
        if ($delivery_drivers->save()) 
            return response()->json(['status'=>'success','message'=>'success']);
        else return response()->json(['status'=>'error','message'=>'failed']);  
    }
    //EDIT
    public function orders_edit(Request $request)
    {

        $order=orders::find($request->order_id)->first();
        if($order){
            $order->user_id=$request->user_id;
            $order->driver_id=$request->driver_id;
            $order->totalPrice=$request->totalprice;
            $order->orderDate=$request->orderDate;
            $order->save();
        return response()->json(['status'=>'success','message'=>'success']);
        }
        else return response()->json(['status'=>'error','message'=>'failed']);
    }
    public function users_edit(Request $request)
    {
    $user=User::find($request->user_id)->first();
    if($user){
        $user->name=$request->name;
        $user->firstname=$request->firstname;
        $user->lastname=$request->lastname;
        $user->email=$request->email;
        $user->address=$request->address;
        $user->birthDate=$request->birthDate;
        $user->img=$request->img;
        $user->admin=$request->admin;
        $user->phone=$request->phone;
        $user->username=$request->username;
        $user->password=app('hash')->make($request->newpassword);
        $user->save();
    return response()->json(['status'=>'success','message'=>'success']);
    }
    else return response()->json(['status'=>'error','message'=>'failed']);
    }
    public function resturants_edit(Request $request)
    {
        $res=resturants::find($request->resturant_id)->first();
        if($res){
            $res->name=$request->name;
            $res->rating=$request->rating;
            $res->address=$request->address;
            $res->phone=$request->phone;
            $res->username=$request->username;
            $res->password=app('hash')->make($request->newpassword);
            $res->save();
        return response()->json(['status'=>'success','message'=>'success']);
        }
        else return response()->json(['status'=>'error','message'=>'failed']);
    }
    public function delivery_edit(Request $request)
    {
        $del=delivery_drivers::find($request->delivery_driver_id)->first();
        if($del){
            $del->fullname=$request->fullname;
            $del->username=$request->username;
            $del->password=app('hash')->make($request->newpassword);
            $del->phone=$request->phone;
            $del->save();
        return response()->json(['status'=>'success','message'=>'success']);
        }
        else return response()->json(['status'=>'error','message'=>'failed']);
    }
    //DELETE
    public function orders_delete(Request $request)
    {
        $order_id=$request->order_id;
        try{
            $null_stuff="";
            $order=orders::where('id',$order_id);
            $user_id=$order['user_id'];
            if($order!=NULL){
                $order->delete();
            }else{
                $null_stuff.="order";
            }
            $order_items=order_items::where('order_id',$order_id);
            if($order_items!=NULL){
                $order_items->delete();
            }else{
                $null_stuff.="order_items";
            }
            $delivery_driver_order=deliverydriver_orders::where('order_id',$order_id);
            if($delivery_driver_order!=NULL){
                $delivery_driver_order->delete();
            }else{
                $null_stuff.="delivery_driver_order";
            }
            $resturant_order=resturant_orders::where('order_id',$order_id);
            if($resturant_order!=NULL){
                $resturant_order->delete();
            }else{
                $null_stuff.="resturant_order";
            }
            $payment_order=DB::table('payments')->where('order_id',$order_id);
            if($payment_order!=NULL){
                $payment_order->delete();
            }else{
                $null_stuff.="payment";
            }
            $temp_orders=temp_orders::where('user_id',$user_id);
            if($temp_orders!=NULL){
                $temp_orders->delete();
            }else{
                $null_stuff.="temp_orders";
            }
            if($null_stuff=="")
                return response()->json(['status'=>'success','message'=>'success']);
            else 
                return response()->json(['status'=>'error_null','message'=>$null_stuff]);
        }catch(\Exception $e){
            return response()->json(['status'=>'error','message'=>$e->getMessage()]);
        }

    }
        public function users_delete(Request $request)
    {
        $userid=$request->user_id;
        try{
            $null_stuff="";
            $user=User::where('id',$userid)->delete();
            $orders=orders::where('user_id',$userid)->get();
            foreach($orders as $order){
               
                $order_items=order_items::where('order_id',$order['id']);
                if($order_items!=NULL){
                    $order_items->delete();
                }else{
                    $null_stuff.="order_items";
                }
                $delivery_driver_order=deliverydriver_orders::where('order_id',$order['id']);
                if($delivery_driver_order!=NULL){
                    $delivery_driver_order->delete();
                }else{
                    $null_stuff.="delivery_driver_order";
                }
                $resturant_order=resturant_orders::where('order_id',$order['id']);
                if($resturant_order!=NULL){
                    $resturant_order->delete();
                }else{
                    $null_stuff.="resturant_order";
                }
                $payment_order=DB::table('payments')->where('user_id',$userid);
                if($payment_order!=NULL){
                    $payment_order->delete();
                }else{
                    $null_stuff.="payment";
                }
                $favorite_list=DB::table('favorites')->where('user_id',$userid);
                if($favorite_list!=NULL){
                    $favorite_list->delete();
                }else{
                    $null_stuff.="favorites";
                }
                $temp_orders=temp_orders::where('user_id',$userid);
                if($temp_orders!=NULL){
                    $temp_orders->delete();
                }else{
                    $null_stuff.="temp_orders";
                }

                if($order!=NULL){
                    $order->delete();
                }else{
                    $null_stuff.="order";
                }
            }
            if($null_stuff=="")
                return response()->json(['status'=>'success','message'=>'success']);
            else 
                return response()->json(['status'=>'success_null','message'=>$null_stuff]);
        }catch(\Exception $e){
            return response()->json(['status'=>'error','message'=>$e->getMessage()]);
        }
    }
    public function resturants_delete(Request $request)
    {
        $resturant_id=$request->resturant_id;
        try{
            $null_stuff="";
            $resturant=resturants::where('id',$resturant_id)->delete();
            $resturant_orders=resturant_orders::where('resturant_id',$resturant_id);
            if($resturant_orders!=NULL){
                if($resturant_orders['finished']==1)
                    $resturant_orders->delete();
                else 
                    return response()->json(['status'=>'wait','message'=>'close the resturant side']);
            }else{
                $null_stuff.="resturant_orders";
            }
            $menu_items=menu_items::where('resturant_id',$resturant_id);
            if($menu_items!=NULL){
                foreach($menu_items->get() as $menu_item){
                    $item_ingredients=item_ingredients::where('itemid',$menu_item['id']);
                    if($item_ingredients!=NULL){
                        foreach($item_ingredients->get() as $item_ingredient){
                            $ingerdet=ingredients::where('id',$item_ingredient['IngredientID']);
                            if($ingerdet!=NULL){
                                $ingerdet->delete();
                            }else{
                                $null_stuff.="ingredients";
                            }
                            $item_ingredient->delete();
                        }
                    }else{
                        $null_stuff.="item_ingredients";
                    }
                    $menu_item->delete();
                }
            }else {
                $null_stuff.="menu_items";
            }
            if($null_stuff=="")
                return response()->json(['status'=>'success','message'=>'success']);
            else 
                return response()->json(['status'=>'success_null','message'=>$null_stuff]);
        }catch(\Exception $e){
            return response()->json(['status'=>'error','message'=>$e->getMessage()]);
        }
    }
    public function delivery_delete(Request $request)
    {
        $driverid=$request->driver_id;
        try{
            $null_stuff="";
            $driver=delivery_drivers::where('id',$driverid)->delete();
            $delivery_driver_orders=deliverydriver_orders::where('driver_id',$driverid);
            if($delivery_driver_orders!=NULL){
                foreach($delivery_driver_orders->get() as $delivery_driver_order)
                    if($delivery_driver_order['delivered']==1)
                        $delivery_driver_order->delete();
                    else{
                        return response()->json(['status'=>'error_wait','message'=>'let the driver finish all his deliveries then delete']);
                    }
            }else{
                $null_stuff.="delivery_driver_order";
            }
            if($null_stuff=="")
                return response()->json(['status'=>'success','message'=>'success']);
            else 
                return response()->json(['status'=>'success_null','message'=>$null_stuff]);
        }catch(\Exception $e){
            return response()->json(['status'=>'error','message'=>$e->getMessage()]);
        }
    }
}


//TODO :- 
//make for finished / processed / done delivery -> it should close the order list like dont inculde that delivery person / resturant so we can delete it later again ...
