<?php

namespace App\Libraries\Mail;

use App\Libraries\Mail\Mails\CSOBEmail;
use App\Libraries\Mail\Mails\AirbankEmail;
use App\Libraries\Mail\Mails\FIObankaEmail;
use App\Libraries\Mail\Mails\KBEmail;
use App\Libraries\Mail\Mails\TatraBankaEmail;
use App\Repositories\BankAccountCheckRepository;

class GetEmails {

	protected $emailConnection = null;
	protected $mailsSender     = null;
	protected $accountNmb      = null;

    public $checkFromDateTime = null;

    protected $mailbox;

    protected $email;

    protected $password;

    protected $tenantPrefix;

	public function __construct($email,$password, $tenantPrefix) {

        //$domain = $this->getMailDomain($email);
        $this->tenantPrefix = $tenantPrefix;
        $this->mailbox = "{imap-190539.m39.wedos.net:143}INBOX";
        $this->email = $email;
        $this->password = $password;

        $bankAccountCheckRepository = new BankAccountCheckRepository();

		$lastBankAccountcheck = $bankAccountCheckRepository->getLastCheckRecord($tenantPrefix);
		if($lastBankAccountcheck != null)
			$this->checkFromDateTime = new \DateTime($lastBankAccountcheck->created_at);


		// log new check
		$bankAccountCheckRepository->createRecord($tenantPrefix);

    }

    //public function getMailDomain($email) {
    //    $ret = explode("@", $email);
    //
    //    return $ret[1];
    //}

	public function getMailsSender() {
		return $this->mailsSender;
	}

	public function getAccountNmb() {
		return $this->accountNmb;
    }

    public function getTenantPrefix() {
        return $this->tenantPrefix;
    }

	/**
	 * retun received payments
	 * @return [type] [description]
	 */
	public function processReceivedPayments() {

		$this->checkEmailConnection();
		$this->loopEmails();
		$this->closeConnection();

	}

	/**
	 * loop emails from emailConnection
	 * @return [type] [description]
	 */
	public function loopEmails() {

		$emailsCount = imap_num_msg($this->emailConnection);

		$processedPayments = [];

		for ($msgNo = 1; $msgNo <= $emailsCount; $msgNo++) {

            $emailFrom = $this->getSender($this->emailConnection, $msgNo);

            switch($emailFrom) {

                case "info@airbank.cz":
                    $msg = new AirbankEmail($this->emailConnection, $msgNo, $this);
                    break;

                case "administrator@tbs.csob.cz":
                    $msg = new CSOBEmail($this->emailConnection, $msgNo, $this);
                    break;

                case "fio":
                    $msg = new FIObankaEmail($this->emailConnection, $msgNo, $this);
                    break;

                case "info@kb.cz":
                    $msg = new KBEmail($this->emailConnection, $msgNo, $this);
                    break;
            }

			if ($msg->isOk()) {

                $msg->parse();
                $msg->logPayment($this->getTenantPrefix());
				$msg->setFlagged();
			}

		}

	}

    public function getSender($connection, $msgNo) {

        $header = imap_header($connection, $msgNo);
        $from = $header->from[0];
		return $from->mailbox . '@' . $from->host;
    }


	/*
	HELPERS
	 */

	/**
	 * check if connection exists, if not, create new one
	 * @return [type] [description]
	 */
	public function checkEmailConnection() {

		if ($this->emailConnection == null) {
			$this->openConnection();
		}

	}

	/**
	 * open connection
	 * @return [type] [description]
	 */
	public function openConnection() {
		$this->emailConnection = imap_open(
			$this->mailbox,
			$this->email,
			$this->password
		);
	}

	/**
	 * close connection
	 * @return [type] [description]
	 */
	public function closeConnection() {
		imap_close($this->emailConnection);
	}

}
