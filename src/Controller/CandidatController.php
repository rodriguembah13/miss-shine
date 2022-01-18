<?php

namespace App\Controller;

use App\Entity\Candidat;
use App\Entity\Vote;
use App\Form\CandidatType;
use App\Repository\CandidatRepository;
use App\Repository\EditionRepository;
use Doctrine\ORM\QueryBuilder;
use Omines\DataTablesBundle\Adapter\Doctrine\ORMAdapter;
use Omines\DataTablesBundle\Column\DateTimeColumn;
use Omines\DataTablesBundle\Column\TextColumn;
use Omines\DataTablesBundle\Column\TwigColumn;
use Omines\DataTablesBundle\DataTableFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin/candidat")
 */
class CandidatController extends AbstractController
{
    private $candidatRepository;
    private $editionRepository;
    private $dataTableFactory;
    /**
     * @param $candidatRepository
     */
    public function __construct(EditionRepository $editionRepository,DataTableFactory $dataTableFactory,CandidatRepository $candidatRepository)
    {
        $this->candidatRepository = $candidatRepository;
        $this->dataTableFactory = $dataTableFactory;
        $this->editionRepository=$editionRepository;
    }

    /**
     * @Route("/", name="candidat_index", methods={"GET","POST"})
     */
    public function index(Request $request,CandidatRepository $candidatRepository): Response
    {
        $table = $this->dataTableFactory->create()
            ->add('idx', TextColumn::class,[
                'field' => 'e.id',
                'className'=>"text-center"
            ])
            ->add('photo', TwigColumn::class, [
                'className' => 'bold',
                'orderable'=>false,
                'template' => 'candidat/photo.html.twig',
                'render' => function ($value, $context) {
                    return $value;
                }
            ])
            ->add('firstname', TextColumn::class, [
                'field' => 'e.firstname',
                'className'=>"text-center"
            ])
            ->add('lastname', TextColumn::class, [
                'field' => 'e.lastname',
                'className'=>"text-center"
            ])
            ->add('nombreVote', TextColumn::class,[
                'label'=>'dt.columns.nombrevote',
                'field' => 'e.vote',
                'className'=>"text-center"
            ])
/*            ->add('monaie', TextColumn::class)*/
            ->add('position', TextColumn::class, [
                'className'=>"text-center",
               'label'=>'dt.columns.position',

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
                'template' => 'candidat/buttonbar.html.twig',
                'render' => function ($value, $context) {
                    return $value;
                }])
            ->createAdapter(ORMAdapter::class, [
                'entity' => Candidat::class,
                'query' => function (QueryBuilder $builder) {
                    $builder
                        ->select('e')
                        ->from(Candidat::class, 'e')
                        ->orderBy("e.position","ASC")
                    ;
                },
            ])->handleRequest($request);
        if ($table->isCallback()) {
            return $table->getResponse();
        }
        return $this->render('candidat/index.html.twig', [
           // 'candidats' => $candidatRepository->findAll(),
            'title'=>"Candidats",
            'datatable' => $table
        ]);
    }
    protected function generateRang(){
        $edition=$this->editionRepository->findOneBy(['status'=>'Publie']);
        $candidats=$this->candidatRepository->findByEdition($edition);
        foreach ($candidats as $candidat){
            $j = 0;
            for ($i = 0; $i < sizeof($candidats); $i++) {
                if ($candidat->getVote() === $candidats[$i]->getVote()) {
                    $j = sizeof($candidats) - $i;
                }
            }
            $candidat->setPosition($j);
        }
    }
    protected function getRangVoting(Candidat $candidat)
    {
        $edition=$this->editionRepository->findOneBy(['status'=>'Publie']);
        $candidats=$this->candidatRepository->findByEdition($edition);

        $j = 0;
        for ($i = 0; $i < sizeof($candidats); $i++) {
            if ($candidat->getVote() === $candidats[$i]->getVote()) {
                $j = sizeof($candidats) - $i;
            }
        }
        return $j;
    }
    /**
     * @Route("/new", name="candidat_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $candidat = new Candidat();
        $form = $this->createForm(CandidatType::class, $candidat)
            ->add('imageFilename',FileType::class,[
                'mapped'=>false,
                'required'=>true,
                'label'=>'Photo'
            ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $imageFilename = $form['imageFilename']->getData();
            if ($imageFilename) {
                $destination = $this->getParameter('kernel.project_dir').'/public/uploads';
                $originalFilename = pathinfo($imageFilename->getClientOriginalName(), PATHINFO_FILENAME);
                $newFilename = $originalFilename.'-'.uniqid().'.'.$imageFilename->guessExtension();

                try {
                    $imageFilename->move(
                        $destination,
                        $newFilename
                    );
                } catch (FileException $e) {
                }
                $candidat->setPhoto($newFilename);
            }
            $url=uniqid()."-".uniqid();
            $candidat->setGenericurl($url);
            $candidat->setVote(0);
            $entityManager->persist($candidat);
            $this->generateRang();
            $entityManager->flush();

            return $this->redirectToRoute('candidat_index');
        }

        return $this->render('candidat/new.html.twig', [
            'candidat' => $candidat,
            'form' => $form->createView(),
            'title'=>"Ajouter une candidate"
        ]);
    }

    /**
     * @Route("/{id}", name="candidat_show", methods={"GET"})
     */
    public function show(Candidat $candidat): Response
    {
        return $this->render('candidat/show.html.twig', [
            'candidat' => $candidat,
            'title'=>"editer une candidate"
        ]);
    }

