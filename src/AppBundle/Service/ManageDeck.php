<?php

/**
 * Created by PhpStorm.
 * User: pierrebaumes
 * Date: 24/04/2017
 * Time: 09:09
 */

namespace AppBundle\Service;

use AppBundle\Entity\Card;
use AppBundle\Entity\Deck;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Kernel;
use \Symfony\Component\Serializer\Encoder\XmlEncoder;
use \Symfony\Component\Serializer\Encoder\JsonEncoder;
use \Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use \Symfony\Component\Serializer\Serializer;

class ManageDeck
{
    /** @var  Kernel */
    protected $kernel;

    public function __construct($kernel)
    {
        $this->kernel = $kernel;
    }

    public function initialize()
    {
        $encoders    = array(new XmlEncoder(), new JsonEncoder());
        $normalizers = array(new ObjectNormalizer());
        $serializer  = new Serializer($normalizers, $encoders);

        $pathDeck = $this->kernel->getRootDir().'/../src/AppBundle/Resources/Json/Deck.json';

        $numbers    = array(1, 2, 3, 4, 5, 6, 7, 8, 9 , 10, 11, 12, 13, 14);
        $signs      = array('clubs', 'diamonds', 'hearts', 'spades');

        $deck = new Deck();

        foreach($numbers as $number) {

            foreach ($signs as $sign) {

                $card = new Card();
                $card->setNumber($number);
                $card->setSign($sign);

                $deck->addCard($card);
            }
        }

        $jsonDeck = $serializer->serialize($deck, 'json');

        file_put_contents($pathDeck, $jsonDeck);

        return new JsonResponse(json_decode($jsonDeck));
    }
}