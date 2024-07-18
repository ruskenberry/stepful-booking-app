<?php

namespace App\Controller;

use App\Entity\Student;
use App\Entity\Session;
use App\Form\StudentType;
use App\Repository\StudentRepository;
use App\Repository\SessionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/student')]
class StudentController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private StudentRepository $studentRepository,
        private SessionRepository $sessionRepository,
        private LoggerInterface $logger
    ) {}

    #[Route('/', name: 'app_student_index', methods: ['GET'])]
    public function index(StudentRepository $studentRepository): Response
    {
        return $this->render('student/index.html.twig', [
            'students' => $studentRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_student_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $student = new Student();
        $form = $this->createForm(StudentType::class, $student);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($student);
            $this->entityManager->flush();

            if ($request->headers->get('HX-Request')) {
                return $this->render('student/_table_row.html.twig', [
                    'student' => $student,
                ]);
            }

            return $this->redirectToRoute('app_student_index');
        }

        $template = $request->headers->get('HX-Request') ? 'student/_modal.html.twig' : 'student/new.html.twig';

        return $this->render($template, [
            'form' => $form->createView(),
            'student' => $student,
            'title' => 'New Student',
            'submit_path' => 'app_student_new',
        ]);
    } 

    #[Route('/{id}', name: 'app_student_show', methods: ['GET'])]
    public function show(Student $student): Response
    {
        $sessions = $this->sessionRepository->findBy(['student' => $student], ['startDateTime' => 'ASC']);

        $this->logger->debug($student->getId());

        return $this->render('student/show.html.twig', [
            'student' => $student,
            'sessions' => $sessions,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_student_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Student $student, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(StudentType::class, $student);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_student_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('student/edit.html.twig', [
            'student' => $student,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_student_delete', methods: ['POST'])]
    public function delete(Request $request, Student $student, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$student->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($student);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_student_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/booking', name: 'app_student_booking', methods: ['GET'])]
    public function booking(Student $student, Request $request): Response
    {
        $availableSessions = $this->sessionRepository->findAvailableSessions();

        // Check if there's a 'booking_success' query parameter
        $bookingSuccess = $request->query->get('booking_success');

        if ($bookingSuccess) {
            $this->addFlash('success', 'New session booked successfully.');
        }

        return $this->render('student/booking.html.twig', [
            'student' => $student,
            'available_sessions' => $availableSessions,
        ]);
    }

    #[Route('/{studentId}/book/{sessionId}', name: 'app_student_book_session', methods: ['POST'])]
    public function bookSession(int $studentId, int $sessionId, EntityManagerInterface $entityManager): Response
    {
        $student = $entityManager->getRepository(Student::class)->find($studentId);
        $session = $entityManager->getRepository(Session::class)->find($sessionId);

        if (!$student || !$session) {
            throw $this->createNotFoundException('Student or Session not found');
        }

        if ($session->getStudent()) {
            $this->addFlash('error', 'This session is already booked');
            return $this->redirectToRoute('app_student_booking', ['id' => $studentId]);
        }

        $session->setStudent($student);
        $entityManager->flush();

        $this->addFlash('success', 'Session booked successfully');
        return $this->redirectToRoute('app_student_booking', ['id' => $studentId]);
    }

    #[Route('/{studentId}/session/{sessionId}', name: 'app_student_session_show', methods: ['GET'])]
    public function showForStudent(int $studentId, int $sessionId, EntityManagerInterface $entityManager): Response
    {
        
        $student = $entityManager->getRepository(Student::class)->find($studentId);
        $session = $entityManager->getRepository(Session::class)->find($sessionId);

        // Ensure the session belongs to the student
        if ($session->getStudent()->getId() != $studentId) {
            //throw $this->createAccessDeniedException('This session does not belong to the specified student.');
        }

        return $this->render('session/show_student.html.twig', [
            'session' => $session,
            'student' => $session->getStudent(),
        ]);
    }
}