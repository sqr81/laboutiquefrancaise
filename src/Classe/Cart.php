<?php

namespace App\Classe;

use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class Cart
{
    private $session;
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager, SessionInterface $session)
    {
        $this->session = $session;
        $this->entityManager = $entityManager;
    }

    public function add($id)
    {
        $cart = $this->session->get('cart', []);

        if (!empty($cart[$id])){
            $cart[$id]++;
        }
        else{
            $cart[$id] = 1;
        }

        $this->session->set('cart', $cart);
    }

    public function get()
    {
        return $this->session->get('cart');
    }

    public function remove()
    {
        return $this->session->remove('cart');
    }

    public function delete($id)
    {
        //on récupère le cart
        $cart = $this->session->get('cart', []);
        //unset pour retirer du tableau cart l entree cart qui a l'id que je souhaite supprimer
        unset($cart[$id]);
        //on retourne le nouveau cart
        return $this->session->set('cart', $cart);
    }

    public function decrease($id)
    {
        //on récupère le cart
        $cart = $this->session->get('cart', []);
        //verification que le cart ait une quantité >1
        if ($cart [$id] > 1){
            //retirer une quantité (-1)
            $cart [$id] -- ;

        }
        else{
            //supprimer mon produit
            unset($cart[$id]);
        }
        return $this->session->set('cart', $cart);
    }

    public function getFull()
    {
        $cartComplete = [];

        if($this->get()) {
            foreach ($this->get() as $id => $quantity) {
                $product_object = $this->entityManager->getRepository(Product::class)->findOneById($id);
                //sécurité pour éviter d inserer un faux id dans l url et donc un produit dans la bdd
                if (!$product_object){
                    $this->delete($id);
                    continue;
                }
                $cartComplete[] = [
                    'product' => $product_object,
                    'quantity' => $quantity,
                ];
            }

        }
        return $cartComplete;
    }
}