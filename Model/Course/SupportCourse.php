<?php
class SupportCourse
{
    private ?int    $idSupport;
    private string  $titre;
    private string  $type;
    private string  $url;
    private ?string $description;
    private ?string $dateAjout;
    private int     $idCourse;

    public function __construct(?int $idSupport, string $titre, string $type, string $url, ?string $description, ?string $dateAjout, int $idCourse)
    {
        $this->idSupport   = $idSupport;
        $this->titre       = $titre;
        $this->type        = $type;
        $this->url         = $url;
        $this->description = $description;
        $this->dateAjout   = $dateAjout;
        $this->idCourse    = $idCourse;
    }

    public function getIdSupport(): ?int { return $this->idSupport; }
    public function setIdSupport(?int $v): void { $this->idSupport = $v; }

    public function getTitre(): string { return $this->titre; }
    public function setTitre(string $v): void { $this->titre = $v; }

    public function getType(): string { return $this->type; }
    public function setType(string $v): void { $this->type = $v; }

    public function getUrl(): string { return $this->url; }
    public function setUrl(string $v): void { $this->url = $v; }

    public function getDescription(): ?string { return $this->description; }
    public function setDescription(?string $v): void { $this->description = $v; }

    public function getDateAjout(): ?string { return $this->dateAjout; }
    public function setDateAjout(?string $v): void { $this->dateAjout = $v; }

    public function getIdCourse(): int { return $this->idCourse; }
    public function setIdCourse(int $v): void { $this->idCourse = $v; }
}
?>
