<?php
namespace Rayanpay\RayanGate\Services;

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

        $error = config("errors-gatway");

        if (array_key_exists("{$code}", $error)) {
            return $error["{$code}"];
        } else {
            return "خطای نامشخص هنگام اتصال به درگاه رایان مهر";
        }
    }

    public static function redirect($url)
    {
        header("Location: " . $url, true, 301);
        exit;
    }

    /*
     * تابع درخواست شروع و اتصال به درگاه بانک می باشد که در صورت درست بودن موارد ارسالی بدون خطا به درگاه رفته
     */
    public static function request()
    {
        $Status = 0;
        $StartPayUrl = "";
        if ($this->type == "soap" && $this->soap_check() === true) {

            $client = new SoapClient(config("config-gatway.ADDRESSSOAP"), [
                'encoding' => 'UTF-8',
                "location" => config("config-gatway.ADDRESSSOAP"),
                'trace' => 1,
                "exception" => 1,
            ]);

            $paymentRequest = new PaymentRequest();
            $paymentRequest->setMerchantId($this->MerchantID);
            $paymentRequest->setAmount((int)$this->Amount);
            $paymentRequest->setDescription($this->Description);
            $paymentRequest->setEmail($this->email);
            $paymentRequest->setMobile($this->mobile);
            $paymentRequest->setCallbackURL($this->CallbackURL);

            $result = $client->PaymentRequest($paymentRequest);
            if (!isset($result->PaymentRequestResult)) return [];
            $Status = (isset($result->PaymentRequestResult->Status) && $result->PaymentRequestResult->Status != "") ? $result->PaymentRequestResult->Status : 0;
            $this->Authority = (isset($result->PaymentRequestResult->Authority) && $result->PaymentRequestResult->Authority != "") ? $result->PaymentRequestResult->Authority : "";
            $StartPayUrl = ($this->Authority != "") ? config("config-gatway.ADDRESSREF") . $this->Authority : "";

        } elseif ($this->type == "rest" && $this->curl_check() === true) {
            $paramter = [
                "merchantID" => $this->MerchantID,
                "amount" => (int)$this->Amount,
                "description" => $this->Description,
                "email" => $this->email,
                "mobile" => $this->mobile,
                "callbackURL" => $this->CallbackURL

            ];
            list($response, $http_status) = $this->getResponse(config("config-gatway.ADDRESSSOAP"), array_filter($paramter));
            $Status = (isset($response->status) && $response->status != "") ? $response->status : 0;
            $this->Authority = (isset($response->authority) && $response->authority != "") ? $response->authority : "";
            $StartPayUrl = ($this->Authority != "") ? config("config-gatway.ADDRESSREF") . $this->Authority : "";
        }
        if ($this->Authority) {
            $this->saveStorage();
        }
        return array(
            "Method" => $this->type,
            "Status" => $Status,
            "Amount" => $this->Amount,
            "Mobile" => $this->mobile,
            "Email" => $this->email,
            "Description" => $this->Description,
            "Message" => self::error_message($Status, $this->CallbackURL, true),
            "StartPay" => $StartPayUrl,
            "Authority" => $this->Authority
        );
    }

    /*
     * تابع درخواست  تایید بود که با توجه به گذاشتن شماره سفارش در ادرس بازگشتی در این تلبع بررسی شده و در صورت درست بودن پول از حساب کاربر کم می شود
     */
    public static function verify()
    {
        $this->readStorage();
        $Status = 0;
        $Message = "";
        $RefID = "";


        if ($this->type == "soap" && $this->soap_check() === true) {


            $client = new SoapClient(config("config-gatway.ADDRESSSOAP"), [
                'encoding' => 'UTF-8',
                "location" => config("config-gatway.ADDRESSSOAP"),
                'trace' => 1,
                "exception" => 1,
            ]);
            $payment_verification = new PaymentVerification();
            $payment_verification->setMerchantId($this->MerchantID);
            $payment_verification->setAmount($this->Amount);
            $payment_verification->setAuthority($this->Authority);
            $result = $client->PaymentVerification(
                $payment_verification
            );
            $Status = isset($result->PaymentVerificationResult->Status) ? $result->PaymentVerificationResult->Status : 0;
            $RefID = (isset($result->PaymentVerificationResult->RefID)) ? $result->PaymentVerificationResult->RefID : "";
            $Message = self::error_message($Status, "", "");


        } elseif ($this->type == "rest" && $this->curl_check() === true) {
            $data = [
                "MerchantID" => $this->MerchantID,
                'Amount' => (int)$this->Amount,
                'Authority' => $this->Authority
            ];
            list($response, $http_status) = $this->getResponse(config("config-gatway.ADDRESSSOAP")_verify, $data);
            $Status = (isset($response->status) && $response->status != "") ? $response->status : 0;
            $RefID = (isset($response->refID) && $response->refID != "") ? $response->refID : "";
            $Message = self::error_message($Status, "", "", false);
        }

        return array(
            "Method" => $this->type,
            "Status" => $Status,
            "Message" => $Message,
            "Amount" => $this->Amount,
            "Mobile" => $this->mobile,
            "Email" => $this->email,
            "Description" => $this->Description,
            "RefID" => $RefID,
            "Authority" => $this->Authority
        );
    }

    public static function notVerify()
    {
        $this->readStorage();
        $Status = 0;
        $Message = "";

        return array(
            "Method" => $this->type,
            "Status" => $Status,
            "Message" => $Message,
            "Amount" => $this->Amount,
            "Mobile" => $this->mobile,
            "Email" => $this->email,
            "Description" => $this->Description,
            "Authority" => $this->Authority
        );
    }


    /*
     * برای چک کردن موارد ارسالی در فرم که عدد وارد شود خالی نباشد
     */
    public static function validationForm($data)
    {
        $error = [];
        if (empty($data['Amount']) || empty($data['MerchantID'])) {
            $error['fill'] = "فیلد های ستاره دار اجباری می باشد";
        }
        if (!filter_var($data['Amount'], FILTER_VALIDATE_INT)) {
            $error["Amount"] = "مقدار  مبلغ ارسالی عدد باشد.";
        }

        if ($data['Amount'] <= 1000) {

            echo $error["price-gt"] = "مقدار مبلغ ارسالی بزگتر از 1000 باشد";
        }

        if ($data['Mobile'] && !$this->perfix_mobile($data['Mobile'])) {

            echo $error["Mobile"] = " شماره موبایل باید با 98 شروع شود و یا تعداد اعداد وارد شده موبایل درست نیست";
        }

        if ($data['Email'] && !filter_var($data['Email'], FILTER_VALIDATE_EMAIL)) {

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
        $jsonData = json_encode($data);
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $jsonData,
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json'
            ),
        ));

        $response = curl_exec($curl);

        $http_status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        $response = json_decode($response);
        return [$response, $http_status];
    }

    /*
     * تابعی برای دریافت آدرس اجرای محل پروژه
     */
    public static function getUrl()
    {
        $protocl = "http:";
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') {
            $protocl = "https://";
        }
        $url = $protocl . '//' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']);

        /*
         * برای اینکه ادرس از مسیر مرور گر برداشته شده احتمال دارد اخرش به صورت پیش فرض / باشد اگر نبود گذاشته شود
         */
        if (substr($url, -1) != "/")
            $url = $url . "/";
        return $url;
    }
    
}