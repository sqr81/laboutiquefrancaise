<?php

namespace App\Controller;

use App\Classe\Mail;
use App\Entity\ResetPassword;
use App\Entity\User;
use App\Form\ResetPasswordType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class ResetPasswordController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/mot-de-passe-oublie", name="reset_password")
     */
    public function index(Request $request): Response
    {
        if ($this->getUser()){
            return $this->redirectToRoute('home');
        }

        if($request->get('email')){
            //dd($request->get('email'));
            $user = $this->entityManager->getRepository(User::class)->findOneByEmail($request->get('email'));
            //dd($user);
            if ($user) {
                //etape1 enregister en bdd la demande de reset avec user token createdAt
                $reset_password = new ResetPassword();
                $reset_password->setUser($user);
                $reset_password->setToken(uniqid());
                $reset_password->setCreatedAt(new \DateTime());

                $this->entityManager->persist($reset_password);
                $this->entityManager->flush();

                //etape2 envoyer un mail à l'utilisateur avec un lien lui permettant de maj son mdp
                $url = $this->generateUrl('update_password', [
                    'token' => $reset_password->getToken()
                ]);


                $content = "Bonjour " . $user->getFirstname() . ",<br>Vous avez demandé à réinitialiser votre mot de passe sur La Boutique Française<br/><br/>";
                $content .= "Merci de bien vouloir cliquer sur le lien suivant pour <a href='" . $url . "'>mettre à jour votre mot de passe</a>";

                $mail = new Mail();
                $mail->send($user->getEmail(), $user->getFirstname() . ' ' . $user->getLastname(), 'Réinitialiser votre mot de passe sur la Boutique Française.', $content);

                $this->addFlash('notice', 'Vous allez recevoir un mail avec la procédure pour réinitialiser votre mot de passe.');
            }else {
                $this->addFlash('notice', 'Cette adresse mail est inconnue');
            }

        }

        return $this->render('reset_password/index.html.twig');
    }

    /**
     * @Route("/modifier-mon-mot-de-passe/{token}", name="update_password")
     */
    public function update(Request $request,$token, UserPasswordHasherInterface $encoder)
    {
        $reset_password = $this->entityManager->getRepository(ResetPassword::class)->findOneByToken($token);

        if(!$reset_password)
        {
            return $this->redirectToRoute('reset_password');
        }

        //vérifier que le createdAt = now-3h
        $now = new \DateTime();
        if ($now > $reset_password->getCreatedAt()->modify('+ 3 hour'))
        {
           $this->addFlash('notice', 'Votre demande de mot de passe a expiré. Merci de la renouveler');
           return $this->redirectToRoute('reset_password');
        }
        //une vue avec mdp et confirmez mdp
        $form = $this->createForm(ResetPasswordType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()){
            $new_pwd = $form->get('new_password')->getData();
            //dd($new_pwd);
            //encodage de mdp
            $password = $encoder->hashPassword($reset_password->getUser(), $new_pwd);
            $reset_password->getUser()->setPassword($password);
            //flush en bdd
            $this->entityManager->flush();
            //redirection de l utilisateur en page de connexion
            $this->addFlash('notice','Votre mot de passe a bien été mis à jour.');
            return $this->redirectToRoute('app_login');
        }

        return $this->render('reset_password/update.html.twig',[
            'form' =>$form->createView()
        ]);

    }
}
