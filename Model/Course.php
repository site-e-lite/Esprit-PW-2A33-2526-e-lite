<?php
class Course
{
    private ?int $idCourse;
    private string $titre;
    private string $description;
    private string $niveau;
    private int $duree;
    private string $statut;
    private string $langue;
    private float $prix;
    private ?string $image;
    private ?string $objectifs;
    private ?string $prerequis;

    public function __construct(
        ?int $idCourse,
        string $titre,
        string $description,
        string $niveau,
        int $duree,
        string $statut,
        string $langue,
        float $prix,
        ?string $image,
        ?string $objectifs,
        ?string $prerequis
    ) {
        $this->idCourse = $idCourse;
        $this->titre = $titre;
        $this->description = $description;
        $this->niveau = $niveau;
        $this->duree = $duree;
        $this->statut = $statut;
        $this->langue = $langue;
        $this->prix = $prix;
        $this->image = $image;
        $this->objectifs = $objectifs;
        $this->prerequis = $prerequis;
    }

    public function getIdCourse(): ?int { return $this->idCourse; }
    public function setIdCourse(?int $idCourse): void { $this->idCourse = $idCourse; }

    public function getTitre(): string { return $this->titre; }
    public function setTitre(string $titre): void { $this->titre = $titre; }

    public function getDescription(): string { return $this->description; }
    public function setDescription(string $description): void { $this->description = $description; }

    public function getNiveau(): string { return $this->niveau; }
    public function setNiveau(string $niveau): void { $this->niveau = $niveau; }

    public function getDuree(): int { return $this->duree; }
    public function setDuree(int $duree): void { $this->duree = $duree; }

    public function getStatut(): string { return $this->statut; }
    public function setStatut(string $statut): void { $this->statut = $statut; }

    public function getLangue(): string { return $this->langue; }
    public function setLangue(string $langue): void { $this->langue = $langue; }

    public function getPrix(): float { return $this->prix; }
    public function setPrix(float $prix): void { $this->prix = $prix; }

    public function getImage(): ?string { return $this->image; }
    public function setImage(?string $image): void { $this->image = $image; }

    public function getObjectifs(): ?string { return $this->objectifs; }
    public function setObjectifs(?string $objectifs): void { $this->objectifs = $objectifs; }

    public function getPrerequis(): ?string { return $this->prerequis; }
    public function setPrerequis(?string $prerequis): void { $this->prerequis = $prerequis; }
}
?>