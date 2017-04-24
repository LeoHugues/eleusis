<?php
/**
 * Created by PhpStorm.
 * User: pierrebaumes
 * Date: 24/04/2017
 * Time: 09:22
 */

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\Annotations as Rest;

class ConnectionController extends Controller
{
    public function getConnectAction($player, Request $request)
    {
        $filePlayers   = $this->get('kernel')->getRootDir().'/../src/AppBundle/Resources/Json/Players.json';
        $pathStateGame = $this->get('kernel')->getRootDir().'/../src/AppBundle/Resources/Json/StateGame.json';

        if(!file_exists($filePlayers)) {

            $idPlayer              = md5(uniqid(rand(), true));
            $this->idCurrentJoueur = $idPlayer;

            // Creation de la partie
            $newStateGame = $this->startGame();
            $newStateGame['status'] = 0;
            $newGameContent = json_encode($newStateGame);
            file_put_contents($pathStateGame, $newGameContent);

            $formatted = [
                'idJoueur'  => $idPlayer,
                'code'      => intval(200),
                'nomJoueur' => $player,
                'numJoueur' => intval(1)
            ];

            $players = array('idPlayer1' => $idPlayer, 'idPlayer2' => null);

            file_put_contents($filePlayers, json_encode($players));

            return new JsonResponse($formatted);

        } else {

            $players = file_get_contents($filePlayers);
            $players = json_decode($players, true);

            if($players['idPlayer2'] == null) {

                $idPlayer              = md5(uniqid(rand(), true));
                $this->idCurrentJoueur = $idPlayer;

                $formatted = [
                    'idJoueur'  => $idPlayer,
                    'code'      => intval(200),
                    'nomJoueur' => $player,
                    'numJoueur' => intval(2)
                ];

                $players['idPlayer2'] = $idPlayer;

                file_put_contents($filePlayers, json_encode($players));

                return new JsonResponse($formatted);

            } else {
                $formatted = [
                    'idJoueur'  => null,
                    'code'      => 401,
                    'nomJoueur' => null,
                    'numJoueur' => null
                ];

                $response = new JsonResponse($formatted);

                $response->setStatusCode(401);
                return $response;
            }
        }
    }

    private function startGame()
    {
        $array = [
            "status"          => null,
            "deck"            => $this->get('manage_deck')->initialize(),
            "dernierCoup"     => null,
            "finPartie"       => false,
            "detailFinPartie" => null,
            "numTour"         => 0,
            "code"            => 200
        ];

        return $array;
    }
}