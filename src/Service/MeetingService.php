<?php

namespace App\Service;

use App\Entity\Employee;
use App\Entity\ProjectPlanning;
use Doctrine\ORM\EntityManagerInterface;

class MeetingService
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    // Get meetings for a specific employee by email
    public function getMeetingsByEmail(string $email): array
    {
        $employee = $this->entityManager->getRepository(Employee::class)->findOneBy(['email' => $email]);

        if (!$employee) {
            $output->writeln("<error>Employee not found!</error>");
            return [];
        }

        return $this->entityManager->getRepository(ProjectPlanning::class)->findBy(['employee' => $employee]);
    }

    // Get meetings for all employees
    public function getAllEmployeesMeetings(): array
    {
        $employees = $this->entityManager->getRepository(Employee::class)->findAll();
        $employeeMeetings = [];

        foreach ($employees as $employee) {
            $meetings = $this->entityManager->getRepository(ProjectPlanning::class)->findBy(['employee' => $employee]);
            if ($meetings) {
                $employeeMeetings[] = ['email' => $employee->getEmail(), 'meetings' => $meetings];
            }
        }

        return $employeeMeetings;
    }
}


?>