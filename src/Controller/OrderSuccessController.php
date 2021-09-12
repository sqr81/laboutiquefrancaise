<?php

namespace App\Controller;

use App\Classe\Cart;
use App\Classe\Mail;
use App\Entity\Order;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class OrderSuccessController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager){
        $this->entityManager = $entityManager;
    }
    /**
     * @Route("/commande/merci/{stripeSessionId}", name="order_success")
     */
    public function index(Cart $cart,$stripeSessionId): Response
    {
        $order = $this->entityManager->getRepository(Order::class)->findOneByStripeSessionId($stripeSessionId);

        if(!$order || $order->getUser() != $this->getUser())
        {
           return $this->redirectToRoute('home');
        }

        if($order->getState() == 0){
            //vider la session "cart"
            $cart->remove();
            //modifier le statut isPaid de notre commande en mettant
            $order->setState(1);
            $this->entityManager->flush();
            //envoyer un email au client pour confirmer la commande
            $mail = new Mail();
            $content = "Bonjour " .$order->getUser()->getFirstname()."<br/>Merci de votre commande sur la boutique dédiée au made in France<br><br/>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum. ";
            $mail->send($order->getUser()->getEmail(), $order->getUser()->getFirstname(), 'Votre commande la Boutique Française est bien validée.', $content);

        }
            //afficher les quelques infos de la commande de l utilisateur
        return $this->render('order_success/index.html.twig',[
            'order' => $order,
        ]);
    }
}
