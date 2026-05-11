<?php
class Enrollment
{
    private ?int $idEnrollment;
    private int $idUser;
    private int $idCourse;
    private string $niveauInitial;
    private string $objectifPersonnel;
    private int $engagement;
    private string $modeAcces;
    private ?string $dateInscription;
    private int $progression;
    private ?string $derniereActivite;
    private int $tempsTotalPasse;
    private string $statut;
    private ?float $noteFinale;
    private bool $certificatObtenu;

    public function __construct(
        ?int $idEnrollment,
        int $idUser,
        int $idCourse,
        string $niveauInitial,
        string $objectifPersonnel,
        int $engagement,
        string $modeAcces,
        ?string $dateInscription,
        int $progression,
        ?string $derniereActivite,
        int $tempsTotalPasse,
        string $statut,
        ?float $noteFinale,
        bool $certificatObtenu
    ) {
        $this->idEnrollment      = $idEnrollment;
        $this->idUser            = $idUser;
        $this->idCourse          = $idCourse;
        $this->niveauInitial     = $niveauInitial;
        $this->objectifPersonnel = $objectifPersonnel;
        $this->engagement        = $engagement;
        $this->modeAcces         = $modeAcces;
        $this->dateInscription   = $dateInscription;
        $this->progression       = $progression;
        $this->derniereActivite  = $derniereActivite;
        $this->tempsTotalPasse   = $tempsTotalPasse;
        $this->statut            = $statut;
        $this->noteFinale        = $noteFinale;
        $this->certificatObtenu  = $certificatObtenu;
    }

    public function getIdEnrollment(): ?int { return $this->idEnrollment; }
    public function setIdEnrollment(?int $v): void { $this->idEnrollment = $v; }

    public function getIdUser(): int { return $this->idUser; }
    public function setIdUser(int $v): void { $this->idUser = $v; }

    public function getIdCourse(): int { return $this->idCourse; }
    public function setIdCourse(int $v): void { $this->idCourse = $v; }

    public function getNiveauInitial(): string { return $this->niveauInitial; }
    public function setNiveauInitial(string $v): void { $this->niveauInitial = $v; }

    public function getObjectifPersonnel(): string { return $this->objectifPersonnel; }
    public function setObjectifPersonnel(string $v): void { $this->objectifPersonnel = $v; }

    public function getEngagement(): int { return $this->engagement; }
    public function setEngagement(int $v): void { $this->engagement = $v; }

    public function getModeAcces(): string { return $this->modeAcces; }
    public function setModeAcces(string $v): void { $this->modeAcces = $v; }

    public function getDateInscription(): ?string { return $this->dateInscription; }
    public function setDateInscription(?string $v): void { $this->dateInscription = $v; }

    public function getProgression(): int { return $this->progression; }
    public function setProgression(int $v): void { $this->progression = $v; }

    public function getDerniereActivite(): ?string { return $this->derniereActivite; }
    public function setDerniereActivite(?string $v): void { $this->derniereActivite = $v; }

    public function getTempsTotalPasse(): int { return $this->tempsTotalPasse; }
    public function setTempsTotalPasse(int $v): void { $this->tempsTotalPasse = $v; }

    public function getStatut(): string { return $this->statut; }
    public function setStatut(string $v): void { $this->statut = $v; }

    public function getNoteFinale(): ?float { return $this->noteFinale; }
    public function setNoteFinale(?float $v): void { $this->noteFinale = $v; }

    public function getCertificatObtenu(): bool { return $this->certificatObtenu; }
    public function setCertificatObtenu(bool $v): void { $this->certificatObtenu = $v; }
}
?>
