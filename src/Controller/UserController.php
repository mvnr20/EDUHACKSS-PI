<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\UserRepository;
use App\Form\UserType;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Security;
use App\Form\RegistrationType;
use App\Service\TokenGenerator;
use App\Controller\MailerInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use PHPMailer\PHPMailer\PHPMailer;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use App\Form\ForgotPasswordType;
use App\Repository\CategoriesRepository;



class UserController extends AbstractController
{
    private $passwordEncoder;
    private $session;
    private $userRepository;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder, SessionInterface $session, UserRepository $userRepository)
    {
        $this->passwordEncoder = $passwordEncoder;
        $this->session = $session;
        $this->userRepository = $userRepository;

    }

    #[Route('/users', name: 'user_index', methods: ['GET'])]
    public function index( CategoriesRepository $categoriesRepository): Response
    {
        $categories = $categoriesRepository->findAll();

        $users = $this->getDoctrine()->getRepository(User::class)->findAll();
        return $this->render('Back_office/User/user/index.html.twig', [
            'users' => $users,
            'categories' => $categories, // Pass the $categories variable to the Twig template

        ]);
    }


    #[Route('/verify-password', name: 'verify_password', methods: ['POST'])]
public function verifyPassword(Request $request, UserPasswordEncoderInterface $passwordEncoder): Response
{
    // Retrieve the user's current password from the database
    $user = $this->getUser();
    $currentEncodedPassword = $user->getPassword();

    // Retrieve the plain-text password entered by the user
    $enteredPassword = $request->request->get('currentPassword');

    // Check if the entered password is null or empty
    if (empty($enteredPassword)) {
        return new Response('Entered password is empty', Response::HTTP_BAD_REQUEST);
    }

    // Encode the entered password
    $hashedEnteredPassword = $passwordEncoder->encodePassword($user, $enteredPassword);

    // Compare the entered hashed password with the hashed password stored in the database
    $passwordIsValid = $passwordEncoder->isPasswordValid($currentEncodedPassword, $hashedEnteredPassword);

    if ($passwordIsValid) {
        // Password verification succeeded
        return new Response('Password verified', Response::HTTP_OK);
    } else {
        // Password verification failed
        return new Response('Password incorrect', Response::HTTP_UNAUTHORIZED);
    }
}

    


    #[Route('/user/new', name: 'user_new', methods: ['GET', 'POST'])]
    public function new(Request $request, CategoriesRepository $categoriesRepository): Response
    {
        $categories = $categoriesRepository->findAll();

        $user = new User();

        $form = $this->createForm(UserType::class, $user, ['is_new_user' => true]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {


            // Hash the password
            $password = $user->getPassword();
            $hashedPassword = $this->passwordEncoder->encodePassword($user, $password);
            $user->setPassword($hashedPassword);

            // Handle file upload for profile picture
            $file = $form->get('picFile')->getData();
            if ($file instanceof UploadedFile) {
                $fileName = md5(uniqid()) . '.' . $file->guessExtension();
                try {
                    $file->move('C:/xampp/htdocs/uploads', $fileName); // Adjust the path here
                    $user->setPic($fileName);
                } catch (FileException $e) {
                    // Handle file upload error
                    $this->addFlash('error', 'Failed to upload the file.');
                    return $this->redirectToRoute('user_new');
                }
            }

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', 'User created successfully.');
            return $this->redirectToRoute('user_index');
        }

        return $this->render('Back_office/User/user/new.html.twig', [
            'form' => $form->createView(),
            'categories' => $categories, // Pass the $categories variable to the Twig template

        ]);
    }

    #[Route('/user/{username}', name: 'user_show', methods: ['GET'])]
    public function show(string $username, UserRepository $userRepository, CategoriesRepository $categoriesRepository): Response
    {
        // Fetch the user entity from the database based on the username
        $user = $userRepository->findOneBy(['username' => $username]);
    
        // Check if the user entity exists
        if (!$user) {
            throw $this->createNotFoundException('User not found');
        }
    
        $categories = $categoriesRepository->findAll();
    
        return $this->render('Back_office/User/user/show.html.twig', [
            'user' => $user,
            'categories' => $categories, // Pass the $categories variable to the Twig template
        ]);
    }
    #[Route('/forgot-password', name: 'forgot_password')]
public function forgotPassword(Request $request, \Symfony\Component\Mailer\MailerInterface $mailer, CategoriesRepository $categoriesRepository): Response
{
    $categories = $categoriesRepository->findAll();

    // Check if the form is submitted
    if ($request->isMethod('POST')) {
        // Get the email from the form submission
        $email = $request->request->get('email');

        // Find the user by email in the database
        $userRepository = $this->getDoctrine()->getRepository(User::class);
        $user = $userRepository->findOneBy(['email' => $email]);

        if (!$user) {
            // Handle the case where the email is not found
            // Display an error message or redirect back to the forgot-password page
            return $this->render('Front_office/User/forgot_password.html.twig', [
                'error' => 'Email not found.',
                'categories' => $categories, // Pass the $categories variable to the Twig template

            ]);
        }

        // Generate a unique token for password reset
        $token = bin2hex(random_bytes(32));
        // Save the token in the user entity
        $user->setResetPasswordToken($token);
        // Save the changes to the database
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->flush();

        // Send email with the reset password link
        try {
            // Generate the reset password URL
            $resetUrl = $this->generateUrl('reset_password', ['token' => $token, 'email' => $email], UrlGeneratorInterface::ABSOLUTE_URL);

            // Create the email message
            $email = (new \Symfony\Component\Mime\Email())
                ->from('fatma.naoui25@gmail.com')
                ->to($user->getEmail())
                ->subject('Password Reset')
                ->html('Click the following link to reset your password: <a href="' . $resetUrl . '">Reset Password</a>');

            // Send the email
            $mailer->send($email);

            // Redirect the user to a success page or display a success message
            return $this->redirectToRoute('login');
        } catch (\Exception $e) {
            // Handle email sending errors
            return $this->render('Front_office/User/forgot_password.html.twig', [
                'error' => 'An error occurred while sending the email.',
                'categories' => $categories, // Pass the $categories variable to the Twig template

            ]);
        }
    }

    return $this->render('Front_office/User/forgot_password.html.twig', [
        'categories' => $categories, // Pass the $categories variable to the Twig template
    ]);
}



#[Route('/reset-password', name: 'reset_password')]
public function resetPassword(Request $request, UserPasswordEncoderInterface $passwordEncoder, CategoriesRepository $categoriesRepository): Response
{
    $categories = $categoriesRepository->findAll();

    // Retrieve the user's email from the URL parameters
    $email = $request->query->get('email');

    // Find the user by email in the database
    $userRepository = $this->getDoctrine()->getRepository(User::class);
    $user = $userRepository->findOneBy(['email' => $email]);

    // Create a new instance of the ForgotPasswordType form
    $form = $this->createForm(ForgotPasswordType::class, $user); // Pass the user entity to the form

    $form->handleRequest($request);

    // Check if the form is submitted and valid
    if ($form->isSubmitted() && $form->isValid()) {
        // Get the data from the form
        $data = $form->getData();
        $newPassword = $data->getPassword();

        // Reset the user's password
        // Encode the new password before setting it
        $encodedPassword = $passwordEncoder->encodePassword($user, $newPassword);
        $user->setPassword($encodedPassword);

        // Save the changes to the database
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->flush();

        // Redirect the user to a success page or display a success message
        return $this->redirectToRoute('app_login');
    }

    // Render the form template if the form is not submitted or invalid
    return $this->render('Front_office/User/reset_password.html.twig', [
        'form' => $form->createView(),
        'categories' => $categories, // Pass the $categories variable to the Twig template

    ]);
}







   
    #[Route('/profile', name: 'user_profile', methods: ['GET', 'POST'])]
public function editProfile(Request $request, Security $security): Response
{
    // Get the logged-in user
    $user = $security->getUser();
    
    if (!$user) {
        // You can fetch the user from another source, such as the session
        $user = $this->getUserFromSession();
        
        // If the user is still null, redirect to the login page or handle as needed
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }
    }
    
    // Create a form instance for the logged-in user
    $form = $this->createForm(UserType::class, $user, [
        'page' => 'profile', // Pass the 'profile' page identifier
    ]);
    
    $form->handleRequest($request);
    
    if ($form->isSubmitted() && $form->isValid()) {
        $this->addFlash('success', 'Information sent to Admin. Awaiting Account validation.');
        
        // Handle file upload for profile picture
        $file = $form->get('picFile')->getData();
        if ($file) {
            $fileName = md5(uniqid()) . '.' . $file->guessExtension();
            try {
                $file->move('C:/xampp/htdocs/uploads', $fileName); // Adjust the path here
                $user->setPic($fileName);
            } catch (FileException $e) {
                // Handle file upload error
                $this->addFlash('error', 'Failed to upload the file.');
                return $this->redirectToRoute('user_profile');
            }
        }
        
        // Update the user entity in the database
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->flush();
        
        $this->addFlash('success', 'Profile updated successfully.');
        return $this->redirectToRoute('user_profile');
    }
    
    // Render the profile edit page template with the form and the logged-in user's information
    return $this->render('Front_office/User/profile.html.twig', [
        'form' => $form->createView(),
        'user' => $user,
    ]);
}
private function getUserFromSession(): ?User
{
    // Implement your logic to retrieve the user from the session
    // For example:
    $userId = $this->session->get('user_id');
    
    // Check if the user ID is available
    if ($userId) {
        // Find the user by ID
        $user = $this->userRepository->find($userId);
        return $user;
    }
    
    return null;
}

