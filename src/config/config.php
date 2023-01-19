<?php

return [
    "MerchantID" => env("MERCHANTID"),
    "type_gateway" => env("TYPEGATEWAY","soap"),//soap,rest
    "address_soap"=>env("ADDRESSSOAP","https://pms.rayanpay.com/pg/services/webgate/wsdl"),
    "address_ref"=>env("ADDRESSREF","https://pms.rayanpay.com/pg/startpay/"),
    "address_rest"=>env("ADDRESSREST","https://pms.rayanpay.com/api/v2/ipg/paymentRequest"),
    "address_rest_verify"=>env("ADDRESSRESTVERIFY","https://pms.rayanpay.com/api/v2/ipg/paymentVerification"),
    "gateway.verify"=>env("CALLBACKBANK","gateway.verify")
];
