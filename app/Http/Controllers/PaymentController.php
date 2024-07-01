<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PaymentController extends Controller
{
    public function process(Request $request)
    {
        // Validate request
        $this->validate($request, [
            'amount' => 'required|numeric',
            'currency' => 'required|string',
            'payment_method' => 'required|string',
        ]);

        // Simulate processing payment
        $paymentData = [
            'user_id' => Auth::id(),
            'amount' => $request->amount,
            'currency' => $request->currency,
            'payment_method' => $request->payment_method,
            'order_id'=>$request->order_id,
            'status' => 'processed', // Simulated status
            
        ];

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
