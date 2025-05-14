<?php

namespace App\Entity;

use App\Entity\Enum\UserRole;
use App\Repository\UserRepository;
use Carbon\CarbonImmutable;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ORM\Table(name: '`user`')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    #[ORM\Column(length: 255)]
    private string $name;

    #[ORM\Column(length: 255)]
    private string $email;

    #[ORM\Column(length: 255)]
    private string $password;

    #[ORM\Column(options: ['default' => UserRole::USER])]
    private UserRole $role;

    #[ORM\Column]
    private bool $enabled = true;

    #[ORM\Column]
    private bool $enable2fa = true;

    #[ORM\Column]
    private bool $enableBioMetricsFor2fa = true;

    #[ORM\Column]
    private CarbonImmutable $createdAt;

    #[ORM\PrePersist]
    public function prePersist(): void
    {
        $this->createdAt = CarbonImmutable::now();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    public function getRole(): UserRole
    {
        return $this->role;
    }

    public function setRole(UserRole $role): self
    {
        $this->role = $role;

        return $this;
    }

    public function isEnabled(): ?bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): static
    {
        $this->enabled = $enabled;

        return $this;
    }

    public function isEnable2fa(): bool
    {
        return $this->enable2fa;
    }

    public function setEnable2fa(bool $enable2fa): static
    {
        $this->enable2fa = $enable2fa;

        return $this;
    }

    public function getCreatedAt(): CarbonImmutable
    {
        return $this->createdAt;
    }


    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getRoles(): array
    {
        return [$this->role->value];
    }

    public function eraseCredentials(): void
    {
        return;
    }

    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    public function isEnableBioMetricsFor2fa(): bool
    {
        return $this->enableBioMetricsFor2fa;
    }

    public function setEnableBioMetricsFor2fa(bool $enableBioMetricsFor2fa): void
    {
        $this->enableBioMetricsFor2fa = $enableBioMetricsFor2fa;
    }
}
