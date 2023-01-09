<?php
require_once('./module/db.php');
require_once('./model/discount_code.php');
require_once('./model/artist_discount.php');
require_once('./model/cart.php');

/*
Class that handles the application and validation of discount codes
*/
class DiscountApplicator {

    public string $artistName;
    public string $discountCodeString;
    public Cart $cart;

    function __construct(Cart $cart) {
        $this->cart = $cart;
        $this->artistName = '';
        $this->discountCodeString = '';
    }

    public function performLookup($conn) : DiscountApplicationResults {
        $lookupResults = new DiscountApplicationResults();
        // This could be changed to do lookups for discounts for artists,
        // but for now it should return empty results (e.g. nothing found)
        // if there is no discount string.
        if (empty($this->discountCodeString)) {
            return $lookupResults;
        }
        
        // Make sure that the cart is set and not empty
        if (empty($this->cart) || count($this->cart->items) === 0) {
            return $lookupResults;
        }

        // If there is no artist name, try to get code 
        if (empty($this->artistName)) {
            $possibleArtists = $this->getArtistsInCartWithDiscountCodeMatching($conn, $this->discountCodeString);
            
            // If there wasn't just one possible artist, return the
            // lookup results. Either there is no code, or there are
            // multiple.
            if (count($possibleArtists) !== 1) {
                $lookupResults->possibleArtists = $possibleArtists;
                return $lookupResults;
            }

            $this->artistName = $possibleArtists[0];
        }

        // If we have both the discount code and the artist name,
        // we should be able to just look up the code.

        $discount = $this->getDiscountUsingArtistAndCode($conn);

        // Make sure that the discount code is active if found
        if (!empty($discount) && $discount->isActive()){
            $lookupResults->discount = $discount;
        }

        return $lookupResults;
    }

    private function getDiscountUsingArtistAndCode($conn) {
        $artistAndDiscount = ArtistDiscount::getByArtistNameAndCodeString($conn, $this->artistName, $this->discountCodeString);
        if (empty($artistAndDiscount)) {
            // TODO: probably throw an error. We should not get here.
            return null;
        }
        // If the discount code came back null, there's nothing we can do
        return DiscountCode::getById($conn, $artistAndDiscount->discountId);
    }

    // Get list of artist names that are in the cart and have an identical 
    // discount code 
    private function getArtistsInCartWithDiscountCodeMatching($conn, $discountCode) {
        // Get IDs of artists in cart
        $artistsInCart = $this->getArtistsInCart();
        $matchingDiscounts = ArtistDiscount::getAllByCode($conn, $discountCode);

        // Dear PHP, why is the argument ordering backwards between
        // array_filter and array_map?
        $artistsInCartWithMatchingDiscount = array_filter(
            $matchingDiscounts,
            function($artistDiscount) use ($artistsInCart){
                return in_array($artistDiscount->artistId, $artistsInCart);
            }
        );

        // Reduce artist discounts down to just artist names
        $artists = array_map(
            function($artistDiscount) {
                return $artistDiscount->artistName;
            },
            $artistsInCartWithMatchingDiscount
        );

        return $artists;
    }

    // Get list of artist IDs of the artists in this cart
    private function getArtistsInCart() {
        $artistIds = [];
        foreach ($this->cart->items as $item) {
            if (!in_array($item->artistId, $artistIds)) {
                $artistIds[] = $item->artistId;
            }
        } 

        return $artistIds;
    }
}

/*
Class representing the results of looking up / applying
a discount. There are three possible results
1. An applicable discount code was found!
2. There were multiple discount codes by the same name
that might apply to this cart- we need to know which artist's
code to use.
3. There are no applicable discount codes. Boo.
*/
class DiscountApplicationResults {

    public DiscountCode $discount;
    public Array $possibleArtists;

    public function __construct() {
        $discount = null;
        $possibleArtists = null;
    }

    // Returns true if there were multiple options available,
    // meaning that an artist name is required.
    public function multipleDiscountOptionsAvailable() {
        return !empty($this->possibleArtists) && count($this->possibleArtists) > 0;
    } 

    // Returns true if a single discount code was found
    public function discountMatchFound() {
        return !empty($this->discount);
    }
}

?>