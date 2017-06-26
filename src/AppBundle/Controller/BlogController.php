<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Article;
use AppBundle\Entity\Commentaire;
use AppBundle\Form\ArticleType;
use AppBundle\Form\CommentaireType;
use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use function dump;

/**
 * @Route("/blog")
 */
class BlogController extends Controller {

    /**
     * @Route("/{p}", name="homepage_blog",
     * defaults={"p": 1},
     * requirements={"p": "\d+"})
     */
    public function indexAction(Request $request, \AppBundle\Service\Extrait $extrait, \AppBundle\Service\ExtraitWithLink $extraitWithLink, $p) {
        $em = $this->getDoctrine()->getManager();

        $ar = $em->getRepository('AppBundle:Article');
        // ar pour Article Repository
        $articles = $ar->getdArticlesByPublicationWithLeftJoin(); // pas oublier que ces tous les articleS
        // Cette ligne de code ci_haut permet de diminuer la quantité de requete executer et le temps de reponse

        foreach ($articles as $article) {

            //$article->setExtrait($extrait->get($article->getContenu()));
            $article->setExtrait($extraitWithLink->get($article));
        }
        return $this->render('blog/index.html.twig', ['articles' => $articles]);
    }

//        /**
//     * @Route("/default", name="homepage")
//     */
//    public function indexAction(Request $request, Extrait $extrait) {
//        $articles = $this->getDoctrine()->getManager()->getRepository('AppBundle:Article')->findAll();
//        foreach ($articles as $article) {
//
//            $article->setExtrait($extrait->get($article->getContenu));
//        }
//        return $this->render('default/index.html.twig', [
//                    'base_dir' => realpath($this->getParameter('kernel.project_dir')) . DIRECTORY_SEPARATOR,
//        ]);
//    }

    /**
     * @Route("/ajouter", name="blog_ajouter")
     */
    public function ajouterAction(Request $request) {

        $article = new Article();
        $form = $this->createForm(ArticleType::class, $article);
        $em = $this->getDoctrine()->getManager();
        // Traitement, si il ya des donnees a recuperer dans la requete elle va hydrater notre article avec celles-ci
        $session = $this->get('session');
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $em->persist($article);
                $em->flush();
                $session->getFlashBag()->add('succes', 'Article créé');
                return $this->redirectToRoute('blog_detail', ['id' => $article->getId()]);
            } catch (\Exception $e) {
                $session->getFlashBag()->add('Erreur', 'Pb creation:' . $e->getMessage() . $e->getFile());
                //return $this->redirectToRoute('blog_ajouter');
            }
        }

//
//        $formBuilder = $this->createFormBuilder($article);
//        $formBuilder->add('titre', TextType::class)
//                ->add('contenu', TextareaType::class)
//                ->add('date', DateType::class, array('widget' => 'single_text', 'html5' => false, 'format' => 'yyyy-MM-dd'))
//                ->add('publication', null, ['required' => false, 'label' => 'Publié ?'])
//                ->add('submit', SubmitType::class, ['label' => 'Envoyer'])
//        ;
//        $form = $formBuilder->getForm(); // on retourne un objet formulaire
        return $this->render('blog/ajouter.html.twig', ['form' => $form->createView(),]);
