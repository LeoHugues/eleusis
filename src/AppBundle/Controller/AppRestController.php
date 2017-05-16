<?php

namespace AppBundle\Controller;

use FOS\RestBundle\Controller\Annotations\Get;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class AppRestController extends Controller
{
    /**
     * @Get("/connect/{player}")
     */
    public function connectAction(Request $request, $player)
    {
        $filePlayers = $this->get('kernel')->getRootDir().'/../src/AppBundle/Resources/Json/Players.json';
        $pathStateGame = $this->get('kernel')->getRootDir().'/../src/AppBundle/Resources/Json/StateGame.json';
        if(!file_exists($filePlayers)) {
            $idPlayer              = md5(uniqid(rand(), true));

            // Creation de la partie
            $newStateGame = $this->startGame();
            $newGameContent = json_encode($newStateGame);
            file_put_contents($pathStateGame, $newGameContent);
            $formatted = [
                'idJoueur'  => $idPlayer,
                'code'      => intval(200),
                'nomJoueur' => $player,
                'numJoueur' => intval(1)
            ];
            $players = array('idPlayer1' => $idPlayer, 'idPlayer2' => null, 'idPlayer3' => null, 'idPlayer4' => null, 'nbJoueur' => 1);
            file_put_contents($filePlayers, json_encode($players));
            return new JsonResponse($formatted);
        } else {
            $players = file_get_contents($filePlayers);
            $players = json_decode($players, true);
            if($players['nbJoueur'] < 3) {
                $idPlayer              = md5(uniqid(rand(), true));
                $players['nbJoueur']++;
                $formatted = [
                    'idJoueur'  => $idPlayer,
                    'code'      => intval(200),
                    'nomJoueur' => $player,
                    'numJoueur' => intval($players['nbJoueur'])
                ];
                $players['idPlayer' . $players['nbJoueur']] = $idPlayer;
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

    /**
     * @Get("/play/{x}/{y}/{idPlayer}")
     */
    public function playAction(Request $request, $x, $y, $idPlayer)
    {
        $pathPlayers   = $this->get('kernel')->getRootDir().'/../src/AppBundle/Resources/Json/Players.json';
        $players       = file_get_contents($pathPlayers);
        $players       = json_decode($players, true);
        $pathStateGame = $this->get('kernel')->getRootDir().'/../src/AppBundle/Resources/Json/StateGame.json';
        $stateGame   = file_get_contents($pathStateGame);
        $stateGame   = json_decode($stateGame, true);
        $fileTurn    = $this->get('kernel')->getRootDir().'/../src/AppBundle/Resources/Json/turn.json';
        $turnContent = file_get_contents($fileTurn);
        $turn = json_decode($turnContent);
        if ($stateGame != null) {
            if ($x < 19 && $y < 19 && $idPlayer != '') {
                if ($players['idPlayer1'] == $idPlayer || $players['idPlayer2'] == $idPlayer) {
                    $boardManager = $this->get('app.board_manager');
                    $boardManager->setPathTime($this->get('kernel')->getRootDir().'/../src/AppBundle/Resources/Json/Times.json');
                    if ($players['idPlayer1'] == $idPlayer && $turn == $players['idPlayer1']) {
                        if ($stateGame['tableau'][$x][$y] == 0) {
                            $stateGame['tableau'][$x][$y] = 1;
                            $stateGame['code'] = 200;
                            $stateGame = $boardManager->manage($x, $y, $stateGame, 1);
                            $this->jaiFinisDeJouer($idPlayer, $players);
                            file_put_contents($pathStateGame,json_encode($stateGame));
                            return new JsonResponse(['code' => 200]);
                        } else {
                            $response = new JsonResponse(['code' => 406]);
                            $response->setStatusCode(406);
                            return $response;
                        }
                    } elseif ($players['idPlayer2'] == $idPlayer && $turn == $players['idPlayer2']) {
                        if ($stateGame['tableau'][$x][$y] == 0) {
                            $stateGame['tableau'][$x][$y] = 2;
                            $stateGame['code'] = 200;
                            $stateGame = $boardManager->manage($x, $y, $stateGame, 2);
                            $this->jaiFinisDeJouer($idPlayer, $players);
                            file_put_contents($pathStateGame, json_encode($stateGame));
                            return new JsonResponse(['code' => 200]);
                        } else {
                            $response = new JsonResponse(['code' => 406]);
                            $response->setStatusCode(406);
                            return $response;                        }
                    } else {
                        $response = new JsonResponse(['code' => 401]);
                        $response->setStatusCode(401);
                        return $response;                    }
                } else {
                    $response = new JsonResponse(['code' => 401]);
                    $response->setStatusCode(401);
                    return $response;
                }
            } else {
                $response = new JsonResponse(['code' => 406]);
                $response->setStatusCode(406);
                return $response;            }
        } else {
            $response = new JsonResponse(['code' => 401]);
            $response->setStatusCode(401);
            return $response;        }
    }

    /**
     * @Get("/turn/{idPlayer}")
     */
    public function turnAction(Request $request, $idPlayer)
    {
        $pathStateGame = $this->get('kernel')->getRootDir().'/../src/AppBundle/Resources/Json/StateGame.json';

        $filePlayers = $this->get('kernel')->getRootDir().'/../src/AppBundle/Resources/Json/Players.json';
        $filePlayersContent = file_get_contents($filePlayers, true);
        $players = json_decode($filePlayersContent, true);

        // Si L'id du joueur existe bien
        if ($idPlayer == $players['idPlayer1'] || $idPlayer == $players['idPlayer2'] || $idPlayer == $players['idPlayer3'] || $idPlayer == $players['idPlayer4']) {
            $stateGameContent = file_get_contents($pathStateGame);
            $stateGame = json_decode($stateGameContent, true);
            // Si au moins trois joueurs sont connectés
            if ($players['nbJoueur'] > 2) {
                $fileTurn    = $this->get('kernel')->getRootDir().'/../src/AppBundle/Resources/Json/turn.json';
                $turnContent = file_get_contents($fileTurn);
                $turn = json_decode($turnContent);

                if ($stateGame['finPartie'] == true) {
                    $stateGame['status'] = 0;
                    return new JsonResponse($stateGame);
                }
                
                // Si c'est bien à lui de jouer
                if ($turn == $idPlayer) {
                    $stateGame['status'] = 1;
                    return new JsonResponse($stateGame);
                } else {
                    $stateGame['status'] = 0;
                    return new JsonResponse($stateGame);
                }
            } else {
                return new JsonResponse($stateGame);
            }
        } else {
            $response = new JsonResponse(['code' => 401]);
            $response->setStatusCode(401);
            return $response;
        }
    }

    /**
     * @Get("/stream/{mdp}")
     */
    public function streamAction($mdp) {
        if ($mdp == "pastaioli") {
            $pathStateGame = $this->get('kernel')->getRootDir().'/../src/AppBundle/Resources/Json/StateGame.json';
            $stateGameContent = file_get_contents($pathStateGame);
            $stateGame = json_decode($stateGameContent, true);
            return new JsonResponse($stateGame);
        }
        return new JsonResponse("mdp incorect");
    }

    private function generateTimers()
    {
        $times = ['turn' => new \DateTime(), 'gameBegin' => new \DateTime()];
        $jsonTime = json_encode($times);
        file_put_contents($this->get('kernel')->getRootDir().'/../src/AppBundle/Resources/Json/Times.json', $jsonTime);
    }

    /**
     * @param Request $request
     * @Get("/restart/{mdp}")
     * @return JsonResponse
     */
    public function restartGameAction(Request $request, $mdp)
    {
        if ($mdp == 'pastaioli') {
            $this->clearJsons();
            return new JsonResponse(['status' => 'success']);
        } else {
            return new JsonResponse(['status' => 'Mauvais mot de passe']);
        }
    }

    private function clearJsons()
    {
        $fileTurn    = $this->get('kernel')->getRootDir().'/../src/AppBundle/Resources/Json/turn.json';
        $pathPlayers   = $this->get('kernel')->getRootDir().'/../src/AppBundle/Resources/Json/Players.json';
        $pathStateGame = $this->get('kernel')->getRootDir().'/../src/AppBundle/Resources/Json/StateGame.json';
        $fs = new Filesystem();
        $fs->remove($fileTurn);
        $fs->remove($pathPlayers);
        $fs->remove($pathStateGame);
        $fs->touch($fileTurn);
        $fs->touch($pathStateGame);
    }

    private function jaiFinisDeJouer($idPlayer, $players) {
        $fileTurn    = $this->get('kernel')->getRootDir().'/../src/AppBundle/Resources/Json/turn.json';
        if ($idPlayer == $players['idPlayer1']) {
            $turn = json_encode($players['idPlayer2']);
        } else {
            $turn = json_encode($players['idPlayer1']);
        }
        file_put_contents($fileTurn, $turn);
    }

    private function startGame()
    {
        $array = [
            "status"          => null,
            "tableau"         => $this->getEmptyDeck(),
            "finPartie"       => false,
            "detailFinPartie" => null,
            "code"            => 200
        ];
        return $array;
    }
    public function getEmptyDeck()
    {
        return "Ceci doit modéliser le deck";
    }
}
