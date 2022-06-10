<?php

namespace App\Controller;

use App\Entity\Article;
use App\Form\ArticleType;
use App\Repository\ArticleRepository;
use App\Service\Fonctions;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ArticleController extends AbstractController
{

    private $fonctions;

    public function __construct(Fonctions $fonctions)
    {
        $this->fonctions = $fonctions;
    }

    /**
     *@Route("/article/ajouter",name="app_ajouterarticle")
     */
    public function addArticle(Request $request,ArticleRepository $repo){    
                // check if is connected else redirect to login
                if($this->getUser()==null){
                    return $this->redirectToRoute("app_login");
                }        
        // création instance article
        $article = new Article();
        // creation du formulaire
        $articleForm = $this->createForm(ArticleType::class,$article);
        // traitement
        // recup 
        $articleForm->handleRequest($request);

        if($articleForm->isSubmitted() && $articleForm->isValid()){
            $repo->add($article,true);
            $this->addFlash("success","l'article à bien été créé");
            return $this->redirectToRoute("app_listearticle");
        }

        return $this->render("article/ajouter.html.twig",
            ["articleForm"=>$articleForm->createView()]
        );
    }


    /**
     *  @Route("/article/modifier/{id}",  name="app_article_update",requirements={"id"="\d+"})
     */
    public function update(Article $article,Request $request,EntityManagerInterface $em){
        // creation du formulaire
        $articleForm = $this->createForm(ArticleType::class,$article);
        $articleForm->handleRequest($request);
        if($articleForm->isSubmitted()  && $articleForm->isValid()){
            $em->flush();
            // ajouter un message
            $this->addFlash("success","l'article à bien été modifié");
            return $this->redirectToRoute("app_listearticle");
        }
        return $this->render("article/modifier.html.twig",["articleForm"=>$articleForm->createView()]);
    }


    /**
     * @Route("/article/liste",name="app_listearticle")
     */
    public function list(ArticleRepository $repo, Fonctions $fonctions){
        
        dump($fonctions->add(200,3));

        return $this->render("article/list.html.twig",
        [
            "articles"=>$repo->findAll()
        ]
    );
    }


    /**
     * @IsGranted("ROLE_ADMIN")
     * @Route("/article/supprimer/{id}", name="app_article_remove",requirements={"id"="\d+"})
     */
    public function remove(ArticleRepository $repo,$id=null){
        if($id!=null){
            $article = $repo->find($id);
            $repo->remove($article,true);
            $this->addFlash("success","l'article à bien été supprimé");
        }
        return $this->redirectToRoute("app_listearticle");
    }



    /**
     * @Route("/article/{id}", name="app_article")
     */
    public function index(ArticleRepository $repo,$id): Response
    {
        return $this->render('article/index.html.twig',
        ["article"=>$repo->find($id)] );
    }




}
