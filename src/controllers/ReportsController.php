<?php

require_once 'src/controllers/AppController.php';


class ReportsController extends AppController{

    public function reports() {
    include 'public/views/reports.html';
    }
}