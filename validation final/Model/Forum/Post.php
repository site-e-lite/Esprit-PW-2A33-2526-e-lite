<?php
class Post {
    private $IdPost;
    private $contenu;
    private $datePost;
    private $pieceJointe;
    private $IdUser;
    private $IdForum;

    public function __construct($contenu, $IdUser, $IdForum, $pieceJointe = null, $datePost = null) {
        $this->contenu = $contenu;
        $this->IdUser = $IdUser;
        $this->IdForum = $IdForum;
        $this->pieceJointe = $pieceJointe;
        $this->datePost = $datePost;
    }

    // Getters
    public function getIdPost() { return $this->IdPost; }
    public function getContenu() { return $this->contenu; }
    public function getDatePost() { return $this->datePost; }
    public function getPieceJointe() { return $this->pieceJointe; }
    public function getIdUser() { return $this->IdUser; }
    public function getIdForum() { return $this->IdForum; }

    // Setters
    public function setContenu($contenu) { $this->contenu = $contenu; }
    public function setDatePost($datePost) { $this->datePost = $datePost; }
    public function setPieceJointe($pieceJointe) { $this->pieceJointe = $pieceJointe; }
    public function setIdUser($IdUser) { $this->IdUser = $IdUser; }
    public function setIdForum($IdForum) { $this->IdForum = $IdForum; }
}
?>
