<?php

namespace AppBundle\Entity;

/**
 * Card
 */
class Card
{
    /**
     * @var int
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var int
     */
    private $number;

    /**
     * @var string
     */
    private $sign;

    /**
     * @ORM\ManyToOne(targetEntity="eleusis\AppBundle\Entity\Deck", inversedBy="cards")
     * @ORM\JoinColumn(nullable=false)
     */
    private $deck;


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
     * Set number
     *
     * @param integer $number
     *
     * @return Card
     */
    public function setNumber($number)
    {
        $this->number = $number;

        return $this;
    }

    /**
     * Get number
     *
     * @return int
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * Set sign
     *
     * @param string $sign
     *
     * @return Card
     */
    public function setSign($sign)
    {
        $this->sign = $sign;

        return $this;
    }

    /**
     * Get sign
     *
     * @return string
     */
    public function getSign()
    {
        return $this->sign;
    }

    /**
     * @return mixed
     */
    public function getDeck()
    {
        return $this->deck;
    }

    /**
     * @param mixed $deck
     */
    public function setDeck($deck)
    {
        $this->deck = $deck;
    }
}

