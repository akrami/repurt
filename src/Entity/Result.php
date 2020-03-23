<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ResultRepository")
 */
class Result
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Report", cascade={"merge"})
     * @ORM\JoinColumn(nullable=true, onDelete="CASCADE")
     */
    private $report;

    /**
     * @ORM\Column(type="text")
     */
    private $outcome;

    /**
     * @ORM\Column(type="datetime")
     */
    private $ranAt;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     */
    private $reporter;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getReport(): ?Report
    {
        return $this->report;
    }

    public function setReport(?Report $report): self
    {
        $this->report = $report;

        return $this;
    }

    public function getOutcome(): ?string
    {
        return $this->outcome;
    }

    public function setOutcome(string $outcome): self
    {
        $this->outcome = $outcome;

        return $this;
    }

    public function getRanAt(): ?\DateTimeInterface
    {
        return $this->ranAt;
    }

    public function setRanAt(\DateTimeInterface $ranAt): self
    {
        $this->ranAt = $ranAt;

        return $this;
    }

    public function getReporter(): ?User
    {
        return $this->reporter;
    }

    public function setReporter(?User $reporter): self
    {
        $this->reporter = $reporter;

        return $this;
    }
}
