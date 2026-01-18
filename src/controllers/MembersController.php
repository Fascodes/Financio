<?php

require_once 'src/controllers/AppController.php';


class MembersController extends AppController{

    public function members() {
    include 'public/views/members.html';
    }
}