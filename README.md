<p align="center"><a href="https://www.rayanmehr.co.ir/" target="_blank"><img src="https://www.rayanmehr.co.ir/wp-content/uploads/2019/12/logo.png" width="400"></a></p>

<p align="center">
<a href="https://travis-ci.org/laravel/framework"><img src="https://travis-ci.org/laravel/framework.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/badge/packagist-v1-blue" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>


##نحوه نصب

````
$ composer require rayanpay/rayan-gate

````
### تنظیمان لازم

- فقط دقت داشته باشید شما برای اتصال به درگاه فروشگاه خود باید شناسه خود را با پارامتر ``MERCHANTID`` در فایل .env خود قرار دهید.

- همچنین نوع اتصال به سرور های رایان پی را با پارمتر ``TYPEGATEWAY``

## اتصال به درگاه پرداخت اینترنتی رایان پی

با استفاده از این پکیج می توانید به درگاه پرداخت اینترنتی رایان پی متصل شوید که برا استفاده از این پکیج می توانید مانند نمونه کد زیر اجرا کنید 

````
Route::get('/payment', function () {
    $payment_request = new \Rayanpay\RayanGate\Objects\PaymentRequest();
    $payment_request->setDescription(json_encode(["name"=>"shahriar","lastname"=>"pahlevansadgh"]));
    $payment_request->setAmount( 15000);
    $payment_request->setEmail("rayanpay@gmail.com");
    $payment_request->setMobile( "989120308527");
    $result = \Rayanpay\RayanGate\Services\RayanPayServices::request($payment_request);
    if(data_get($result,"Status") == 100)
        \Rayanpay\RayanGate\Services\RayanPayServices::redirect(data_get($result,"StartPay"));
    return response()->json($result,200);
});

````






لاراول گسترده‌ترین و کامل‌ترین [اسناد] (https://laravel.com/docs) و کتابخانه آموزشی ویدیویی را در بین تمام فریم‌ورک‌های کاربردی وب مدرن دارد که شروع کار با فریم‌ورک را آسان می‌کند.

اگر حوصله خواندن ندارید، [Laracasts](https://laracasts.com) می تواند کمک کند. Laracasts شامل بیش از 1500 آموزش ویدیویی در زمینه موضوعات مختلف از جمله لاراول، PHP مدرن، تست واحد و جاوا اسکریپت است. با جستجو در کتابخانه جامع ویدیویی ما، مهارت های خود را تقویت کنید.


## آسیب پذیری های امنیتی

اگر آسیب‌پذیری امنیتی در پکیج کشف کردید، لطفاً از طریق [support@apachish.ir] (mailto:support@apachish.ir) یک ایمیل به شهریار پهلوان صادق ارسال کنید. تمام مشکلات به سرعت برطرف خواهد شد.

## مجوز

فریم ورک لاراول یک نرم افزار منبع باز است که تحت مجوز [MIT license](https://opensource.org/licenses/MIT).
