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
        $this->idEnrollment = $idEnrollment;
        $this->idUser = $idUser;
        $this->idCourse = $idCourse;
        $this->niveauInitial = $niveauInitial;
        $this->objectifPersonnel = $objectifPersonnel;
        $this->engagement = $engagement;
        $this->modeAcces = $modeAcces;
        $this->dateInscription = $dateInscription;
        $this->progression = $progression;
        $this->derniereActivite = $derniereActivite;
        $this->tempsTotalPasse = $tempsTotalPasse;
        $this->statut = $statut;
        $this->noteFinale = $noteFinale;
        $this->certificatObtenu = $certificatObtenu;
    }

    public function getIdEnrollment(): ?int { return $this->idEnrollment; }
    public function setIdEnrollment(?int $idEnrollment): void { $this->idEnrollment = $idEnrollment; }

    public function getIdUser(): int { return $this->idUser; }
    public function setIdUser(int $idUser): void { $this->idUser = $idUser; }

    public function getIdCourse(): int { return $this->idCourse; }
    public function setIdCourse(int $idCourse): void { $this->idCourse = $idCourse; }

    public function getNiveauInitial(): string { return $this->niveauInitial; }
    public function setNiveauInitial(string $niveauInitial): void { $this->niveauInitial = $niveauInitial; }

    public function getObjectifPersonnel(): string { return $this->objectifPersonnel; }
    public function setObjectifPersonnel(string $objectifPersonnel): void { $this->objectifPersonnel = $objectifPersonnel; }

    public function getEngagement(): int { return $this->engagement; }
    public function setEngagement(int $engagement): void { $this->engagement = $engagement; }

    public function getModeAcces(): string { return $this->modeAcces; }
    public function setModeAcces(string $modeAcces): void { $this->modeAcces = $modeAcces; }

    public function getDateInscription(): ?string { return $this->dateInscription; }
    public function setDateInscription(?string $dateInscription): void { $this->dateInscription = $dateInscription; }

    public function getProgression(): int { return $this->progression; }
    public function setProgression(int $progression): void { $this->progression = $progression; }

    public function getDerniereActivite(): ?string { return $this->derniereActivite; }
    public function setDerniereActivite(?string $derniereActivite): void { $this->derniereActivite = $derniereActivite; }

    public function getTempsTotalPasse(): int { return $this->tempsTotalPasse; }
    public function setTempsTotalPasse(int $tempsTotalPasse): void { $this->tempsTotalPasse = $tempsTotalPasse; }

    public function getStatut(): string { return $this->statut; }
    public function setStatut(string $statut): void { $this->statut = $statut; }

    public function getNoteFinale(): ?float { return $this->noteFinale; }
    public function setNoteFinale(?float $noteFinale): void { $this->noteFinale = $noteFinale; }

    public function getCertificatObtenu(): bool { return $this->certificatObtenu; }
    public function setCertificatObtenu(bool $certificatObtenu): void { $this->certificatObtenu = $certificatObtenu; }
}
?>