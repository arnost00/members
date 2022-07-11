<?php

class User
{
    private $id;
    private $userId;
    private $reg;
    private $firstName;
    private $lastName;
    private $si;
    private $clubId;

    public function __construct()
    {
        $this->setId(0);
	$this->setUserId(0);
        $this->setReg("");
        $this->setFirstName("");
        $this->setLastName("");
        $this->setSI(0);
        $this->setClubId(0);
    }
    
     public function create($userid, $firstname, $lastname, $reg, $si, $clubid)
    {
        $this->setId(0);
	$this->setUserId($userid);
        $this->setReg($reg);
        $this->setFirstName($firstname);
        $this->setSI($si);
        $this->setLastName($lastname);
        $this->setClubId($clubid);
    }
    
    /**
     * Fills instance of Action with data in params array
     * params = array of names of attributes with first letter Uppercase and
     * 			their new values 
     * @param $params
     */
    public function fill($params)
    {
        foreach ($params as $var => $value)
        {
            $this->$var = $value;
//            $var[0] = strtoupper($var[0]);
//            $var = ucfirst($var);
//            $setter = "set$var";
//        	  $this->{$setter}($value);
        }
       print_r($this);
    }
    
    public function getId(){
        return $this->id;
    }

    public function setId($id){
        $this->id = $id;
    }

    public function getUserId(){
        return $this->userId;
    }

    public function setUserId($userId){
        $this->userId = $userId;
    }

    public function getReg(){
        return $this->reg;
    }

    public function setReg($reg){
        $this->reg = $reg;
    }
    
    public function getFirstName(){
        return $this->firstName;
    }

    public function setFirstName($firstname){
        $this->firstName = $firstname;
    }
    
    public function getLastName(){
        return $this->lastName;
    }

    public function setLastName($lastname){
        $this->lastName = $lastname;
    }

    public function getSI(){
        return $this->si;
    }

    public function setSI($si){
        $this->si = $si;
    }
    
    public function getClubId(){
        return $this->clubId;
    }

    public function setClubId($clubId){
        $this->clubId = $clubId;
    } 
    

    /*
     *  Save actual state into db
     */
    public function save()
    {
	$params = array("firstName"=>$this->getFirstName(), "lastName"=>$this->getLastName(), "reg"=>$this->getReg(), "si"=>$this->getSI(), "clubId"=>$this->getCLubId(), "userId"=>$this->getUserId());
        $query = "";
        if ($this->getId())
        {
            $query = "update user set ";
            foreach ($params as $var => $value)
            {
                $query.="`".$var."` = '".$value."',";
            }
            $query = substr($query, 0, -1);
            $query.=" where id = '{$this->getId()}';";
        }
        else
        {
            $query = "insert into user (`id_race`, ";
            foreach ($params as $var => $value)
            {
                $query.="`".$var."`,";
            }
            $query = substr($query, 0, -1);
            $query.=") VALUES ( ".$this->getRaceId().", ";
            foreach ($params as $var => $value)
            {
                $query.="'".$value."',";
            }
            $query = substr($query, 0, -1);
            $query.=");";
        }
//        echo $query;
        mysql_query($query);
    }

    public function fill_from_db($zaznam)
    {
        $this->setId($zaznam["id"]);
        $this->setUserId($zaznam["userId"]);
        $this->setFirstName($zaznam["firstName"]);
        $this->setLastName($zaznam["lastName"]);
        $this->setReg($zaznam["reg"]);
        $this->setSI($zaznam["si"]);
        $this->setClubId($zaznam["clubId"]);
    }
    
    public function print_detail()
    {
	echo $this->getUserId()." ".$this->getFirstName()." ".$this->getLastName()." ".$this->getReg()." ".$this->getSI()." ";
	echo $this->getClubId()."<br>";
    }
    
    public function print_editable()
    {
	echo "<div name='runner-1st-row'>";
	echo "<input runnerid=".$this->getId()." type=text name='name-".$this->getId()."' id='name-".$this->getId()."' value='".$this->getName()."' disabled size=\"25\">";
	echo "<input runnerid=".$this->getId()." type=text name='si-".$this->getId()."' id='si-".$this->getId()."' value='".$this->getSI()."' disabled maxlength=\"8\" size=\"8\">";
	echo "</div>";
	echo "<div name='runner-2nd-row'>";
	echo "<input runnerid=".$this->getId()." type=text name='reg-".$this->getId()."' id='reg-".$this->getId()."' value='".$this->getReg()."' disabled maxlength=\"8\" size=\"8\">";
	echo "<input runnerid=".$this->getId()." type=text name='cat-".$this->getId()."' id='cat-".$this->getId()."' value='".$this->getCat()."' disabled maxlength=\"8\" size=\"8\">";
	echo "<input runnerid=".$this->getId()." type=text name='sttime-".$this->getId()."' id='sttime-".$this->getId()."' value='".$this->getStTime()."' disabled maxlength=\"8\" size=\"8\">";
	echo "</div>";
    }
    

}

?>