<?php

namespace App\Controller;

use App\Entity\Candidat;
use App\Entity\Vote;
use App\Repository\CandidatRepository;
use App\Repository\EditionRepository;
use App\Repository\VoteRepository;
use App\Utils\ClientServer;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends AbstractController
{
    private $candidatRepository;
    private $editionrepository;
    private $logger;
    private $params;
    private $voteRepository;

    /**
     * @param $candidatRepository
     * @param $editionrepository
     */
    public function __construct(VoteRepository $voteRepository,ParameterBagInterface $paramConverter, LoggerInterface $logger, CandidatRepository $candidatRepository, EditionRepository $editionrepository)
    {
        $this->candidatRepository = $candidatRepository;
        $this->editionrepository = $editionrepository;
        $this->logger = $logger;
        $this->params = $paramConverter;
        $this->voteRepository=$voteRepository;
    }

    /**
     * @Route("/", name="home")
     */
    public function index(): Response
    {
        return $this->render('default/index.html.twig', [
            'candidats' => $this->candidatRepository->findAll(),
        ]);
    }

    /**
     * @Route("/show/{url}", name="showcandidat")
     */
    public function show(Request $request): Response
    {
        $candidat = $this->candidatRepository->findOneBy(["genericurl" => $request->get("url")]);
        if ($candidat == null) {
            throw  new BadRequestException('This user does not have access to this section.');
        }
        $this->logger->info("-------ic" . $request->get("url"));
        return $this->render('default/detail-candidat.html.twig', [
            'candidat' => $candidat,
        ]);
    }

    /**
     * @Route("/sendpaiementcinetpay/ajax", name="sendpaiementcinetpay", methods={"POST"})
     */
    public function sendpaiementcinetpay(Request $request): Response
    {
        $candidat_id = $request->get("candidatid");
        $candidat = $this->candidatRepository->find($candidat_id);
        $client_phone = $request->get("clientphone");
        $client_currency = $request->get("clientcurrency");
        $client_votes = $request->get("clientvote");
        $endpoints = "payment";
        $notify_url = $this->generateUrl('notifyurlajax', ['vote' => $client_votes, 'candidat' => $candidat_id]);
        $transaction_id = "";
        $allowed_characters = [1, 2, 3, 4, 5, 6, 7, 8, 9, 0];
        for ($i = 1; $i <= 12; ++$i) {
            $transaction_id .= $allowed_characters[rand(0, count($allowed_characters) - 1)];
        }
        $reference = $transaction_id;
        $product = $candidat->getFirstname() . " " . $candidat->getLastname();
        $amount = $this->getAmount($client_votes);
        $redirect_url = $this->generateUrl('home');
        $notify_url=$this->params->get('domain').$notify_url;
        $data = array(
            'site_id' => $this->params->get('site_id'),
            'currency' => 'XAF',
            'lang' => 'fr',
            'description' => $product,
            'amount' => $amount,
            'returnUrl' => $redirect_url,
            'transaction_id' => $reference,
            'apikey' => $this->params->get('api_key'),
            'notify_url' => $notify_url,
            'channels' => 'ALL',
        );
        $this->logger->info(json_encode($data));
       // $this->createVote($candidat,$client_votes);
        $client = new ClientServer();
        $response = $client->postfinal($endpoints, $data);
        dump($response);
      //  $this->logger->info($response);
        if ($response['code'] == "201") {
            $this->createVote($candidat,$client_votes);
            $url = $response["data"]["payment_url"];
            $link_array = explode('/', $url);
            return $this->redirect($url);
        }

       // return $this->redirectToRoute("home");
        return $this->render('default/detail-candidat.html.twig', [
            'candidat' => $candidat,
        ]);
    }

    /**
     * @Route("/sendpaiementcinetpayinternational/ajax", name="sendpaiementcinetpayinternational", methods={"POST"})
     */
    public function sendpaiementcinetpayinternational(Request $request): Response
    {
        $candidat_id = $request->get("candidatid");
        $candidat = $this->candidatRepository->find($candidat_id);
        $client_phone = $request->get("clientphone");
        $client_email = $request->get("clientemail");
        $client_currency = $request->get("clientcurrency");
        $client_votes = $request->get("clientintvote");
        $endpoints = "payment";
        $current_url = "";
        $transaction_id = "";
        $allowed_characters = [1, 2, 3, 4, 5, 6, 7, 8, 9, 0];
        $notify_url = $this->generateUrl('notifyurlajax', ['vote' => $client_votes, 'candidat' => $candidat_id]);
        for ($i = 1; $i <= 12; ++$i) {
            $transaction_id .= $allowed_characters[rand(0, count($allowed_characters) - 1)];
        }
        $reference = $transaction_id;
        $product = "vote miss-shinne ".$candidat->getFirstname() . " " . $candidat->getLastname();
        $amount = $this->convertXAF("USD",$this->getAmountInternational($client_votes));
        $redirect_url = "";
        $notify_url=$this->params->get('domain').$notify_url;
        $data = array(
            'site_id' => $this->params->get('site_id'),
            'currency' => 'XAF',
            'lang' => 'fr',
            'description' => $product,
            'amount' => $amount,
            'returnUrl' => $redirect_url,
            'transaction_id' => $reference,
            'apikey' => $this->params->get('api_key'),
            'notify_url' => $notify_url,
            'channels' => 'ALL',
            'customer_id' => "",
            "customer_name" => "client1",
            "customer_surname" => $candidat->getLastname(),
            "customer_phone_number" => $client_phone,
            "customer_email" => $client_email,
            "customer_address" => "AKWA ",
            "customer_city" => "DOUALA",
            "customer_country" => "CM",
            "alternative_currency" => "USD",
            "customer_zip_code" => "77777"
        );
        dump($data);
        $client = new ClientServer();
        $response = $client->postfinal($endpoints, $data);
        $this->logger->info($reference);
        if ($response['code'] == "201") {
            $this->createVote($candidat,$client_votes);
            $url = $response["data"]["payment_url"];
            $this->logger->info($url);
            $link_array = explode('/', $url);
            return $this->redirect($url);
        }

        return $this->render('default/detail-candidat.html.twig', [
            'candidat' => $candidat,
        ]);
    }

    protected function convertXAF($currency, $amount)
    {
        $val = $amount;
        if ($currency == "USD") {
            $val = $amount * 579.75;
        }
        if ($currency == "EUR") {
            $val = $amount * 655.78;
        }
        return $val;
    }
    protected function getAmountInternational($votes)
    {
        $val = 0;
        if ($votes == 10) {
            $val = 20;
        } elseif ($votes == 50) {
            $val = 30;
        } elseif ($votes == 70) {
            $val = 50;
        } elseif ($votes == 150) {
            $val = 100;
        }
        return $val;
    }
    protected function getAmount($votes)
    {
        $val = 0;
        if ($votes == 1) {
            $val = 100;
        } elseif ($votes == 5) {
            $val = 300;
        } elseif ($votes == 7) {
            $val = 500;
        } elseif ($votes == 15) {
            $val = 1000;
        }
        return $val;
    }
    protected function createVote(Candidat $candidat,$qtevote){
        $entityManager = $this->getDoctrine()->getManager();
        $vote=new Vote();
        $vote->setCandidat($candidat);
        $vote->setNombreVote($qtevote);
        $vote->setMonaie("XAF");
        $vote->setStatus("PENDING");
        $vote->setCreatedAt(new \DateTime('now'));
        $vote->setUpdatedAt(new \DateTime('now'));
        $entityManager->persist($vote);
        $entityManager->flush();
    }
    protected function updateVote(Vote $vote,$status){
        if ($status=="ACCEPTED"){
            $vote->setStatus($status);
            $candidat=$vote->getCandidat();
            $candidat->setVote($candidat->getVote()+$vote->getNombreVote());
        }else{
            $vote->setStatus($status);
        }
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->flush();
    }

    /**
     * @Route("/returnurl/ajax/", name="returnurlajax", methods={"POST","GET"})
     */
    public function returnurl(Request $request): Response
    {


    }

    /**
     * @Route("/notifyurl/ajax", name="notifyurlajax", methods={"POST","GET"})
     */
    public function notifyurl(Request $request): Response
    {
        $site_id=$_POST['cpm_site_id'];
        $transaction=$_POST['cpm_trans_id'];
        $vote_=$this->voteRepository->find($_GET['vote']);
        $base_url = "https://api-checkout.cinetpay.com/v2/payment/check";
        if ($vote_->getStatus() =="PENDING"){
        $data = array(
            'apikey' => $this->params->get('api_key'),
            'site_id' => $site_id,
            'transaction_id' => $transaction
        );
        $client = new ClientServer();
        $response = $client->postfinal($base_url, $data);
        if ($response['data']['status']=="ACCEPTED"){
            $this->updateVote($vote_,'ACCEPTED');
        }elseif ($response['data']['status']=="REFUSED"){
            $this->updateVote($vote_,'REFUSED');
        }
        }
        return new JsonResponse($response,200);
    }

    /**
     * @Route("/sendpaiement/ajax", name="sendpaiementajax", methods={"POST"})
     */
    public function sendpaiement(Request $request): Response
    {
        $candidat_id = $request->get("candidat_id");
        $candidat = $this->candidatRepository->find($candidat_id);
        $client_phone = $request->get("clientphone");
        $client_votes = $request->get("clientvote");
        $this->logger->log(200, $request->get("clientphone"));
        $this->logger->info($request->get("clientvote"));
        $current_url = "";
        $transaction_id = "";
        $allowed_characters = [1, 2, 3, 4, 5, 6, 7, 8, 9, 0];
        for ($i = 1; $i <= 12; ++$i) {
            $transaction_id .= $allowed_characters[rand(0, count($allowed_characters) - 1)];
        }
        $reference = $transaction_id;
        $product = $candidat->getFirstname() . " " . $candidat->getLastname();
        //  $_SESSION['VOTING_ID'] = $id;
        // $_SESSION['VOTING_REFERENCE'] = $reference;
        $SUCCESS_CALLBACK_URL = 'wc_payment_success';
        $FAILURE_CALLBACK_URL = 'wc_payment_failure';
        $SUCCESS_REDIRECT_URL = 'wc_payment_success';
        $FAILURE_REDIRECT_URL = 'wc_payment_failure';
        $data = [
            'amount' => 100,
            'currency_code' => 'XAF',
            'ccode' => 'CM',
            'lang' => 'en',
            'item_ref' => $reference,
            'item_name' => $product,
            'description' => 'Voting session',
            'email' => 'exemple@email.com',
            'phone' => '+237675066919',
            'first_name' => 'Name',
            'last_name' => 'Surname',
            'public_key' => 'PK_1muq3V1baPup9s1JAk6f',
            'logo' => 'https://paymooney.com/images/logo_paymooney2.png',
            'redirectUrl' => $current_url . '?orderpay=' . $reference, //$this->siteUrl . $this->SUCCESS_REDIRECT_URL . $orderIdString,
            //"redirectOnFailureUrl" => $order->get_cancel_order_url(),//$this->siteUrl . $this->FAILURE_REDIRECT_URL . $orderIdString,
            'callbackUrl' => $current_url . '//wc-api/' . $SUCCESS_CALLBACK_URL . $reference,
            'callbackOnFailureUrl' => $current_url . '//wc-api/' . $FAILURE_CALLBACK_URL . $reference,
            'redirectTarget' => 'TOP',
            'merchantCustomerId' => $reference,
            'environement' => 'test',
        ];
        $client = new ClientServer();
        $response = $client->post("payment_url", $data);
        $this->logger->info($response['response']);
        if ($response['response'] == "success") {
            $url = $response["payment_url"];
            $link_array = explode('/', $url);
            return $this->redirect($link_array);
        }

        return $this->redirectToRoute("home");

        //return new JsonResponse($data, 200);
    }

}
