<?
class Member {

    private $id;

    public function __construct($id) {
        $this->id = $id;
        return $this;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function getId() {
        return $this->id;
    }

    public function updateCategoryOnRace($race_id, $cat) {
        $race_id = is_numeric($race_id) ? (int)$race_id : 0;
        $cat = correct_sql_string($cat);
        console_log('update cat '.$cat.' on race '.$race_id.' for user '.$this->getId());
        $query = "update ".TBL_ZAVXUS." set `kat` = \"$cat\" where `id_zavod` = $race_id and `id_user` = ".$this->getId();
        $result = query_db($query);
    }

};
?>