#[Route('/user/{username}/status/change', name: 'user_status_change', methods: ['POST'])]
public function changeStatus(Request $request, string $username): Response
{
    $entityManager = $this->getDoctrine()->getManager();
    $userRepository = $entityManager->getRepository(User::class);

    // Fetch the user by username
    $user = $userRepository->findOneBy(['username' => $username]);

    if (!$user) {
        throw $this->createNotFoundException('User not found');
    }

    // Assuming the request sends a parameter 'status' with the value 'active'
    $user->setStatus('active');

    $entityManager->flush();

    $this->addFlash('success', 'User status changed to Active.');

    return $this->redirectToRoute('user_index');
}

    #[Route('/signup', name: 'signup')]
public function signup(Request $request, UserPasswordEncoderInterface $passwordEncoder): Response
{
    // Create a new User entity
    $user = new User();
    $user->setStatus('active');

    // Create a form for user registration
    $form = $this->createForm(RegistrationType::class, $user);

    // Handle form submission
    $form->handleRequest($request);
    
    if ($form->isSubmitted() && $form->isValid()) {
        // Hash the password
        $password = $user->getPassword();
        $hashedPassword = $passwordEncoder->encodePassword($user, $password);
        $user->setPassword($hashedPassword);

        // Persist the user entity to the database
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($user);
        $entityManager->flush();

        // Redirect the user to the profile page
        return $this->redirectToRoute('user_profile');
    }

    // If the form is not submitted or is not valid, render the signup form template with form errors
    return $this->render('Front_office/User/Signupin.html.twig', [
        'form' => $form->createView(),
    ]);
}




    

