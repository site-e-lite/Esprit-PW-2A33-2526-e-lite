<?php
class SupportCourse
{
    private ?int $idSupport;
    private string $titre;
    private string $type;
    private string $url;
    private ?string $description;
    private ?string $dateAjout;
    private int $idCourse;

    public function __construct(
        ?int $idSupport,
        string $titre,
        string $type,
        string $url,
        ?string $description,
        ?string $dateAjout,
        int $idCourse
    ) {
        $this->idSupport = $idSupport;
        $this->titre = $titre;
        $this->type = $type;
        $this->url = $url;
        $this->description = $description;
        $this->dateAjout = $dateAjout;
        $this->idCourse = $idCourse;
    }

    public function getIdSupport(): ?int { return $this->idSupport; }
    public function setIdSupport(?int $idSupport): void { $this->idSupport = $idSupport; }

    public function getTitre(): string { return $this->titre; }
    public function setTitre(string $titre): void { $this->titre = $titre; }

    public function getType(): string { return $this->type; }
    public function setType(string $type): void { $this->type = $type; }

    public function getUrl(): string { return $this->url; }
    public function setUrl(string $url): void { $this->url = $url; }

    public function getDescription(): ?string { return $this->description; }
    public function setDescription(?string $description): void { $this->description = $description; }

    public function getDateAjout(): ?string { return $this->dateAjout; }
    public function setDateAjout(?string $dateAjout): void { $this->dateAjout = $dateAjout; }

    public function getIdCourse(): int { return $this->idCourse; }
    public function setIdCourse(int $idCourse): void { $this->idCourse = $idCourse; }
}
?>