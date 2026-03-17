<?php

class RaceDTO {
    public ?string $ext_id;
    public ?string $datum;
    public ?string $datum2;
    public ?string $nazev;
    public ?string $misto;
    public ?int $typ;
    public ?int $zebricek2;
    public ?string $ranking;
    public ?string $odkaz;
    public ?int $prihlasky;
    public ?int $prihlasky1;
    public ?int $prihlasky2;
    public ?int $prihlasky3;
    public ?int $prihlasky4;
    public ?int $prihlasky5;
    public ?int $etap;
    public ?string $poznamka;
    public ?int $vicedenni;
    public ?string $oddil;
    public ?int $modify_flag;
    public ?string $kategorie;
    public ?string $oris_entry_start;
    public ?string $typ0;

    public function __construct(array $data) {
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
        $this->oris_entry_start = $data['oris_entry_start'] ?? null;
        $this->typ0 = $data['typ0'] ?? null;
    }
}

class OrisEntryRequestDTO {
    public ?string $clubuser;
    public ?string $classId;
    public ?string $si;
    public int $rentSi;
    public ?string $note;
    public ?string $entryId;

    public function __construct(
        ?string $clubuser,
        ?string $classId,
        ?string $si,
        bool $rentSi = false,
        ?string $note = null,
        ?string $entryId = null
    ) {
        $this->clubuser = $clubuser;
        $this->classId = $classId;
        $this->si = $si;
        $this->rentSi = $rentSi ? 1 : 0;
        $this->note = $note;
        $this->entryId = $entryId;
    }

    public function toArray(): array {
        $data = [];
        if ($this->clubuser !== null) $data['clubuser'] = $this->clubuser;
        if ($this->classId !== null) $data['class'] = $this->classId;
        if ($this->si !== null && $this->si !== '') $data['si'] = $this->si;
        if ($this->rentSi) $data['rent_si'] = 1;
        if ($this->note !== null && $this->note !== '') $data['note'] = $this->note;
        if ($this->entryId !== null) $data['entryid'] = $this->entryId;
        return $data;
    }
}
