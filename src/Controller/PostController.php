<?php

namespace App\Controller;

use App\Entity\Post;
use App\Entity\User;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Illuminate\Http\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse as HttpFoundationJsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PostController extends AbstractController
{

    private $em;
    
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    #[Route('/post/{id}', name: 'app_post')]
    public function index($id): Response
    {
        $posts = $this->em->getRepository(Post::class)->find($id);
        $custom_post = $this->em->getRepository(Post::class)->findPost($id);
        return $this->render('post/index.html.twig', [
            'posts' => $posts,
            'custom_post' => $custom_post
        ]);
    }

    #[Route('/insert/post', name: 'insert_post')]
    public function insert(){
        $post = new Post('Meu post criado', 'debate', 'esse foi postado pelo construtor', 'arquivo', 'outra-outra-post');
        $user = $this->em->getRepository(User::class)->find(1);
        $post->setUser($user);
        $this->em->persist($post);
        $this->em->flush();
        return new HttpFoundationJsonResponse((['success' => true]));
    }

    #[Route('/update/post/', name: 'insert_post')]
    public function update(){
        $post = $this->em->getRepository(Post::class)->find(1);
        $post->setTitle('novo titulo');
        $this->em->persist($post);
        $this->em->flush();
        return new HttpFoundationJsonResponse((['success' => true]));
    }

    #[Route('/remove/post/', name: 'insert_post')]
    public function remove(){
        $post = $this->em->getRepository(Post::class)->find(1);
        $this->em->remove($post);
        $this->em->flush();
        return new HttpFoundationJsonResponse((['success' => true]));
    }
}


// Opção 1

// class PostController extends AbstractController
// {
//     #[Route('/post/{id}', name: 'app_post')]
//     public function index(Post $post): Response
//     {
//         dump($post);
//         return $this->render('post/index.html.twig', [
//             'controller_name' => [
//                 'saludo' => 'Hola mundo', 
//                 'nombre' => 'Germanno'
//             ], 'post' => $post,
//         ]);
//     }
// }
