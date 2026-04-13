<?php
class Question {
    private $IdQuestion;
    private $enonce;
    private $type;
    private $choixA;
    private $choixB;
    private $choixC;
    private $choixD;
    private $bonneReponse;
    private $note;
    private $explication;
    private $IdQuiz;

    public function __construct($enonce, $type, $choixA, $choixB, $choixC, $choixD, $bonneReponse, $note, $IdQuiz, $explication = null, $IdQuestion = null) {
        $this->enonce = $enonce;
        $this->type = $type;
        $this->choixA = $choixA;
        $this->choixB = $choixB;
        $this->choixC = $choixC;
        $this->choixD = $choixD;
        $this->bonneReponse = $bonneReponse;
        $this->note = $note;
        $this->explication = $explication;
        $this->IdQuiz = $IdQuiz;
        $this->IdQuestion = $IdQuestion;
    }

    // Getters
    public function getIdQuestion() { return $this->IdQuestion; }
    public function getEnonce() { return $this->enonce; }
    public function getType() { return $this->type; }
    public function getChoixA() { return $this->choixA; }
    public function getChoixB() { return $this->choixB; }
    public function getChoixC() { return $this->choixC; }
    public function getChoixD() { return $this->choixD; }
    public function getBonneReponse() { return $this->bonneReponse; }
    public function getNote() { return $this->note; }
    public function getExplication() { return $this->explication; }
    public function getIdQuiz() { return $this->IdQuiz; }

    // Setters
    public function setEnonce($enonce) { $this->enonce = $enonce; }
    public function setType($type) { $this->type = $type; }
    public function setChoixA($choixA) { $this->choixA = $choixA; }
    public function setChoixB($choixB) { $this->choixB = $choixB; }
    public function setChoixC($choixC) { $this->choixC = $choixC; }
    public function setChoixD($choixD) { $this->choixD = $choixD; }
    public function setBonneReponse($bonneReponse) { $this->bonneReponse = $bonneReponse; }
    public function setNote($note) { $this->note = $note; }
    public function setExplication($explication) { $this->explication = $explication; }
    public function setIdQuiz($IdQuiz) { $this->IdQuiz = $IdQuiz; }
    public function setIdQuestion($IdQuestion) { $this->IdQuestion = $IdQuestion; }
}
?>
