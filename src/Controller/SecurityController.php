<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Security\AppCustomAuthenticator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use App\Repository\CategoriesRepository;


class SecurityController extends AbstractController
{
    #[Route('/login', name: 'app_login')]
#[Route('/register', name: 'app_register')]
public function loginOrRegister(
    Request $request,
    AuthenticationUtils $authenticationUtils,
    UserPasswordHasherInterface $userPasswordHasher,
    UserAuthenticatorInterface $userAuthenticator,
    AppCustomAuthenticator $authenticator,
    EntityManagerInterface $entityManager
): Response {
    $user = new User();
    $registrationForm = $this->createForm(RegistrationFormType::class, $user);
    $registrationForm->handleRequest($request);

    // Get the login error if there is one
    $error = $authenticationUtils->getLastAuthenticationError();
    // Last username entered by the user
    $lastUsername = $authenticationUtils->getLastUsername();

    // Check if the form is submitted and valid
    if ($registrationForm->isSubmitted() && $registrationForm->isValid()) {
        // Get the reCAPTCHA response from the request
        $recaptchaResponse = $request->request->get('g-recaptcha-response');

        // Verify reCAPTCHA response
        $recaptchaSecret = '6Ld7htMpAAAAAAq0R9uEzrRpsbEE21GQZPrSLyNd';
        $recaptchaVerify = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret={$recaptchaSecret}&response={$recaptchaResponse}");
        $recaptchaResponseData = json_decode($recaptchaVerify);

        // Check if reCAPTCHA verification succeeded
        if (!$recaptchaResponseData->success) {
            // reCAPTCHA verification failed, show a flash message
            $this->addFlash('error', 'reCAPTCHA verification failed. Please try again.');
            return $this->redirectToRoute('app_register'); // Reload the registration page
        } else {
            // Proceed with user registration
            // Encode the plain password
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $registrationForm->get('password')->getData()
                )
            );

            // Set role based on registration form data
            $role = $user->getRole(); // Assuming there's a method to get role from the form
            if ($role === 'Student') {
                $user->setRoles(['ROLE_STUDENT']);
            } elseif ($role === 'Teacher') {
                $user->setRoles(['ROLE_TEACHER']);
            } elseif ($role === 'Admin') {
                $user->setRoles(['ROLE_ADMIN']);
            }

            $entityManager->persist($user);
            $entityManager->flush();
            // Do anything else you need here, like send an email

            return $userAuthenticator->authenticateUser(
                $user,
                $authenticator,
                $request
            );
        }
    }

    // If the form is not submitted or valid, or reCAPTCHA verification failed, render the registration form
    return $this->render('Front_office/User/Signupin.html.twig', [
        'registrationForm' => $registrationForm->createView(),
        'error' => $error,
        'last_username' => $lastUsername,
    ]);
}

#[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

}
