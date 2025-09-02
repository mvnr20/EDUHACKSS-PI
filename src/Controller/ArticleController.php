<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Categories;
use App\Form\ArticleType;
use App\Repository\ArticleRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\CommentsRepository;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use App\Repository\CategoriesRepository;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use App\Entity\Comments;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Contracts\HttpClient\HttpClientInterface;



class ArticleController extends AbstractController
{

    private $categoriesRepository;
    private $categories;
    
    public function __construct(CategoriesRepository $categoriesRepository)
    {
        $this->categoriesRepository = $categoriesRepository;
    }

    
   
    #[Route('/backoffice/article', name: 'app_article')]
    public function index(CategoriesRepository $categoriesRepository): Response
    {
        $categories = $categoriesRepository->findAllCategories();

        return $this->render('Back_office/article/index.html.twig', [
            'controller_name' => 'ArticleController',
            'categories' => $categories,
        ]);
    }
    #[Route('/backoffice/article', name: 'app_article_home')]
    public function home(CategoriesRepository $categoriesRepository): Response
    {
        $categories = $categoriesRepository->findAllCategories();

        return $this->render('Back_office/article/dashbord.html.twig', [
            'controller_name' => 'ArticleController',
            'categories' => $categories,
        ]);
    }

    #[Route('/backoffice/article/list', name: 'article_list')]
    public function list(Request $request, ArticleRepository $articleRepository, CategoriesRepository $categoriesRepository): Response
    {
        $categoryId = $request->get('categoryId');
    
        // Fetch articles based on the selected category or fetch all articles if no category is selected
        if ($categoryId) {
            $articles = $articleRepository->findByCategory($categoryId);
        } else {
            $articles = $articleRepository->findAll();
        }
        
        // Fetch all categories to populate the dropdown
        $categories = $categoriesRepository->findAllCategories();
        
        // Fetch the most viewed article
        $mostViewedArticle = $articleRepository->findMostViewedArticle();
    
        return $this->render('Back_office/article/list.html.twig', [
            'articles' => $articles,
            'categories' => $categories,
            'mostViewedArticle' => $mostViewedArticle,
        ]);
        
    }
    
    

    
    #[Route('/backoffice/article/{id}', name: 'viewarticle', requirements: ['id' => '\d+'])]
public function viewArticle(int $id, CategoriesRepository $categoriesRepository): Response
{
    // Find the article by ID
    $article = $this->getDoctrine()->getRepository(Article::class)->find($id);

    // Fetch all categories to populate the dropdown
    $categories = $categoriesRepository->findAllCategories();

    // If the article does not exist, throw a not found exception
    if (!$article) {
        throw $this->createNotFoundException('Article not found');
    }

    // Fetch comments associated with the article
    $comments = $article->getComments();

    // Render the template and pass the article, comments, and categories data
    return $this->render('Back_office/article/viewarticle.html.twig', [
        'article' => $article,
        'comments' => $comments,
        'categories' => $categories, // Include the categories variable
    ]);
}



#[Route('/backoffice/article/add', name: 'app_article_add')]
public function add(Request $request, EntityManagerInterface $entityManager, ParameterBagInterface $parameterBag): Response
{
    $userId = 1;

    // Fetch categories from the repository
    $categories = $this->categoriesRepository->findAllCategories();

    $article = new Article();
    $article->setAuthorId($userId);

    // Create the ArticleType form with the image field
    $form = $this->createForm(ArticleType::class, $article);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        /** @var UploadedFile|null $imageFile */
        $imageFile = $form->get('image')->getData();

        if ($imageFile) {
            // Get the directory where images should be saved
            $articleImagesDirectory = $parameterBag->get('kernel.project_dir') . '/public/uploads/articles';

            // Ensure the directory exists, create it if necessary
            if (!file_exists($articleImagesDirectory)) {
                mkdir($articleImagesDirectory, 0777, true);
            }

            // Generate a unique filename
            $newFilename = md5(uniqid()) . '.' . $imageFile->guessExtension();

            // Move the uploaded file to the destination directory
            try {
                $imageFile->move(
                    $articleImagesDirectory,
                    $newFilename
                );
            } catch (FileException $e) {
                // Handle exception if file upload fails
                return new Response('Error uploading file', Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            // Set the image filename in the Article entity
            $article->setImage($newFilename);
        }

        // Set the selected category to the article
        $categoryId = $form->get('category')->getData();
        $category = $entityManager->getRepository(Categories::class)->find($categoryId);
        if (!$category) {
            // Handle case where selected category does not exist
            return new Response('Category not found', Response::HTTP_NOT_FOUND);
        }

        // Associate article with category
        $article->setCategory($category);

        // Persist the Article entity
        $entityManager->persist($article);
        $entityManager->flush();

        // Redirect to the article list page
        return $this->redirectToRoute('article_list');
    }

    // Pass the categories variable to the template
    return $this->render('Back_office/Article/addarticle.html.twig', [
        'form' => $form->createView(),
        'categories' => $categories,
    ]);
}
    



#[Route('/backoffice/article/{id}/delete', name: 'delete_article', methods: ['POST'])]
public function deleteArticle(Request $request, ArticleRepository $articleRepository, EntityManagerInterface $entityManager): Response
    {
        $articleId = $request->get('id');
        $article = $articleRepository->find($articleId);

        if ($article) {
            $entityManager->remove($article);
            $entityManager->flush();
        }

        return $this->redirectToRoute('article_list');
    }

#[Route('/backoffice/article/{id}/edit', name: 'app_article_edit')]
public function editForm(Request $request, int $id, CategoriesRepository $categoriesRepository, EntityManagerInterface $entityManager): Response
{
    // Fetch all categories to populate the dropdown
    $categories = $categoriesRepository->findAllCategories();
    
    // Find the article by ID
    $article = $entityManager->getRepository(Article::class)->find($id);

    // If the article does not exist, throw a not found exception
    if (!$article) {
        throw $this->createNotFoundException('Article not found');
    }

    // Create the form for editing the article
    $form = $this->createForm(ArticleType::class, $article);
    $form->handleRequest($request);

    // If the form is submitted and valid, save the changes and redirect to the article list page
    if ($form->isSubmitted() && $form->isValid()) {
        $entityManager->flush();
        return $this->redirectToRoute('article_list');
    }

    // Render the edit form template and pass the form and categories data
    return $this->render('Back_office/article/edit.html.twig', [
        'form' => $form->createView(),
        'categories' => $categories, // Include the categories variable
    ]);
}


    #[Route('/backoffice/article/{id}/comments', name: 'view_comments')]
public function viewComments(int $id, CommentsRepository $commentsRepository): Response
{
    $article = $this->getDoctrine()->getRepository(Article::class)->find($id);

    if (!$article) {
        throw $this->createNotFoundException('Article not found');
    }

    $comments = $commentsRepository->findBy(['article' => $article]);

    return $this->render('Back_office/article/view_comments.html.twig', [
        'article' => $article,
        'comments' => $comments,
    ]);
}
#[Route('/backoffice/article/{id}/upload-image', name: 'upload_image', methods: ['POST'])]
public function uploadImage(int $id, Request $request, EntityManagerInterface $entityManager): Response
{
    $article = $entityManager->getRepository(Article::class)->find($id);

    if (!$article) {
        throw $this->createNotFoundException('Article not found');
    }

    $imageFile = $request->files->get('image');

    if ($imageFile instanceof UploadedFile) {
        $newFilename = md5(uniqid()) . '.' . $imageFile->guessExtension();

        try {
            $imageFile->move('your_upload_directory', $newFilename);
            $article->setImage($newFilename);
            $entityManager->flush();
        } catch (FileException $e) {
            // Handle file upload error
            // Redirect or display error message
        }
    }

    return $this->redirectToRoute('viewarticle', ['id' => $id]);
}

#[Route('/backoffice/article/{id}/display-image', name: 'display_image')]
public function displayImage(int $id, EntityManagerInterface $entityManager): Response
{
    $article = $entityManager->getRepository(Article::class)->find($id);

    if (!$article || !$article->getImage()) {
        throw $this->createNotFoundException('Article or image not found');
    }

    // Return a response with the image content
    // You might need to adjust the content type based on the image type (e.g., JPEG, PNG)
    $imagePath = 'photo_directory/' . $article->getImage();
    $response = new BinaryFileResponse($imagePath);
    $response->headers->set('Content-Type', 'image/jpeg'); // Adjust content type as needed

    return $response;
}

#[Route('/frontoffice/article', name: 'app_article_front')]
public function indexf(CategoriesRepository $categoriesRepository): Response
{
    $categories = $categoriesRepository->findAllCategories();

    return $this->render('Front_office/article/index.html.twig', [
        'controller_name' => 'ArticleController',
        'categories' => $categories,
    ]);
}

#[Route('/frontoffice/home', name: 'app_homearticle_front')]
public function index2(CategoriesRepository $categoriesRepository, ArticleRepository $articleRepository): Response
{
    $categories = $categoriesRepository->findAllCategories();
    
    // Retrieve the three most viewed articles
    $mostViewedArticles = $articleRepository->findMostViewedArticles(3);
    

    return $this->render('Front_office/article/Homepage.html.twig', [
        'controller_name' => 'ArticleController',
        'categories' => $categories,
        'mostViewedArticles' => $mostViewedArticles, // Pass the most viewed articles to the template
    ]);
}



#[Route('/frontoffice/article/list', name: 'article_list_front')]
public function listf(Request $request, ArticleRepository $articleRepository, CategoriesRepository $categoriesRepository): Response
{
    $searchQuery = $request->query->get('q');

    // Fetch articles based on the search query
    if ($searchQuery) {
        $articles = $articleRepository->findBySearchQuery($searchQuery);
    } else {
        // Fetch articles based on the selected category or fetch all articles if no category is selected
        $categoryId = $request->query->get('categoryId');
        if ($categoryId) {
            $articles = $articleRepository->findByCategory($categoryId);
        } else {
            $articles = $articleRepository->findAll();
        }
    }
    
    // Fetch all categories to populate the dropdown
    $categories = $categoriesRepository->findAllCategories();
    
    // Fetch the most viewed article
    $mostViewedArticle = $articleRepository->findMostViewedArticle();
    
    return $this->render('Front_office/article/list.html.twig', [
        'articles' => $articles,
        'categories' => $categories,
        'mostViewedArticle' => $mostViewedArticle, // Pass the most viewed article to the template
    ]);
}


    
#[Route('/frontoffice/article/{id}', name: 'viewarticle_front', requirements: ['id' => '\d+'])]
public function viewArticlef(int $id, CategoriesRepository $categoriesRepository, CommentsRepository $commentsRepository, Request $request, ArticleRepository $articleRepository): Response
{
    // Find the article by ID
    $article = $this->getDoctrine()->getRepository(Article::class)->find($id);

    // If the article does not exist, throw a not found exception
    if (!$article) {
        throw $this->createNotFoundException('Article not found');
    }

    // Increment the view count for the article
    $articleRepository->increaseViewCount($article);

    // Check if the translation request is made
    if ($request->query->has('translate')) {
        // Make a request to the translation API
        $client = HttpClientInterface::create();
        $response = $client->request('POST', 'https://google-translate113.p.rapidapi.com/api/v1/translator/text', [
            'headers' => [
                'X-RapidAPI-Host' => 'google-translate113.p.rapidapi.com',
                'X-RapidAPI-Key' => 'c7f3689482msh05357e6a0218f29p1c5ea6jsn95bc60fd0837',
                'content-type' => 'application/x-www-form-urlencoded',
            ],
            'body' => [
                'from' => 'auto',
                'to' => 'fr',
                'text' => $article->getBody(), // Assuming $article is the variable containing the article object
            ],
        ]);

        // Decode the response
        $translatedText = $response->getContent();

        // Return the translated text as JSON response
        return new JsonResponse(['translatedText' => $translatedText]);
    }

    // Fetch all categories to populate the dropdown
    $categories = $categoriesRepository->findAllCategories();

    // Fetch comments associated with the article
    $comments = $commentsRepository->findBy(['article' => $article]);

    // Find the most liked comment
    $mostLikedComment = $this->mostLikedComment($comments);

    // Render the template and pass the article, comments, categories, and most liked comment data
    return $this->render('Front_office/article/viewarticle.html.twig', [
        'article' => $article,
        'comments' => $comments,
        'categories' => $categories,
        'mostLikedComment' => $mostLikedComment,
    ]);
}




public function mostLikedComment(array $comments): ?Comments
{
    $mostLiked = null;
    $maxLikes = 0;

    foreach ($comments as $comment) {
        if ($comment->getLikes() > $maxLikes) {
            $mostLiked = $comment;
            $maxLikes = $comment->getLikes();
        }
    }

    return $mostLiked;
}


#[Route('/frontoffice/article/add', name: 'app_article_add_front')]
public function addf(Request $request, EntityManagerInterface $entityManager, ParameterBagInterface $parameterBag): Response
{
    $userId = 1;

    // Fetch categories from the repository
    $categories = $this->categoriesRepository->findAllCategories();

    $article = new Article();
    $article->setAuthorId($userId);

    // Create the ArticleType form with the image field
    $form = $this->createForm(ArticleType::class, $article);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        /** @var UploadedFile|null $imageFile */
        $imageFile = $form->get('image')->getData();

        if ($imageFile) {
            // Get the directory where images should be saved
            $articleImagesDirectory = $parameterBag->get('kernel.project_dir') . '/public/uploads/articles';

            // Ensure the directory exists, create it if necessary
            if (!file_exists($articleImagesDirectory)) {
                mkdir($articleImagesDirectory, 0777, true);
            }

            // Generate a unique filename
            $newFilename = md5(uniqid()) . '.' . $imageFile->guessExtension();

            // Move the uploaded file to the destination directory
            try {
                $imageFile->move(
                    $articleImagesDirectory,
                    $newFilename
                );
            } catch (FileException $e) {
                // Handle exception if file upload fails
                // You might want to add error handling here
                return new Response('Error uploading file', Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            // Set the image filename in the Article entity
            $article->setImage($newFilename);
        }

        // Set the selected category to the article
        $categoryId = $form->get('category')->getData();
        $category = $entityManager->getRepository(Categories::class)->find($categoryId);
        if (!$category) {
            // Handle case where selected category does not exist
            // You might want to add error handling here
            return new Response('Category not found', Response::HTTP_NOT_FOUND);
        }

        // Associate article with category
        $article->setCategory($category);

        // Persist the Article entity
        $entityManager->persist($article);
        $entityManager->flush();

        // Redirect to the article list page
        return $this->redirectToRoute('article_list_front');
    }

    // Pass the categories variable to the template
    return $this->render('Front_office/Article/addarticle.html.twig', [
        'form' => $form->createView(),
        'categories' => $categories,
    ]);
}
    



#[Route('/frontoffice/article/{id}/delete', name: 'delete_article_front', methods: ['POST'])]
public function deleteArticlef(Request $request, ArticleRepository $articleRepository, EntityManagerInterface $entityManager): Response
    {
        $articleId = $request->get('id');
        $article = $articleRepository->find($articleId);

        if ($article) {
            $entityManager->remove($article);
            $entityManager->flush();
        }

        return $this->redirectToRoute('article_list_front');
    }


#[Route('/frontoffice/article/{id}/edit', name: 'app_article_edit_front')]
public function editFormf(Request $request, int $id, CategoriesRepository $categoriesRepository, EntityManagerInterface $entityManager): Response
{
    // Fetch all categories to populate the dropdown
    $categories = $categoriesRepository->findAllCategories();
    
    // Find the article by ID
    $article = $entityManager->getRepository(Article::class)->find($id);

    // If the article does not exist, throw a not found exception
    if (!$article) {
        throw $this->createNotFoundException('Article not found');
    }

    // Create the form for editing the article
    $form = $this->createForm(ArticleType::class, $article);
    $form->handleRequest($request);

    // If the form is submitted and valid, save the changes and redirect to the article list page
    if ($form->isSubmitted() && $form->isValid()) {
        $entityManager->flush();
        return $this->redirectToRoute('article_list_front');
    }

    // Render the edit form template and pass the form and categories data
    return $this->render('Front_office/article/edit.html.twig', [
        'form' => $form->createView(),
        'categories' => $categories, // Include the categories variable
    ]);
}


    #[Route('/frontoffice/article/{id}/comments', name: 'view_comments_front')]
public function viewCommentsf(int $id, CommentsRepository $commentsRepository): Response
{
    $article = $this->getDoctrine()->getRepository(Article::class)->find($id);

    if (!$article) {
        throw $this->createNotFoundException('Article not found');
    }

    $comments = $commentsRepository->findBy(['article' => $article]);

    return $this->render('Front_office/article/view_comments.html.twig', [
        'article' => $article,
        'comments' => $comments,
    ]);
}
#[Route('/frontoffice/article/{id}/upload-image', name: 'upload_image_front', methods: ['POST'])]
public function uploadImagef(int $id, Request $request, EntityManagerInterface $entityManager): Response
{
    $article = $entityManager->getRepository(Article::class)->find($id);

    if (!$article) {
        throw $this->createNotFoundException('Article not found');
    }

    $imageFile = $request->files->get('image');

    if ($imageFile instanceof UploadedFile) {
        $newFilename = md5(uniqid()) . '.' . $imageFile->guessExtension();

        try {

            $article->setImage($newFilename);
            $entityManager->flush();
        } catch (FileException $e) {
            // Handle file upload error
            // Redirect or display error message
        }
    }

    return $this->redirectToRoute('viewarticle_front', ['id' => $id]);
}

#[Route('/frontoffice/article/{id}/display-image', name: 'display_image_front')]
public function displayImagef(int $id, EntityManagerInterface $entityManager): Response
{
    $article = $entityManager->getRepository(Article::class)->find($id);

    if (!$article || !$article->getImage()) {
        throw $this->createNotFoundException('Article or image not found');
    }

    // Return a response with the image content
    // You might need to adjust the content type based on the image type (e.g., JPEG, PNG)
    $imagePath = 'photo_directory/' . $article->getImage();
    $response = new BinaryFileResponse($imagePath);
    $response->headers->set('Content-Type', 'image/jpeg'); // Adjust content type as needed

    return $response;
}

#[Route('/frontoffice/article/most-viewed', name: 'most_viewed_articles_front')]
public function mostViewedArticlesFront(ArticleRepository $articleRepository): Response
{
    // Fetch the three most viewed articles
    $mostViewedArticles = $articleRepository->findMostViewedArticles(3);
    
    // Increment the view count for each most viewed article
    foreach ($mostViewedArticles as $article) {
        $articleRepository->increaseViewCount($article);
    }

    return $this->render('Front_office/article/most_viewed.html.twig', [
        'mostViewedArticles' => $mostViewedArticles,
    ]);
}


#[Route('/frontoffice/article/{id}/like', name: 'like_article_front', methods: ['POST'])]
public function likeArticle(int $id, EntityManagerInterface $entityManager): JsonResponse
{
    $article = $entityManager->getRepository(Article::class)->find($id);

    if (!$article) {
        throw $this->createNotFoundException('Article not found');
    }

    $article->incrementLikes(); // Assuming you have a method to handle incrementing likes in your Article entity

    $entityManager->flush();

    return new JsonResponse(['likes' => $article->getLikes()]);
}

#[Route('/frontoffice/article/{id}/dislike', name: 'dislike_article_front', methods: ['POST'])]
public function dislikeArticle(int $id, EntityManagerInterface $entityManager): JsonResponse
{
    $article = $entityManager->getRepository(Article::class)->find($id);

    if (!$article) {
        throw $this->createNotFoundException('Article not found');
    }

    $article->incrementDislikes(); // Assuming you have a method to handle incrementing dislikes in your Article entity

    $entityManager->flush();

    return new JsonResponse(['dislikes' => $article->getDislikes()]);
}

}

