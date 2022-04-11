<?php
/**
 * Created by PhpStorm.
 * User: shahriar
 * Date: 3/9/19
 * Time: 3:18 PM
 */

namespace Rayanpay\RayanGate\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentVerification extends Model
{

    protected $fillable = [
        "RefID",
        "Status",
        "payment_request_id",
    ];
    
}
