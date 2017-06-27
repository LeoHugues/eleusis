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
    const JOUEUR_DIT_PROPHETE       = 3;
    const JOUEUR_SELECTIONNE_CARTES = 4;

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

        $this->resetRefresh();
        $players = $this->getPlayers();
        $godPath = $this->get('kernel')->getRootDir().'/../src/AppBundle/Resources/Json/God.json';

        if (file_exists($godPath) == false) {
            $godId = $players['idJoueur' . rand(1, self::NB_JOUEUR)];
            file_put_contents($godPath, json_encode($godId));
            $players = $this->refactorPlayers($godId);
            $this->initTurn();

            // Initialisation du jeu
            $state = $this->getStateGame();
            $state['partie'] = $this->getEmptyDeck();
            $this->setStateGame($state);
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

        $numCard           = array(1,2,3,4,5,6,7,8,9,10,11,12,13);
        $peer              = array('yes','no');
        $cardType          = array('trèfle', 'pique', 'carreau', 'coeur');
        $colors            = array('noir', 'rouge');
        $numberCardsToPut  = array(1,2,3,4,5);
        $redundancy        = array(0,1,2,3,4); // The number of intermediate(s) card(s)
        $alternancy        = array('yes', 'no'); // ex : One red card then one black card

        $rule = array('numberCardsToPut' => 2,'colors' => 'rouge', 'numCard' => null, 'cardType' => null, 'redundancy' => 0, 'alternancy' => 'no');

        

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
    public function dieuxDitSiLesCarteRentrenteAction()
    {
        dump($this->getEmptyDeck());die;
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
            /** $refresh => nb de joueur ayant déjà rafraichis le plateau */
            $refresh = $this->getRefresh();
            if ($refresh != null && in_array($idPlayer, $refresh)) {// S'il y a pas besoin de rafrichir le plateau de jeu
                $stateGame['refresh'] = false;
            } else {
                $this->addRefresh($idPlayer);
            }

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
        $rulesPath      = $this->get('kernel')->getRootDir().'/../src/AppBundle/Resources/Json/Rules.json';
        $refreshPath    = $this->get('kernel')->getRootDir().'/../src/AppBundle/Resources/Json/Refresh.json';

        $fs = new Filesystem();
        $fs->remove($fileTurn);
        $fs->remove($pathPlayers);
        $fs->remove($pathStateGame);
        $fs->remove($pathGodId);
        $fs->remove($rulesPath);
        $fs->remove($refreshPath);
        $fs->touch($fileTurn);
        $fs->touch($pathStateGame);
        $fs->touch($refreshPath);
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
            "refresh"         => true,                   // True si il y a eu un changement
            "godRole"         => self::DIEUX_INVENTE_UNE_REGLE,
            "finPartie"       => false,                  // True si c'est terminé sinon false
            "detailFinPartie" => null,                   // Explique pourquoi la partie est terminée
            "code"            => 200                     // code erreur...
        ];

        return $array;
    }


    public function getEmptyDeck()
    {
        /** Deux paquets de 52 cartes*/
        $cartes = array(
            array('couleur' => 'pique',  'valeur' => '1'),
            array('couleur' => 'trefle', 'valeur' => '1'),
            array('couleur' => 'coeur',  'valeur' => '1'),
            array('couleur' => 'carreau','valeur' => '1'),

            array('couleur' => 'pique',  'valeur' => '2'),
            array('couleur' => 'trefle', 'valeur' => '2'),
            array('couleur' => 'coeur',  'valeur' => '2'),
            array('couleur' => 'carreau','valeur' => '2'),

            array('couleur' => 'pique',  'valeur' => '3'),
            array('couleur' => 'trefle', 'valeur' => '3'),
            array('couleur' => 'coeur',  'valeur' => '3'),
            array('couleur' => 'carreau','valeur' => '3'),

            array('couleur' => 'pique',  'valeur' => '4'),
            array('couleur' => 'trefle', 'valeur' => '4'),
            array('couleur' => 'coeur',  'valeur' => '4'),
            array('couleur' => 'carreau','valeur' => '4'),

            array('couleur' => 'pique',  'valeur' => '5'),
            array('couleur' => 'trefle', 'valeur' => '5'),
            array('couleur' => 'coeur',  'valeur' => '5'),
            array('couleur' => 'carreau','valeur' => '5'),

            array('couleur' => 'pique',  'valeur' => '6'),
            array('couleur' => 'trefle', 'valeur' => '6'),
            array('couleur' => 'coeur',  'valeur' => '6'),
            array('couleur' => 'carreau','valeur' => '6'),

            array('couleur' => 'pique',  'valeur' => '7'),
            array('couleur' => 'trefle', 'valeur' => '7'),
            array('couleur' => 'coeur',  'valeur' => '7'),
            array('couleur' => 'carreau','valeur' => '7'),

            array('couleur' => 'pique',  'valeur' => '8'),
            array('couleur' => 'trefle', 'valeur' => '8'),
            array('couleur' => 'coeur',  'valeur' => '8'),
            array('couleur' => 'carreau','valeur' => '8'),

            array('couleur' => 'pique',  'valeur' => '9'),
            array('couleur' => 'trefle', 'valeur' => '9'),
            array('couleur' => 'coeur',  'valeur' => '9'),
            array('couleur' => 'carreau','valeur' => '9'),

            array('couleur' => 'pique',  'valeur' => '10'),
            array('couleur' => 'trefle', 'valeur' => '10'),
            array('couleur' => 'coeur',  'valeur' => '10'),
            array('couleur' => 'carreau','valeur' => '10'),

            array('couleur' => 'pique',  'valeur' => '11'),
            array('couleur' => 'trefle', 'valeur' => '11'),
            array('couleur' => 'coeur',  'valeur' => '11'),
            array('couleur' => 'carreau','valeur' => '11'),

            array('couleur' => 'pique',  'valeur' => '12'),
            array('couleur' => 'trefle', 'valeur' => '12'),
            array('couleur' => 'coeur',  'valeur' => '12'),
            array('couleur' => 'carreau','valeur' => '12'),

            array('couleur' => 'pique',  'valeur' => '13'),
            array('couleur' => 'trefle', 'valeur' => '13'),
            array('couleur' => 'coeur',  'valeur' => '13'),
            array('couleur' => 'carreau','valeur' => '13'),
            
            array('couleur' => 'pique',  'valeur' => '1'),
            array('couleur' => 'trefle', 'valeur' => '1'),
            array('couleur' => 'coeur',  'valeur' => '1'),
            array('couleur' => 'carreau','valeur' => '1'),

            array('couleur' => 'pique',  'valeur' => '2'),
            array('couleur' => 'trefle', 'valeur' => '2'),
            array('couleur' => 'coeur',  'valeur' => '2'),
            array('couleur' => 'carreau','valeur' => '2'),

            array('couleur' => 'pique',  'valeur' => '3'),
            array('couleur' => 'trefle', 'valeur' => '3'),
            array('couleur' => 'coeur',  'valeur' => '3'),
            array('couleur' => 'carreau','valeur' => '3'),

            array('couleur' => 'pique',  'valeur' => '4'),
            array('couleur' => 'trefle', 'valeur' => '4'),
            array('couleur' => 'coeur',  'valeur' => '4'),
            array('couleur' => 'carreau','valeur' => '4'),

            array('couleur' => 'pique',  'valeur' => '5'),
            array('couleur' => 'trefle', 'valeur' => '5'),
            array('couleur' => 'coeur',  'valeur' => '5'),
            array('couleur' => 'carreau','valeur' => '5'),

            array('couleur' => 'pique',  'valeur' => '6'),
            array('couleur' => 'trefle', 'valeur' => '6'),
            array('couleur' => 'coeur',  'valeur' => '6'),
            array('couleur' => 'carreau','valeur' => '6'),

            array('couleur' => 'pique',  'valeur' => '7'),
            array('couleur' => 'trefle', 'valeur' => '7'),
            array('couleur' => 'coeur',  'valeur' => '7'),
            array('couleur' => 'carreau','valeur' => '7'),

            array('couleur' => 'pique',  'valeur' => '8'),
            array('couleur' => 'trefle', 'valeur' => '8'),
            array('couleur' => 'coeur',  'valeur' => '8'),
            array('couleur' => 'carreau','valeur' => '8'),

            array('couleur' => 'pique',  'valeur' => '9'),
            array('couleur' => 'trefle', 'valeur' => '9'),
            array('couleur' => 'coeur',  'valeur' => '9'),
            array('couleur' => 'carreau','valeur' => '9'),

            array('couleur' => 'pique',  'valeur' => '10'),
            array('couleur' => 'trefle', 'valeur' => '10'),
            array('couleur' => 'coeur',  'valeur' => '10'),
            array('couleur' => 'carreau','valeur' => '10'),

            array('couleur' => 'pique',  'valeur' => '11'),
            array('couleur' => 'trefle', 'valeur' => '11'),
            array('couleur' => 'coeur',  'valeur' => '11'),
            array('couleur' => 'carreau','valeur' => '11'),

            array('couleur' => 'pique',  'valeur' => '12'),
            array('couleur' => 'trefle', 'valeur' => '12'),
            array('couleur' => 'coeur',  'valeur' => '12'),
            array('couleur' => 'carreau','valeur' => '12'),

            array('couleur' => 'pique',  'valeur' => '13'),
            array('couleur' => 'trefle', 'valeur' => '13'),
            array('couleur' => 'coeur',  'valeur' => '13'),
            array('couleur' => 'carreau','valeur' => '13'),
        );

        shuffle($cartes);

        $partie = array(
            'pioche'            => $cartes,
            'bonnes-cartes'     => array(),
            'mauvaises-cartes'  => array(),
        );
        
        $players = $this->getPlayers();
        
        foreach ($players as $key => $idPlayer) {
            if ($key != "god" && $key != "nbJoueur" && $idPlayer != null) {
                $deckTmp = array_slice($partie['pioche'], 0, 5, true);
                $deck = array_slice($partie['pioche'], 0, 5);
                $partie['pioche'] = $this->array_diff_assoc_recursive($partie['pioche'], $deckTmp);
                $partie['deckJ' . substr($key, -1)] = $deck;
            }
        }

        return $partie;
    }

    private function array_diff_assoc_recursive($array1, $array2) {
        $difference=array();
        foreach($array1 as $key => $value) {
            if( is_array($value) ) {
                if( !isset($array2[$key]) || !is_array($array2[$key]) ) {
                    $difference[$key] = $value;
                } else {
                    $new_diff = $this->array_diff_assoc_recursive($value, $array2[$key]);
                    if( !empty($new_diff) )
                        $difference[$key] = $new_diff;
                }
            } else if( !array_key_exists($key,$array2) || $array2[$key] !== $value ) {
                $difference[$key] = $value;
            }
        }
        return $difference;
    }

    private function getRefresh()
    {
        $refreshPath = $this->get('kernel')->getRootDir().'/../src/AppBundle/Resources/Json/Refresh.json';
        $refresh = file_get_contents($refreshPath, true);
        return json_decode($refresh, true);
    }

    private function addRefresh($idJoueur) {
        $refreshPath = $this->get('kernel')->getRootDir().'/../src/AppBundle/Resources/Json/Refresh.json';
        $refresh = file_get_contents($refreshPath, true);
        $refresh = json_decode($refresh, true);
        $refresh[] = $idJoueur;
        file_put_contents($refreshPath, json_encode($refresh, true));
    }

    private function resetRefresh() {
        $refreshPath    = $this->get('kernel')->getRootDir().'/../src/AppBundle/Resources/Json/Refresh.json';
        $fs = new Filesystem();
        $fs->remove($refreshPath);
        $fs->touch($refreshPath);
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

    private function setStateGame($state) {
        $pathStateGame = $this->get('kernel')->getRootDir().'/../src/AppBundle/Resources/Json/StateGame.json';
        file_put_contents($pathStateGame, json_encode($state, true));
    }

    private function getTurn() {
        $pathTurn = $this->get('kernel')->getRootDir().'/../src/AppBundle/Resources/Json/turn.json';
        $turn = file_get_contents($pathTurn);
        return json_decode($turn, true);
    }

    private function setTurn($turn) {
        $pathTurn = $this->get('kernel')->getRootDir().'/../src/AppBundle/Resources/Json/turn.json';
        file_put_contents($pathTurn, json_encode($turn, true));
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
