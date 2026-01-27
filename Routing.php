<?php

require_once 'src/controllers/DashboardController.php';
require_once 'src/controllers/TransactionsController.php';
require_once 'src/controllers/ReportsController.php';
require_once 'src/controllers/MembersController.php';
require_once 'src/controllers/LoginController.php';


class Routing{

    public static $routes = [
        'login' => [
            'controller' => "LoginController",
            'action' => 'showLogin'
        ],
        'api/login' => [
            'controller' => "LoginController",
            'action' => 'login'
        ],
        'api/register' => [
            'controller' => "LoginController",
            'action' => 'register'
        ],
        'api/logout' => [
            'controller' => "LoginController",
            'action' => 'logout'
        ],
        'api/current-user' => [
            'controller' => "LoginController",
            'action' => 'getCurrentUser'
        ],
        'dashboard' => [
            'controller' => "DashboardController",
            'action' => 'dashboard'
        ],
        'api/dashboard-data' => [
            'controller' => "DashboardController",
            'action' => 'getChartData'
        ],
        'api/category-data' => [
            'controller' => "DashboardController",
            'action' => 'getCategoryData'
        ],
        'api/recent-transactions' => [
            'controller' => "DashboardController",
            'action' => 'getRecentTransactions'
        ],
        'api/group-members' => [
            'controller' => "DashboardController",
            'action' => 'getGroupMembers'
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
        if (isset(self::$routes[$path])) {
            $controller = new self::$routes[$path]['controller'];
            $action = self::$routes[$path]['action'];
            $controller->$action();
        } else {
            include 'public/views/404.html';
        }
    }
}