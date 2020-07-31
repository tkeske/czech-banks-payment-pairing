<?php

namespace App\Libraries\Mail\Mails;

use App\Libraries\Mail\Traits\GenericEmailTrait;

class KBEmail extends EmailBase {

    use GenericEmailTrait;

	public function __construct($conn, $msgNo, $mainClass) {
        parent::__construct($conn, $msgNo, $mainClass);

        $this->initializePaymentObject();
    }

    public function parseDate() {

        preg_match("'Datum splatnosti: (.*.[\r\n])'", $this->getBody(), $match);

		if (isset($match[1])) {
            $this->paymentObject->setDate((new \DateTime($match[1]))->format("Y-m-d H:i:s"));
		}
    }

    public function parseFromAccount() {
        preg_match("'Číslo protiúčtu: (.*\/.{4}.[\r\n])'", $this->getBody(), $match);

        var_dump($match);
		if (isset($match[1])) {
            $number = trim($match[1]);
            $this->paymentObject->setFromAccount($number);
		}
    }

    public function parseAmount() {
        preg_match("'Částka a měna: (.*,.{2} CZK.[\r\n])'", $this->getBody(), $match);
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

    public function parseConstantSymbol () {
        preg_match("'Konstantní symbol: (.*.[\r\n])'", $this->getBody(), $match);
		if (isset($match[1])) {
            $this->paymentObject->setCS($match[1]);
		}
    }

    public function parseMessage() {
        preg_match("'Zpráva pro příjemce: (.*.[\r\n])'", $this->getBody(), $match);
		if (isset($match[1])) {
            $this->paymentObject->setMessage($match[1]);
		}
    }

}
