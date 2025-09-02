<?php

namespace App\Controller;

use App\Entity\Comments;
use App\Form\CommentsType;
use App\Repository\CommentsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Article;
use App\Repository\ArticleRepository;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use App\Repository\CategoriesRepository;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use GuzzleHttp\Client;
use NotificationAPI\NotificationAPI;


class CommentsController extends AbstractController
{
    #[Route('/backoffice/comments', name: 'app_comments')]
    public function index(): Response
    {
        return $this->render('Back_office/comments/index.html.twig', [
            'controller_name' => 'commentsController',
        ]);
    }

    #[Route('/backoffice/comments/affiche-comments/{articleId}', name: 'affiche_comments')]
public function afficheComments(int $articleId, CommentsRepository $commentsRepository, ArticleRepository $articleRepository): Response
{
    // Retrieve the selected article
    $article = $articleRepository->find($articleId);

    if (!$article) {
        throw $this->createNotFoundException('Article not found');
    }

    // Retrieve comments associated with the selected article
    $comments = $commentsRepository->findBy(['article' => $article]);

    // Render the template to display the comments
    return $this->render('Back_office/comments/affiche_comments.html.twig', [
        'article' => $article,
        'comments' => $comments,
    ]);

}

private static function checkForBadWords(string $content): bool
{
    // Define your list of bad words
    $badWords = ['fuck', 'shit', 'Damn', 'asshole'];

    // Convert the content to lowercase for case-insensitive matching
    $lowerContent = strtolower($content);

    // Check if any bad words are present in the content
    foreach ($badWords as $badWord) {
        if (strpos($lowerContent, $badWord) !== false) {
            return true; // Bad word found
        }
    }

    return false; // No bad words found
}



#[Route('/backoffice/article/{article_id}/comment/add', name: 'add_comment')]
public function addComment(Request $request, int $article_id, EntityManagerInterface $entityManager, ArticleRepository $articleRepository, CategoriesRepository $categoriesRepository): Response
{
    // Retrieve the article by its ID
    $article = $articleRepository->find($article_id);

    // Check if the article exists
    if (!$article) {
        throw $this->createNotFoundException('Article not found');
    }

    // Fetch categories to pass to the form
    $categories = $categoriesRepository->findAllCategories();

    // Create the comment form
    $comment = new Comments();
    $form = $this->createForm(CommentsType::class, $comment);

    // Handle form submission
    $form->handleRequest($request);
    if ($form->isSubmitted() && $form->isValid()) {
        // Set the article for the comment
        $comment->setArticle($article);

        // Persist the comment to the database
        $entityManager->persist($comment);
        $entityManager->flush();

        // Redirect back to the article page after adding the comment
        return $this->redirectToRoute('viewarticle', ['id' => $article_id]);
    }

    // Render the form for adding comments along with the article content
    return $this->render('Back_office/comment/add.html.twig', [
        'article' => $article,
        'categories' => $categories,
        'form' => $form->createView(),
    ]);
}


#[Route('/frontoffice/comments', name: 'app_comments_front')]
    public function indexf(): Response
    {
        return $this->render('Front_office/comments/index.html.twig', [
            'controller_name' => 'commentsController',
        ]);
    }

