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
    const NB_JOUEUR = 3;
    const DIEUX_INVENTE_UNE_REGLE   = 0;
    const DIEUX_VERIFIE_DES_CARTES  = 1;
    const DIEUX_DIT_SI_PROPHETE     = 2;

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
            $players = array(
                'idJoueur1' => $idPlayer,
                'idJoueur2' => null,
                'idJoueur3' => null,
                'idJoueur4' => null,
                'nbJoueur' => 1
            );
            file_put_contents($filePlayers, json_encode($players));
            return new JsonResponse($formatted);
        } else {
            $players = file_get_contents($filePlayers);
            $players = json_decode($players, true);
            if($players['nbJoueur'] < self::NB_JOUEUR) {
                $idPlayer              = md5(uniqid(rand(), true));
                $players['nbJoueur']++;
                $formatted = [
                    'idJoueur'  => $idPlayer,
                    'code'      => intval(200),
                    'nomJoueur' => $player,
                    'numJoueur' => intval($players['nbJoueur'])
                ];
                $players['idJoueur' . $players['nbJoueur']] = $idPlayer;

                // Si le nombre de joueur est atteint on peut commencer la partie
                if ($players['nbJoueur'] == self::NB_JOUEUR) {
                    $stateGameContent = file_get_contents($pathStateGame);
                    $stateGame = json_decode($stateGameContent, true);
                    $stateGame['commencerPartie'] = true;
                    file_put_contents($pathStateGame, json_encode($stateGame));
                }
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
     * @Get("/ready/{idJoueur}")
     */
    public function readyAction($idJoueur) {

        $players = $this->getPlayers();
        $godPath = $this->get('kernel')->getRootDir().'/../src/AppBundle/Resources/Json/God.json';

        if (file_exists($godPath) == false) {
            $godId = $players['idJoueur' . rand(1, self::NB_JOUEUR)];
            file_put_contents($godPath, json_encode($godId));
            $players = $this->refactorPlayers($godId);
            $this->initTurn();
        }

        foreach ($players as $key => $id)
        {
            if ($id == $idJoueur) {
                return new JsonResponse(array('numJoueur' => $key));
            }
        }


        return new JsonResponse(array('numJoueur' => "joueur" . 2222));
    }

    /**
     * @Get("/god-choose-rules/{rules}")
     */
    public function dieuxChoisiUneRegleAction($rules)
    {
        $rulesPath = $this->get('kernel')->getRootDir().'/../src/AppBundle/Resources/Json/Rules.json';

        if (!file_exists($rulesPath)) {
            file_put_contents($rulesPath, json_encode($rules));
        }

        $turn = $this->getTurn();

        $turn['lastPlayer'] = $this->getGodId();
        $turn['nextPlayer'] = $this->getPlayers()['idJoueur1'];

        $this->setTurn($turn);

        return new JsonResponse(array('status' => 'success'));
    }

    /**
     * @Get("/god-say-if-cards-match")
     */
    public function dieuxDitSiLesCarteRentrenteAction($isProphete)
    {

    }

    /**
     * @Get("/god-say-if-prophete/{isProphete")
     */
    public function dieuxDitSiPropheteAction($isProphete)
    {

    }

    /**
     * @Get("/player-choose-cards/{idJoueur}")
     */
    public function joueurSelectionneDesCartesAction(Request $request, $idJoueur)
    {
        $cards = $request->get('cards');

        //
    }

    /**
     * @Get("/player-say-prophete/{idJoueur}")
     */
    public function joueurDitPropheteAction($idJoueur)
    {

    }

    /**
     * @Get("/turn/{idPlayer}")
     */
    public function turnAction(Request $request, $idPlayer)
    {
        $players = $this->getPlayers();

        $idPlayerExist = false;
        foreach ($players as $player) {
            if ($player == $idPlayer) {
                $idPlayerExist = true;
            }
        }

        // Si L'id du joueur existe bien
        if ($idPlayerExist) {
            $stateGame = $this->getStateGame();
            // Si au moins trois joueurs sont connectés
            if ($players['nbJoueur'] >= self::NB_JOUEUR) {
                $turn = $this->getTurn();

                if ($stateGame['finPartie'] == true) {
                    $stateGame['status'] = 0;
                    return new JsonResponse($stateGame);
                }
                
                // Si c'est bien à lui de jouer
                if ($turn['nextPlayer'] == $idPlayer) {
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
        $fileTurn       = $this->get('kernel')->getRootDir().'/../src/AppBundle/Resources/Json/turn.json';
        $pathPlayers    = $this->get('kernel')->getRootDir().'/../src/AppBundle/Resources/Json/Players.json';
        $pathStateGame  = $this->get('kernel')->getRootDir().'/../src/AppBundle/Resources/Json/StateGame.json';
        $pathGodId      = $this->get('kernel')->getRootDir().'/../src/AppBundle/Resources/Json/God.json';
        $rulesPath = $this->get('kernel')->getRootDir().'/../src/AppBundle/Resources/Json/Rules.json';

        $fs = new Filesystem();
        $fs->remove($fileTurn);
        $fs->remove($pathPlayers);
        $fs->remove($pathStateGame);
        $fs->remove($pathGodId);
        $fs->remove($rulesPath);
        $fs->touch($fileTurn);
        $fs->touch($pathStateGame);
    }

    private function jaiFinisDeJouer($idPlayer, $players) {
        $fileTurn    = $this->get('kernel')->getRootDir().'/../src/AppBundle/Resources/Json/turn.json';
        if ($idPlayer == $players['idPlayer1']) {
            $turn = json_encode($players['idJoueur2']);
        } else {
            $turn = json_encode($players['idJoueur1']);
        }
        file_put_contents($fileTurn, $turn);
    }

    private function startGame()
    {
        $array = [
            "commencerPartie" => false,                  // True si la partie peu commencer
            "status"          => null,                   // True si c'est à moi sinon false
            "godRole"         => self::DIEUX_INVENTE_UNE_REGLE,
            "partie"          => $this->getEmptyDeck(),  // Modelisation des cartes
            "finPartie"       => false,                  // True si c'est terminé sinon false
            "detailFinPartie" => null,                   // Explique pourquoi la partie est terminée
            "code"            => 200                     // code erreur...
        ];

        return $array;
    }
    public function getEmptyDeck()
    {
        return "Ceci doit modéliser le deck";
    }

    private function getPlayers()
    {
        $filePlayers = $this->get('kernel')->getRootDir().'/../src/AppBundle/Resources/Json/Players.json';
        $filePlayersContent = file_get_contents($filePlayers, true);
        return json_decode($filePlayersContent, true);
    }

    private function getGodId()
    {
        $godPath   = $this->get('kernel')->getRootDir().'/../src/AppBundle/Resources/Json/God.json';
        $jsonGodId = file_get_contents($godPath, true);

        return json_decode($jsonGodId, true);
    }

    private function getStateGame() {
        $pathStateGame = $this->get('kernel')->getRootDir().'/../src/AppBundle/Resources/Json/StateGame.json';
        $stateGameContent = file_get_contents($pathStateGame);
        return json_decode($stateGameContent, true);
    }

    private function getTurn() {
        $pathTurn = $this->get('kernel')->getRootDir().'/../src/AppBundle/Resources/Json/turn.json';
        $turn = file_get_contents($pathTurn);
        return json_decode($turn, true);
    }

    private function setTurn($turn) {
        $pathTurn = $this->get('kernel')->getRootDir().'/../src/AppBundle/Resources/Json/turn.json';
        file_put_contents($pathTurn, $turn);
    }

    private function initTurn() {
        $turn = array(
            'lastPlayer' => null,
            'nextPlayer' => $this->getGodId()
        );
        $fileTurn    = $this->get('kernel')->getRootDir().'/../src/AppBundle/Resources/Json/turn.json';
        file_put_contents($fileTurn, json_encode($turn));
    }

    private function refactorPlayers($godId) {
        $players = $this->getPlayers();
        $i = 1;
        $godIsDefine = false;
        $newPlayers = array();
        foreach ($players as $key => $value) {

            if ($key != 'nbJoueur') {
                if ($value == $godId) {
                    $newPlayers['god'] = $value;
                    $godIsDefine = true;
                } else {
                    if ($godIsDefine == true) {
                        $numJoueur = $i-1;
                        $newPlayers["idJoueur" . $numJoueur] = $value;
                    } else {
                        $newPlayers["idJoueur" . $i] = $value;
                    }
                }
            } else {
                $newPlayers[$key] = $value;
            }
            $i++;
        }

        $filePlayers = $this->get('kernel')->getRootDir().'/../src/AppBundle/Resources/Json/Players.json';

        file_put_contents($filePlayers, json_encode($newPlayers));

        return $newPlayers;
    }


    private function nextPlayer()
    {
        $players = $this->getPlayers();
    }
}