    /**
     * @Route("/{id}/edit", name="candidat_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Candidat $candidat): Response
    {
        $form = $this->createForm(CandidatType::class, $candidat)
            ->add('imageFilename',FileType::class,[
                'mapped'=>false,
                'required'=>false,
                'label'=>'Photo'
            ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFilename = $form['imageFilename']->getData();
            if ($imageFilename) {
                $destination = $this->getParameter('kernel.project_dir').'/public/uploads';
                $originalFilename = pathinfo($imageFilename->getClientOriginalName(), PATHINFO_FILENAME);
                $newFilename = $originalFilename.'-'.uniqid().'.'.$imageFilename->guessExtension();

                try {
                    $imageFilename->move(
                        $destination,
                        $newFilename
                    );
                } catch (FileException $e) {
                }
                $candidat->setPhoto($newFilename);
            }
            $url=uniqid()."-".uniqid();
            if ($candidat->getGenericurl()==null){
                $candidat->setGenericurl($url);
            }
           // $candidat->setPosition($this->getRangVoting($candidat));
$this->generateRang();
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('candidat_index');
        }

        return $this->render('candidat/edit.html.twig', [
            'candidat' => $candidat,
            'form' => $form->createView(),
            'title'=>"editer une candidate"
        ]);
    }

    /**
     * @Route("/{id}", name="candidat_delete", methods={"POST"})
     */
    public function delete(Request $request, Candidat $candidat): Response
    {
        if ($this->isCsrfTokenValid('delete'.$candidat->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($candidat);
            $entityManager->flush();
        }

        return $this->redirectToRoute('candidat_index');
    }
    /**
     * @Route("/delete/ajax", name="candidat_delete_ajax", methods={"GET"})
     */
    public function deleteAjax(Request $request): JsonResponse
    {
        $campaign = $this->candidatRepository->find($request->get('item_id'));
        try {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($campaign);
            $entityManager->flush();
            $this->addFlash('success', 'operation effectue avec success');
        } catch (\Exception $exception) {
            $this->addFlash('error', 'operation impossible'.$exception->getMessage());
        }

        return new JsonResponse('success', 200);
    }
    /**
     * @Route("/getcandidat/ajax", name="getcandidat_ajax", methods={"GET"})
     */
    public function getcandidatAjax(Request $request): JsonResponse
    {
        $candidat = $this->candidatRepository->find($request->get('item_id'));
        $data=[
           'id'=> $candidat->getId(),
            'firstname'=>$candidat->getFirstname(),
            'lastname'=>$candidat->getLastname(),
            'photo'=>$candidat->getPhoto(),
        ];

        return new JsonResponse($data, 200);
    }
}
