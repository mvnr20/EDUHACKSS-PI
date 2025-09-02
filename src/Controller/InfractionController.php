<?php

namespace App\Controller;

use App\Entity\Infraction;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\InfractionRepository;
use App\Form\InfractionType;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\User; 
use App\Repository\CategoriesRepository;




class InfractionController extends AbstractController
{
    #[Route('/infractions', name: 'infraction_index', methods: ['GET'])]
public function index(InfractionRepository $infractionRepository, EntityManagerInterface $entityManager, CategoriesRepository $categoriesRepository): Response
{
    $categories = $categoriesRepository->findAll();

    $infractions = $infractionRepository->findAll();

    // Iterate over each infraction and check if the associated user exists
    foreach ($infractions as $infraction) {
        $user = $infraction->getUser();
        if ($user === null) {
            // Handle gracefully if the associated user does not exist
            // For example, you can remove the infraction from the list
            // Or display a message indicating the user is no longer available
            $this->addFlash('warning', 'User associated with an infraction was not found.');
        } else {
            // Fetch the user with the correct ID
            $userId = $user->getId();
            $existingUser = $entityManager->getRepository(User::class)->find($userId);
            
            // Update the user reference if it has changed
            if ($existingUser !== null && $existingUser->getId() !== $userId) {
                $infraction->setUser($existingUser);
            }
        }
    }

    // Flush the changes to update user references
    $entityManager->flush();

    return $this->render('Back_office/User/infraction/index.html.twig', [
        'infractions' => $infractions,
        'categories' => $categories, // Pass the $categories variable to the Twig template

    ]);
}

#[Route('/infraction/new', name: 'infraction_new', methods: ['GET', 'POST'])]
public function newInfraction(Request $request, CategoriesRepository $categoriesRepository): Response
{
    $categories = $categoriesRepository->findAll();

    $infraction = new Infraction();
    $form = $this->createForm(InfractionType::class, $infraction);

    $form->handleRequest($request);
    if ($form->isSubmitted() && $form->isValid()) {
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($infraction);
        $entityManager->flush();

        $this->addFlash('success', 'Infraction created successfully.');
        return $this->redirectToRoute('infraction_index');
    }

    return $this->render('Back_office/User/infraction/new.html.twig', [
        'form' => $form->createView(),
        'categories' => $categories, // Pass the $categories variable to the Twig template
    ]);
}


#[Route('/infraction/{id}', name: 'infraction_show', methods: ['GET'])]
public function showInfraction(int $id, InfractionRepository $infractionRepository, CategoriesRepository $categoriesRepository): Response
{
    // Fetch the infraction entity from the database based on the id
    $infraction = $infractionRepository->find($id);

    // Check if the infraction entity exists
    if (!$infraction) {
        throw $this->createNotFoundException('Infraction not found');
    }

    $categories = $categoriesRepository->findAll();

    return $this->render('Back_office/User/infraction/show.html.twig', [
        'infraction' => $infraction,
        'categories' => $categories, // Pass the $categories variable to the Twig template
    ]);
}
    

    #[Route("/infraction/{id}/edit", name: "edit_infraction_form", methods: ['GET', 'POST'])]
    public function editInfractionForm(Request $request, InfractionRepository $infractionRepository, EntityManagerInterface $entityManager, int $id, CategoriesRepository $categoriesRepository): Response
    {
        // Fetch the infraction entity by ID from the repository
        $infraction = $infractionRepository->find($id);
    
        // Create the form using the InfractionType and pass the infraction entity
        $form = $this->createForm(InfractionType::class, $infraction);
        $form->handleRequest($request);
    
        // Fetch categories from the repository
        $categories = $categoriesRepository->findAll();
    
        if ($form->isSubmitted() && $form->isValid()) {
            // Handle form submission, e.g., update the entity in the database
            $entityManager->flush();
    
            // Redirect to infraction index page after infraction update
            return $this->redirectToRoute('infraction_index');
        }
    
        return $this->render('Back_office/User/infraction/edit.html.twig', [
            'form' => $form->createView(),
            'categories' => $categories, // Pass the $categories variable to the Twig template
        ]); 
    }
    

    #[Route('/infraction/delete/{id}', name: 'infraction_delete', methods: ["GET"])]
public function deleteInfraction($id, InfractionRepository $infractionRepository): Response
{
    $infraction = $infractionRepository->find($id); // Fetch the Infraction entity using the repository

    if (!$infraction) {
        throw $this->createNotFoundException('Infraction not found');
    }

    $entityManager = $this->getDoctrine()->getManager();
    $entityManager->remove($infraction);
    $entityManager->flush();

    $this->addFlash('success', 'Infraction deleted successfully.');

    return $this->redirectToRoute('infraction_index');
}

}