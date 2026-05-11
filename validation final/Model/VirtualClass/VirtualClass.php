<?php
class VirtualClass
{
    private $idClass;
    private $titre;
    private $description;
    private $lienAcces;
    private $plateforme;
    private $capacite;
    private $idCourse;

    public function __construct(
        $idClass     = null,
        $titre       = '',
        $description = '',
        $lienAcces   = '',
        $plateforme  = '',
        $capacite    = 30,
        $idCourse    = null
    ) {
        $this->idClass     = $idClass;
        $this->titre       = $titre;
        $this->description = $description;
        $this->lienAcces   = $lienAcces;
        $this->plateforme  = $plateforme;
        $this->capacite    = (int) $capacite;
        $this->idCourse    = $idCourse;
    }

    public function getIdClass()     { return $this->idClass; }
    public function setIdClass($v)   { $this->idClass = $v; }
    public function getTitre()       { return $this->titre; }
    public function setTitre($v)     { $this->titre = $v; }
    public function getDescription() { return $this->description; }
    public function setDescription($v) { $this->description = $v; }
    public function getLienAcces()   { return $this->lienAcces; }
    public function setLienAcces($v) { $this->lienAcces = $v; }
    public function getPlateforme()  { return $this->plateforme; }
    public function setPlateforme($v){ $this->plateforme = $v; }
    public function getCapacite()    { return $this->capacite; }
    public function setCapacite($v)  { $this->capacite = (int) $v; }
    public function getIdCourse()    { return $this->idCourse; }
    public function setIdCourse($v)  { $this->idCourse = $v; }
}
