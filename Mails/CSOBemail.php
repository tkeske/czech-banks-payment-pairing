<?php

namespace App\Libraries\Mail\Mails;

use App\Libraries\Mail\Mails\EmailBase;
use App\Libraries\Mail\Payment;

class CSOBEmail extends EmailBase {

    protected $paymentObjects = [];

	public function __construct($conn, $msgNo, $mainClass) {

        parent::__construct($conn, $msgNo, $mainClass, true);

        $this->getPayments();
    }

    public function setPaymentObjects(array $paymentObjects) {
        $this->paymentObjects = $paymentObjects;
    }

    public function getPaymentObjects() {
        return $this->paymentObjects;
    }

	/**
	 * get message payments
	 * @return [type] [description]
	 */
	public function getPayments() {

		$return        = [];
		$paymentsArray = [];

		if (strpos($this->body, 'zaúčtovaná transakce') !== false) {
			$paymentsArray = explode('zaúčtovaná transakce', $this->body);
			unset($paymentsArray[0]); // remove first part
		} else if (strpos($this->body, 'zaúčtován hotovostní vklad') !== false) {
			$paymentsArray = explode('zaúčtován hotovostní vklad', $this->body);
			unset($paymentsArray[0]); // remove first part
		}

		foreach ($paymentsArray as $payment) {

			// remove last part from payment string
			$paymentArray  = explode('Zůstatek na účtu po zaúčtování transakce:', $payment);
            $paymentString = $paymentArray[0];
            $paymentObject = new Payment();
            $paymentObject->setString($paymentString);
            $paymentObject->setDate($this->date);
			$return[]      = $paymentObject;

		}

        $this->setPaymentObjects($return);

        return $this->getPaymentObjects();

	}

    public function parse() {
        $this->parseAmounts();
        $this->parseFromAccounts();
        $this->parseConstantSymbols();
        $this->parseVariableSymbols();
        $this->parseMessages();
        $this->parseDates();
    }

    public function parseAmounts() {

        foreach($this->getPaymentObjects() as $object) {
            preg_match("'částka (.*?)[\n\t]'si", $object->getString(), $match);
            $object->setAmount(isset($match[1]) ? intval($match[1]) : null);
        }

    }

    public function parseFromAccounts() {

        foreach($this->getPaymentObjects() as $object){
            preg_match("'z účtu (.*?)[\n\t]'si", $object->getString(), $match);
            $object->setFromAccount(isset($match[1]) ? $match[1] : null);
        }
    }

    public function parseConstantSymbols() {

        foreach($this->getPaymentObjects() as $object) {

            preg_match("'KS (.*?)[\n\t]'si", $object->getString(), $match);
            $object->setConstantSymbol(isset($match[1]) ? $match[1] : null);
        }
    }

    public function parseVariableSymbols() {

        foreach($this->getPaymentObjects() as $object) {

            preg_match("'VS (.*?)[\n\t]'si", $$object->getString(), $match);
            $object->setVariableSymbol(isset($match[1]) ? $match[1] : null);
        }
    }

    public function parseMessages() {

        foreach($this->getPaymentObjects() as $object) {
            preg_match("'zpráva:(.*?)[\n\t]'si", $$object->getString(), $match);
            $object->setMessage(isset($match[1]) ? $match[1] : null);
            if ($object->getMessage() == null) {
                preg_match("'zpráva pro příjemce:(.*?)[\n\t]'si", $$object->getString(), $match);
                $object->setMessage(isset($match[1]) ? $match[1] : null);
                if ($object->getMessage() == null) {
                    preg_match("'zpráva pro příjemce:\n(.*?)[\n\t]'si", $$object->getString(), $match);
                    $object->setMessage(isset($match[1]) ? $match[1] : null);
                }
            }
        }
    }

	public function parseDates() {

		preg_match("'dne (.*?) byla'si", $this->getBody(), $match);
		if (isset($match[1])) {
			$this->date = (new \DateTime($match[1]))->format("Y-m-d H:i:s");
		}

    }

    public function logPayment($tenantPrefix) {
        foreach ($this->getPaymentObjects() as $object) {
            $object->logPayment($tenantPrefix);
        }
    }

}
