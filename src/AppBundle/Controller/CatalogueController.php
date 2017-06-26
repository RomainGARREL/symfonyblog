<?php

namespace AppBundle\Controller;

//use AppBundle\Entity\Categorie; pas besoin car je fais pas de =New Categorie


use AppBundle\Entity\Categorie;
use AppBundle\Entity\Note;
use AppBundle\Form\NoteType;
use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use function dump;

/**
 * @Route("/catalogue")
 *
 */
class CatalogueController extends Controller {

    /**
     * @Route("/", name="catalogue_homepage")
     *
     */
    public function indexAction() {
        $cm = $this->getDoctrine()->getManager()->getRepository('AppBundle:Categorie');
        $categories = $cm->findBy([], ['titre' => 'ASC']); // Il attend un tableau de criteres, si rien a lui passer, alors par defaut je lui donne un tableau vide.
        return $this->render('catalogue/index.html.twig', ['categories' => $categories]);
    }

    /**
     * @Route("/categorie/{id}", name="categorie_catalogue", requirements={"id": "\d+"})
     *
     */
    public function categorieAction(Request $request, Categorie $cat) {
        $pm = $this->getDoctrine()->getManager()->getRepository('AppBundle:Produit');
        $produits = $pm->getProduitsByCategorie($cat);
        return $this->render('catalogue/categorie.html.twig', ['produits' => $produits, 'cat' => $cat]);
    }

    /**
     * @Route("/detail/{id}", name="detail_catalogue",
     * requirements={"id": "\d+"})
     */
    public function detailAction(Request $request, $id) {
        //1er Partie
        $em = $this->getDoctrine()->getManager();

        $ar = $em->getRepository('AppBundle:Produit');

        $produit = $ar->getProduitByIdWithJoin($id);

        $note = new Note();
        $note->setProduit($produit);

        $form = $this->createForm(NoteType::class, $note);

        $session = $this->get('session');

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Mettre le Try Catch here
            try {
                dump($note);
                $em->persist($note);
                $em->flush();
                $session->getFlashBag()->add('succes', 'Note Ajouté');
                return $this->redirectToRoute('detail_catalogue', ['id' => $id]);
            } catch (Exception $e) {
                $session->getFlashBag()->add('Erreur', 'Pb notation:' . $e->getMessage() . $e->getFile());
                //return $this->redirectToRoute('blog_ajouter');
            }
        }

//        return $this->render('catalogue/detail.html.twig', ['produit' => $produit]);
        // ajout du formulaire ci dessous
        return $this->render('catalogue/detail.html.twig', ['produit' => $produit, 'form' => $form->createView()]);
    }

    /**
     * @Route("/ajax_note_catalogue", name="ajax_note_catalogue")
     */
    public function ajouterAjaxNoteAction(Request $request) {

        if ($request->isXmlHttpRequest()) {
            $id = $request->request->get('id');
            $valeur = $request->request->get('valeur');

            // Insertion du code pour charger en base de données
            $em = $this->getDoctrine()->getManager();
            $ar = $em->getRepository('AppBundle:Produit');
            $produit = $ar->find($id);
            $note = new Note();
            $note->setProduit($produit);
            $note->setValeur($valeur);
            $em->persist($note);

            try {
                $em->flush();
                return new JsonResponse(
                        ['success' => true,
                    'note' => [
                        'id' => $note->getId(),
                        'valeur' => $note->getValeur()
                    ]
                        ]
                );
            } catch (\Exception $exc) {
                return new JsonResponse(['success' => false]);
            }
        } else {
            throw new HttpException(403);
        }
    }

    /**
     * @Route("/new_produits_catalogue", name="new_produits_catalogue")
     *
     */
    public function ajaxNewProduitAction(Request $request) {
        $pm = $this->getDoctrine()->getManager()->getRepository('AppBundle:Produit');
        $produits = $pm->findNewProd(3);
        return $this->render('catalogue/ajaxNewProduits.html.twig', ['produits' => $produits]);
    }

}