#[Route('/user/{username}/edit', name: 'user_edit', methods: ['GET', 'POST'])]
public function editUser(Request $request, string $username, CategoriesRepository $categoriesRepository): Response
{
    // Fetch the user entity from the database based on the username
    $userRepository = $this->getDoctrine()->getRepository(User::class);
    $user = $userRepository->findOneBy(['username' => $username]);

    // Check if the user entity exists
    if (!$user) {
        throw $this->createNotFoundException('User not found');
    }

    $categories = $categoriesRepository->findAll();

    $form = $this->createForm(UserType::class, $user);

    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        // Handle file upload for profile picture
        $file = $form->get('picFile')->getData();
        if ($file) {
            $fileName = md5(uniqid()) . '.' . $file->guessExtension();
            try {
                $file->move('C:/xampp/htdocs/uploads', $fileName); // Adjust the path here
                $user->setPic($fileName);
            } catch (FileException $e) {
                // Handle file upload error
                $this->addFlash('error', 'Failed to upload the file.');
                return $this->redirectToRoute('user_edit', ['username' => $user->getUsername()]);
            }
        }

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->flush();

        $this->addFlash('success', 'User updated successfully.');
        return $this->redirectToRoute('user_index');
    }

    return $this->render('Back_office/User/user/edit.html.twig', [
        'form' => $form->createView(),
        'user' => $user,
        'categories' => $categories, // Pass the $categories variable to the Twig template
    ]);
}





#[Route('/user/delete/{id}', name: 'user_delete', methods: ["GET"])]
public function delete(User $user): Response
{
    $entityManager = $this->getDoctrine()->getManager();
    $entityManager->remove($user);
    $entityManager->flush();

    // Optionally, add a flash message to indicate successful deletion
    $this->addFlash('success', 'User deleted successfully.');

    return $this->redirectToRoute('user_index');
}

}
