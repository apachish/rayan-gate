<?php
/**
 * Created by PhpStorm.
 * User: shahriar
 * Date: 3/9/19
 * Time: 3:18 PM
 */

namespace Rayanpay\RayanGate\Models;


use Illuminate\Database\Eloquent\Model;

class PaymentRequest extends Model
{

    protected $fillable = [
        "Amount",
        "CallbackURL",
        "Description",
        "Email",
        "MerchantId",
        "Mobile",
    ];

    public function __construct()
    {
        $this->CallbackURL = 
    }

}
