<?php

class OrisException extends Exception {}

class OrisNetworkException extends OrisException {}

class OrisApiException extends OrisException {
    protected $apiStatus;
    protected $apiData;

    public function __construct($message, $apiStatus = null, $apiData = null, $code = 0, Throwable $previous = null) {
        parent::__construct($message, $code, $previous);
        $this->apiStatus = $apiStatus;
        $this->apiData = $apiData;
    }

    public function getApiStatus() {
        return $this->apiStatus;
    }

    public function getApiData() {
        return $this->apiData;
    }
}

class OrisValidationException extends OrisException {}
