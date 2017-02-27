<?php

namespace AppBundle\Entity;

/**
 * Card
 */
class Card
{
    /**
     * @var int
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
     * Set color
     *
     * @param boolean $color
     *
     * @return Card
     */
    public function setColor($color)
    {
        $this->color = $color;

        return $this;
    }

    /**
     * Get color
     *
     * @return bool
     */
    public function getColor()
    {
        return $this->color;
    }
}

