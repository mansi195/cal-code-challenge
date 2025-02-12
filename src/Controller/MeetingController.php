<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;

use App\Entity\ProjectPlanning;
use Symfony\Component\HttpFoundation\Request;
use App\Form\MeetingType;

class MeetingController extends AbstractController
{

    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }
    
    #[Route('/meeting/edit/{id}', name: 'meeting_edit')]
    public function edit(ProjectPlanning $project, Request $request): Response
    {
        $form = $this->createForm(MeetingType::class, $project);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $this->entityManager->flush();

            $this->addFlash(
                'notice',
                'Meeting updated successfully!'
            );

            return $this->redirectToRoute('employee_meetings', [
                'email' => $project->getEmployee()->getEmail(),
            ]);

        }

        return $this->render('meeting/edit.html.twig', [
            'form' => $form,
        ]);
    }
}
