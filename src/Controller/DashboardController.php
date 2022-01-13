<?php

namespace App\Controller;

use App\Repository\CandidatRepository;
use App\Repository\EditionRepository;
use App\Repository\VoteRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin")
 */
class DashboardController extends AbstractController
{
    private $voteRepository;
    private $candidatRepository;
    private $editionRepository;

    /**
     * @param $voteRepository
     * @param $candidatRepository
     * @param $editionRepository
     */
    public function __construct(VoteRepository $voteRepository, CandidatRepository $candidatRepository, EditionRepository $editionRepository)
    {
        $this->voteRepository = $voteRepository;
        $this->candidatRepository = $candidatRepository;
        $this->editionRepository = $editionRepository;
    }

    /**
     * @Route("/", name="dashboard")
     */
    public function index(): Response
    {
        $votes=$this->voteRepository->findBy(['status'=>"ACCEPTED"]);
        return $this->render('dashboard/index.html.twig', [
            'controller_name' => 'DashboardController',
            'title'=>"Dashboard"
        ]);
    }
}
