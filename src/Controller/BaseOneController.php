<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Comment;
use App\Form\FormListArticleType;
use App\Form\FormModifArticleType;
use App\Form\FormCommentType;
use App\Form\FormNouvelArticleType;

use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
use App\Repository\ArticleRepository;// Injection de dépendance(pour eviter de re-instanicier l'entité Article)
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;//supprimerArticle()

class BaseOneController extends AbstractController
{
    /**
     * @Route("/", name="page_list")
     * @Route("/listArticles/edit")
     */
    public function index( Request $request, ObjectManager $manager )
    {
        //dump($request)//dump($manager)
        //if(!$article){ $article = new Article(); } 
         $article = new Article(); 
            
        //appel du formulaire (déjà  generé en ligne de commande)
        $form = $this->createForm(FormListArticleType::class, $article);
        $form->handleRequest($request); //demande au formulaire d'analyser  la $request// à partir de là le formulaire et fonctionnell// tester avec dump($article);     
        if($form->isSubmitted() && $form->isValid()){
            if($article){ $article->setCreatedAt(new \DateTime()); }            
            $manager->persist($article);
            $manager->flush();
            // sans cette redirection on peut rediriger vers show.html.twig après l'ajout d'1 nouvel articel. Bizarre mais c'est ainsi!!!!!!!!!!
            return $this->redirectToRoute('suite_article', ['id'=>$article->getId()]);
        }    
        $listedesarticles = $this->getDoctrine()->getRepository(Article::class)->findAll();
        return $this->render('base_one/listeArticle.html.twig',[
            'f'=>$form->createView(),
            'article' => $article,
            'listedesarticles'=> $listedesarticles
        ]); 
    }


    /**
     * @Route("/blog/new", name="blog_new")
     * @Route("/blog/", name="route_commun")
     * @Route("/blog/{id}/edit", name="blog_edit")
     */
    public function form(Article $article=null, Request $request, ObjectManager $manager)
    {
        if(!$article){
            $article = new Article();// si nouvel Article: instancier
        }      
        //1.Appel du formulaire (déjà  generé en ligne de commande)
        $form = $this->createForm(FormModifArticleType::class, $article);
        //2.Sauvegarde dans la base
        $form->handleRequest($request); 
        if($form->isSubmitted() && $form->isValid()){ 
            if(!$article->getId()){
                $article->setCreatedAt(new \DateTime());//si nouvel Article: le dater
            }                                          
            $manager->persist($article);
            $manager->flush();
            return $this->redirectToRoute('suite_article', ['id'=>$article->getId()]);
        } 
        //3.Vue correspondant au formulaire precedent 
        $listedesarticles = $this->getDoctrine()->getRepository(Article::class)->findAll();  
        return $this->render('base_one/listeArticle.html.twig',[
            'f'=>$form->createView(),
            'listedesarticles'=> $listedesarticles  
        ]);        
    }

    /**
     * @Route("/blog/{id}", name="suite_article")
     */
    public function show(Article $article, Request $request, ObjectManager $manager){
        $comment = new Comment();
        $form = $this->createForm(FormCommentType::class, $comment );
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
           $comment->setCreatedAt(new \DateTime())
                   ->setArticle($article);  
           $manager->persist($comment);
           $manager->flush();
           return $this->redirectToRoute('suite_article', [
               'id' => $article->getId()
           ]);
        }
        return $this->render('base_one/show.html.twig', [
            'article' => $article,
            'commentForm' => $form->createView()
        ]);
    }



    /**
     * @Route("/supprimerArticle/{id}", name="page_delet")
     * @Template()
     * @param Article $article
     * @return
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function supprimerArticle(Article $article)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $em->remove($article);
        $em->flush();
        //return $this->redirectToRoute('page_list');
        return $this->redirect($this->generateUrl("page_list"));
    }
    /**
     * @Route("/mes_liens", name="links")
     */
    public function link()
    {
        return $this->render('base_one/mesliens.html.twig', [
            'title' => "Mes liens !"
        ]);
    }
    
}