//        $article->setTitre('Hello Word')
//                ->setContenu('Lorem ipsum');
//
//        //2eme Partie Image
//        $image = new \AppBundle\Entity\Image();
//        $image->setAlt('robobohash');
//        $image->setUrl('https://robohash.org/' . rand() . '.png');
//        $article->setImage($image); // la ligne la plus importante
//        //
//        //3eme Partie Commentaire
//        $commentaire = new \AppBundle\Entity\Commentaire();
//        $commentaire->setContenu('Commentaire 1 de larticle');
//        $commentaire->setDate(new \DateTime);
//        $commentaire->setArticle($article);
//        //
//        $commentaire2 = new \AppBundle\Entity\Commentaire();
//        $commentaire2->setContenu('Commentaire 2 de larticle');
//        $commentaire2->setDate(new \DateTime);
//        $commentaire2->setArticle($article);
//
//        //4eme Partie
//        $tag1 = new \AppBundle\Entity\Tag();
//        $tag1->setTitre('NomTag1');
//        $article->addTag($tag1);
//
//        $tag2 = new \AppBundle\Entity\Tag();
//        $tag2->setTitre('NomTag2');
//        $article->addTag($tag2);
//
//
//        //  Common Partie 1, 2 et 3
//        $doctrine = $this->getDoctrine();
//        $em = $doctrine->getManager();
//        $em->persist($article);
//        //
//        $em->persist($commentaire);
//        $em->persist($commentaire2);
//
//        // pas besoin de mettre l'image en persist puisque fait dans Cascade Image dans Article
//        $em->flush();
//
//        return $this->redirectToRoute('blog_detail', ['id' => $article->getId()]);
//         return $this->render('blog/ajouter.html.twig');
    }

    /**
     * @Route("/modifier/{id}", name="blog_modifier",
     * requirements={"id": "\d+"})
     */
    public function modifierAction(Request $request, $id) {
        //$ar = $em->getRepository('AppBundle:Article');
        $em = $this->getDoctrine()->getManager();
        $article = $em->getRepository('AppBundle:Article')->find($id);


        $form = $this->createForm(ArticleType::class, $article);
        //$em = $this->getDoctrine()->getManager();
        // Traitement, si il ya des donnees a recuperer dans la requete elle va hydrater notre article avec celles-ci
        $session = $this->get('session');
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $em->persist($article);
                $em->flush();
                $session->getFlashBag()->add('succes', 'Article créé');
                return $this->redirectToRoute('blog_detail', ['id' => $article->getId()]);
            } catch (\Exception $e) {
                $session->getFlashBag()->add('Erreur', 'Pb creation:' . $e->getMessage() . $e->getFile());
                //return $this->redirectToRoute('blog_ajouter');
            }
        }

        return $this->render('blog/modifier.html.twig', ['form' => $form->createView(),]);
    }

    /**
     * @Route("/suprimer/{id}", name="blog_suprimer",
     * requirements={"id": "\d+"})
     */
    public function suprimerAction($id) {
        // faire un lien sur le detail de chaqye article avec un lien pour le suprimer
        // on utilise le path et l'id de l'article a suprimer
        //1er Partie
        $em = $this->getDoctrine()->getManager();
        $ar = $em->getRepository('AppBundle:Article');
        $article = $ar->find($id);

        $em->remove($article);

        $session = $this->get('session'); //je recupere ce service session car Doctrine ci-haut

        try {
            $em->flush();
            $session->getFlashBag()->add('succes', 'Article Suprimé'); // c'est un tableau mais un seul '' aurait suffit
            return $this->redirectToRoute('homepage_blog');
        } catch (Exception $e) {
            $session->getFlashBag()->add('Erreur', 'Pb suppression:' . $e->getMessage() . $e->getFile());
            return $this->redirectToRoute('blog_detail', ['id' => $article->getId()]);
        }
    }

    /**
     * @Route("/detail/{id}", name="blog_detail",
     * requirements={"id": "\d+"})
     */
    public function detailAction(Request $request, $id) {

        $em = $this->getDoctrine()->getManager();

        $ar = $em->getRepository('AppBundle:Article');

        $article = $ar->getArticleByIdWithLeftJoin($id);

        $commentaire = new Commentaire();
        $commentaire->setArticle($article);

        $form = $this->createForm(CommentaireType::class, $commentaire, ['action' => $this->generateUrl('ajouter_commentaire_blog', ['id' => $id])]);





        return $this->render('blog/detail.html.twig', ['article' => $article, 'form' => $form->createView()]);
    }

    /**
     * @Route("/tag/{id}", name="blog_tag",
     * requirements={"id": "\d+"})
     */
    public function tagAction($id) {
        $em = $this->getDoctrine()->getManager();

        $ar = $em->getRepository('AppBundle:Article');
        $tr = $em->getRepository('AppBundle:Tag');

        $articles = $ar->getArticlesByTagWithLeftJoin($id);
        $tag = $tr->find($id);
        $count = $ar->getNumberOfArticlesByTagWithLeftJoin($id);

        return $this->render('blog/tag.html.twig', [
                    'tag' => $tag,
                    'articles' => $articles,
                    'count' => $count
        ]);
    }

    public function footerAction($nb) {
        //besoin du repository d'article sur laquelle on applique la methode findby'


        $em = $this->getDoctrine()->getManager();

        $ar = $em->getRepository('AppBundle:Article');
        // ar pour Article Repository
        //$articles = $ar->findBy(['publication' => true], ['date' => 'DESC', 'titre' => 'ASC'], $nb);
        $articles = $ar->getLastThreeArticlesByDateWithLeftJoin($nb); //$nb

        return $this->render('blog/footer.html.twig', ['articles' => $articles]);
    }

    public function yearsArchivesAction() {
        $em = $this->getDoctrine()->getManager();
        $ar = $em->getRepository('AppBundle:Article');

        $date = $ar->getYears();

        return $this->render('blog/footer2.html.twig', ['years' => $date]);
    }

    /**
     * @Route("/year/{year}", name="year_articles",
     * requirements={"year": "\d+"})
     */
    public function yearsArticleAction($year) {
        $em = $this->getDoctrine()->getManager();

        $ar = $em->getRepository('AppBundle:Article');
        // ar pour Article Repository
        //$articles = $ar->findBy(['publication' => true], ['date' => 'DESC', 'titre' => 'ASC'], $nb);
        $articles = $ar->getYearArticles($year);

        return $this->render('blog/year.html.twig', ['articles' => $articles]);
    }

    /**
     * @Method({"POST"})
     * @Route("/ajouter_commentaire_blog/{id}", name="ajouter_commentaire_blog",
     * requirements={"id": "\d+"})
     */
    public function ajouterCommentaireAction(Request $request, \AppBundle\Entity\Article $article) {
        $commentaire = new Commentaire();
        $commentaire->setArticle($article);
        $form = $this->createForm(CommentaireType::class, $commentaire);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $session = $this->get('session');
            $em = $this->getDoctrine()->getManager();
            $em->persist($commentaire);

            try {
                //dump($commentaire);
                //
                $em->flush();
                $session->getFlashBag()->add('succes', 'commentaire Ajouté');
                return $this->redirectToRoute('blog_detail', ['id' => $article->getId()]);
                //
            } catch (Exception $e) {
                $session->getFlashBag()->add('Erreur', 'Pb commentaire:' . $e->getMessage() . $e->getFile());
                //return $this->redirectToRoute('blog_ajouter');
            }
        }
    }

    /**
     * @Route("/ajax_commentaire_blog", name="ajax_commentaire_blog")
     */
    public function ajouterAjaxCommentaireAction(Request $request) {

        if ($request->isXmlHttpRequest()) {
            $id = $request->request->get('id');
            $contenu = $request->request->get('contenu');

            // Insertion du code pour charger en base de données
            $em = $this->getDoctrine()->getManager();
            $ar = $em->getRepository('AppBundle:Article');
            $article = $ar->find($id);
            $commentaire = new Commentaire();
            $commentaire->setArticle($article);
            $commentaire->setContenu($contenu);
            $em->persist($commentaire);

            try {
                $em->flush();
                return new \Symfony\Component\HttpFoundation\JsonResponse(
                        ['success' => true,
                    'commentaire' => [
                        'id' => $commentaire->getId(),
                        'contenu' => $commentaire->getContenu(),
                        'date' => $commentaire->getDate()->format('Y-m-d')
                    ]
                        ]
                );
            } catch (\Exception $exc) {
                return new \Symfony\Component\HttpFoundation\JsonResponse(['success' => false]);
            }
        } else {
            throw new \Symfony\Component\HttpKernel\Exception\HttpException(403);
        }
    }

}
