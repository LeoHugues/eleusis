<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Game
 *
 * @ORM\Table(name="game")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\GameRepository")
 */
class Game
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var array
     *
     * @ORM\Column(name="listPlayers", type="array")
     */
    private $listPlayers;

    /**
     * @var array
     *
     * @ORM\Column(name="listAllCards", type="array")
     */
    private $listAllCards;

    /**
     * @var array
     *
     * @ORM\Column(name="listCardsTrue", type="array")
     */
    private $listCardsTrue;

    /**
     * @var array
     *
     * @ORM\Column(name="listCardsFalse", type="array")
     */
    private $listCardsFalse;


    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set listPlayers
     *
     * @param array $listPlayers
     *
     * @return Game
     */
    public function setListPlayers($listPlayers)
    {
        $this->listPlayers = $listPlayers;

        return $this;
    }

    /**
     * Get listPlayers
     *
     * @return array
     */
    public function getListPlayers()
    {
        return $this->listPlayers;
    }

    /**
     * Set listAllCards
     *
     * @param array $listAllCards
     *
     * @return Game
     */
    public function setListAllCards($listAllCards)
    {
        $this->listAllCards = $listAllCards;

        return $this;
    }

    /**
     * Get listAllCards
     *
     * @return array
     */
    public function getListAllCards()
    {
        return $this->listAllCards;
    }

    /**
     * Set listCardsTrue
     *
     * @param array $listCardsTrue
     *
     * @return Game
     */
    public function setListCardsTrue($listCardsTrue)
    {
        $this->listCardsTrue = $listCardsTrue;

        return $this;
    }

    /**
     * Get listCardsTrue
     *
     * @return array
     */
    public function getListCardsTrue()
    {
        return $this->listCardsTrue;
    }

    /**
     * Set listCardsFalse
     *
     * @param array $listCardsFalse
     *
     * @return Game
     */
    public function setListCardsFalse($listCardsFalse)
    {
        $this->listCardsFalse = $listCardsFalse;

        return $this;
    }

    /**
     * Get listCardsFalse
     *
     * @return array
     */
    public function getListCardsFalse()
    {
        return $this->listCardsFalse;
    }
}

