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
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

/**
 * @Route("/{_locale}/blog", defaults={"_locale": "fr"}, requirements={"_locale": "fr|en"})
 */
class BlogController extends Controller {

    /**
     * @Route("/{p}", name="homepage_blog",
     * defaults={"p": 1},
     * requirements={"p": "\d+"})
     */
    public function indexAction(
    Request $request, \AppBundle\Service\Extrait $extrait, \AppBundle\Service\ExtraitWithLink $extraitWithLink, $p
    ) {
        $limit = $this->getParameter('item_par_page');
        $offset = $limit * $p - 1;
        $ar = $this->getDoctrine()->getManager();
        $articles = $ar->getRepository('AppBundle:Article')->getdArticlesWithJoinAndWithPagination($offset, $limit, $request->getLocale());

        // Mon Code [
        $nbArticle = $articles->count();
        $nbPages = ceil($nbArticle / $limit);

        return $this->render('blog/index.html.twig', [
                    'articles' => $articles,
                    'pages' => $nbPages,
                    'page_active' => $p,
        ]);
        //Mon Code ]
    }

    /**
     * @Security("has_role('ROLE_ADMIN')")
     * @Route("/ajouter", name="blog_ajouter")
     */
    public function ajouterAction(Request $request) {

        if ($request->getLocale() != $this->getParameter('locale')) {
            return $this->redirectToRoute('blog_ajouter', ['_locale' => $this->getParameter('locale')]);
        }

        //$this->denyAccessUnlessGranted('ROLE_ADMIN', null, 'Unable to access this page!');

        $user = $this->getUser();
        $article = new Article();
        $article->setUser($user);

        $form = $this->createForm(ArticleType::class, $article);
        $em = $this->getDoctrine()->getManager();

        $session = $this->get('session');



        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            //Code Armand not in the Doc [
            $uploadableManager = $this->get('stof_doctrine_extensions.uploadable.manager');
            $image = $article->getImage();
            if ($request->files->get($form->getName())['image']['url']) {
                $image->setUrl($request->files->get($form->getName())['image']['url']);
                $uploadableManager->markEntityToUpload($image, $image->getUrl());
            }
            // ]
            try {
                $em->persist($article);
                $em->flush();
                $session->getFlashBag()->add('succes', 'Article créé');
                return $this->redirectToRoute('blog_detail', ['slug' => $article->getSlug()]);
            } catch (\Exception $e) {
                $session->getFlashBag()->add('Erreur', 'Pb creation:' . $e->getMessage() . $e->getFile());
                //return $this->redirectToRoute('blog_ajouter');
            }
        }

        return $this->render('blog/ajouter.html.twig', ['form' => $form->createView(),]);
    }

    /**
     * @Security("is_granted('ROLE_SUPER_ADMIN') or user == article.getUser()")
     * @Route("/modifier/{id}", name="blog_modifier",
     * requirements={"id": "\d+"})
     */
    public function modifierAction(Request $request, $id, Article $article) {
        // le Article $article above c'est only pour que l'annotation de securi fonctionne
        $em = $this->getDoctrine()->getManager();

        $article = $em->getRepository('AppBundle:Article')->find($id);
        //Mon Code [
        $user = $this->getUser();
        $article->setUser($user);
        //]
        $form = $this->createForm(ArticleType::class, $article);

        $session = $this->get('session');
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            //**************************************
            $uploadableManager = $this->get('stof_doctrine_extensions.uploadable.manager');
            $image = $article->getImage();
            $url = $request->files->get($form->getName())['image']['url'];
            if ($url) {
                if (is_file($image->getUrl()))
                    unlink($image->getUrl());
                $image->setUrl($url);
                $uploadableManager->markEntityToUpload($image, $image->getUrl());
            }
            //**************************************
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

        $em = $this->getDoctrine()->getManager();
        $ar = $em->getRepository('AppBundle:Article');
        $article = $ar->find($id);

        if ($article->getImage()) // pour eviter le plantage si on a pas chargé d'image
            $article->getImage()->getUrl(); // Pour forcer à hydrater l'image final day matin

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
     * @Route("/detail/{slug}", name="blog_detail",
     * requirements={"slug": "[a-z0-9\-]+$"})
     */
    public function detailAction(Request $request, $slug) {

        $em = $this->getDoctrine()->getManager();

        $ar = $em->getRepository('AppBundle:Article');

        $article = $ar->getArticleBySlugWithLeftJoin($slug);

        $commentaire = new Commentaire();
        $commentaire->setArticle($article);

        $form = $this->createForm(CommentaireType::class, $commentaire, ['action' => $this->generateUrl('ajouter_commentaire_blog', ['slug' => $slug])]);

        return $this->render('blog/detail.html.twig', ['article' => $article, 'form' => $form->createView()]);
    }

    /**
     * @Route("/tag/{id}", name="blog_tag",
     * requirements={"id": "\d+"})
     */
    public function tagAction(Request $request, $id) {

        $em = $this->getDoctrine()->getManager();

        $ar = $em->getRepository('AppBundle:Article');
        $tr = $em->getRepository('AppBundle:Tag');

        $articles = $ar->getArticlesByTagWithLeftJoin($id);
        $tag = $tr->find($id);
        $count = $ar->getNumberOfArticlesByTagWithLeftJoin($id);

        //My code perso [
        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
                $articles, $request->query->getInt('page', 1)/* page number */, 2/* limit per page */
        );

        return $this->render('blog/tag.html.twig', [
                    //'tag' => $tag,
                    //'articles' => $articles,
                    //'count' => $count,
                    'pagination' => $pagination
        ]);
        //My code perso ]
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
     * @Route("/ajouter_commentaire_blog/{slug}", name="ajouter_commentaire_blog",
     * requirements={"slug": "[a-z0-9\-]+$"})
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

            //Below my code [
            $user = $this->getUser();
            $commentaire->setUser($user);
            //
            $em->persist($commentaire);

            try {
                $em->flush();
                return new \Symfony\Component\HttpFoundation\JsonResponse(
                        ['success' => true,
                    'commentaire' => [
                        'id' => $commentaire->getId(),
                        'contenu' => $commentaire->getContenu(),
                        'date' => $commentaire->getDate()->format('Y-m-d'),
                        'user' => $user->getUsername()
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

    /**
     * @Route("/traduction", name="trad_blog")
     */
    public function traductionAction() {
        $message = $this->get('translator')->trans('message');
        $date = new \DateTime; // le \ c'est parce que class native de php donc indique repertoire racine
        return $this->render('blog/traduction.html.twig', ['messageController' => $message, 'date' => $date]);
        // Pour recuperer ma clé message dans le controlleur
    }

}
