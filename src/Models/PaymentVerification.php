<?php
/**
 * Created by PhpStorm.
 * User: shahriar
 * Date: 3/9/19
 * Time: 3:18 PM
 */

namespace Rayanpay\RayanGate\Models;


use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class PaymentVerification extends Model
{

    protected $fillable = [
        "MerchantId",
        "Amount",
        "Authority",
    ];
    
}
