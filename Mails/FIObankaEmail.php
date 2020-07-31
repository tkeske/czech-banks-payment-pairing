<?php

namespace App\Libraries\Mail\Mails;

use App\Libraries\Mail\Traits\GenericEmailTrait;

class FIObankaEmail extends EmailBase {

    use GenericEmailTrait;

	public function __construct($conn, $msgNo, $mainClass) {
        parent::__construct($conn, $msgNo, $mainClass);

        $this->initializePaymentObject();
    }

    public function parseDate() {

        //fio banka neuvadi cas zauctovani, bereme z hlavicky prichoziho emailu
        $this->paymentObject->setDate($this->getReceivedAtDateTime()->format("Y-m-d H:i:s"));
    }

    public function parseFromAccount() {
        preg_match("'Protiúčet: (.*\/.{4}.[\r\n])'", $this->getBody(), $match);
		if (isset($match[1])) {
            $number = trim($match[1]);
            $this->paymentObject->setFromAccount($number);
		}
    }

    public function parseAmount() {
        preg_match("'Částka: (.*,.{2}.[\r\n])'", $this->getBody(), $match);
		if (isset($match[1])) {
            $number = trim(str_replace(' CZK', '', $match[1]));
            $number = str_replace(',', '.', $number);
            $number = floatval($number);
            $this->paymentObject->setAmount($number);
		}
    }

    public function parseVariableSymbol() {
        preg_match("'VS: (.*.[\r\n])'", $this->getBody(), $match);
		if (isset($match[1])) {
            $this->paymentObject->setVS($match[1]);
		}
    }

    public function parseConstantSymbol () {
        preg_match("'KS: (.*.[\r\n])'", $this->getBody(), $match);
		if (isset($match[1])) {
            $this->paymentObject->setCS($match[1]);
		}
    }

    public function parseMessage() {
        preg_match("'Zpráva příjemci: (.*.[\r\n])'", $this->getBody(), $match);
		if (isset($match[1])) {
            $this->paymentObject->setMessage($match[1]);
		}
    }

}
