<?php

if (!isset($g_external_is_connector))
{
	require_once('./cfg/_cfg.php');
}

require_once __DIR__ . '/lib/OrisIntegrationService.php';

class RacePayement {
    public int $raceId;
    public RaceOverview $overview;
    /** @var array<int, RaceParticipant> RegNo => User */
    public array $participants = [];

    public function __construct(int $raceId) {
        $this->raceId = $raceId;
        $this->overview = new RaceOverview();
    }

    public function addPatricipant(RaceParticipant $user): void {
        $this->participants[$user->regNo] = $user;
    }

	public function addCategory(string $name, int $feeTier, int $fee ): void {
		$this->overview->addCategory($name,$feeTier,$fee);
	}

	public function addService(string $name, int $fee, int $count ): void {
		$this->overview->addService( $name, $fee, $count );
	}
}

class RaceOverview {
    /** @var array<string, array<int, float>> Category => [EntryStop => Fee] */
    public array $categories = [];

    /** @var array<string, array<float,int>> ServiceName => [ Fee => Count ]  */
    public array $services = [];

    /** @var array<int, bool> feeTier => exist */
    public array $feeTiers = [];

    public function addCategory(string $name, int $feeTier, int $fee ): void {

		if (!isset($this->categories[$name])) {
			$this->categories[$name] = [];
		}
		$this->categories[$name][$feeTier] = $fee;

		// Store only unique sorted feeTier values
    	$this->feeTiers[$feeTier] = true;
    }

    public function addService(string $name, int $fee, int $count ): void {
		if ( isset ( $this->services[$name][$fee] ) ) {
        	$this->services[$name][$fee] += $count;
		} else {
        	$this->services[$name][$fee] = $count;
		}
    }
}

class RaceParticipant {
    public string $regNo;
    public string $classDesc;
    public string $name;
    public bool $rentSI;
    public string $licence;
	public $fee;
	public int $feeTier;

    public function __construct(string $regNo, string $classDesc, string $name, bool $rentSI, string|null $licence, int $fee, int $feeTier) {
        $this->regNo = $regNo;
        $this->classDesc = $classDesc;
        $this->name = $name;
        $this->rentSI = $rentSI;
        $this->licence = $licence ?? '';
		$this->fee = $fee;
		$this->feeTier = $feeTier;
    }
}

interface ConnectorInterface {
	public function getSystemName(): string;
	public function getRaceURL(string $id): string;
	public function getRaceInfo(string $id);
	public function getRacesList($fromDate, $toDate);
	public function getRacePayement(string $id) : ?RacePayement;
}

class OrisCZConnector implements ConnectorInterface {
	private $sourceUrl = 'https://oris.ceskyorientak.cz/';
	private $apiUrl;
	private $service;

	public function __construct() {
		$this->apiUrl = $this->sourceUrl . 'API/';
		$this->service = new OrisIntegrationService(null);
	}

	public function getSystemName(): string {
		return "Oris";
	}

	public function getRaceURL($raceId) : string {
		return $this->sourceUrl . 'Zavod?id=' . $raceId;
	}

	private function mapLevelToZebricek2($levelId) {
		$map = [
			1  => 17, // MCR
			3  => 6,  // ZB
			4  => 8,  // OZ
			7  => 33, // CPS
			8  => 1,  // CP
			11 => 24,  // OM
			17 => 17   // VET
		];
		return $map[$levelId] ?? 0x0080; // Default to 0x80 if not found
	}

	private function mapSport($sportId) {
		$map = [
			1  => 1, // OB
			2  => 4,  // LOB
			3  => 2,  // MTBO
			4  => 8, // TRAIL
		];
		return $map[$sportId] ?? 1; // Default is OB
	}

	private function getClubs(&$raceData) {
		$oddily = [];
		if (isset($raceData['Org1']['Abbr'])) {
			$oddily[] = $raceData['Org1']['Abbr'];
		}
		if (isset($raceData['Org2']['Abbr'])) {
			$oddily[] = $raceData['Org2']['Abbr'];
		}
		return implode('+', $oddily);
	}

	public function getRaceDate($raceId) {
		try {
			$raceData = $this->service->getEvent($raceId);
			if (isset($raceData['Date'])) {
				return String2DateDMY(formatDate($raceData['Date']));
			}
		} catch (OrisException $e) {
			// fallback
		}
		return '';
	}

