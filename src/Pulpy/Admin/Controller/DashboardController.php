<?php

namespace Pulpy\Admin\Controller;

use Silex\Application,
    Symfony\Component\HttpFoundation\Request,
    Symfony\Component\HttpFoundation\Response;

class DashboardController {

    public function __construct() {
    }

    public function indexAction(Request $request, Application $app) {
        return 'Hello, World !<h2>Dashboard !</h2>';
    }
}