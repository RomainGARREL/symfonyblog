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

/**
 * @Route("/blog")
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
        $articles = $ar->getRepository('AppBundle:Article')->getdArticlesWithJoinAndWithPagination($offset, $limit);

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
     * @Route("/ajouter", name="blog_ajouter")
     */
    public function ajouterAction(Request $request) {

        $article = new Article();
        $form = $this->createForm(ArticleType::class, $article);
        $em = $this->getDoctrine()->getManager();

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

        return $this->render('blog/ajouter.html.twig', ['form' => $form->createView(),]);
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
