## Setup

I'm using php version 8.1.12 on macOS. There are no other dependencies.
(other than jQuery which retrieved)

### Creating database

I don't like uploading database files to source control. To get a
basic database setup, run the `populate_db.php` script from the project
root directory:
`cd discount_code; php populate_db.php`

This creates 3 users:
- jeffrosenstock (who is an artist)
- PUP (who is an artist)
- leah (who would like to be an artist one day)

It also creates a few discount codes:
Code | Artist | Times Redeemable | Discount Type
--- | --- | --- | ---
SKADREAM20 | jeffrosenstock | 1 | Percent
WORRY | jeffrosenstock | 5 | Flat
DVP | PUP | 0 (no limit) | Buy one, get one
SKADREAM20 | PUP | 0 (no limit) | Buy one, get one

The intention with these is to show that each of the discount types 
(percentage, flat, bogo) work, and that codes can be duplicated
globally, but must be unique to each artist. 

### Running Server

To run the dev server:
`cd src; php -S localhost:8000`

## Site Map

Login:
/login

Discount code overview:
/discount_codes/admin

Discount code creation:
/discount_codes/create

Checkout:
/checkout

## Disclaimer

Router took inspiration from [this example](https://steampixel.de/simple-and-elegant-url-routing-with-php/),
but I made it reusable.

## Self-critiques

- No tests. Don't know PHP well enough.

- Models know FAR too much about the DB for my liking.

- Errors currently unhandled because I'm not quite sure how yet.

- Lack of consistency with terminology (render sometimes means to echo, sometimes returns string)

