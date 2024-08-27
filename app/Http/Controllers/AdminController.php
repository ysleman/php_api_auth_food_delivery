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
use Mockery\Undefined;

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

    public function orders_Check(Request $request)
    {
        $orders=orders::find($request->id);
        return response()->json(['status'=>'success','message'=>$orders]);
    }
    public function users_Check(Request $request)
    {
        $orders_list=User::find($request->id);
        return response()->json(['status'=>'success','message'=>$orders_list]);
    }
    public function resturants_Check(Request $request)
    {
        $orders_list=resturants::find($request->id);
        return response()->json(['status'=>'success','message'=>$orders_list]);
    }
    public function delivery_Check(Request $request)
    {
        $orders_list=delivery_drivers::find($request->id);
        return response()->json(['status'=>'success','message'=>$orders_list]);
    }

    public function resturants_money_all_tax(){
        $restaurants = resturants::all(); // Assuming you want to calculate for all restaurants
        $results = [];
    
        foreach ($restaurants as $restaurant) {
            $whattype = $restaurant->typeoftax;
            $request = new \Illuminate\Http\Request();
            $request->setMethod('POST');
            $request->request->add(['id' => $restaurant['id']]); //add request
            $request->request->add(['years'=>"true"]);
            $totals = $this->resturants_money_tax($request); 
            
            $totals=$totals->getData();
            $results[] = [
                'restaurant_id' => $restaurant->id,
                'restaurant_name' => $restaurant->name,
                'monthly_totals' => $totals->monthly_totals
            ];
        }
    
        return response()->json(['status' => 'success', 'data' => $results]);
    }
    public function resturants_money_all_total(){
        $restaurants = resturants::all(); // Assuming you want to calculate for all restaurants
        $results = [];
    
        foreach ($restaurants as $restaurant) {
            $whattype = $restaurant->typeoftax;
            $request = new \Illuminate\Http\Request();
            $request->setMethod('POST');
            $request->request->add(['id' => $restaurant['id']]); //add request
            $request->request->add(['years'=>"true"]);
            $totals = $this->resturants_money_total($request); 
            
            $totals=$totals->getData();
            $results[] = [
                'restaurant_id' => $restaurant->id,
                'restaurant_name' => $restaurant->name,
                'monthly_totals' => $totals->monthly_totals
            ];
        }
    
        return response()->json(['status' => 'success', 'data' => $results]);
    }
 
    
    public function resturants_money_tax(Request $request){
        $res_id = $request->id;
        $years=$request->years;
        $resturant = resturants::find($res_id);
        $whattype = $resturant['typeoftax'];
        $totals = array_fill(0, 12, 0); 
        $dateforresturant=new DateTime($resturant['created_at']);
        $startmonth=(int)$dateforresturant->format('m')-1;
        $startyear=(int)$dateforresturant->format('Y');
        $currentyear=date('Y');
        $avaiable=true;
        $mes='';

        if($years==NULL || empty($years) || !isset($years))$years=2024;
        else $years=(int)$years;
        if($currentyear==$startyear){
            if($startmonth+1>date('m')){
               $avaiable=false;
            }
            $mes='year : '.$currentyear;
        }else if($currentyear>$startyear)
            $avaiable=true;
        else 
            $avaiable=false;

        $mes.=' Get for all';
            switch ($whattype) {
                case 'monthly':
                    $monthly_amount = $resturant['moneyorpercentage'];
                    if($avaiable){
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
                            if((int)$order_date->format('Y')==$years){
                                $month = (int)$order_date->format('m') - 1;  // Get month (0-based index)
                                $totals[$month] += (int)$s1['totalPrice'];
                            }else{
                            }
                        }
                            for ($month = $startmonth; $month < 12; $month++) {
                                if($totals[$month]!=0){
                                    if($totals[$month]>=$monthly_amount)$totals[$month]=$monthly_amount;//above monthly amount
                                    else $totals[$month]=($monthly_amount-$totals[$month]);
                                }
                            }
                    } else $mes="Sorry, this restaurant is not available in this time";
                    break;
                case 'perorder':
                    if($avaiable){
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
                            if((int)$order_date->format('Y')==$years){
                                $month = (int)$order_date->format('m') - 1;  // Get month (0-based index)
                                $totals[$month] += (int)$s1['totalPrice'];
                            }else{
                            }
                        }
                        $percentage = $resturant['moneyorpercentage'] / 100;
                            for ($month = $startmonth; $month < 12; $month++) {
                                if($totals[$month]!=0)
                                $totals[$month] *= $percentage;
                            }
                    } else $mes="Sorry, this restaurant is not available in this time";
                    break;
        } 
        return response()->json(['status' => 'success', 'monthly_totals' => $totals,'message'=>$years]);   
    }

    public function orders_resturants_all(){
        $restaurants = resturants::all(); // Assuming you want to calculate for all restaurants
        $results = [];
    
        foreach ($restaurants as $restaurant) {
            $request = new \Illuminate\Http\Request();
            $request->setMethod('POST');
            $request->request->add(['id' => $restaurant['id']]); //add request
            $request->request->add(['years'=>"true"]);
            $totals = $this->orders_resturant_all($request); 
            $dateforresturant=new DateTime($restaurant['created_at']);
            $startyear=(int)$dateforresturant->format('Y');
            $totals=$totals->getData();
            $results[] = [
                'restaurant_id' => $restaurant->id,
                'restaurant_name' => $restaurant->name,
                'year'=>$startyear,
                'orders' => $totals->Orders
            ];
        }
    
        return response()->json(['status' => 'success', 'data' => $results]);
    }

    public function orders_resturant_all(Request $request){
        $res_id = $request->id;
        $years=$request->years;
        $resturant = resturants::find($res_id);
        $totalsOrders = array_fill(0, 13, 0); 
        $dateforresturant=new DateTime($resturant['created_at']);
        $startmonth=(int)$dateforresturant->format('m')-1;
        $startyear=(int)$dateforresturant->format('Y');
        $currentyear=date('Y');
        $avaiable=true;
        $mes='';
        if($currentyear==$startyear){
            if($startmonth+1>date('m')){
               $avaiable=false;
            }
            $mes='year : '.$currentyear;
        }else if($currentyear>$startyear)
            $avaiable=true;
        else 
            $avaiable=false;

        $mes.=' Get for all';
        if(empty($years)||$years=null){
                    if($avaiable){
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
                            if((int)$order_date->format('Y')==$startyear){
                                $month = (int)$order_date->format('m') - 1;  // Get month (0-based index)
                                $totalsOrders[$month] += 1;
                                $totalsOrders[12] = (int)$order_date->format('Y');
                            }
                        }
                    } else $mes="Sorry, this restaurant is not available in this time";
                
        } else {
                    $totalsOrders=array_fill(0,($currentyear-$startyear)+1,array_fill(0, 13, 0));                    
                    if($avaiable){
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
                            if((int)$order_date->format('Y')==$startyear){
                                $month = (int)$order_date->format('m') - 1; 
                                $totalsOrders[0][$month] += 1;
                                $totalsOrders[0][12] =(int)$order_date->format('Y');
                            }else{
                                $month = (int)$order_date->format('m') - 1;
                                $totalsOrders[($currentyear-(int)$order_date->format('Y'))+1][$month] += 1;
                                $totalsOrders[($currentyear-(int)$order_date->format('Y'))+1][12] =(int)$order_date->format('Y');
                            }
                        }
                    }
                    else $mes="Sorry, this restaurant is not available in this time";
                } 
                return response()->json(['status' => 'success', 'Orders' => $totalsOrders,'message'=>$mes,'startyear'=>$startyear]);
            }      

        


    public function resturants_money_total(Request $request){
        $res_id = $request->id;
        $years=$request->years;
        $resturant = resturants::find($res_id);
        $whattype = $resturant['typeoftax'];
        $totals = array_fill(0, 12, 0); 
        $dateforresturant=new DateTime($resturant['created_at']);
        $startmonth=(int)$dateforresturant->format('m')-1;
        $startyear=(int)$dateforresturant->format('Y');
        $currentyear=date('Y');
        $avaiable=true;
        $mes='';
        if($years==NULL || empty($years) || !isset($years))$years=2024;
        else $years=(int)$years;
        if($currentyear==$startyear){
            if($startmonth+1>date('m')){
               $avaiable=false;
            }
            $mes='year : '.$currentyear;
        }else if($currentyear>$startyear)
            $avaiable=true;
        else 
            $avaiable=false;

        $mes.=' Get for all';
            switch ($whattype) {
                case 'monthly':
                    $monthly_amount = $resturant['moneyorpercentage'];
                    if($avaiable){
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
                            if((int)$order_date->format('Y')==$years){
                                $month = (int)$order_date->format('m') - 1;  // Get month (0-based index)
                                $totals[$month] += (int)$s1['totalPrice'];
                            }else{
                            }
                        }
                    } else $mes="Sorry, this restaurant is not available in this time";
                    break;
                case 'perorder':
                    if($avaiable){
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
                            if((int)$order_date->format('Y')==$years){
                                $month = (int)$order_date->format('m') - 1;  // Get month (0-based index)
                                $totals[$month] += (int)$s1['totalPrice'];
                            }
                        }
                    } else $mes="Sorry, this restaurant is not available in this time";
                    break;
        }

    
        return response()->json(['status' => 'success', 'monthly_totals' => $totals,'message'=>$mes]);
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
                $stock_x="stock".$i;
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
        $delivery_drivers->resturant_id=$request->resturant_id;
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
            if(!empty($request->user_id)||($request->user_id)!=NULL)
            $order->user_id=$request->user_id;
            if(!empty($request->driver_id)||($request->driver_id)!=NULL)
            $order->driver_id=$request->driver_id;
            if(!empty($request->totalprice)||($request->totalprice)!=NULL)
            $order->totalPrice=$request->totalprice;
            if(!empty($request->orderDate)||($request->orderDate)!=NULL)
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
        if(!empty($request->firstname)||($request->firstname)!=NULL)
        $user->firstname=$request->firstname;
        if(!empty($request->lastname)||($request->lastname)!=NULL)
        $user->lastname=$request->lastname;
        if(!empty($request->email)||($request->email)!=NULL)
        $user->email=$request->email;
        if(!empty($request->address)||($request->address)!=NULL)
        $user->address=$request->address;
        if(!empty($request->birthDate)||($request->birthDate)!=NULL)
        $user->birthDate=$request->birthDate;
        if(!empty($request->img)||($request->img)!=NULL)
        $user->img=$request->img;
        if(!empty($request->phone)||($request->phone)!=NULL)
        $user->phone=$request->phone;
        if(!empty($request->username)||($request->username)!=NULL)
        $user->username=$request->username;
        if(!empty($request->newpassword)||($request->newpassword)!=NULL)
        $user->password=app('hash')->make($request->newpassword);
        $user->save();
    return response()->json(['status'=>'success','message'=>'success']);
    }
    else return response()->json(['status'=>'error','message'=>'failed']);
    }
    public function resturants_edit(Request $request)
    {
        $res=resturants::find($request->id)->first();
        if($res){
            if(!empty($request->name)||($request->name)!=NULL)
            $res->name=$request->name;
            if(!empty($request->rating)||($request->rating)!=NULL)
            $res->rating=$request->rating;
            if(!empty($request->address)||($request->address)!=NULL)
            $res->address=$request->address;
            if(!empty($request->phone)||($request->phone)!=NULL)
            $res->phone=$request->phone;
            if(!empty($request->username)||($request->username)!=NULL)
            $res->username=$request->username;
            if(!empty($request->typeoftax)||($request->typeoftax)!=NULL)
            $res->typeoftax=$request->typeoftax;
            if(!empty($request->moneyorpercentage)||($request->moneyorpercentage)!=NULL)
            $res->moneyorpercentage=$request->moneyorpercentage;
            if(!empty($request->newpassword)||($request->newpassword)!=NULL)
            $res->password=app('hash')->make($request->newpassword);
            $res->save();
        return response()->json(['status'=>'success','message'=>'success']);
        }
        else return response()->json(['status'=>'error','message'=>'failed']);
    }
    public function delivery_edit(Request $request)
    {
        try {
            $del = delivery_drivers::where('id', $request->delivery_driver_id)->first();
            if ($del) {
                if (!empty($request->fullname) || ($request->fullname) != NULL) {
                    $del->full_Name = $request->fullname;
                }
                if (!empty($request->username) || ($request->username) != NULL) {
                    $del->username = $request->username;
                }
                if (!empty($request->password) || ($request->password) != NULL) {
                    $del->password = app('hash')->make($request->password);
                }
                if (!empty($request->phone) || ($request->phone) != NULL) {
                    $del->phone = $request->phone;
                }
                $del->save();
                return response()->json(['status' => 'success', 'message' => 'success']);
            }
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'failed']);
        }
    }
    //DELETE
    public function orders_delete(Request $request)
    {
        $order_id=$request->order_id;
        $null_stuff="";
        try{
            $order=orders::where('id',$order_id)->first();
            $user_id=$order['user_id'];
            if($order!=NULL){
                $order->delete();
            }else{
                $null_stuff.="order";
            }
            $order_items=order_items::where('order_id',$order_id)->get();
            if($order_items!=NULL && !isset($order_items)){
                $order_items->delete();
            }else{
                $null_stuff.="order_items";
            }
            $delivery_driver_order=deliverydriver_orders::where('order_id',$order_id)->get();
            if($delivery_driver_order!=NULL && !isset($delivery_driver_order)){
                $delivery_driver_order->delete();
            }else{
                $null_stuff.="delivery_driver_order";
            }
            $resturant_order=resturant_orders::where('order_id',$order_id)->get();
            if($resturant_order!=NULL && !isset($resturant_order)){
                $resturant_order->delete();
            }else{
                $null_stuff.="resturant_order";
            }
            $payment_order=DB::table('payments')->where('order_id',$order_id)->get();
            if($payment_order!=NULL && !isset($payment_order)){
                $payment_order->delete();
            }else{
                $null_stuff.="payment";
            }
            $temp_orders=temp_orders::where('user_id',$user_id)->get();
                if($temp_orders!=NULL  && !isset($temp_orders) ){
                    $temp_orders->delete();
                }else{
                    $null_stuff.="temp_orders";
                }
            if($null_stuff=="")
                return response()->json(['status'=>'success','message'=>'success']);
            else 
                return response()->json(['status'=>'success','message'=>$null_stuff]);
        }catch(\Exception $e){
            return response()->json(['status'=>'error','message'=>$e->getMessage(),"null"=>$null_stuff]);
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
            $resturant_orders=resturant_orders::where('resturant_id',$resturant_id)->get();
            if($resturant_orders!=NULL){
                if($resturant_orders['finished']==1)
                    $resturant_orders->delete();
                else 
                    return response()->json(['status'=>'wait','message'=>'close the resturant side']);
            }else{
                $null_stuff.="resturant_orders";
            }
            $menu_items=menu_items::where('resturant_id',$resturant_id)->get();
            if($menu_items!=NULL || count($menu_items) >0){
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
                return response()->json(['status'=>'success','message'=>$null_stuff]);
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
    public function check_auth(){
        return response()->json(['status'=>'success','message'=>'You are authenticated']);
    }
}