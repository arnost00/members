<?php

// Define a class to represent the race data
class Race {
    public $ext_id;
    public $datum;
    public $datum2;
    public $nazev;
    public $misto;
    public $typ;
    public $zebricek2;
    public $ranking;
    public $odkaz;
    public $prihlasky;
    public $prihlasky1;
    public $prihlasky2;
    public $prihlasky3;
    public $prihlasky4;
    public $prihlasky5;
    public $etap;
    public $poznamka;
    public $vicedenni;
    public $oddil;
    public $modify_flag;
    public $kategorie;

    // Constructor to initialize the object with key-value pairs
    public function __construct($data) {
        $this->ext_id = $data['ext_id'] ?? null;
        $this->datum = $data['datum'] ?? null;
        $this->datum2 = $data['datum2'] ?? null;
        $this->nazev = $data['nazev'] ?? null;
        $this->misto = $data['misto'] ?? null;
        $this->typ = $data['typ'] ?? null;
        $this->zebricek2 = $data['zebricek2'] ?? null;
        $this->ranking = $data['ranking'] ?? null;
        $this->odkaz = $data['odkaz'] ?? null;
        $this->prihlasky = $data['prihlasky'] ?? null;
        $this->prihlasky1 = $data['prihlasky1'] ?? null;
        $this->prihlasky2 = $data['prihlasky2'] ?? null;
        $this->prihlasky3 = $data['prihlasky3'] ?? null;
        $this->prihlasky4 = $data['prihlasky4'] ?? null;
        $this->prihlasky5 = $data['prihlasky5'] ?? null;
        $this->etap = $data['etap'] ?? null;
        $this->poznamka = $data['poznamka'] ?? null;
        $this->vicedenni = $data['vicedenni'] ?? null;
        $this->oddil = $data['oddil'] ?? null;
        $this->modify_flag = $data['modify_flag'] ?? null;
        $this->kategorie = $data['kategorie'] ?? null;
    }
}

interface ConnectorInterface {
    public function getSystemName(): string;
    public function getRaceURL(string $id);
    public function getRaceInfo(string $id);
}

class OrisCZConnector implements ConnectorInterface {
	private $sourceUrl = 'https://oris.orientacnisporty.cz/';
    private $apiUrl;

    public function __construct() {
        $this->apiUrl = $this->sourceUrl . 'API/';
    }


    // Method to get the system name
    public function getSystemName(): string {
        return "Oris";
    }

	// Race URL
	public function getRaceURL($raceId) : string {
		return $this->sourceUrl . 'Zavod?id=' . $raceId;
	}

    // Method to get detailed race information based on race ID
    public function getRaceInfo($raceId) {
        $url = $this->apiUrl . '?format=json&method=getEvent&id=' . $raceId;

        $response = $this->makeRequest($url);

        if ($response && $response['Status'] == "OK") {
            $raceData = $response['Data'];
            
			$classNames = [];
			if (isset($raceData['Classes'])) {
				foreach ($raceData['Classes'] as $class) {
					if (isset($class['Name'])) {
						$classNames[] = $class['Name'];
					}
				}
			}
			
			sort($classNames);

			$oddily = [];
			if (isset($raceData['Org1']['Abbr'])) {
				$oddily[] = $raceData['Org1']['Abbr'];
			}
			if (isset($raceData['Org2']['Abbr'])) {
				$oddily[] = $raceData['Org2']['Abbr'];
			}
			
			sort($classNames);
            
            // Use associative array to pass data to constructor
            return new Race([
                'ext_id' => $raceData['ID'],
                'datum' => String2DateDMY(formatDate($raceData['Date'])),
//                'date2' => $raceData['Date2'],
                'nazev' => $raceData['Name'],
                'misto' => $raceData['Place'],
//                'category' => $raceData['Category'],
				 //typ0 => Typ akce
                'typ0' => 'Z',
				//typ => Sport
                'typ' => $raceData['Sport']['NameCZ'],
				//TODO domyslet zebricek
				'zebricek2' => 0x0080,
				'ranking' => $raceData['Ranking'],
				'odkaz' => $this->getRaceURL($raceData['ID']),
				'prihlasky' => strtotime($raceData['EntryDate1']),
				'prihlasky1' => strtotime($raceData['EntryDate2']),
				'prihlasky2' => strtotime($raceData['EntryDate3']),
//				'prihlasky3' => '',
//				'prihlasky4' => '',
//				'prihlasky5' => '',
				'etap' => $raceData['Stages'],
//				'poznamka' => $poznamka,
				'vicedenni' => ($raceData['Stages']>1?1:0),
				'oddil' => implode('+', $oddily),
				//TODO vyresit druhy oddil
				//TODO na co je modify_flag???
				'modify_flag' => 0,
				'kategorie' => implode(';', $classNames )
				]);
        } else {
            return null; // Return null if race not found or error
        }
    }

    // Helper method to make HTTP requests
    private function makeRequest($url) {
		$response = file_get_contents($url);
		
		// Decode JSON response
        return json_decode($response, true);
	}
    
    private function makeRequestCurl($url) {
        $ch = curl_init($url);

        // Set curl options
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);

        // Check for errors
        if (curl_errno($ch)) {
            echo 'Curl error: ' . curl_error($ch);
            return null;
        }

        curl_close($ch);

        // Decode JSON response
        return json_decode($response, true);
    }
}

class ConnectorFactory {
    public static function create(): ConnectorInterface {
//        $config = require 'config.php';
//        $connectorName = $config['connector'];
		$connectorName = 'OrisCZConnector';

        if (class_exists($connectorName)) {
            return new $connectorName();
        }

        throw new Exception("Connector '$connectorName' not found.");
    }
}
