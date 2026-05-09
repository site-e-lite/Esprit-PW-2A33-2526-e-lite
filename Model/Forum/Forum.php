<?php
class Forum {
    private $IdForum;
    private $titre;
    private $description;
    private $dateCreation;
    private $IdCourse;

    public function __construct($titre, $description, $IdCourse, $dateCreation = null) {
        $this->titre = $titre;
        $this->description = $description;
        $this->IdCourse = $IdCourse;
        $this->dateCreation = $dateCreation;
    }

    // Getters
    public function getIdForum() { return $this->IdForum; }
    public function getTitre() { return $this->titre; }
    public function getDescription() { return $this->description; }
    public function getDateCreation() { return $this->dateCreation; }
    public function getIdCourse() { return $this->IdCourse; }

    // Setters
    public function setTitre($titre) { $this->titre = $titre; }
    public function setDescription($description) { $this->description = $description; }
    public function setDateCreation($dateCreation) { $this->dateCreation = $dateCreation; }
    public function setIdCourse($IdCourse) { $this->IdCourse = $IdCourse; }
}
?>
