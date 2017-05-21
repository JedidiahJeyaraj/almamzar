<?php

    include_once 'model/crypt.php';

  $app->get('/', function($request, $response, $args){
    // return "hello";
    $db = $this->db;
    $cnt = $db->select('functional','*',[
        'AND'=>[
            "sno"=>1
        ]
    ]);
    $cnt = $cnt[0]['count'] + 1;
    $db->update('functional',[
        "count"=> $cnt
    ],[
        "sno"=>1
    ]);
    return $this->view->render($response, 'index.twig');
  })->setName('index_page');

  /**
   * for loading the login page
   */
  // $app->get('/logininto', function($request, $response){
  //   //   $db = $this->db;
  //   //   $router = $this->router;
  //   //   $result = $db->select('login','*');
  //   //   var_dump($result[0]['password']);
  //     return CRYPT::encrypt_decrypt('encrypt', 'ashish');
  // })->setName('login');







  /*****************************************************************************************************************************************************************************************************************************************************************************************
  ::::::::::::::::::::::::::::::::::::::::      ADMIN CONTROLS      :::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
  *****************************************************************************************************************************************************************************************************************************************************************************************/
  $app->get('/admin', function($request, $response, $args){
    // return "hello";
    $session = new RKA\Session();

    // If user not logged in redirect to Login.
    if(!$session->get('active', false)){
        return $response->withRedirect($this->router->pathFor('login'));
    }
    $db = $this->db;

    $projects = $db->select('project', '*');
    $procount = count($projects);
    $galimg = $db->select('portfolio', '*',[
            "AND" => [
                "gal_disp" => 1
            ]
        ]);
    $galimgcount = count($galimg);
    $pagehits = $db->select('functional','*',[
        'AND'=>[
            "sno"=>1
        ]
    ]);
    $pagehits = $pagehits[0]['count'];
    // var_dump($project);


    return $this->view->render($response, 'admin/index.twig', [
        "projects"=>$projects,
        "pro_count"=>$procount,
        "gal_count"=>$galimgcount,
        "pagehits"=>$pagehits
    ]);
})->setName('admin_dashboard');




$app->get('/projects', function($request, $response, $args){
  // return "hello";
  $session = new RKA\Session();

  // If user not logged in redirect to Login.
  if(!$session->get('active', false)){
      return $response->withRedirect($this->router->pathFor('login'));
  }
  $db = $this->db;

  $projects = $db->select('project', '*');
  return $this->view->render($response, 'admin/viewpro.twig', [
      "projects"=>$projects,
  ]);

  })->setName('view_project');

  $app->post('/createpro', function($request, $response){
      $session = new RKA\Session();
      $db = $this->db;
      $body = $response->getBody();
      $data = $request->getParsedBody();

      if(!$session->get('active', false)){
          return $response->withRedirect($this->router->pathFor('login'));
      }

      $pro_name = $data['pro_name'] or NULL;
    //   return $user;
      if($pro_name == NULL)
      {
          $body->write(json_encode(['status'=>'fail', 'message'=>'Empty values.']));
          return $response->withHeader('Content-Type', 'application/json')->withBody($body);
      }

      $projects = $db->select('project','*', [
              'AND'=>[
                  "name" => strtoupper($pro_name)
              ]
          ]
      );

      if(count($projects)>0)
        {
            $body->write(json_encode(['status'=>'fail', 'message'=>"Project Name Already exists."]));
            return $response->withHeader('Content-Type', 'application/json')->withBody($body);
        }

        $db->insert('project',[
            "name" => strtoupper($pro_name)
        ]);

        $body->write(json_encode(['status'=>'success','message'=>'Project Created Successfully']));
        return $response->withHeader('Content-Type', 'application/json')->withBody($body);
        // return $response->withHeader('Content-Type', 'application/json')->withAddedHeader('Location', '/')->withBody($body);

  })->setName('createpro_api');

/******************************************************************************************************************************************
-----------------------------------------------Login API----------------------------------------------------------------------------------
******************************************************************************************************************************************/
    $app->get('/login', function($request, $response){
       return $this->view->render($response, 'admin/login.twig');
    })->setName('login');

  $app->post('/login', function($request, $response){
      $session = new RKA\Session();
      $db = $this->db;
      $body = $response->getBody();
      $data = $request->getParsedBody();

      $user = $data['user'] or NULL;
      $pass = $data['pass'] or NULL;
    //   return $user;
      if($user == NULL or $pass == NULL)
      {
          $body->write(json_encode(['status'=>'fail', 'message'=>'Empty values.']));
          return $response->withHeader('Content-Type', 'application/json')->withBody($body);
      }

      $users = $db->select('login','*', [
              'AND'=>[
                  "username" => strtoupper($user),
                  "password" => CRYPT::encrypt_decrypt("encrypt", $pass)
              ]
          ]
      );

      if(!count($users))
        {
            $body->write(json_encode(['status'=>'fail', 'message'=>"Username/Password doesn't exists."]));
            return $response->withHeader('Content-Type', 'application/json')->withBody($body);
        }

        $session->set('active', true);
        $session->set('user',$users[0]['username']);

        $body->write(json_encode(['status'=>'success','message'=>'Login Successful']));
        return $response->withHeader('Content-Type', 'application/json')->withBody($body);
        // return $response->withHeader('Content-Type', 'application/json')->withAddedHeader('Location', '/')->withBody($body);

  })->setName('login_api');



  $app->get('/logout', function($request, $response){

    RKA\Session::destroy();
    return $response->withRedirect($this->router->pathFor('login'));
})->setName('page_logout');

/**********************************************************************************************************************************************
-----------------------------------------------Login API---------------------------------------------------------------------------------------
**********************************************************************************************************************************************/


/**********************************************************************************************************************************************
-----------------------------------------------Forgot API---------------------------------------------------------------------------------------
**********************************************************************************************************************************************/
$app->get('/forgot', function($request, $response){
   return $this->view->render($response, 'admin/forgot.twig');
})->setName('forgot');

$app->post('/forgot', function($request, $response){
    $db = $this->db;
    $body = $response->getBody();
    $data = $request->getParsedBody();
    $user = $data['user'] or NULL;
    $email = $data['email'] or NULL;
    if($user == NULL or $email == NULL)
    {
        $body->write(json_encode(['status'=>'fail', 'message'=>'Empty values.']));
        return $response->withHeader('Content-Type', 'application/json')->withBody($body);
    }
    $users = $db->select('login','*', [
            'AND'=>[
                "username" => strtoupper($user),
                "email" => $email
            ]
        ]
    );
    if(!count($users))
      {
        //   var_dump($email);
          $body->write(json_encode(['status'=>'fail', 'message'=>"Username/Email doesn't exists."]));
          return $response->withHeader('Content-Type', 'application/json')->withBody($body);
      }
      $password = CRYPT::encrypt_decrypt("decrypt", $users[0]['password']);
      $subject = "PASSWORD RECOVERY";
      $content = "Your Account Details are:\nUSERNAME:\t$user\nPASSWORD:\t$password\n\nYou can change your password in your Admin Dashboard";
      $info = "From: no-reply@Almamzar.com";
      $email =filter_var($email, FILTER_SANITIZE_EMAIL);
      $email = filter_var($email, FILTER_VALIDATE_EMAIL);
      mail($email, $subject, $content, $info);
      $body->write(json_encode(['status'=>'success','message'=>'Password Sent To Registered Email']));
      return $response->withHeader('Content-Type', 'application/json')->withBody($body);

})->setName('forgot_api');
