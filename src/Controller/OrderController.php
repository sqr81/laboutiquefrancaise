<?php

namespace App\Controller;

use App\Classe\Cart;
use App\Entity\Order;
use App\Entity\OrderDetails;
use App\Form\OrderType;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\Checkout\Session;
use Stripe\Stripe;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class OrderController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/commande", name="order")
     */
    public function index(Cart $cart, Request $request): Response
    {
        //l utilisateur a t il une adresse?
        if (!$this->getUser()->getAddresses()->getValues())
        {
            return $this->redirectToRoute('account_address_add');
        }

        $form = $this->createForm(OrderType::class, null, [
            //on recupere le user
            'user' => $this->getUser()
        ]);



        return $this->render('order/index.html.twig', [
            'form' =>$form->createView(),
            'cart' =>$cart->getFull(),
        ]);
    }

    /**
     * @Route("/commande/recapitulatif", name="order_recap", methods={"POST"})
     * @throws \Stripe\Exception\ApiErrorException
     */
    public function add(Cart $cart, Request $request): Response
    {

        $form = $this->createForm(OrderType::class, null, [
            //on recupere le user
            'user' => $this->getUser()
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid())
        {
            $date = new \DateTime();
            $carriers = $form->get('carriers')->getData();
            $delivery = $form->get('adresses')->getData();
            $delivery_content = $delivery->getFirstname().' '. $delivery->getLastname();
            $delivery_content .= '<br/>'.$delivery->getPhone();

            if($delivery->getCompany())
            {
                $delivery_content .= '<br/>'.$delivery->getCompany();
            }

            $delivery_content .= '<br/>'.$delivery->getAddress();
            $delivery_content .= '<br/>'.$delivery->getPostal().' '.$delivery->getCity();
            $delivery_content .= '<br/>'.$delivery->getCountry();

            //dd($delivery_content);

            //dd($form->getData());
            //enregistrer ma commande Order()
            $order = new Order();
            $order->setUser($this->getUser());
            $order->setCreatedAt($date);
            $order->setCarrierName($carriers->getName());
            $order->setCarrierPrice($carriers->getPrice());
            $order->setDelivery($delivery_content);
            $order->setIsPaid(0);

            $this->entityManager->persist($order);

            //stripe
            $products_for_stripe = [];
            $YOUR_DOMAIN = 'https://127.0.0.1:8000';

            //enregistrer mon produit OrderDetail()
            foreach ($cart->getFull() as $product)
            {
                $orderDetails = new OrderDetails();
                $orderDetails ->setMyOrder($order);
                $orderDetails ->setProduct($product['product']->getName());
                $orderDetails ->setQuantity($product['quantity']);
                $orderDetails ->setPrice($product['product']->getPrix());
                $orderDetails ->setTotal($product['product']->getPrix() * ($product['quantity']));

                $this->entityManager->persist($orderDetails);

                $products_for_stripe[] = [
                    'price_data' => [
                        'currency' => 'eur',
                        'unit_amount' => $product['product']->getPrix(),
                        'product_data' => [
                            'name' => $product['product']->getName(),
                            'images' => [$YOUR_DOMAIN."/uploads/".$product['product']->getIllustration()],
                            ],

                    ],
                    'quantity' => $product['quantity'],];
            }
            //dd($products_for_stripe);


            //$this->entityManager->flush();//on commente pour eviter de surcharger la page  à chaque raffrachissement

            //Gestion de Stripe
            Stripe::setApiKey('sk_test_51JI75WFC3ueao0OhpkCnhW81xCzQrxv5D7bpo7IvhvmYmyfibUijsKPOJhHJAstbTtfjRfiZeumRjsfboMrTE4Cn00K5ArHYQl');

            $checkout_session = Session::create([

                'payment_method_types' => ['card'],
                'line_items' => [
                    $products_for_stripe
                ],
                'mode'=> 'payment',
                'success_url'=> $YOUR_DOMAIN . '/success.html',
                'cancel_url'=> $YOUR_DOMAIN . '/cancel.html',
            ]);

            dump($checkout_session->id);
            dd($checkout_session);
            return $this->render('order/add.html.twig', [

                'cart' =>$cart->getFull(),
                'carrier'=> $carriers,
                'delivery' => $delivery_content,
            ]);
        }

        return $this->redirectToRoute('cart');


    }
}
