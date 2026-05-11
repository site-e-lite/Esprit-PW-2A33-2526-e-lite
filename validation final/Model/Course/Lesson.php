<?php
class Lesson
{
    private ?int   $idLesson;
    private int    $idCourse;
    private string $titre;
    private int    $ordre;

    public function __construct(?int $idLesson, int $idCourse, string $titre, int $ordre)
    {
        $this->idLesson = $idLesson;
        $this->idCourse = $idCourse;
        $this->titre    = $titre;
        $this->ordre    = $ordre;
    }

    public function getIdLesson(): ?int        { return $this->idLesson; }
    public function setIdLesson(?int $v): void { $this->idLesson = $v; }

    public function getIdCourse(): int         { return $this->idCourse; }
    public function setIdCourse(int $v): void  { $this->idCourse = $v; }

    public function getTitre(): string         { return $this->titre; }
    public function setTitre(string $v): void  { $this->titre = $v; }

    public function getOrdre(): int            { return $this->ordre; }
    public function setOrdre(int $v): void     { $this->ordre = $v; }
}
?>
