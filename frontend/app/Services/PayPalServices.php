<?php

namespace App\Services;

use PayPal\PayPalClient;
use PayPal\Environment\SandboxEnvironment;

class PayPalService {
    public static function client() {
        $environment = new SandboxEnvironment(
            env('PAYPAL_CLIENT_ID'),
            env('PAYPAL_SECRET')
        );
        return new PayPalClient($environment);
    }
}
