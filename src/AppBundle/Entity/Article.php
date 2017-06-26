<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Article
 *
 * @ORM\HasLifecycleCallbacks()
 * @ORM\Table(name="article")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ArticleRepository")
 */
class Article {

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     * @Assert\Length(
     *      min = 2,
     *      max = 50,
     *      minMessage = "Votre titr doit avoir au minimum {{ limit }} characters long",
     *      maxMessage = "Votre titr doit avoir au maximmu {{ limit }} characters"
     * )
     *
     * @ORM\Column(name="titre", type="string", length=255)
     */
    private $titre;

    /**
     * @var string
     * @Assert\NotBlank(
     *  message = "Entrer un contenu valide"
     * )
     *
     * @ORM\Column(name="contenu", type="text")
     */
    private $contenu;

    /**
     *
     * @var string
     */
    private $extrait;

    public function getExtrait() {
        return $this->extrait;
    }

    public function setExtrait($extrait) {
        $this->extrait = $extrait;
        return $this;
    }

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="Tag", cascade={"persist"}, inversedBy="articles")
     */
    private $tags;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="datetime")
     */
    private $date;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_modification", type="datetime")
     */
    private $dateModification;

    /**
     * @var boolean
     *
     * @ORM\Column(name="publication", type="boolean")
     */
    private $publication;

    /**
     *
     * @var Image
     * @Assert\Valid
     *
     * @ORM\OneToOne(targetEntity="Image", cascade={"persist", "remove"})
     *
     */
    private $image;

    /**
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     * @ORM\OneToMany(targetEntity="Commentaire", mappedBy="article", cascade={"remove"})
     *
     * @ORM\OrderBy({"date"="DESC"})
     *
     */
    private $commentaires;

    // Pour rendre la liaison ci-haut obligatoire:
    // @ORM\JoinColumn(nullable=false)

    /**
     * Get id
     *
     * @return int
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Set titre
     *
     * @param string $titre
     *
     * @return Article
     */
    public function setTitre($titre) {
        $this->titre = $titre;

        return $this;
    }

    /**
     * Get titre
     *
     * @return string
     */
    public function getTitre() {
        return $this->titre;
    }

    /**
     * Set contenu
     *
     * @param string $contenu
     *
     * @return Article
     */
    public function setContenu($contenu) {
        $this->contenu = $contenu;

        return $this;
    }

    /**
     * Get contenu
     *
     * @return string
     */
    public function getContenu() {
        return $this->contenu;
    }

    /**
     * Set date
     *
     * @param \DateTime $date
     *
     * @return Article
     */
    public function setDate($date) {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date
     *
     * @return \DateTime
     */
    public function getDate() {
        return $this->date;
    }

    /**
     * Set publication
     *
     * @param boolean $publication
     *
     * @return Article
     */
    public function setPublication($publication) {
        $this->publication = $publication;

        return $this;
    }

    /**
     * Get publication
     *
     * @return boolean
     */
    public function getPublication() {
        return $this->publication;
    }

//    function __construct() {
//        $this->publication = true;
//        $this->date = New \DateTime;
//    }

    /**
     * Set image
     *
     * @param \AppBundle\Entity\Image $image
     *
     * @return Article
     */
    public function setImage(\AppBundle\Entity\Image $image = null) {
        $this->image = $image;

        return $this;
    }

    /**
     * Get image
     *
     * @return \AppBundle\Entity\Image
     */
    public function getImage() {
        return $this->image;
    }

    /**
     * Constructor
     */
    public function __construct() {
        $this->tags = new \Doctrine\Common\Collections\ArrayCollection();
        $this->commentaires = new \Doctrine\Common\Collections\ArrayCollection();

        //ci-dessous a incorporer manuellement
        $this->publication = true;
        $this->date = New \DateTime;
        $this->dateModification = New \DateTime;
    }

    /**
     * Add tag
     *
     * @param \AppBundle\Entity\Tag $tag
     *
     * @return Article
     */
    public function addTag(\AppBundle\Entity\Tag $tag) {
        $this->tags[] = $tag;

        return $this;
    }

    /**
     * Remove tag
     *
     * @param \AppBundle\Entity\Tag $tag
     */
    public function removeTag(\AppBundle\Entity\Tag $tag) {
        $this->tags->removeElement($tag);
    }

    /**
     * Get tags
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getTags() {
        return $this->tags;
    }

    /**
     * Add commentaire
     *
     * @param \AppBundle\Entity\Commentaires $commentaire
     *
     * @return Article
     */
    public function addCommentaire(\AppBundle\Entity\Commentaires $commentaire) {
        $this->commentaires[] = $commentaire;

        return $this;
    }

    /**
     * Remove commentaire
     *
     * @param \AppBundle\Entity\Commentaires $commentaire
     */
    public function removeCommentaire(\AppBundle\Entity\Commentaires $commentaire) {
        $this->commentaires->removeElement($commentaire);
    }

    /**
     * Get commentaires
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getCommentaires() {
        return $this->commentaires;
    }

    /**
     * Set dateModification
     *
     * @param \DateTime $dateModification
     *
     * @return Article
     */
    public function setDateModification($dateModification) {
        $this->dateModification = $dateModification;

        return $this;
    }

    /**
     * Get dateModification
     *
     * @return \DateTime
     */
    public function getDateModification() {
        return $this->dateModification;
    }

    /**
     * @ORM\PreUpdate
     */
    public function updateDateModification() {
        $this->setDateModification(new \DateTime);
    }

}
