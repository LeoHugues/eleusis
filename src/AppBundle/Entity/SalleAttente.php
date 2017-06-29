<?php
/**
 * Created by PhpStorm.
 * User: leo
 * Date: 3/2/17
 * Time: 9:43 AM
 */

namespace AppBundle\Entity;


use Doctrine\Common\Collections\ArrayCollection;

class SalleAttente
{
    /** @var  Integer */
    private $id;

    /** @var  ArrayCollection */
    private $joueurs;

    public function __construct()
    {
        $this->id = 0;
        $this->joueurs = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return ArrayCollection
     */
    public function getJoueurs()
    {
        return $this->joueurs;
    }

    /**
     * @param Player $joueur
     */
    public function addJoueur($joueur)
    {
        $this->joueurs->add($joueur);
    }
}