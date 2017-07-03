
if (Array.indexOf === undefined) {
    // doens't exist in oldIE
    /* Finds the index of the first occurence of item in the array, or -1 if not found */
    Array.prototype.indexOf = function(v) {
        for (var i = 0; i < this.length; ++i) {
            if (this[i] === v) {
                return i;
            }
        }
        return - 1;
    };
}
 (function(window,document,undefined){
    var cardsRegisted = false;
    var cardsHand = [];
    /**
     * The playing card library core object
     *
     * @param obj conf Configuration option overrides
     *
     * @return obj an instance of the constructed library object (deck of cards)
     */
    var playingCards = window.playingCards = function(conf) {
        if(cardsRegisted == false)
        {            
            cardsHand = conf;
            
            cardsRegisted = true;
        }
        
        var c = objExtend(playingCards.defaults, conf);
        if (! (this instanceof playingCards)) {
            // in jquery mode
            c.el = $(this); // capture the context (this will be the cardTable/Deck element)
            return new playingCards(c);
        }
        this.conf = c;
        this.init();
        return this;
    };
    /**
     * initializer - builds the deck
     */
    playingCards.prototype.init = function() {        
        this.cards = [];
        var o = this.conf,
            l,i,s,r,j;
        // populate draw pile
        for(i=0;i <cardsHand.length;i++)
        {
            var splitedString = cardsHand[i].split('-');
            var Chiffre = splitedString[0];
            var Couleur = splitedString[1];
             this.cards[i] = new playingCards.card(Chiffre, o.ranks[r], Couleur, o.suits[s]);    
        }
             
    };
    // TODO: create more methods:
    // playingCards.prototype.order (set to out-of-box ordering)
    // -- do we want other special formations (like trick deck ordering systems that deal perfect hands)?
    // -- probably going to leave this as an extension option
    
    /*
     * requires jquery (currently)
     * TODO: put this in a UI extension pack along with all the other demo methods
     */
    playingCards.prototype.spread = function(dest) {

        if (!this.conf.el && !dest) {
            return false;
        }
        var to = this.conf.el || dest,
            l = this.cards.length,
            i;
        to.html('');
        // clear (just a demo)
        for (i = 0; i < l; i++) {
            to.append(this.cards[i].getHTML());
        }
    };
    /**
     * configuration defaults
     */
    playingCards.defaults = {
        "decks": 1,
        // TODO: enable 'font' option -- loading cards.ttf
        "renderMode": 'css',
        // For a coustom " of "-String
        "ofString": " of ",
        "startShuffled": true,
        "ranks": {
            "2": "Two",
            "3": "Three",
            "4": "Four",
            "5": "Five",
            "6": "Six",
            "7": "Seven",
            "8": "Eight",
            "9": "Nine",
            "10": "Ten",
            "11": "Jack",
            "12": "Queen",
            "13": "King",
            "1": "Ace"
        },
        "suits": {
            "S": "Spades",
            "D": "Diamonds",
            "C": "Clubs",
            "H": "Hearts"
        }
    };

    /**
     */
    playingCards.card = function(rank, rankString, suit, suitString, conf) {
        if (! (this instanceof playingCards.card)) {
            return new playingCards.card(rank, rankString, suit, suitString, conf);
        }

        this.conf = objExtend(playingCards.card.defaults, conf);

        if (suit === undefined) {
            //Arguments are rank, suit
            suit = rankString;
            rankString = playingCards.defaults.ranks[rank];
            suitString = playingCards.defaults.suits[suit];
        }

        this.rank = rank;
        this.rankString = rankString;
        this.suit = suit;
        this.suitString = suitString;
        return this;
    };
    /**
     * configuration defaults
     */
    playingCards.card.defaults = {
        "singleFace": false
        // false will use a different image for each suit/face, true will use diamond image for all
    };
    /**
     * get the text representation of the card
     */
    playingCards.card.prototype.toString = function() {
        return this.suitString !== "" ? this.rankString + playingCards.defaults.ofString + this.suitString: this.rankString;
    };

    /**
     * Simple object extend to override default settings
     */
    function objExtend(o, ex) {
        if (!ex) {
            return o;
        }
        for (var p in ex) {
            o[p] = ex[p];
        }
        return o;
    }

    

})(this,this.document);
