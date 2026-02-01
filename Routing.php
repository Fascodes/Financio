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
        'api/budget-summary' => [
            'controller' => "DashboardController",
            'action' => 'getBudgetSummary'
        ],
        'transactions' => [
            'controller' => "TransactionsController",
            'action' => 'transactions'
        ],
        'api/transactions' => [
            'controller' => "TransactionsController",
            'action' => 'getTransactions'
        ],
        'api/categories' => [
            'controller' => "TransactionsController",
            'action' => 'getCategories'
        ],
        'api/group-users' => [
            'controller' => "TransactionsController",
            'action' => 'getGroupUsers'
        ],
        'api/transactions/add' => [
            'controller' => "TransactionsController",
            'action' => 'addTransaction'
        ],
        'api/transactions/get' => [
            'controller' => "TransactionsController",
            'action' => 'getTransaction'
        ],
        'api/transactions/update' => [
            'controller' => "TransactionsController",
            'action' => 'updateTransaction'
        ],
        'api/transactions/delete' => [
            'controller' => "TransactionsController",
            'action' => 'deleteTransaction'
        ],
        'reports' => [
            'controller' => "ReportsController",
            'action' => 'reports'
        ],
        'api/reports/summary' => [
            'controller' => "ReportsController",
            'action' => 'getSummaryStats'
        ],
        'api/reports/monthly-by-member' => [
            'controller' => "ReportsController",
            'action' => 'getMonthlyByMember'
        ],
        'api/reports/spending-trends' => [
            'controller' => "ReportsController",
            'action' => 'getSpendingTrends'
        ],
        'api/reports/category-distribution' => [
            'controller' => "ReportsController",
            'action' => 'getCategoryDistribution'
        ],
        'api/reports/member-contributions' => [
            'controller' => "ReportsController",
            'action' => 'getMemberContributions'
        ],
        'members' => [
            'controller' => "MembersController",
            'action' => 'members'
        ],
        'api/members/stats' => [
            'controller' => "MembersController",
            'action' => 'getMembersStats'
        ],
        'api/members/list' => [
            'controller' => "MembersController",
            'action' => 'getMembers'
        ],
        'api/members/update-role' => [
            'controller' => "MembersController",
            'action' => 'updateMemberRole'
        ],
        'api/members/remove' => [
            'controller' => "MembersController",
            'action' => 'removeMember'
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