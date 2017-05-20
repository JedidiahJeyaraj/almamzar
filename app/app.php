<?php

  require __DIR__ . '/../vendor/autoload.php' ;
  require __DIR__ .'/config.php';
  use Medoo\Medoo;

  /**
   * Creating a new slim instance
   * initializing pdo and settings
   */
   $app = new \Slim\App([
    'settings' =>[
      'displayErrorDetails' => true,
      //for database
      'pdo' => [
                    'engine' => 'mysql',
                    'host' => $host,
                    'database' => $db,
                    'username' => $username,
                    'password' => $pass,
                    'charset' => 'utf8',
                    'options' => [
                        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES   => true,
                      ]
                ]
    ]
  ]);

  /**
   * Creating a Session Middleware
   */
  $app->add(new \RKA\SessionMiddleware(['name' => 'reminder']));

  /**
   * Creating a Container instance
   */
  $container = $app->getContainer();

  /**
   * Creation a view container
   * Here view is linked with Twig
   * and the location of twig file is given here
   */
  $container['view'] = function($container){
    $view = new \Slim\Views\Twig(__DIR__ . '/../resources/views',[
      'cache' => false,
    ]);
    $view->addExtension(new \Slim\Views\TwigExtension(
      $container->router,
      $container->request->getUri()
    ));
    return $view;
  };


  /**
   * Creation of a database container
   * Here a db object is created
   * and db is configured
   */

  $container['db'] = function($container) {
    $config = $container->get('settings')['pdo'];
    $database = new medoo([
        'database_type' => 'mysql',
        'database_name' => $config['database'],
        'server' => $config['host'],
        'username' => $config['username'],
        'password' => $config['password'],
        'charset' => 'utf8'
    ]);
    return $database;
  };


  /**
   * This will be calling the routes which runs thw whole website
   */
  require __DIR__ . '/../app/routes.php';
