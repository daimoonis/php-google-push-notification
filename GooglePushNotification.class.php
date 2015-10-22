<?php

namespace App;

/**
 * Description of GooglePushNotification
 *
 * @author Daimoonis
 */
class GooglePushNotification {

	const ERR_CURL_QUERY = 5;
	
	const GOOGLE_API_KEY = "<YOUR_API_KEY>";
	const GOOGLE_GCM_URL = "https://android.googleapis.com/gcm/send";

	private $aRegistrationIDs;
	private $message;
	private $aExtraData;
	private $collapseKey;

	public function __construct($message) {
		$this->aRegistrationIDs = $this->aExtraData = array();
		$this->message = $message;
	}

	/**
	 * 
	 * @param array $registrationIDs
	 * @param string $message
	 * @return object
	 * @throws \ErrorException
	 */
	private function sendGcmNotification() {
		$fields = array(
			'registration_ids' => $this->aRegistrationIDs,
			'data' => array("message" => $this->message),
		);

		if (!is_null($this->collapseKey)) {
			$fields['collapse_key'] = $this->collapseKey;
		}

		if (count($this->aExtraData) > 0) {
			$fields['data'] = array_merge($this->aExtraData, $fields['data']);
		}

		$headers = array(
			'Authorization: key=' . self::GOOGLE_API_KEY,
			'Content-Type: application/json'
		);

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, self::GOOGLE_GCM_URL);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));

		$result = curl_exec($ch);
		if ($result === FALSE) {
			throw new \ErrorException('Curl no response data: ' . curl_error($ch), self::ERR_CURL_QUERY);
		}

		curl_close($ch);
		return json_decode($result);
	}

	public function addRegistrationID($sID) {
		if (!in_array($sID, $this->aRegistrationIDs)) {
			array_push($this->aRegistrationIDs, $sID);
		}
	}

	/**
	 * metoda pro přidání společných dat do odeslání zprávy např. čas
	 * 
	 * @param string $key
	 * @param string $value
	 */
	public function addExtraData($key, $value) {
		$this->aExtraData[$key] = $value;
	}

	public function setCollapseKey($value) {
		$this->collapseKey = $value;
	}

	/*
	 * @return object
	 * @throws \ErrorException - error executing curl query
	 */
	public function send() {
		if (count($this->aRegistrationIDs) == 0) {
			$oResult = new \stdClass();
			$oResult->success = FALSE;
			return $oResult;
		}

		return $this->sendGcmNotification();
	}

}
