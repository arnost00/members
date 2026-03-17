<?php

if (!isset($g_external_is_connector))
{
	require_once('./cfg/_cfg.php');
}

require_once __DIR__ . '/lib/OrisIntegrationService.php';

interface ConnectorInterface {
	public function getSystemName(): string;
	public function getRaceURL(string $id): string;
	public function getRaceInfo(string $id);
}

class OrisCZConnector implements ConnectorInterface {
	private $sourceUrl = 'https://oris.ceskyorientak.cz/';
	private $service;

	public function __construct() {
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
			
			$classNames = [];
			if (isset($raceData['Classes'])) {
				foreach ($raceData['Classes'] as $class) {
					if (isset($class['Name'])) {
						$classNames[] = $class['Name'];
					}
				}
			}
			
			sort($classNames);
			$oddily = $this->getClubs($raceData);
			
			$date2 = ($raceData['Stages'] > 1) ? $this->getRaceDate($raceData['Stage'.$raceData['Stages']]) : 0;
			
			return new RaceDTO([
				'ext_id' => $raceData['ID'],
				'datum' => String2DateDMY(formatDate($raceData['Date'])),
				'datum2' => $date2,
				'nazev' => $raceData['Name'],
				'misto' => $raceData['Place'],
				'typ0' => 'Z',
				'typ' => $this->mapSport($raceData['Sport']['ID']), 
				'zebricek2' => $this->mapLevelToZebricek2($raceData['Level']['ID']),
				'ranking' => $raceData['Ranking'],
				'odkaz' => $this->getRaceURL($raceData['ID']),
				'prihlasky' => strtotime($raceData['EntryDate1']),
				'prihlasky1' => strtotime($raceData['EntryDate2']),
				'prihlasky2' => strtotime($raceData['EntryDate3']),
				'etap' => $raceData['Stages'],
				'vicedenni' => ($raceData['Stages']>1?1:0),
				'oddil' => $oddily,
				'modify_flag' => 0,
				'kategorie' => implode(';', $classNames ),
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
}

class ConnectorFactory {
	public static function create(): ?ConnectorInterface {
		global $g_external_is_connector;

		if ( $g_external_is_connector && class_exists( $g_external_is_connector)) {
			return new $g_external_is_connector();
		}

		return null;
	}
}
