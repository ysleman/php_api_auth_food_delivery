<?php

namespace App\Http\Controllers;

use App\Models\resturant_orders;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PaymentController extends Controller
{
    public function process(Request $request)
    {
        // // Validate request
        // $this->validate($request, [
        //     'amount' => 'required|numeric',
        //     'currency' => 'required|string',
        //     'payment_method' => 'required|string',
        // ]);

        // // Simulate processing payment
        $paymentData = [
            'user_id' =>auth($guard='api')->user()['id'],
            'amount' => $request->amount,
            'currency' => $request->currency,
            'payment_method' => $request->payment_method,
            'order_id'=>$request->order_id,
            'status' => 'processed', // Simulated status
            'cvv'=>$request->cvv,
           'cardNumber'=>$request->cardNumber,
           'expirationDate'=>$request->expirationDate,
            'Name'=>$request->Name,
        ];


        $order_id=$request->order_id;
        $cardlist=json_decode($request->cardlist,true);
        $x=0;
        $resturants_ids=array();
        for($i=0;$i<count($cardlist);$i++){
            $x=$cardlist[$i]['product'];
            array_push($resturants_ids,$x['resturant_id']);
        }
        $resturants_ids2=array_unique($resturants_ids);
        for($i=0;$i<count($resturants_ids2);$i++){
                $order=new resturant_orders();
                $order->resturant_id=$resturants_ids2[$i];
                $order->order_id=$order_id;
                $order->finished="no";
                $order->accepted="no";
                $order->save();   
        }         
        
        // Save payment data to the database
        DB::table('payments')->insert($paymentData);

        return response()->json([
            'success' => true,
            'message' => 'Payment processed successfully',
            'payment' => $paymentData,
        ]);
    }


    public function getMethods()
    {
        // Retrieve payment methods for the authenticated user
        $paymentMethods = DB::table('payments')
                            ->where('user_id', Auth::id())
                            ->get();

        return response()->json([
            'success' => true,
            'payment_methods' => $paymentMethods,
        ]);
    }
}
