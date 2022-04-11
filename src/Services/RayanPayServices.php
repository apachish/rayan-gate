<?php
namespace Rayanpay\RayanGate\Services;

use Rayanpay\RayanGate\Models\PaymentRequest;
use Rayanpay\RayanGate\Models\PaymentVerification;
use Rayanpay\RayanGate\Objects\PaymentRequest as PaymentRequestObject;
use Rayanpay\RayanGate\Objects\PaymentVerification as PaymentVerificationObject;
use SoapClient;

class RayanPayServices
{
    public $Authority = "";
    public $MerchantID = "";
    public $Description = "";
    public $Amount = "";
    public $mobile = "";
    public $email = "";
    public $CallbackURL = "";
    public $type = "rest";


    private static function soap_check()
    {
        return (extension_loaded('soap')) ? true : false;
    }

    private static function curl_check()
    {
        return (function_exists('curl_version')) ? true : false;
    }


    /**
     * تابعی برای مشخص کردن پیام خطا با استفاده از کد بازگشتی از درخواست پاسخ
     * @param $error
     * @param $method
     * @param $prepend
     * @return string
     */
    private static function error_message($code, $cb, $request = false)
    {
        if (empty($cb) && $request === true) {
            return "لینک بازگشت ( CallbackURL ) نباید خالی باشد";
        }

        $error = config("errors-gateway.errors");
        return data_get($error,$code,"خطای نامشخص هنگام اتصال به درگاه رایان مهر");

    }

    public static function redirect($url)
    {
        header("Location: " . $url, true, 301);
        exit;
    }

    /*
     * تابع درخواست شروع و اتصال به درگاه بانک می باشد که در صورت درست بودن موارد ارسالی بدون خطا به درگاه رفته
     */
    public static function request(PaymentRequestObject $paymentRequest)
    {
        $Status = 0;
        $StartPayUrl = "";
        $type_gateway = config("config-gateway.type_gateway");
        $paymentRequest->setMerchantId( config("config-gateway.MerchantID"));
        $paymentRequest->setAmount(  (int)$paymentRequest->Amount);
        $paymentRequest->setCallbackURL( route("gateway.verify"));

        if ($type_gateway == "soap" && self::soap_check() === true) {
            $client = new SoapClient(config("config-gateway.address_soap"), [
                'encoding' => 'UTF-8',
                "location" => config("config-gateway.address_soap"),
                'trace' => 1,
                "exception" => 1,
            ]);

            $result = $client->PaymentRequest($paymentRequest);
            if (!isset($result->PaymentRequestResult)) return [];
            $paymentRequest->Status = (isset($result->PaymentRequestResult->Status) && $result->PaymentRequestResult->Status != "") ? $result->PaymentRequestResult->Status : 0;
            $paymentRequest->Authority = (isset($result->PaymentRequestResult->Authority) && $result->PaymentRequestResult->Authority != "") ? $result->PaymentRequestResult->Authority : "";
            $StartPayUrl = ($paymentRequest->Authority != "") ? config("config-gateway.address_ref") . $paymentRequest->Authority : "";

        } elseif ($type_gateway == "rest" && self::curl_check() === true) {
            list($response, $http_status) = self::getResponse(config("config-gateway.address_rest"), array_filter((array)$paymentRequest));
            $paymentRequest->Status = (isset($response->status) && $response->status != "") ? $response->status : 0;
            $paymentRequest->Authority = (isset($response->authority) && $response->authority != "") ? $response->authority : "";
            $StartPayUrl = ($paymentRequest->Authority != "") ? config("config-gateway.address_ref") . $paymentRequest->Authority : "";
        }
        $paymentRequestModel = new PaymentRequest();
        $paymentRequestModel = $paymentRequestModel->create((array)$paymentRequest);
        return array(
            "Method" => $type_gateway,
            "Status" => $paymentRequest->Status,
            "paymentRequest" => $paymentRequestModel,
            "Message" => self::error_message($paymentRequest->Status, $paymentRequest->CallbackURL, true),
            "StartPay" => $StartPayUrl,
        );
    }

