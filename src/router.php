<?php

class Router {

    function __construct() {
        $this->routes = [];
    }

    /* Add a route to the router.
       $expression - regular expression to match as a path.
       $function - function to call if the requested URL matches
            this expression. */
    public function addRoute($expression, $function) {
        $this->routes[] = Array(
            'expression' => '#^'.$expression.'$#',
            'function' => $function
        );
    }

    /* Route the requested URI to the provided routes */
    public function run() {
        $uri = $_SERVER['REQUEST_URI'];
        $path = parse_url($uri, PHP_URL_PATH);
        $url_params = parse_url($uri, PHP_URL_QUERY);
        $parsed_params = '';
        
        // TODO: determine if this is the correct way to check if a string is non-null/empty
        if ($url_params !== '' && $url_params !== null) {
            parse_str($url_params, $parsed_params);
        }

        foreach ($this->routes as $route) {
            if (preg_match($route['expression'], $path, $matches)) {
                $function_args = Array($parsed_params, $matches);

                // Pass URL args, and then regex matched params
                call_user_func_array($route['function'], $function_args);

                break;
            }
        } 
    }

}

?>