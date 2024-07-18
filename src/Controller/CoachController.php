<?php

namespace App\Controller;

use App\Entity\Coach;
use App\Entity\Session;
use App\Form\CoachType;
use App\Form\SessionFormType;
use App\Form\SessionType;
use App\Repository\CoachRepository;
use App\Repository\SessionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/coach')]
class CoachController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private CoachRepository $coachRepository, 
        private SessionRepository $sessionRepository
    ) {}
    
    #[Route('/', name: 'app_coach_index', methods: ['GET'])]
    public function index(CoachRepository $coachRepository): Response
    {
        return $this->render('coach/index.html.twig', [
            'coaches' => $coachRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_coach_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $coach = new Coach();
        $form = $this->createForm(CoachType::class, $coach);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($coach);
            $this->entityManager->flush();

            if ($request->headers->get('HX-Request')) {
                return $this->render('coach/_table_row.html.twig', [
                    'coach' => $coach,
                ]);
            }

            return $this->redirectToRoute('app_coach_index');
        }

        $template = $request->headers->get('HX-Request') ? 'coach/_modal.html.twig' : 'coach/new.html.twig';

        return $this->render($template, [
            'form' => $form->createView(),
            'coach' => $coach,
            'title' => 'New Coach',
            'submit_path' => 'app_coach_new',
        ]);
    }

    #[Route('/{id}', name: 'app_coach_show', methods: ['GET'])]
    public function show(Coach $coach): Response
    {

        $sessions = $this->sessionRepository->findBy(['coach' => $coach], ['startDateTime' => 'ASC']);

        return $this->render('coach/show.html.twig', [
            'coach' => $coach,
            'sessions' => $sessions,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_coach_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Coach $coach, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(CoachType::class, $coach);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_coach_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('coach/edit.html.twig', [
            'coach' => $coach,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_coach_delete', methods: ['POST'])]
    public function delete(Request $request, Coach $coach, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$coach->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($coach);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_coach_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/session/new', name: 'app_coach_session_new', methods: ['GET', 'POST'])]
    public function newSession(Request $request, Coach $coach, EntityManagerInterface $entityManager): Response
    {
        $session = new Session();
        $session->setCoach($coach);

        $form = $this->createForm(SessionType::class, $session, [
            'coach' => $coach,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($session);
            $entityManager->flush();

            $this->addFlash('success', 'New session created successfully.');

            return $this->redirectToRoute('app_coach_show', ['id' => $coach->getId()]);
        }

        return $this->render('coach/new_session.html.twig', [
            'coach' => $coach,
            'form' => $form->createView(),
        ]);
    }
    /*
    #[Route('/{coachId}/session/{id}', name: 'app_coach_session_show', methods: ['GET'])]
    public function showForCoach(int $coachId, Session $session): Response
    {
        // Ensure the session belongs to the coach
        if ($session->getCoach()->getId() != $coachId) {
            throw $this->createAccessDeniedException('This session does not belong to the specified coach.');
        }

        return $this->render('session/show_coach.html.twig', [
            'session' => $session,
            'coach' => $session->getCoach(),
        ]);
    }
        */

    #[Route('/{coachId}/session/{id}', name: 'app_coach_session_show', methods: ['GET', 'POST'])]
    public function showForCoach(int $coachId, Session $session, CoachRepository $coachRepository, Request $request, EntityManagerInterface $entityManager): Response
    {
        $coach = $coachRepository->find($coachId);

        if (!$coach) {
            throw $this->createNotFoundException('Coach not found');
        }

        if ($session->getCoach() !== $coach) {
            throw $this->createAccessDeniedException('This session does not belong to the specified coach.');
        }

        $closeForm = $this->createForm(SessionFormType::class, $session);
        $closeForm->handleRequest($request);

        if ($closeForm->isSubmitted() && $closeForm->isValid()) {
            $session->setStatus(false); // Close the session
            $entityManager->flush();

            $this->addFlash('success', 'Session closed successfully.');

            return $this->redirectToRoute('app_coach_session_show', ['coachId' => $coachId, 'id' => $session->getId()]);
        }

        return $this->render('session/show_coach.html.twig', [
            'session' => $session,
            'coach' => $coach,
            'close_form' => $closeForm->createView(),
        ]);
    }
}
