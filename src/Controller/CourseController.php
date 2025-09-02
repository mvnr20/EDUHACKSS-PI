<?php

namespace App\Controller;

use App\Entity\Course;
use App\Form\CourseType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use DateTime;
use Twilio\Rest\Client;



#[Route('/course')]
class CourseController extends AbstractController
{
    #[Route('/', name: 'app_course_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $courses = $entityManager
            ->getRepository(Course::class)
            ->findAll();

        return $this->render('Back_office/back/course.html.twig', [
            'courses' => $courses,
        ]);
    }

    #[Route('/sortcreated', name: 'sortcreated', methods: ['GET'])]
    public function sortcreated(EntityManagerInterface $entityManager): Response
    {
        $courses = $entityManager
            ->getRepository(Course::class)
            ->findBy([], ['created' => 'DESC']);

        return $this->render('Back_office/back/course.html.twig', [
            'courses' => $courses,
        ]);
    }
    #[Route('/sorttitle', name: 'sorttitle', methods: ['GET'])]
    public function sorttitle(EntityManagerInterface $entityManager): Response
    {
        $courses = $entityManager
            ->getRepository(Course::class)
            ->findBy([], ['title' => 'DESC']);

        return $this->render('Back_office/back/course.html.twig', [
            'courses' => $courses,
        ]);
    }
    #[Route('/sortid', name: 'sortid', methods: ['GET'])]
    public function sortid(EntityManagerInterface $entityManager): Response
    {
        $courses = $entityManager
            ->getRepository(Course::class)
            ->findBy([], ['id' => 'DESC']);

        return $this->render('Back_office/back/course.html.twig', [
            'courses' => $courses,
        ]);
    }
    #[Route('/front', name: 'app_course_indexfront', methods: ['GET'])]
    public function indexf(EntityManagerInterface $entityManager): Response
    {
        $courses = $entityManager
            ->getRepository(Course::class)
            ->findAll();

        return $this->render('front/courses.html.twig', [
            'courses' => $courses,
        ]);
    }

    #[Route('/back', name: 'indexback', methods: ['GET'])]
    public function indexback(): Response
    {
        return $this->render('Back_office/back/index.html.twig', []);
    }


    #[Route('/new', name: 'app_course_new', methods: ['GET', 'POST'])]
    public function new(Client $twilioClient, Request $request, EntityManagerInterface $entityManager): Response
    {
        $course = new Course();
        $form = $this->createForm(CourseType::class, $course);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $course->setCreated(new DateTime());
            $entityManager->persist($course);
            $entityManager->flush();
            $twilioClient->messages->create(
                '+21655369782',
                array(
                    'from' => $this->getParameter('twilio_number'),
                    'body' => "Bonjour un nouveau cours vein d'etre ajouter"
                )
            );
            return $this->redirectToRoute('app_course_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('Back_office/back/newcourse.html.twig', [
            'course' => $course,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_course_show', methods: ['GET'])]
    public function show(Course $course): Response
    {
        return $this->render('course/show.html.twig', [
            'course' => $course,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_course_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, int $id, EntityManagerInterface $entityManager): Response
    {
        $course = $entityManager->getRepository(Course::class)->find($id);

        if (!$course) {
            throw $this->createNotFoundException('Course not found.');
        }

        $form = $this->createForm(CourseType::class, $course);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_course_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('Back_office/back/updcourse.html.twig', [
            'course' => $course,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_course_delete', methods: ['POST'])]
    public function delete(Request $request, int $id, EntityManagerInterface $entityManager): Response
    {
        $course = $entityManager->getRepository(Course::class)->find($id);

        if (!$course) {
            throw $this->createNotFoundException('Course not found.');
        }

        if ($this->isCsrfTokenValid('delete' . $course->getId(), $request->request->get('_token'))) {
            $entityManager->remove($course);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_course_index', [], Response::HTTP_SEE_OTHER);
    }
}
