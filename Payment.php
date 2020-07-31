<?php

namespace App\Libraries\Mail;

use App\Repositories\BankAccountPaymentRepository;

class Payment {

protected $string = null;

protected $date           = null;
protected $fromAcccount   = null;
protected $amount         = null;
protected $variableSymbol = null;
protected $constantSymbol = null;
protected $message        = null;

protected $state          = null;

public function setString(string $string) {
    $this->string = $string;
}

public function setVS(string $vs) {
    $this->variableSymbol = $vs;
}

public function setCS(string $cs) {
    $this->constantSymbol = $cs;
}

public function setAmount(string $amount) {
    $this->amount = $amount;
}

public function setFromAccount(string $fa) {
    $this->fromAcccount = $fa;
}

public function setDate($date) {
    $this->date = $date;
}

public function setState(string $state) {
    $this->state = $state;
}

public function setMessage(string $message) {
    $this->message = $message;
}

/**
 * get message string
 * @return [type] [description]
 */
public function getString() {
    return $this->string;
}

public function getVS() {
    // add retype to remove leading zeros
    return (int) $this->variableSymbol;
}

public function getCS() {
    // add retype to remove leading zeros
    return (int) $this->constantSymbol;
}

public function getAmount() {
    return $this->amount;
}

public function getFromAccount() {
    return $this->fromAcccount;
}

public function getDate() {
    return $this->date;
}

public function getState() {
    return $this->state;
}

public function getInvestment() {
    return $this->investment;
}


public function getMessage() {
    return $this->message;
}

// save payment into database
public function logPayment($tenantPrefix) {

    $paymentArray = [
        'date'            => $this->getDate(),
        'from_account'    => $this->getFromAccount(),
        'amount'          => $this->getAmount(),
        'variable_symbol' => $this->getVS(),
        'constant_symbol' => $this->getCS(),
        'message'         => $this->getMessage() ? $this->getMessage(): '' ,
        'state'           => $this->getState() ? $this->getState(): '',
        'string'          => $this->getString() ? $this->getString(): ''
    ];

    $repository = new BankAccountPaymentRepository();
    $repository->createRecord($tenantPrefix, $paymentArray);

}

}
