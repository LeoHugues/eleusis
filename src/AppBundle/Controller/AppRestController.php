<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Card;
use AppBundle\Entity\Deck;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class AppRestController extends Controller
{
    public function getInitGameAction(Request $request)
    {
        $encoders    = array(new XmlEncoder(), new JsonEncoder());
        $normalizers = array(new ObjectNormalizer());
        $serializer  = new Serializer($normalizers, $encoders);

        $pathDeck = $this->get('kernel')->getRootDir().'/../src/AppBundle/Resources/Json/Deck.json';

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

        return new JsonResponse($jsonDeck);
    }
}