	public function getRaceInfo($raceId) {
		try {
			$raceData = $this->service->getEvent($raceId);

			$classFees = [];
			if (isset($raceData['Classes'])) {
				foreach ($raceData['Classes'] as $class) {
					if (isset($class['Name'])) {
						$name = $class['Name'];
						$fee  = $class['Fee'] ?? null;
						$classFees[$name] = $fee;
					}
				}
			}

			ksort($classFees);

			$oddily = $this->getClubs($raceData);
			$oblasti = [];
			if (isset($raceData['Regions'])) {
				foreach ($raceData['Regions'] as $tag => $region) {
					$oblasti[] = $region['ID'];
				}
			}

			$date2 = ($raceData['Stages'] > 1) ? $this->getRaceDate($raceData['Stage'.$raceData['Stages']]) : 0;

			return new RaceDTO([
				'ext_id' => $raceData['ID'],
				'datum' => String2DateDMY(formatDate($raceData['Date'])),
				'datum2' => $date2,
				'nazev' => $raceData['Name'],
				'misto' => $raceData['Place'],
				'oblasti' => $oblasti,
				'typ0' => 'Z',
				'typ' => $this->mapSport($raceData['Sport']['ID']),
				'zebricek2' => $this->mapLevelToZebricek2($raceData['Level']['ID']),
				'ranking' => $raceData['Ranking'],
				'odkaz' => $this->getRaceURL($raceData['ID']),
				'prihlasky' => strtotime($raceData['EntryDate1']),
				'prihlasky1' => strtotime($raceData['EntryDate2']),
				'prihlasky2' => strtotime($raceData['EntryDate3']),
				'koeficient1' => $raceData['EntryKoef2'],
				'koeficient2' => $raceData['EntryKoef3'],
				'etap' => $raceData['Stages'],
				'vicedenni' => ($raceData['Stages']>1?1:0),
				'oddil' => $oddily,
				'modify_flag' => 0,
				'kategorie' => implode(';', array_keys($classFees)),
				'startovne' => $classFees,
				'cancelled' => (!empty($raceData['Cancelled']) || !empty($raceData['Canceled']) || !empty($raceData['cancelled']) || !empty($raceData['canceled'])) ? 1 : 0,
				'oris_entry_start' => !empty($raceData['EntryStart']) ? $raceData['EntryStart'] : null
			]);
		} catch (OrisException $e) {
			return null;
		}
	}

	function getRacesList($fromDate, $toDate) {
		try {
			$racesData = $this->service->getEventList($fromDate, $toDate, 1);
			$rows = array();
			foreach($racesData as $oneRace) {
				$oddily = $this->getClubs($oneRace);

				$row = array();
				$row[] = $oneRace['ID'];
				$row[] = $oneRace['Date'];
				$row[] = $oneRace['Name'];
				$row[] = $oddily;
				$rows[] = $row;
			}
			return $rows;
		} catch (OrisException $e) {
			return null;
		}
	}

	// Helper method to make HTTP requests (used by getRacePayement)
	private function makeRequest($url) {
		$response = file_get_contents($url);

		// Decode JSON response
		return json_decode($response, true);
	}

	public function getRacePayement($raceId) : ?RacePayement {

		global $g_external_is_club_id;

		if ( !IsSet ($g_external_is_club_id) || $g_external_is_club_id === '' ) return null;

		$url = $this->apiUrl . '?format=json&method=getEventEntries&clubid=' . $g_external_is_club_id . '&eventid=' . $raceId;

		$response = $this->makeRequest($url);
		$racePayement = null;

		if ($response && $response['Status'] == "OK") {
			$racePayement = new RacePayement($raceId);

			foreach ($response['Data'] as $entry) {
				if (isset($entry['Fee'])) {
					if (isset($entry['RegNo']) ) {
						$racePayement->addPatricipant(
							new RaceParticipant($entry['RegNo'], $entry['ClassDesc'], $entry['Name'],
							 $entry['RentSI'], $entry['Licence'], (int)$entry['Fee'], $entry['EntryStop']));
					}
					if (isset($entry['ClassDesc'])&&isset($entry['ClassDesc'])) {
						$racePayement->addCategory($entry['ClassDesc'], $entry['EntryStop'], (int)$entry['Fee']);
					}
				}
			}
		}

		$url = $this->apiUrl . '?format=json&method=getEventServiceEntries&clubid=' . $g_external_is_club_id . '&eventid=' . $raceId;

		$response = $this->makeRequest($url);

		if ($response && $response['Status'] == "OK") {
			if ( $racePayement ===  null ) $racePayement = new RacePayement($raceId);
			foreach ($response['Data'] as $entry) {
				if ( isset ( $entry['Service'] ) ) {
					$racePayement->addService($entry['Service']['NameCZ'] ?? 'Name?', $entry['Service']['UnitPrice'] , $entry['Quantity'] );
				} else {
					if ( isset ( $entry['Quantity'] ) && isset ( $entry['TotalFee'] ) ) {
						$racePayement->addService('Name?', $entry['TotalFee'] / $entry['Quantity'], $entry['Quantity'] );
					}
				}
			}
		}

		return $racePayement;
	}
}

class ConnectorFactory {
	public static function create(): ?ConnectorInterface {
		global $g_external_is_connector;

		if ( $g_external_is_connector && class_exists( $g_external_is_connector)) {
			return new $g_external_is_connector();
		}

		return null; // Return null explicitly if no valid connector is found
	}
}
