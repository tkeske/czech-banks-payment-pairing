<?php

namespace App\Libraries\Mail\Mails;

class EmailBase {

    protected $conn;
	protected $msgNo;
	protected $uid;
	protected $mainClass = null;

	protected $header;
	protected $body;

	protected $date;
    protected $receivedAtDateTime;

    protected $convert;

    public function __construct($conn, $msgNo, $mainClass, $convert = false) {
		$this->mainClass = $mainClass;
		$this->conn      = $conn;
        $this->msgNo     = $msgNo;
        $this->convert   = $convert;
		$this->uid       = imap_uid($this->conn, $this->msgNo);

		$headerInfo = imap_headerinfo($this->conn, $this->msgNo);
        $this->receivedAtDateTime = new \DateTime($headerInfo->date);

        $this->loadHeader();
        $this->loadBody();
    }

    /*
	GETTERS
	 */

	/**
	 * get message body
	 * @return [type] [description]
	 */
	public function getBody() {

		return $this->body;

	}

	/**
	 * get message header
	 * @return [type] [description]
	 */
	public function getHeader() {
		return $this->header;
    }


	/**
	 * get message sender
	 * @return [type] [description]
	 */
	public function getSender() {
		$from = $this->header->from[0];
		return $from->mailbox . '@' . $from->host;
	}

	/**
	 * get message subject
	 * @return [type] [description]
	 */
	public function getSubject() {
		$subject = $this->header->subject;
		$subject = iconv_mime_decode($subject);
		return $subject;
    }

    public function getReceivedAtDateTime() {
        return $this->receivedAtDateTime;
    }

	/*
	SETTERS
	 */
	/**
	 * set as seen
	 */
	public function setSeen() {
		imap_setflag_full($this->conn, $this->msgNo, "\\Seen");
	}

	/**
	 * set as unseen
	 */
	public function setUnseen() {
		imap_clearflag_full($this->conn, $this->msgNo, "\\Seen");
	}

	/**
	 * set as flagged
	 */
	public function setFlagged() {
		imap_setflag_full($this->conn, $this->msgNo, "\\Flagged");
	}

	/**
	 * set as unflagged
	 */
	public function setUnflagged() {
		imap_clearflag_full($this->conn, $this->msgNo, "\\Flagged");
	}

	/* ----------------------------------------
	OTHER METHODS
	---------------------------------------- */

	/**
	 * chech if message was sent from specified account
	 * check if message contains specified account
	 *
	 * @return boolean [description]
	 */
	public function isOk($checkFlagged = true) {

		// check if message is flagged
		if ($checkFlagged && $this->isFlagged()) {
			return false;
        }

		// check
		if($this->receivedAtDateTime < $this->mainClass->checkFromDateTime)
			return false;

		return true;

	}

	/**
	 * return is is seen
	 * @return boolean [description]
	 */
	public function isFlagged() {
		return $this->header->Flagged == 'F' ? true : false;
	}

	public function isSeen() {
		return $this->header->Unseen == 'U' ? false : true;
    }

    	/* ----------------------------------------
	HELPER METHODS
	---------------------------------------- */

	/**
	 * return part of message
	 * @param  [type]  $mimetype   [description]
	 * @param  boolean $structure  [description]
	 * @param  boolean $partNumber [description]
	 * @return [type]              [description]
	 */
	public function getPart($mimetype, $structure = false, $partNumber = false) {

		if (!$structure) {
			$structure = imap_fetchstructure($this->conn, $this->uid, FT_UID);
		}

		if ($structure) {
			if ($mimetype == $this->getMimeType($structure)) {
				if (!$partNumber) {
					$partNumber = 1;
				}
				$text = imap_fetchbody($this->conn, $this->uid, $partNumber, FT_UID);
				switch ($structure->encoding) {
				case 3:return imap_base64($text);
				case 4:return imap_qprint($text);
				default:return $text;
				}
			}
			// multipart
			if ($structure->type == 1) {
				foreach ($structure->parts as $index => $subStruct) {
					$prefix = "";
					if ($partNumber) {
						$prefix = $partNumber . ".";
					}
					$data = $this->getPart($mimetype, $subStruct, $prefix . ($index + 1));
					if ($data) {
						return $data;
					}
				}
			}
		}
		return false;
	}

	/**
	 * get structure mime type
	 * @param  [type] $structure [description]
	 * @return [type]            [description]
	 */
	public function getMimeType($structure) {
		$primaryMimetype = ["TEXT", "MULTIPART", "MESSAGE", "APPLICATION", "AUDIO", "IMAGE", "VIDEO", "OTHER"];
		if ($structure->subtype) {
			return $primaryMimetype[(int) $structure->type] . "/" . $structure->subtype;
		}
		return "TEXT/PLAIN";
	}

	/**
	 * get message date
	 * @return [type] [description]
	 */
	public function getDate() {
		return $this->date;
	}

	/**
	 * load header into variable
	 * @return [type] [description]
	 */
	public function loadHeader() {
		$this->header = imap_header($this->conn, $this->msgNo);
	}

	/**
	 * load body
	 * @return [type] [description]
	 */
	public function loadBody() {

        $body       = $this->getPart("TEXT/PLAIN");

        if ($this->convert)
            $body       = iconv("windows-1250", "UTF-8//IGNORE", $body);

		$this->body = $body;

    }
}
