<?php

namespace Rayanpay\RayanGate\App\Http\Controllers;



use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Rayanpay\RayanGate\Models\PaymentRequest;
use Rayanpay\RayanGate\Models\PaymentVerification;
use Rayanpay\RayanGate\Services\RayanPayServices;

class GatewayController extends Controller
{
    public function verification(Request $request)
    {
        $payment_request = PaymentRequest::where("Authority",$request->Authority)->first();
        if($payment_request == null)return response()->json([$payment_request,$request->Authority],404);
        $result = RayanPayServices::verify($payment_request);
        return response()->json($result,200);
    }


}
