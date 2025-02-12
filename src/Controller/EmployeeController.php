<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;

use App\Entity\Employee;
use App\Entity\ProjectPlanning;
use DateTime;

class EmployeeController extends AbstractController
{

    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/', name: 'employee_data')]
    public function index(): Response
    {
        return $this->render('employee/index.html.twig', [
            'employees' => $this->entityManager->getRepository(Employee::class)->findAll(),
        ]);
    }


    #[Route('/employee/meetings/{email}', name: 'employee_meetings')]
    public function meetings(string $email): Response
    {
        $employee = $this->entityManager->getRepository(Employee::class)->findOneBy(['email' => $email]);

        if (!$employee) {
            return new Response('Employee not found', Response::HTTP_NOT_FOUND);
        }


         // Get the current date and calculate the start and end of the current week
        $currentDate = new DateTime();
        $startweek = (clone $currentDate)->modify('monday this week')->setTime(0, 0, 0);
        $endweek = (clone $currentDate)->modify('sunday this week')->setTime(23, 59, 59);

        // Get the meetings for the current week using QueryBuilder
        $qb = $this->entityManager->createQueryBuilder();

        // SELECT * FROM `project_planning` p 
        // LEFT JOIN employee e ON p.emp_id = e.id 
        // WHERE p.emp_id = 'f4fe90bc-bc8c-4116-a553-7e900396ac82';


        $qb->select('p')
           ->from(ProjectPlanning::class, 'p')
           ->innerJoin('p.employee', 'e')
           ->where('e.id = :employeeId')
           ->andWhere('p.startDate >= :startweek') 
           ->andWhere('p.endDate <= :endweek') 
           ->setParameter('employeeId', $employee->getId())
           ->setParameter('startweek', $startweek)
           ->setParameter('endweek', $endweek); 

        $meetings = $qb->getQuery()->getResult();

        return $this->render('employee/meetings.html.twig', [
            'meetings' => $meetings,                            
            'employee' => $employee->getFirstName()." ".$employee->getLastName()
        ]);
    }

}
