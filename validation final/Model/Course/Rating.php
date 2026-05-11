<?php
class Rating
{
    private ?int $id;
    private int  $userId;
    private int  $courseId;
    private int  $rating;

    public function __construct(?int $id, int $userId, int $courseId, int $rating)
    {
        $this->id       = $id;
        $this->userId   = $userId;
        $this->courseId = $courseId;
        $this->rating   = $rating;
    }

    public function getId(): ?int         { return $this->id; }
    public function setId(?int $id): void { $this->id = $id; }

    public function getUserId(): int              { return $this->userId; }
    public function setUserId(int $userId): void  { $this->userId = $userId; }

    public function getCourseId(): int               { return $this->courseId; }
    public function setCourseId(int $courseId): void { $this->courseId = $courseId; }

    public function getRating(): int             { return $this->rating; }
    public function setRating(int $rating): void { $this->rating = $rating; }
}
?>
