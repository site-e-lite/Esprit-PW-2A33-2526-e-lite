<?php
class Certificate
{
    private ?int   $id;
    private int    $userId;
    private int    $courseId;
    private string $dateObtained;

    public function __construct(?int $id, int $userId, int $courseId, string $dateObtained)
    {
        $this->id           = $id;
        $this->userId       = $userId;
        $this->courseId     = $courseId;
        $this->dateObtained = $dateObtained;
    }

    public function getId(): ?int          { return $this->id; }
    public function setId(?int $id): void  { $this->id = $id; }

    public function getUserId(): int              { return $this->userId; }
    public function setUserId(int $userId): void  { $this->userId = $userId; }

    public function getCourseId(): int               { return $this->courseId; }
    public function setCourseId(int $courseId): void { $this->courseId = $courseId; }

    public function getDateObtained(): string                    { return $this->dateObtained; }
    public function setDateObtained(string $dateObtained): void  { $this->dateObtained = $dateObtained; }
}
?>
