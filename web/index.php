<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Log\LoggerInterface;

use DI\Container;
use DI\Bridge\Slim\Bridge;
use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\Logger;
use Slim\Factory\AppFactory;
use Slim\Views\Twig;

require(__DIR__.'/../vendor/autoload.php');

// Create DI container
$container = new Container();
// Add Twig to Container
$container->set(Twig::class, function() {
  return Twig::create(__DIR__.'/../views');
});
// Add Monolog to Container
$container->set(LoggerInterface::class, function () {
  $logger = new Logger('default');
  $logger->pushHandler(new StreamHandler('php://stderr'), Level::Debug);
  return $logger;
});

// Create main Slim app
$app = Bridge::create($container);
$app->addErrorMiddleware(true, false, false);

// Our web handlers
$app->get('/', function(Request $request, Response $response, LoggerInterface $logger, Twig $twig) {
  $logger->debug('logging output.');
  return $twig->render($response, 'index.twig');
});

// Figure out how to consolidate the following four routes.
$app->get('/html', function(Request $request, Response $response, LoggerInterface $logger, Twig $twig) {
  $logger->debug('logging output from /html route.');
  return $twig->render($response, 'instructions.twig');
});

$app->get('/html/', function(Request $request, Response $response, LoggerInterface $logger, Twig $twig) {
  $logger->debug('logging output from /html/ route.');
  return $twig->render($response, 'instructions.twig');
});

$app->get('/json', function(Request $request, Response $response, LoggerInterface $logger, Twig $twig) {
  $logger->debug('logging output from /json route.');
  return $twig->render($response, 'instructions.twig');
});

$app->get('/json/', function(Request $request, Response $response, LoggerInterface $logger, Twig $twig) {
  $logger->debug('logging output from /json/ route.');
  return $twig->render($response, 'instructions.twig');
});

$app->get('/html/{data}', function(string $data, Request $request, Response $response, LoggerInterface $logger, Twig $twig) {
  $data = ['data' => $data];
  $logger->debug('logging output from /html/{data} route');
  return $twig->render($response, 'html.twig', $data);
});

$app->get('/json/{data}', function(string $data, Request $request, Response $response, LoggerInterface $logger) {
  $response->getBody()->write(json_encode(['data' => $data]));
  $response = $response->withHeader('Content-Type', 'application/json');
  $logger->debug('logging output from /json/{data} route');
  return $response;
});

$app->run();
