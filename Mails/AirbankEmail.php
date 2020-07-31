<?php

namespace App\Libraries\Mail\Mails;

use App\Libraries\Mail\Mails\EmailBase;
use App\Libraries\Mail\Traits\GenericEmailTrait;

class AirbankEmail extends EmailBase {

    use GenericEmailTrait;

	public function __construct($conn, $msgNo, $mainClass) {
        parent::__construct($conn, $msgNo, $mainClass);

        $this->initializePaymentObject();
    }

	public function parseDate() {

        preg_match("'Datum zaúčtování: (.*)'", $this->getBody(), $match);

		if (isset($match[1])) {
            $this->paymentObject->setDate((new \DateTime($match[1]))->format("Y-m-d H:i:s"));
		}
    }

    public function parseFromAccount() {
        preg_match("'číslo (.*\/.{4}.[\r\n])'", $this->getBody(), $match);
		if (isset($match[1])) {
            $number = trim($match[1]);
            $this->paymentObject->setFromAccount($number);
		}
    }

    public function parseAmount() {
        preg_match("'Částka: (.*,.{2} CZK.[\r\n])'", $this->getBody(), $match);
		if (isset($match[1])) {
            $number = trim(str_replace(' CZK', '', $match[1]));
            $number = str_replace(',', '.', $number);
            $number = floatval($number);
            $this->paymentObject->setAmount($number);
		}
    }

    public function parseVariableSymbol() {
        preg_match("'Variabilní symbol: (.*.[\r\n])'", $this->getBody(), $match);
		if (isset($match[1])) {
            $this->paymentObject->setVS($match[1]);
		}
    }

    public function parseConstantSymbol() {
        preg_match("'Konstantní symbol: (.*.[\r\n])'", $this->getBody(), $match);
		if (isset($match[1])) {
            $this->paymentObject->setCS($match[1]);
		}
    }

    public function parseMessage() {
        //todo get email from bank with message for recipient
    }
}
