<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Deck;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class AppRestController extends Controller
{
    public function getInitGameAction()
    {
        $deck = new Deck();

        $deck->getCards();

        return 'hello world';
    }
}
