<?php
/**
 * Modèle Progress — représente un enregistrement de progression
 * d'un utilisateur dans un cours.
 *
 * Respecte le pattern MVC : aucune logique métier ni SQL ici.
 */
class Progress
{
    private ?int $id;
    private int  $userId;
    private int  $courseId;
    private int  $progressPercent;
    private string $lastAccessed;

    public function __construct(
        ?int   $id,
        int    $userId,
        int    $courseId,
        int    $progressPercent,
        string $lastAccessed
    ) {
        $this->id              = $id;
        $this->userId          = $userId;
        $this->courseId        = $courseId;
        $this->progressPercent = $progressPercent;
        $this->lastAccessed    = $lastAccessed;
    }

    public function getId(): ?int            { return $this->id; }
    public function setId(?int $id): void    { $this->id = $id; }

    public function getUserId(): int              { return $this->userId; }
    public function setUserId(int $userId): void  { $this->userId = $userId; }

    public function getCourseId(): int               { return $this->courseId; }
    public function setCourseId(int $courseId): void { $this->courseId = $courseId; }

    public function getProgressPercent(): int                    { return $this->progressPercent; }
    public function setProgressPercent(int $progressPercent): void { $this->progressPercent = $progressPercent; }

    public function getLastAccessed(): string                  { return $this->lastAccessed; }
    public function setLastAccessed(string $lastAccessed): void { $this->lastAccessed = $lastAccessed; }
}
?>
