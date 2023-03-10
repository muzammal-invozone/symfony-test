<?php

namespace App\Controller;

use App\Entity\Fruits;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

class FruitsController extends AbstractController
{
    public $entityManager;
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/fruits', name: 'app_fruits')]
    public function index(Request $request , PaginatorInterface $paginator): Response
    {
        $search = $request->query->get('searchValue') ?  $request->query->get('searchValue') :  '';
        $page = $request->query->getInt('page', 1);
        $limit = $request->query->getInt('limit', 10);

        $query = $this->entityManager->getRepository(Fruits::class)->createQueryBuilder('e')
        ->where('e.name LIKE :query OR e.family LIKE :query')
            ->setParameter('query', '%'.$search.'%');

        $pagination = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            10
        );
       
        return $this->render('fruits.html.twig', [
            'data' => $pagination,
        ]);
    }

    public function addFavourite(Request $request , SerializerInterface $serializer){
        $favourites = $this->entityManager->getRepository(Fruits::class)
        ->findBy(array('status' => 1), null, 10);
        
        $response = new JsonResponse();
        if(count($favourites) >= 10){
            return  $response->setData([
                'status' => 'error',
                'message' => 'Favourite limit reached',
            ]);
        }

        $id = $request->get('id');        
        $status = $request->get('status');   

        $fruit = $this->entityManager->getRepository(Fruits::class)->find($id);
        $fruit->setStatus($status);
        $this->entityManager->flush();

        $fruitData = $serializer->normalize($fruit, null, [
            AbstractNormalizer::ATTRIBUTES => ['id', 'name','genus','family','status'],
        ]);
        
        $response->setData([
            'status' => 'success',
            'message' => 'Fruit Add to favourite',
            'data' => $fruitData
        ]);
        return $response;
    }
}
