<?php

require_once 'src/controllers/AppController.php';


class DashboardController extends AppController{

    public function dashboard() {
    include 'public/views/dashboard.html';
    }
}