    /*
     * تابع درخواست  تایید بود که با توجه به گذاشتن شماره سفارش در ادرس بازگشتی در این تلبع بررسی شده و در صورت درست بودن پول از حساب کاربر کم می شود
     */
    public static function verify(PaymentRequest $paymentRequest)
    {
        $Status = 0;
        $Message = "";
        $RefID = "";

        $type_gateway = config("config-gateway.type_gateway");
        $payment_verification = new PaymentVerificationObject();
        $payment_verification->payment_request_id = $paymentRequest->id;
        $payment_verification->setMerchantID(config("config-gateway.MerchantID"));
        $payment_verification->setAmount($paymentRequest->Amount);
        $payment_verification->setAuthority($paymentRequest->Authority);
        if ($type_gateway == "soap" && self::soap_check() === true) {
            $client = new SoapClient(config("config-gateway.address_soap"), [
                'encoding' => 'UTF-8',
                "location" => config("config-gateway.address_soap"),
                'trace' => 1,
                "exception" => 1,
            ]);
            $result = $client->PaymentVerification(
                $payment_verification
            );
            $payment_verification->Status = data_get($result,"PaymentVerificationResult.Status",0) ;
            $payment_verification->RefID = data_get($result,"PaymentVerificationResult.RefID","");
            $Message = self::error_message($payment_verification->Status, "", "");


        } elseif ($type_gateway == "rest" && self::curl_check() === true) {
            list($response, $http_status) = self::getResponse(config("config-gateway.address_rest_verify"), (array)$payment_verification);
            $payment_verification->Status = data_get($response,"status",0);
            $payment_verification->RefID = data_get($response,"refID","");
            $Message = self::error_message($payment_verification->Status, "", "", false);
        }
        $payment_verification_model =  new PaymentVerification();
        $payment_verification_model = $payment_verification_model->create((array)$payment_verification);
        return array(
            "Method" => $type_gateway,
            "Status" => $payment_verification->Status,
            "Message" => $Message,
            "payment_verification"=>$payment_verification_model
        );
    }

    
    /*
     * برای چک کردن موارد ارسالی در فرم که عدد وارد شود خالی نباشد
     */
    public static function validationForm($data)
    {
        $error = [];
        if (!data_get($data,'Amount') || !data_get($data,'MerchantID')) {
            $error['fill'] = "فیلد های ستاره دار اجباری می باشد";
        }
        if (!filter_var(data_get($data,'Amount'), FILTER_VALIDATE_INT)) {
            $error["Amount"] = "مقدار  مبلغ ارسالی عدد باشد.";
        }

        if (data_get($data,'Amount') <= 1000) {

            echo $error["price-gt"] = "مقدار مبلغ ارسالی بزگتر از 1000 باشد";
        }

        if (data_get($data,'Mobile') && !self::perfix_mobile(data_get($data,'Mobile'))) {

            echo $error["Mobile"] = " شماره موبایل باید با 98 شروع شود و یا تعداد اعداد وارد شده موبایل درست نیست";
        }

        if (data_get($data,'Email') && !filter_var(data_get($data,'Email'), FILTER_VALIDATE_EMAIL)) {

            echo $error["Email"] = " ایمیل وارد شده صحیح نمی باشد";
        }
        return $error;
    }

    /*
     * تابع بررس شماره موبایل با ۹۸ شروع شود
     */
    public static function perfix_mobile($phone_number)
    {

        $pattern = "/^989[0-9]{9}$/";
        if (preg_match($pattern, $phone_number)) {
            return true;
        }
        return false;

    }


    /**
     * تابعی برای ارسال درخواست به سرور رایان پی با تابع curl
     * @param string $url ادرس درخواست
     * @param array $data داده ارسالی  در درخواست
     * @param array $header مقدار ارایه ست شد در هد  درخواست
     * @return bool|string
     */
    public static function getResponse($url, array $data)
    {
        /*
         * داده ارسالی در داخل بدنه درخواست
         */
        $client = new \GuzzleHttp\Client([
            'timeout' => 10,
            'read_timeout' => 10,
            'connect_timeout' => 10 ,
            'headers' => []]);
        $response = $client->post($url, ['json' => $data]);
        $result = $response->getBody();
        $http_status = $response->getStatusCode();
        $response = json_decode($result);
        return [$response, $http_status];
    }
    
}