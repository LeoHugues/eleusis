<?php
/**
 * Created by PhpStorm.
 * User: pierrebaumes
 * Date: 24/04/2017
 * Time: 09:22
 */

namespace AppBundle\Controller;

use FOS\RestBundle\Controller\Annotations\Get;
use Symfony\Component\BrowserKit\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

class ConnectionController
{
    /**
     * @Get("/connect/{player}")
     */
    public function connectAction(Request $request, $player)
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
            "tableau"         => $this->get('connection_service')->getInitGameAction(),
            "nbTenaillesJ1"   => 0,
            "nbTenaillesJ2"   => 0,
            "dernierCoupX"    => null,
            "dernierCoupY"    => null,
            "prolongation"    => false,
            "finPartie"       => false,
            "detailFinPartie" => null,
            "numTour"         => 0,
            "code"            => 200
        ];

        return $array;
    }
}