<?php

namespace App\Controller;

use App\Entity\Post;
use App\Entity\User;
use App\Form\PostType;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Illuminate\Http\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse as HttpFoundationJsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class PostController extends AbstractController
{

    private $em;
    
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    #[Route('/', name: 'app_post')]
    public function index(Request $request, SluggerInterface $slugger): Response
    {
        $post = new Post();
        $posts = $this->em->getRepository(Post::class)->findAllPosts();
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
                $newFile = $form->get('file')->getData();

                  if ($newFile) {
                  $originalFilename = pathinfo($newFile->getClientOriginalName(), PATHINFO_FILENAME);
                
                  $safeFilename = $slugger->slug($originalFilename);
                  $newFilename = $safeFilename.'-'.uniqid().'.'.$newFile->guessExtension();
  
                  // Move the file to the directory where brochures are stored
                  try {
                      $newFile->move(
                          $this->getParameter('files_directory'),
                          $newFilename
                      );
                  } catch (FileException $e) {
                      throw new \Exception('Opa, tem algum problema com seu arquivo');
                  }
  
                  // updates the 'brochureFilename' property to store the PDF file name
                  // instead of its contents
                  $post->setFile($newFilename);
              }
  
            
            $url = str_replace(" ", "-", $form->get('title')->getData());
            $post->setUrl($url);
            $user = $this->em->getRepository(User::class)->find(1);
            $post->setUser($user);
            $this->em->persist($post);
            $this->em->flush();
            return $this->redirectToRoute('app_post');
        }

        return $this->render('post/index.html.twig', [
            'form' => $form->createView(),
            'posts' => $posts
        ]);

    }

    #[Route('/post/details/{id}', name: 'postDetails')]
    public function postDetails(Post $post)
    {
        return $this->render('post/post-details.html.twig', ['post' => $post]);
    }

    // #[Route('/insert/post', name: 'insert_post')]
    // public function insert(){
    //     $post = new Post('Meu post criado', 'debate', 'esse foi postado pelo construtor', 'arquivo', 'outra-outra-post');
    //     $user = $this->em->getRepository(User::class)->find(1);
    //     $post->setUser($user);
    //     $this->em->persist($post);
    //     $this->em->flush();
    //     return new HttpFoundationJsonResponse((['success' => true]));
    // }

    // #[Route('/update/post/', name: 'insert_post')]
    // public function update(){
    //     $post = $this->em->getRepository(Post::class)->find(1);
    //     $post->setTitle('novo titulo');
    //     $this->em->persist($post);
    //     $this->em->flush();
    //     return new HttpFoundationJsonResponse((['success' => true]));
    // }

    #[Route('/remove/post/{id}', name: 'remove_post')]
    public function remove(Post $id){
        $post = $this->em->getRepository(Post::class)->find($id);
        $this->em->remove($post);
        $this->em->flush();
        return new HttpFoundationJsonResponse((['success' => true]));
    }
}