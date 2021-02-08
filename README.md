# Nacho

## About
This is my own little PHP Framework, slowly developed as I need functionality in my own projects.

## Installation
1. `composer require christiangroeber/nacho`
2. Copy `public/index.php` to your root directory

## First Route
1. Add a `routes.json` file under`/config` with the following content:
```
[
    {
        "route": "/",
        "controller": "App\\Controllers\\HomeController",
        "function": "index" 
    }
]
```
2. Create a file `HomeController.php` under `src/Controllers`, add the following Content:
```
<?php

namespace App\Controllers;

use Nacho\Controllers\AbstractControllers;

class HomeController extends AbstractController
{
    public function index($request)
    {
        return "hello world"; 
    }
}
```