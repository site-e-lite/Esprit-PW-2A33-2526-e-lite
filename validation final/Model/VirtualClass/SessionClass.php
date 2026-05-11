<?php
class SessionClass
{
    private $idSession;
    private $dateSession;
    private $heureDebut;
    private $heureFin;
    private $statut;
    private $idClass;

    public function __construct($idSession = null, $dateSession = '', $heureDebut = '', $heureFin = '', $statut = '', $idClass = null)
    {
        $this->idSession   = $idSession;
        $this->dateSession = $dateSession;
        $this->heureDebut  = $heureDebut;
        $this->heureFin    = $heureFin;
        $this->statut      = $statut;
        $this->idClass     = $idClass;
    }

    public function getIdSession()   { return $this->idSession; }
    public function setIdSession($v) { $this->idSession = $v; }
    public function getDateSession() { return $this->dateSession; }
    public function setDateSession($v){ $this->dateSession = $v; }
    public function getHeureDebut()  { return $this->heureDebut; }
    public function setHeureDebut($v){ $this->heureDebut = $v; }
    public function getHeureFin()    { return $this->heureFin; }
    public function setHeureFin($v)  { $this->heureFin = $v; }
    public function getStatut()      { return $this->statut; }
    public function setStatut($v)    { $this->statut = $v; }
    public function getIdClass()     { return $this->idClass; }
    public function setIdClass($v)   { $this->idClass = $v; }
}
