<?php
require_once('./module/db.php');
require_once('./model/discount_code.php');
require_once('./model/artist_discount.php');
require_once('./model/transaction.php');
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

        $lookupResults->providedDiscountString = $this->discountCodeString;
        
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
        if ($this->discountCanBeApplied($conn, $discount)){
            $lookupResults->discount = $discount;
            $lookupResults->artistName = $this->artistName;
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
    // discount code. This is an expensive operation. 
    private function getArtistsInCartWithDiscountCodeMatching($conn, $discountCode) {
        // Get IDs of artists in cart
        $artistsInCart = $this->getArtistsInCart();
        $matchingDiscounts = ArtistDiscount::getAllByCode($conn, $discountCode);

        // Filter so that we only have discounts that can be applied.
        // This checks the transaction count.
        $applicableDiscounts = array_filter(
            $matchingDiscounts,
            function($artistDiscount) use ($conn) {
                $discount = DiscountCode::getById($conn, $artistDiscount->discountId);
                return $this->discountCanBeApplied($conn, $discount);
            }
        );

        // Dear PHP, why is the argument ordering backwards between
        // array_filter and array_map?
        $artistsInCartWithMatchingDiscount = array_filter(
            $applicableDiscounts,
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

        // I'm not sure why, but it was screwing up the indices. 
        return array_values($artists);
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

    private function discountCanBeApplied($conn, $discount) {
        if (empty($discount) || !$discount->isActive()) {
            return false;
        }

        $timesRedeemed = Transaction::getTotalUsagesOfDiscount($conn, $discount->id);

        // If timesRedeemable = 0, then it can always be redeemed.
        // If it has been redeemed the same number (or more!) times as it is redeemable,
        // then we cannot apply it.
        // IDEALLY this check should be done in DiscountCode, but I couldn't find a
        // non-gross way of doing so because it requires knowledge of Transaction
        // and a database connection. This is a limitation of my model framework.
        if ($discount->timesRedeemable > 0 && $timesRedeemed >= $discount->timesRedeemable) {
            return false;
        }

        return true;
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
    public string $artistName;
    public string $providedDiscountString;
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
    // and it is active. The DiscountApplicator should have
    // guaranteed the code is active but double check.
    public function discountMatchFound() {
        return (!empty($this->discount) && $this->discount->isActive());
    }
}

?>