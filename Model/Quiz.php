<?php
// Objet simple qui transporte les données d'un quiz.
class Quiz {
    // Les attributs représentent l'état du quiz en base.
    private $IdQuiz;
    private $titre;
    private $duree;
    private $seuilReussite;
    private $niveau;
    private $statut;
    private $idCourse;

    public function __construct($titre, $duree, $seuilReussite, $niveau, $statut, $idCourse = null, $IdQuiz = null) {
        // Le constructeur prépare l'objet avant son passage au contrôleur.
        $this->titre = $titre;
        $this->duree = $duree;
        $this->seuilReussite = $seuilReussite;
        $this->niveau = $niveau;
        $this->statut = $statut;
        $this->idCourse = $idCourse;
        $this->IdQuiz = $IdQuiz;
    }

    // Accesseurs et mutateurs standards pour garder le modèle léger.
    public function getIdQuiz() { return $this->IdQuiz; }
    public function getTitre() { return $this->titre; }
    public function getDuree() { return $this->duree; }
    public function getSeuilReussite() { return $this->seuilReussite; }
    public function getNiveau() { return $this->niveau; }
    public function getStatut() { return $this->statut; }
    public function getIdCourse() { return $this->idCourse; }

    public function setTitre($titre) { $this->titre = $titre; }
    public function setDuree($duree) { $this->duree = $duree; }
    public function setSeuilReussite($seuilReussite) { $this->seuilReussite = $seuilReussite; }
    public function setNiveau($niveau) { $this->niveau = $niveau; }
    public function setStatut($statut) { $this->statut = $statut; }
    public function setIdCourse($idCourse) { $this->idCourse = $idCourse; }
    public function setIdQuiz($IdQuiz) { $this->IdQuiz = $IdQuiz; }
}
?>
