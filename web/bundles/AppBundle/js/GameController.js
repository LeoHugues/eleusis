/**
 * Created by lehug on 16/05/17.
 */

var url = "http://127.0.0.1:8000/app_dev.php/";
var idJoueur = null;
var numJoueur = null;
var name = null;
var gameAlreadyStart = false;

$(document).ready(function() {
    $('#card-ok').on('click', function () {
        WS_godCheckCard(true);
    });
    $('#card-ko').on('click', function () {
        WS_godCheckCard(false);
    });

    $("#connect").on('click', function(){

        $.ajax({
            url: url + 'connect/' + $('#player-name').val(), // form action url
            type: 'get', // form submit method get/post
            dataType: 'json', // request type html/json/xml
            success: function(result) {
                idJoueur = result.idJoueur;
                console.log('retour call connect success, IdJoueur : ' + idJoueur);
                name = result.nomJoueur;
                // On ferme la modal, on cache le bouton de connection et on affiche un loader en attendant que d'autres joueurs se connectent
                $('#connect-modal').modal('hide');
                $('#start-btn').hide();
                $('#loader').show();

                // Lancement de la fonction de refresh
                refreshGame();
            },
            error: function (error) {
                alert(error.statusText);
            }

        });
    });
});

function refreshGame() {
    WS_refresh();
    setTimeout(refreshGame, 1000);
}

function WS_godRules(rules) {
    $.ajax({
        url: url + 'god-choose-rules/' + rules, 
        type: 'get', 
        dataType: 'json',
        success: function(result) {
            console.log('success');
            $('#game-modal').modal('hide');
        },
        error: function (error) {
            alert(error.statusText);
        }
    });
}

//WS FUNCTIONS
function WS_refresh() {
    //Appel le Web Service et rafraichi l'etat du jeu (tableau)
    $.ajax( {
        type:'Get',
        url: url + 'turn/' + idJoueur,
        success:function(retour)
        {
            console.log('retour call refresh success');
            if (retour.commencerPartie == true && gameAlreadyStart == false) {
                WS_ready();
            } else {
                if (retour.finPartie == true) {
                    // Afficher la fin de la partie....
                    console.log('la partie est termine')
                } else {
                    if (retour.refresh == true) {
                        // mettre à jour les élément du plateau de jeu
                        refreshBoard(retour);
                    }

                    if (retour.status == 1) {
                        //faire jouer le joueur
                        console.log('a moi');

                        $('.action-btn').removeClass('disabled');

                        if (numJoueur == "god") {
                            // En tant que dieux
                            if (retour.godRole == 0) {// dieux invente une regle
                                $('#game-modal').modal();
                                $('#send').on('click', function () {
                                    WS_godRules($('#rules-content').val());
                                });
                            } else if (retour.godRole == 1) { // dieux dit si les cartes rentres ou non
                                $('#check-card').modal();
                                
                            } else if (retour.godRole == 1) { // dieux dit si prophete ou non
                                
                            }
                        } else {
                            // En temps que joueur
                        }
                    } else {
                        // Le laisser attendre
                        console.log('pas a moi');
                        $('.action-btn').addClass('disabled');
                    }
                }
            }

            console.log(retour);
        }
    });
}


