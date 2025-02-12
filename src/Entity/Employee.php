<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Entity\ProjectPlanning;

#[ORM\Entity(repositoryClass: "App\Repository\EmployeeRepository")]
class Employee
{

    #[ORM\OneToMany(targetEntity: "App\Entity\ProjectPlanning", mappedBy: "employee")]
    private $projectPlannings;

    public function __construct()
    {
        $this->projectPlannings = new \Doctrine\Common\Collections\ArrayCollection();
    }

    // Getter and Setter for projectPlannings
    public function getProjectPlannings(): \Doctrine\Common\Collections\Collection
    {
        return $this->projectPlannings;
    }

    #[ORM\Column(type: 'string', length: 36, unique: true), ORM\Id]
    private string $id;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $firstName = null;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $lastName = null;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $email = null;

    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id): Employee
    {
        $this->id = $id;
        return $this;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(?string $firstName): Employee
    {
        $this->firstName = $firstName;
        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(?string $lastName): Employee
    {
        $this->lastName = $lastName;
        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): Employee
    {
        $this->email = $email;
        return $this;
    }

}