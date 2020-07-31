<?php

namespace App\Libraries\Mail\Traits;

use App\Libraries\Mail\Payment;

trait GenericEmailTrait {

    protected $paymentObject;

    public function initializePaymentObject() {
        $this->paymentObject = new Payment();
    }

    public function parse() {
        $this->parseDate();
        $this->parseFromAccount();
        $this->parseAmount();
        $this->parseVariableSymbol();
        $this->parseConstantSymbol();
        $this->parseMessage();
    }

    public function logPayment($tenantPrefix) {
        $this->paymentObject->logPayment($tenantPrefix);
    }

}
