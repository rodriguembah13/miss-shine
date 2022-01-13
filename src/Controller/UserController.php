<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @Route("/user")
 *
 */
class UserController extends AbstractController
{
    private $passwordEncoder;
    private $userRepository;

    /**
     * UserController constructor.
     * @param $passwordEncoder
     * @param $userRepository
     */
    public function __construct(UserPasswordEncoderInterface $passwordEncoder, UserRepository $userRepository)
    {
        $this->passwordEncoder = $passwordEncoder;
        $this->userRepository = $userRepository;
    }

    /**
     * @Route("/", name="user_index", methods={"GET"})
     */
    public function index(UserRepository $userRepository): Response
    {
        return $this->render('user/index.html.twig', [
            'users' => $userRepository->findAll(),
            'title'=>"Users"
        ]);
    }

    /**
     * @Route("/new", name="user_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user)
        ->add('imageFilename',FileType::class,[
            'mapped'=>false,
            'label'=>'Photo'
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $plainPassword= $form['password']->getData();
            $encodedPassword = $this->passwordEncoder->encodePassword($user, $plainPassword);
            $user->setPassword($encodedPassword);
            $user->setPhone("675895623");
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
                $user->setImageFilename($newFilename);
            }
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('user_index');
        }

        return $this->render('user/new.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
            'title'=>"User"
        ]);
    }

    /**
     * @Route("/{id}", name="user_show", methods={"GET"})
     */
    public function show(User $user): Response
    {
        return $this->render('user/show.html.twig', [
            'user' => $user,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="user_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, User $user): Response
    {
        $form = $this->createForm(UserType::class, $user)->add('imageFilename',FileType::class,[
            'mapped'=>false,
            'label'=>'Photo',
            'required'=>false,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword= $form['password']->getData();
            $encodedPassword = $this->passwordEncoder->encodePassword($user, $plainPassword);
            $user->setPassword($encodedPassword);
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
                $user->setImageFilename($newFilename);
            }
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('user_index');
        }

        return $this->render('user/edit.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="user_delete", methods={"POST"})
     */
    public function delete(Request $request, User $user): Response
    {
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($user);
            $entityManager->flush();
        }

        return $this->redirectToRoute('user_index');
    }
    /**
     * @Route("/get/getusers", name="user_index_ajax", methods={"GET"})
     */
    public function getUsers(Request $request): JsonResponse
    {
        $array = [];
        $idx = 0;
        try {
            $lists = $this->userRepository->findBy([]);
            foreach ($lists as $local) {
                $temp = [
                    'id' => $local->getId(),
                    'username' => $local->getUsername(),
                    'fullname' => $local->getFullName(),
                    'email' => $local->getEmail(),
                    'roles' => $local->getRoles(),
                ];
                $array[$idx++] = $temp;
            }

            return new JsonResponse($array,Response::HTTP_OK);
        } catch (\Exception $ex) {
            //Log exception
            return new JsonResponse($ex->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    /**
     * @Route("/profil", name="profil")
     */
    public function profil(): Response
    {
        $user = $this->getUser();
        if (!is_object($user) || !$user instanceof UserInterface) {
            throw new AccessDeniedException('This user does not have access to this section.');
        }
        return $this->render('user/profil.html.twig', [
            'user' => $user,
        ]);
}
}
