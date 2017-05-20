<?php

    include_once 'model/login.php';

  $app->get('/', function($request, $response, $args){
    // return "hello";
    return $this->view->render($response, 'index.twig');
  })->setName('index_page');

  /**
   * for loading the login page
   */
  $app->get('/login', function($request, $response){
      $db = $this->db;
      $router = $this->router;
      $result = $db->select('login','*');
      var_dump($result);
    //   return LOGIN::encrypt_decrypt('encrypt', 'jedi');
  })->setName('login');







  /*****************************************************************************************************************************************************************************************************************************************************************************************
  ::::::::::::::::::::::::::::::::::::::::      ADMIN CONTROLS      :::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
  *****************************************************************************************************************************************************************************************************************************************************************************************/
  $app->get('/admin_dashboard', function($request, $response, $args){
    // return "hello";
    return $this->view->render($response, 'admin/index.twig');
  })->setName('index_page');