function refreshBoard(retour) {
    // Affichage des cartes qui rentrent dans la règle
    var winCardlot = retour.partie.bonnesCartes;
    var winCardHTML = document.getElementById('true-card');
    $('#true-card').empty();

    for (j = 0, taille = winCardlot.length; j < taille; j++) {
        for (i = 0, len = winCardlot[j].length; i < len; i++) {
            div = getHTMLcard(winCardlot[j][i]);
            winCardHTML.appendChild(div);
        }
    }

    // Affichage des cartes qui ne sont pas rentré dans la regle
    var loseCardlot = retour.partie.mauvaisesCartes;
    var loseCardHTML =document.getElementById('false-card');
    $('#false-card').empty();

    for (j = 0, taille = loseCardlot.length; j < taille; j++) {
        for (i = 0, len = loseCardlot[j].length; i < len; i++) {
            div = getHTMLcard(loseCardlot[j][i]);
            loseCardHTML.appendChild(div);
        }
    }

    // Affichage des cartes selectionné par un joueur
    var selectedCards = document.getElementById('selected-card');
    $('#selected-card').empty();
    var cards = retour.partie.selectedCard;
    for (i = 0, len = cards.length; i < len; i++) {
        selectedCards.appendChild(getHTMLcard(cards[i]));
    }

    if (numJoueur != "god") {
        // Affichage de la main du joueur
		
        var num = numJoueur.substring(numJoueur.length-1, numJoueur.length);
        var deck = retour.partie['deckJ' + num];
        var deckNode = document.getElementById('my-deck');
        $('#my-deck').empty();
		showHand(deck);


        $(".playingCard").on('click', function () {
            if ($(this).hasClass('selected')) {
                $(this).removeClass('selected');
            } else {
                $(this).addClass('selected');
            }
        });
    }
}
function showHand(card)
{
	var tabCard = [];
	var couleurCard = '';
	var valueCard = '';
	for(i=0; i < card.length;i++){
		switch (card[i].color)
		{
			case 'trefle': couleurCard = "C";break;
			case 'carreau': couleurCard = "D";break;
			case 'pique': couleurCard = "S";break;
			case 'coeur': couleurCard = "H";break;
		}
		valueCard = card[i].number;
		
		tabCard[i] = valueCard.concat('-',couleurCard);
	}
	var cardDeck = $("#cardDeck").playingCards(tabCard);
    cardDeck.spread(); // show it       
}
function getHTMLcard(card) {
    var labelValue = document.createElement("label");
    labelValue.innerText = card.number;
    
    var br = document.createElement("br");

    var labelColor = document.createElement("label");
    labelColor.innerText = card.color;

    var div = document.createElement("div");
    div.className = "col-sm-1 card my-card";
    div.dataset.color = card.color;
    div.dataset.value = card.number;
    div.appendChild(labelValue);
    div.appendChild(br);
    div.appendChild(labelColor);

    return div;
}

//WS FUNCTIONS
function WS_ready() {
    //Appel le Web Service pour confirmer qu'on est pret à jouer et reccupérer des infos de debut de partie
    $.ajax( {
        type:'Get',
        url: url + 'ready/' + idJoueur,
        success:function(retour)
        {
            numJoueur = retour.numJoueur;
            console.log('call READY success, numJoueur : ' + numJoueur);
            $('#connect-element').hide();
            if (numJoueur == 'god') {
                alert('Vous êtes Dieux !');
            } else {
                alert('Vous êtes le ' + numJoueur);
            }
            $('#game-element').show();
            gameAlreadyStart = true;

            $("#send-card").on('click', function(){

                selectedCards = getSelectedCard();

                $.ajax({
                    url: url + 'player-choose-cards/' + idJoueur, // form action url
                    type: 'get', // form submit method get/post
                    dataType: 'json', // request type html/json/xml
                    data: {cards: selectedCards},
                    success: function(result) {
                    },
                    error: function (error) {
                        alert(error.statusText);
                    }

                });
            });
        }
    });
}

function getSelectedCard() {
    var selectedCards  = [];

    $('.my-card').each(function () {

        if ($(this).hasClass('selected')) {
            card = { color: $(this).data('color'), number: $(this).data('value') };
            selectedCards.push(card);
            $(this).removeClass('selected');
        }
    });

    return selectedCards;
}

function WS_godCheckCard(reponse) {
    $.ajax({
        url: url + 'god-say-if-cards-match/' + reponse, // form action url
        type: 'get', // form submit method get/post
        dataType: 'json', // request type html/json/xml
        success: function(result) {
            $('#check-card').modal('toggle');
            $('#selected-card').empty();
        },
        error: function (error) {
            alert(error.statusText);
        }

    });
}