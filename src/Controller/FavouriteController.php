<?php

namespace App\Controller;
use App\Entity\Fruits;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;

class FavouriteController extends AbstractController
{
    public $entityManager;
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/favourite', name: 'app_favourite')]
    public function index(): Response
    {
        $favourites = $this->entityManager->getRepository(Fruits::class)
        ->findBy(array('status' => 1), null, 10);

        return $this->render('favourite.html.twig', [
            'data' => $favourites,
        ]);
    }
}
