<?php

require_once 'src/controllers/AppController.php';


class TransactionsController extends AppController{

    public function transactions() {
    include 'public/views/transactions.html';
    }
}