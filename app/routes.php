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
    $imgs = $db->query("SELECT * FROM `portfolio`, `project` WHERE home_display = 1 and project.sno = portfolio.project_no LIMIT 8") ->fetchAll();

    return $this->view->render($response, 'index.twig', [
        "imgs" =>$imgs
    ]);
  })->setName('index_page');


    $app->get('/aboutus', function($request, $response, $args){
        return $this->view->render($response, 'about2.twig');
    })->setName('about_page');


    $app->get('/services', function($request, $response, $args){
        return $this->view->render($response, 'services.twig');
    })->setName('services');

    $app->get('/clients', function($request, $response, $args){
        return $this->view->render($response, 'clients.twig');
    })->setName('clients');

    $app->get('/contact', function($request, $response, $args){
        return $this->view->render($response, 'contact.twig');
    })->setName('contact');

    $app->post('/contact', function($request, $response, $args){
        $body = $response->getBody();
        $data = $request->getParsedBody();

        $name = $data['name'] or NULL;
        $phone = $data['phone'] or NULL;
        $email = $data['email'] or NULL;
        $datetimepicker = $data['datetimepicker'] or NULL;
        $comment = $data['comment'] or NULL;
      //   return $user;
        if($name == NULL or $phone == NULL or $datetimepicker == NULL or $email == NULL or $comment == NULL)
        {
            $body->write(json_encode(['status'=>'fail', 'message'=>'Empty values.']));
            return $response->withHeader('Content-Type', 'application/json')->withBody($body);
        }

        // $to = 'jedidiahjeyaraj@gmail.com';
        // $email_subject = "Website Contact From:  $name";
        // $email_body = "You have received a new appointment message from your website contact form.\n\n"."Here are the details:\n\nName: $name\n\nEmail: $email\n\nPhone: $phone\n\nPreffered Date & Time: $datetimepicker\n\nMessage:\n$comment";
        // $headers = "From: no-reply@Almamzar.com\n";
        // $headers .= "Reply-To: $email_address";
        // mail($to,$email_subject,$email_body,$headers);

          $body->write(json_encode(['status'=>'success','message'=>'Our Expert will contact you soon']));
          return $response->withHeader('Content-Type', 'application/json')->withBody($body);

        // return $this->view->render($response, 'contact.twig');
    })->setName('contact_api');


    $app->get('/gallery', function($request, $response, $args){
        $db = $this->db;
    $imgs = $db->query("SELECT * FROM `portfolio`, `project` WHERE portfolio.project_no = project.sno GROUP BY project_no")->fetchAll();
    var_dump($imgs);
        return $this->view->render($response, 'gallery3.twig', [
            "imgs"=>$imgs
        ]);
    })->setName('gallery');


    $app->get('/gallery/{id}',function($request, $response, $args){
        $db = $this->db;
        $id = $args['id'];
        $imgs = $db->query("SELECT * FROM `portfolio`, `project` WHERE portfolio.project_no = project.sno and portfolio.project_no = $id")->fetchAll();
        // var_dump($imgs);
        return $this->view->render($response, 'portfolio.twig',[
            "imgs"=>$imgs
        ]);
    })->setName('ind_gal');

  /**
   * for loading the login page
   */
   $app->get('/logininto', function($request, $response){
     //   $db = $this->db;
     //   $router = $this->router;
     //   $result = $db->select('login','*');
     //   var_dump($result[0]['password']);
       return CRYPT::encrypt_decrypt('decrypt', 'c1RiYnlWTXNwUW5ETDcremF2clN0dz09');
   })->setName('login');







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
                "home_display" => 1
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
        $projects = $db->select('project','*',[
                    "ORDER" => ['sno' => "DESC"],
                    "LIMIT" => 1
                ]
        );
        $cnt = $projects[0]['sno'];
        // $id = $db-sno();
        $filepath = __DIR__ . "/../images/".$cnt;
        mkdir($filepath , 0777);
        $body->write(json_encode(['status'=>'success','message'=>'Project Created Successfully']));
        return $response->withHeader('Content-Type', 'application/json')->withBody($body);
        // return $response->withHeader('Content-Type', 'application/json')->withAddedHeader('Location', '/')->withBody($body);

  })->setName('createpro_api');

  $app->get('/view/{sno}/{erno}', function($request, $response, $args){
      $session = new RKA\Session();
      $db = $this->db;
      if(!$session->get('active', false)){
          return $response->withRedirect($this->router->pathFor('login'));
      }
      $sno = $args['sno'];
      $err = $args['erno'];
      $sno = base64_decode($sno);
      $project = $db->select('project','*',[
          'AND'=>[
              "sno"=>$sno
          ]
      ]);
      $imgs = $db->select('portfolio','*',[
          'AND'=>[
              "project_no"=>$sno
          ]
      ]);
    //   var_dump($imgs);
      return $this->view->render($response, 'admin/proview.twig',[
          "project"=>$project,
          "images"=>$imgs,
          "err" => $err
      ]);
  })->setName('ind_pro_view');


  $app->get('/mainpageimages/{err}', function($request, $response, $args){
      $session = new RKA\Session();
      $db = $this->db;
      $err = $args['err'];
      if(!$session->get('active', false)){
          return $response->withRedirect($this->router->pathFor('login'));
      }
      $project = $db->select('project','*');
      $imgs = $db->select('portfolio','*',[
          "AND" =>[
              "home_display" => 1
          ]
      ]);
    //   var_dump($imgs);
      return $this->view->render($response, 'admin/mainpageimg.twig',[
          "project"=>$project,
          "images"=>$imgs,
          "err" => $err
      ]);
  })->setName('mainpageimages');

  $app->post('/mainpageimages', function($request, $response, $args){
      $session = new RKA\Session();
      $db = $this->db;
      if(!$session->get('active', false)){
          return $response->withRedirect($this->router->pathFor('login'));
      }
      $Post = $request->getParsedBody();
      $pro_sno = $Post['pro_sno'];
      $view_list = $Post['view_list'] or NULL;
      if($view_list == NULL)
      {
          return $response->withRedirect($this->router->pathFor('mainpageimages', ['err'=>'1']));
      }
      $disp = $db->select('portfolio', '*',[
          "AND" => [
              "home_display" => 1
          ]
      ]);
      $dispcnt = count($disp);
      if($dispcnt <= 3 )
      {
         return $response->withRedirect($this->router->pathFor('mainpageimages', ['err'=>'2']));
      }
      foreach ($view_list as $view) {
          var_dump($view);
          $db->update('portfolio',[
              "home_display" => 0
          ],[
              "sno" => $view
          ]);
      }
      return $response->withRedirect($this->router->pathFor('mainpageimages', ['err'=>'3']));
  })->setName('homeimgdel_api');

 $app->post('/homeimgview', function($request, $response, $args){
     $session = new RKA\Session();
     $db = $this->db;
     if(!$session->get('active', false)){
         return $response->withRedirect($this->router->pathFor('login'));
     }
     $Post = $request->getParsedBody();
     $pro_sno = $Post['pro_sno'];
     $view_list = $Post['view_list'] or NULL;
     if($view_list == NULL)
     {
         return $response->withRedirect($this->router->pathFor('ind_pro_view', ['sno'=>base64_encode($pro_sno), 'erno'=>'1']));
     }
     $disp = $db->select('portfolio', '*',[
         "AND" => [
             "home_display" => 1
         ]
     ]);
     $dispcnt = count($disp);
     if($dispcnt > 8 )
     {
        return $response->withRedirect($this->router->pathFor('ind_pro_view', ['sno'=>base64_encode($pro_sno), 'erno'=>'2']));
     }
     foreach ($view_list as $view) {
         var_dump($view);
         $db->update('portfolio',[
             "home_display" => 1
         ],[
             "sno" => $view
         ]);
     }
     return $response->withRedirect($this->router->pathFor('ind_pro_view', ['sno'=>base64_encode($pro_sno), 'erno'=>'3']));
 })->setName('homeimgview_api');

  $app->get('/add/{sno}/{erno}', function($request, $response, $args){
      $session = new RKA\Session();
      $db = $this->db;
      if(!$session->get('active', false)){
          return $response->withRedirect($this->router->pathFor('login'));
      }
      $sno = $args['sno'];
      $erno = $args['erno'];
      $sno = base64_decode($sno);
      $project = $db->select('project','*',[
          'AND'=>[
              "sno"=>$sno
          ]
      ]);
      $imgs = $db->select('portfolio','*',[
          'AND'=>[
              "project_no"=>$sno
          ]
      ]);
    //   var_dump($imgs);
      return $this->view->render($response, 'admin/addimg.twig', [
          "project"=>$project,
          "err"=>$erno
      ]);
  })->setName('add_img');

  $app->post('/add', function($request, $response, $args){
      $session = new RKA\Session();
      $db = $this->db;
      if(!$session->get('active', false)){
          return $response->withRedirect($this->router->pathFor('login'));
      }
      $Post = $request->getParsedBody();
      $pro_sno = $Post['pro_sno'];
      $image = $_FILES['pro_img']['tmp_name'];
      $image_name = $_FILES['pro_img']['name'];
      $ext = pathinfo($image_name, PATHINFO_EXTENSION);
      $size = filesize($image);
      $size = intval($size/1024);
    //   var_dump($ext);
      if($ext == "jpg" or $ext == "png" or $ext == "jpeg" or $ext="JPG")
      {
          if($size<8000)
          {
              $filepath = __DIR__ . "/../images/".$pro_sno."/".$image_name;
              move_uploaded_file($image, $filepath);
              $db->insert('portfolio',[
                  "project_no"=>$pro_sno,
                  "img_name" => $image_name,
                  "img_type" =>$ext,
                  "img_path" => $filepath
              ]);
              return $response->withRedirect($this->router->pathFor('add_img', ['sno'=>base64_encode($pro_sno), 'erno'=>'3']));
          }
          else{
              return $response->withRedirect($this->router->pathFor('add_img', ['sno'=>base64_encode($pro_sno), 'erno'=>'2']));
          }

      }
      else{
          return $response->withRedirect($this->router->pathFor('add_img', ['sno'=>base64_encode($pro_sno), 'erno'=>'1']));
      }

  })->setName('add_img_api');


  $app->get('/delete/{sno}', function($request, $response, $args){
      $session = new RKA\Session();
      $db = $this->db;
      if(!$session->get('active', false)){
          return $response->withRedirect($this->router->pathFor('login'));
      }
      $sno = $args['sno'];
      $sno = base64_decode($sno);
      $db->delete('project',[
          'AND'=>[
              "sno"=>$sno
          ]
      ]);
      $db->delete('portfolio',[
          'AND' =>[
              "project_no"=>$sno
          ]
      ]);
    //   var_dump($imgs
      return $response->withRedirect($this->router->pathFor('view_project'));
  })->setName('add_img');


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
