<?php

namespace App\Entity;

use App\Repository\BioMetricDataRepository;
use Carbon\CarbonImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BioMetricDataRepository::class)]
class BioMetricData
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private User $user;

    #[ORM\Column(type: 'blob_string')]
    private string $data;

    #[ORM\Column(type: 'string')]
    private string $credentialId;

    #[ORM\Column]
    private CarbonImmutable $createdTime;

    #[ORM\Column(nullable: true)]
    private ?CarbonImmutable $lastUsedTime = null;

    public function __construct()
    {
        $this->createdTime = CarbonImmutable::now();
    }
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getData(): string
    {
        return $this->data;
    }

    public function setData(string $data): static
    {
        $this->data = $data;

        return $this;
    }

    public function getCredentialId(): string
    {
        return $this->credentialId;
    }

    public function setCredentialId(string $credentialId): static
    {
        $this->credentialId = $credentialId;

        return $this;
    }

    public function getCreatedTime(): CarbonImmutable
    {
        return $this->createdTime;
    }

    public function setCreatedTime(CarbonImmutable $createdTime): static
    {
        $this->createdTime = $createdTime;

        return $this;
    }

    public function getLastUsedTime(): ?CarbonImmutable
    {
        return $this->lastUsedTime;
    }

    public function setLastUsedTime(?CarbonImmutable $lastUsedTime): static
    {
        $this->lastUsedTime = $lastUsedTime;

        return $this;
    }
}