    #[Route('/frontoffice/comments/affiche-comments/{articleId}', name: 'affiche_comments_front')]
public function afficheCommentsf(int $articleId, CommentsRepository $commentsRepository, ArticleRepository $articleRepository): Response
{
    // Retrieve the selected article
    $article = $articleRepository->find($articleId);

    if (!$article) {
        throw $this->createNotFoundException('Article not found');
    }

    // Retrieve comments associated with the selected article
    $comments = $commentsRepository->findBy(['article' => $article]);

    // Find the most liked comment
    $mostLikedComment = $commentsRepository->mostLikedComment($comments);

    // Render the template to display the comments
    return $this->render('Front_office/comments/affiche_comments.html.twig', [
        'article' => $article,
        'comments' => $comments,
        'mostLikedComment' => $mostLikedComment,
    ]);
}

#[Route('/frontoffice/article/{article_id}/comment/add', name: 'add_comment_front')]
public function addCommentf(Request $request, int $article_id, EntityManagerInterface $entityManager, ArticleRepository $articleRepository, CategoriesRepository $categoriesRepository, SessionInterface $session): Response
{
    // Retrieve the article by its ID
    $article = $articleRepository->find($article_id);

    // Check if the article exists
    if (!$article) {
        throw $this->createNotFoundException('Article not found');
    }

    // Fetch categories to pass to the form
    $categories = $categoriesRepository->findAllCategories();

    // Create the comment form
    $comment = new Comments();
    $form = $this->createForm(CommentsType::class, $comment);

    // Handle form submission
    $form->handleRequest($request);
    if ($form->isSubmitted() && $form->isValid()) {
        // Check for bad words
        $content = $comment->getContent();
        if ($this->checkForBadWords($content)) {
            // Display error message for bad words
            $this->addFlash('error', 'Your comment contains inappropriate language. It Will Not Be Added !!!.');
            return $this->redirectToRoute('add_comment_front', ['article_id' => $article_id]);
        }

        // Set the article for the comment
        $comment->setArticle($article);

        // Persist the comment to the database
        $entityManager->persist($comment);
        $entityManager->flush();

        // Redirect back to the article page after adding the comment
        return $this->redirectToRoute('viewarticle_front', ['id' => $article_id]);
    }

    // Render the form for adding comments along with the article content
    return $this->render('Front_office/comment/add.html.twig', [
        'article' => $article,
        'categories' => $categories,
        'form' => $form->createView(),
    ]);
}


private static function checkForBadWordss(string $content): bool
{
    $apiUrl = "https://neutrinoapi.net/bad-word-filter";
    $apiKey = "h2tcUvl97UL2imusD9jJy6eM5XNHsJfVRMidlW1oVE8sZ1E4"; // Replace with your API key
    $userId = "ramramdouf"; // Replace with your user ID

    $curl = curl_init();

    curl_setopt_array($curl, [
        CURLOPT_URL => $apiUrl,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => http_build_query([
            "censor-character" => "",
            "catalog" => "strict",
            "content" => $content
        ]),
        CURLOPT_HTTPHEADER => [
            "User-ID: $userId",
            "API-Key: $apiKey",
            "Content-Type: application/x-www-form-urlencoded"
        ],
    ]);

    $response = curl_exec($curl);
    $err = curl_error($curl);

    curl_close($curl);

    if ($err) {
        // Handle cURL error
        error_log("cURL Error: $err");
        return false;
    } else {
        $jsonResponse = json_decode($response, true);
        if (isset($jsonResponse['is-bad'])) {
            return $jsonResponse['is-bad'];
        } else {
            // Handle unexpected API response
            error_log("Unexpected API response");
            return false;
        }
    }
}



#[Route('/frontoffice/comment/{id}/edit', name: 'edit_comment_front')]
public function editCommentf(Request $request, int $id, CommentsRepository $commentsRepository, CategoriesRepository $categoriesRepository): Response
{
    // Retrieve the comment entity by ID
    $comment = $commentsRepository->find($id);

    // Check if the comment exists
    if (!$comment) {
        throw $this->createNotFoundException('Comment not found');
    }

    // Fetch categories to pass to the form
    $categories = $categoriesRepository->findAllCategories();

    // Create the comment edit form
    $form = $this->createForm(CommentsType::class, $comment);

    // Handle form submission
    $form->handleRequest($request);
    if ($form->isSubmitted() && $form->isValid()) {
        // Update the comment in the database
        $this->getDoctrine()->getManager()->flush();

        // Redirect back to the article page after editing the comment
        return $this->redirectToRoute('viewarticle_front', ['id' => $comment->getArticle()->getId()]);
    }

    // Render the form for editing comments
    return $this->render('Front_office/Comment/editcomm.html.twig', [
        'comment' => $comment,
        'form' => $form->createView(),
        'categories' => $categories, // Pass the categories variable to the template
    ]);
}



#[Route('/frontoffice/comment/{id}/delete', name: 'delete_comment_front')]
public function deleteCommentf(int $id, CommentsRepository $commentsRepository): RedirectResponse
{
    // Find the comment by its ID
    $comment = $commentsRepository->find($id);

    // Check if the comment exists
    if (!$comment) {
        throw $this->createNotFoundException('Comment not found');
    }

    // Delete the comment from the database
    $entityManager = $this->getDoctrine()->getManager();
    $entityManager->remove($comment);
    $entityManager->flush();

    // Redirect to a suitable route after deleting the comment, such as a page displaying remaining comments or the article page
    return $this->redirectToRoute('viewarticle_front', ['id' => $comment->getArticle()->getId()]);
}

#[Route('/frontoffice/comment/{id}/like', name: 'like_comment_front', methods: ['POST'])]
public function likeCommentFront(int $id, CommentsRepository $commentsRepository): Response
{
    // Find the comment by its ID
    $comment = $commentsRepository->find($id);

    // Check if the comment exists
    if (!$comment) {
        throw $this->createNotFoundException('Comment not found');
    }

    // Increment the likes count for the comment
    $commentsRepository->incrementLikes($comment);

    // Redirect back to the article page after liking the comment
    return $this->redirectToRoute('viewarticle_front', ['id' => $comment->getArticle()->getId()]);
}

#[Route('/frontoffice/comment/{id}/dislike', name: 'dislike_comment_front', methods: ['POST'])]
public function dislikeCommentFront(int $id, CommentsRepository $commentsRepository): Response
{
    // Find the comment by its ID
    $comment = $commentsRepository->find($id);

    // Check if the comment exists
    if (!$comment) {
        throw $this->createNotFoundException('Comment not found');
    }

    // Increment the dislikes count for the comment
    $commentsRepository->incrementDislikes($comment);

    // Redirect back to the article page after disliking the comment
    return $this->redirectToRoute('viewarticle_front', ['id' => $comment->getArticle()->getId()]);
}


}

