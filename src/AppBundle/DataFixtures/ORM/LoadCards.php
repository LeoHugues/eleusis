<?php

namespace eleusis\AppBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use AppBundle\Entity\Card;

/**
 * Created by PhpStorm.
 * User: pierrebaumes
 * Date: 23/02/2017
 * Time: 10:27
 */
class LoadCards implements FixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $numbers    = array(1, 2, 3, 4, 5, 6, 7, 8, 9 , 10, 11, 12, 13, 14);
        $signs      = array('coeur', 'pique', 'caro', 'trefle');

        foreach($numbers as $number) {

            foreach($signs as $sign) {

                $card = new Card();
                $card->setNumber($number);
                $card->setSign($sign);

                $manager->persist($card);

                $manager->flush();

            }
        }
    }

    public function getOrder()
    {
        return 1;
    }
}