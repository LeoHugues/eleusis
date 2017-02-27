<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Player
 *
 * @ORM\Table(name="player")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\PlayerRepository")
 */
class Player
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
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var bool
     *
     * @ORM\Column(name="isGod", type="boolean")
     */
    private $isGod;

    /**
     * @var bool
     *
     * @ORM\Column(name="isInGame", type="boolean")
     */
    private $isInGame;

    /**
     * @var array
     *
     * @ORM\Column(name="listCard", type="array")
     */
    private $listCard;


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
     * Set name
     *
     * @param string $name
     *
     * @return Player
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set isGod
     *
     * @param boolean $isGod
     *
     * @return Player
     */
    public function setIsGod($isGod)
    {
        $this->isGod = $isGod;

        return $this;
    }

    /**
     * Get isGod
     *
     * @return bool
     */
    public function getIsGod()
    {
        return $this->isGod;
    }

    /**
     * Set isInGame
     *
     * @param boolean $isInGame
     *
     * @return Player
     */
    public function setIsInGame($isInGame)
    {
        $this->isInGame = $isInGame;

        return $this;
    }

    /**
     * Get isInGame
     *
     * @return bool
     */
    public function getIsInGame()
    {
        return $this->isInGame;
    }

    /**
     * Set listCard
     *
     * @param array $listCard
     *
     * @return Player
     */
    public function setListCard($listCard)
    {
        $this->listCard = $listCard;

        return $this;
    }

    /**
     * Get listCard
     *
     * @return array
     */
    public function getListCard()
    {
        return $this->listCard;
    }
}

