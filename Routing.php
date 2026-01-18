<?php

require_once 'src/controllers/DashboardController.php';
require_once 'src/controllers/TransactionsController.php';
require_once 'src/controllers/ReportsController.php';
require_once 'src/controllers/MembersController.php';


class Routing{

    // W TYM PLIKU COS TAM DODAC ZAPYTAC SIE SZYMONA O CO CHODZILO
    public static $routes = [
        'dashboard' => [
            'controller' => "DashboardController",
            'action' => 'dashboard'
        ],
        'transactions' => [
            'controller' => "TransactionsController",
            'action' => 'transactions'
        ],
        'reports' => [
            'controller' => "ReportsController",
            'action' => 'reports'
        ],
        'members' => [
            'controller' => "MembersController",
            'action' => 'members'
        ]
    ];
    public static function run(string $path){
        switch($path) {
            case 'dashboard':
                $controller = new Routing::$routes[$path]['controller'];
                $action = Routing::$routes[$path]['action'];
                $controller->$action();
                break;
            case 'transactions':
                $controller = new Routing::$routes[$path]['controller'];
                $action = Routing::$routes[$path]['action'];
                $controller->$action();
                break;
            case 'reports':
                $controller = new Routing::$routes[$path]['controller'];
                $action = Routing::$routes[$path]['action'];
                $controller->$action();
                break;
            case 'members':
                $controller = new Routing::$routes[$path]['controller'];
                $action = Routing::$routes[$path]['action'];
                $controller->$action();
                break;
            default:
                include 'public/views/404.html';
                break;
        }
    }
}