<?php

namespace App\Controller;

use App\Entity\Edition;
use App\Form\EditionType;
use App\Repository\EditionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin/edition")
 */
class EditionController extends AbstractController
{
    /**
     * @Route("/", name="edition_index", methods={"GET"})
     */
    public function index(EditionRepository $editionRepository): Response
    {
        return $this->render('edition/index.html.twig', [
            'editions' => $editionRepository->findAll(),
            'title'=>"Editions"
        ]);
    }

    /**
     * @Route("/new", name="edition_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $edition = new Edition();
        $form = $this->createForm(EditionType::class, $edition);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($edition);
            $entityManager->flush();

            return $this->redirectToRoute('edition_index');
        }

        return $this->render('edition/new.html.twig', [
            'edition' => $edition,
            'form' => $form->createView(),
            'title'=>"Ajouter une Edition"
        ]);
    }

    /**
     * @Route("/{id}", name="edition_show", methods={"GET"})
     */
    public function show(Edition $edition): Response
    {
        return $this->render('edition/show.html.twig', [
            'edition' => $edition,
            'title'=>"Editer une Edition"
        ]);
    }

    /**
     * @Route("/{id}/edit", name="edition_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Edition $edition): Response
    {
        $form = $this->createForm(EditionType::class, $edition);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('edition_index');
        }

        return $this->render('edition/edit.html.twig', [
            'edition' => $edition,
            'form' => $form->createView(),
            'title'=>"Editer une Edition"
        ]);
    }

    /**
     * @Route("/{id}", name="edition_delete", methods={"POST"})
     */
    public function delete(Request $request, Edition $edition): Response
    {
        if ($this->isCsrfTokenValid('delete'.$edition->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($edition);
            $entityManager->flush();
        }

        return $this->redirectToRoute('edition_index');
    }
}
