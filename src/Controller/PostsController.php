<?php

namespace App\Controller;

use App\Entity\Posts;
use App\Repository\PostsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/posts')]
class PostsController extends AbstractController
{

    // Retourner tous les posts avec normalisation à la main 
    // #[Route('/', name: 'list', methods: ['GET'])]
    // public function list(PostsRepository $postsRepo): JsonResponse
    // {
    //     $posts = $postsRepo->findAll();
    //     // Normaliser les posts --> tranformer un objet php en tableau associatif
    //     $data = [];
    //     foreach ($posts as $post) {
    //         $data[] = [
    //             'id' => $post->getId(),
    //             'title' => $post->getTitle(),
    //             'content' => $post->getContent()
    //         ];
    //     }
    //     return $this->json($data);
    // }

    // Retourner tous les posts avec le serializer de symfony
    #[Route('/', name: 'list', methods: ['GET'])]
    public function list(PostsRepository $postsRepo, SerializerInterface $serializer): JsonResponse
    {
        $posts = $postsRepo->findAll();
        // Normaliser les posts --> tranformer un objet php en tableau associatif
        $data = $serializer->serialize($posts,'json');
        // Ici on utilise l'instance de JsonResponse pour retourner une réponse en json, le dernier param nous permet de signifier qu'on lui envoie déjà du json et donc il sait qu'il ne faut pas le convertir en json.
        return new JsonResponse($data, 200, [], true);
    }



    // Retourner un post particulier
    // #[Route('/{id}', name: 'show', methods: ['GET'])]
    // public function show(Posts $post): JsonResponse
    // {
    //     $data = [];
    //         $data[] = [
    //             'id' => $post->getId(),
    //             'title' => $post->getTitle(),
    //             'content' => $post->getContent()
    //         ];
    //     return $this->json($data);
    // }

    // Retourner un post particulier en utilisant le serializer et les groupes de sérialisation
    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(Posts $post, SerializerInterface $serializer): JsonResponse
    {
        // Ici le troisième paramètre spécifie le groupe de sérialisation à utiliser, les groupes de sérialisation sont définis dans l'entité Posts
      $post = $serializer->normalize($post, 'json', ['groups' => 'grp1']);
        return $this->json($post);
    }


    // Ajouter un post
    #[Route('/create', name: 'create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        if(!isset($data['title']) || !isset($data['content'])){
            return $this->json(['error' => 'Missing title or content'], 400);
        }
        $post = new Posts();
        $post->setTitle($data['title']);
        $post->setContent($data['content']);

        $em->persist($post);
        $em->flush();

        return $this->json(['message'=> 'Post created'], 201);
    }


    // Modifier un post
    #[Route('/update/{id}', name: 'update', methods: ['PUT'])]
    public function update(Posts $post, Request $request, EntityManagerInterface $em): JsonResponse
    {
        if(!$post){
            return $this->json(['error' => 'Post not found'], 404);
        }

        $data = json_decode($request->getContent(), true);

        if(isset($data['title'])){
            $post->setTitle($data['title']);
        }
        if(isset($data['content'])){
            $post->setContent($data['content']);
        }

        $em->persist($post);
        $em->flush();

        return $this->json(['message'=> 'Post updated'], 200);
    }

    // Supprimer un post
    #[Route('/delete/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(Posts $post, EntityManagerInterface $em): JsonResponse
    {
        if(!$post){
            return $this->json(['error' => 'Post not found'], 404);
        }


        $em->remove($post);
        $em->flush();

        return $this->json(['message'=> 'Post deleted'], 200);
    }
}
