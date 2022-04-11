<?php

namespace ArmanTadbir\AuthPassport\App\Http\Controllers;



use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Rayanpay\RayanGate\Models\PaymentRequest;
use Rayanpay\RayanGate\Models\PaymentVerification;
use Rayanpay\RayanGate\Services\RayanPayServices;

class GatewayController extends Controller
{
    public function verification(Request $request)
    {
        $payment_request = PaymentRequest::find($request->Authority);
        if($payment_request == null)return response()->json([],404);
        $result = RayanPayServices::verify($payment_request);
        return response()->json($result,200);
    }


}
