# Intro
## Setup

I'm using php version 8.1.12 and sqlite3 version 3.37 on macOS. There are no other dependencies.
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

# Design

## Goals
All websites that I've worked on before have been single-page apps,
so I wanted to use MVC. I'm also less familiar with PHP than I am
with the rest of the stack, so I wanted to do most of the work with
PHP to get a feel for the language. I also wanted to put an emphasis
on security (although the app is by no means secure) by trusting 
nothing sent from the client (with the exception of the UserID cookie).


## Project layout

The project is a fairly standard MVC application. I've added `src/module`
to host some additional logic. At the top layer of the `src` directory,
`index.php` is where all initial requests come in before being passed
to the `Router`, which filters those requests to their respective
controllers. The `Router` is also responsible for parsing URI query
parameters and regex matching (for routes that allow sub-routes).


## Design Conventions / Philosophy

I wanted all pages to extend `abc_page.php` which is responsible for
the majority of the webpage, and also handles some common functionality
like requiring a user be logged in, or redirecting to another page. I 
wanted all views to be objects, that the controller would instantiate,
provide with the necessary data, and then render. Since each view
should only be a small part of the page, this in theory would allow it 
to be reusable. For example, while the `CreateDiscountView` is
its own page, I envisioned it originally as a modal overtop of the
discount overview page.

I also did not want to pass much state back and forth between client
and server. For AJAX requests from the client, I wanted the server
to reply with a partial view. This is most notable with the `Checkout`
page where the client only handles sending the server the discount
code and artist name. When passing responses back to the client,
I wanted to represent them as objects that I populated with data before
serialization.

# Retrospective

## Future work

A cleanup is in order, first of all. Some of the controllers
get a little hairy for my liking. The routes... also don't make 
much sense. I would like to do more research into what's considered
"good practice" for URL routes. I'm also not consistent with a lot of
my conventions. I would like to define a set of code conventions for
the project and go through everything to make it conformant. More
comments are also in order.

I did not consider page responsiveness. I made use of a CSS grid
and percentages rather than raw pixels so that different layouts
could be used for different platforms without huge redesigns, but
I'm otherwise unfamiliar with the process.

I'd like to make more use of modals rather than separate pages.
As mentioned before, I think it would be helpful to have the 
discount creation / edit page be a modal rather than a separate page.

I'd like to give each page its own `.js` and `.css` file so as to keep
the main `abc_page.php` style and javascript clean. I imagine each
view knowing about its CSS and Javascript files, and the controller
adding said files to the ABCPage right before the call to `renderBasePage()`.

I'd like to handle errors in a more robust manner. A logging system
would have made my life a lot easier for debugging. I'd also like to
move away from C-style error handling (return data or null if invalid)
and get more into the habit of taking advantage of exceptions for 
errors. I'd love to learn canonical ways of handling errors with 
PHP because I found it annoying that certain errors would not 
cause a fatal exception and would instead end up echo'd to the view.
I thought about handling this by wrapping the router with `ob_start()`,
but a lot of my views were created with that method and I'm not sure if
activating output buffering while already buffering would work.

I'd also like to go back and write a suite of unit tests for the
application. Starting this project, I wasn't comfortable enough with
PHP to do Test-Driven Development as I usually do (for Python, at 
least).