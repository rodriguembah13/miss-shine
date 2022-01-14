<?php

namespace App\Controller;

use App\Entity\Vote;
use App\Form\VoteType;
use App\Repository\VoteRepository;
use Doctrine\ORM\QueryBuilder;
use Omines\DataTablesBundle\Adapter\Doctrine\ORMAdapter;
use Omines\DataTablesBundle\Column\DateTimeColumn;
use Omines\DataTablesBundle\Column\TextColumn;
use Omines\DataTablesBundle\Column\TwigColumn;
use Omines\DataTablesBundle\DataTableFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin/vote")
 */
class VoteController extends AbstractController
{
    private $dataTableFactory;

    /**
     * @param $dataTableFactory
     */
    public function __construct(DataTableFactory $dataTableFactory)
    {
        $this->dataTableFactory = $dataTableFactory;
    }

    /**
     * @Route("/", name="vote_index", methods={"GET","POST"})
     */
    public function index(Request $request): Response
    {
        $table = $this->dataTableFactory->create()
            ->add('idx', TextColumn::class,[
                'field' => 'e.id',
                'className'=>"text-center"
            ])
            ->add('candidat', TextColumn::class, [
                'field' => 'c.firstname',
                'className'=>"text-center"
            ])
            ->add('nombreVote', TextColumn::class,[
                'label'=>'dt.columns.nombrevote',
                'className'=>"text-center"
            ])
            ->add('monaie', TextColumn::class)
            ->add('status', TextColumn::class, [
                'className'=>"text-center",
                'render' => function ($value, $context) {
                if ($value=="PENDING"){
                    return '<span class="btn btn-sm btn-warning">'.$value.'</span>';
                }elseif ($value=="REFUSED"){
                    return '<span class="btn btn-sm btn-danger">'.$value.'</span>';
                }else{
                    return '<span class="btn btn-sm btn-success">'.$value.'</span>';
                }

                }
            ])
            ->add('createdAt',  DateTimeColumn::class, [
                'format' => 'd-m-Y',
                'className'=>"text-center",
                'orderable' => false,
                'searchable' => false,
                'label'=>'dt.columns.createdat'
            ])

            ->add('id', TwigColumn::class, [
                'className' => 'buttons text-center',
                'label' => 'action',
                'template' => 'vote/buttonbar.html.twig',
                'render' => function ($value, $context) {
                    return $value;
                }])
            ->createAdapter(ORMAdapter::class, [
                'entity' => Vote::class,
                'query' => function (QueryBuilder $builder) {
                    $builder
                        ->select('e')
                        ->addSelect('c')
                        ->from(Vote::class, 'e')
                        ->leftJoin('e.candidat', 'c')
                    ;
                },
            ])->handleRequest($request);
        if ($table->isCallback()) {
            return $table->getResponse();
        }
        return $this->render('vote/index.html.twig', [
            'votes' => [],
            'title'=>"Votes",
            'datatable' => $table
        ]);
    }

    /**
     * @Route("/new", name="vote_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $vote = new Vote();
        $form = $this->createForm(VoteType::class, $vote);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($vote);
            $entityManager->flush();

            return $this->redirectToRoute('vote_index');
        }

        return $this->render('vote/new.html.twig', [
            'vote' => $vote,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="vote_show", methods={"GET"})
     */
    public function show(Vote $vote): Response
    {
        return $this->render('vote/show.html.twig', [
            'vote' => $vote,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="vote_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Vote $vote): Response
    {
        $form = $this->createForm(VoteType::class, $vote);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('vote_index');
        }

        return $this->render('vote/edit.html.twig', [
            'vote' => $vote,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="vote_delete", methods={"POST"})
     */
    public function delete(Request $request, Vote $vote): Response
    {
        if ($this->isCsrfTokenValid('delete'.$vote->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($vote);
            $entityManager->flush();
        }

        return $this->redirectToRoute('vote_index');
    }
}
