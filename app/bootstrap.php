<?php

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

define('APIKEY', '6b4713c9-0f20-48e7-9195-4945e919b0ca');
define('APISECRET', '>5YFtyik?WVKBf)y#6cAu2<SJ&OW,Z');

/**
 * Autoload classes
 */
require_once(__DIR__ . "/../vendor/autoload.php");

/**
 * Get slim DI container
 */
$container = new Slim\Container([
    'settings' => [
        'displayErrorDetails' => true,
    ],
]);

/**
 * Create new slim application
 */
$app = new Slim\App($container);

/**
 * Routes
 */

$app->get('/public/get', 
function (Request $request, Response $response)
{
    // access API with get
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, "http://playground.test/authapi/api/public");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1) ;
    curl_setopt($ch, CURLOPT_HEADER, 0);

    $resp = curl_exec($ch);
    $info = curl_getinfo($ch);
    $error = curl_error($ch);

    $json = json_decode($resp, true);

    curl_close($ch);
    return $response->withJson( $json );
});

$app->get('/private/post', 
function (Request $request, Response $response)
{
    // access API with get
    $ch = curl_init();

    $params = [
        'key' => APIKEY,
        'important' => 'data'
    ];

    // create signature
    $signature = hash_hmac('sha384', json_encode($params), APISECRET);
    $params['signature'] = $signature;

    curl_setopt($ch, CURLOPT_URL, "http://playground.test/authapi/api/private/post");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1) ;
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $params);

    $resp = curl_exec($ch);
    $info = curl_getinfo($ch);
    $error = curl_error($ch);

    print_r($resp);

    $json = json_decode($resp, true);

    curl_close($ch);
    //return $response->withJson( $json );
    return $response->write( "" );
});

// public get endpoint
$app->get('/api/public', 
function (Request $request, Response $response)
{
    $json = [ 'success' => true, 'code' => $response->getStatusCode() ];
    return $response->withJson( $json );
});

// private get endpoint
$app->get('/api/private', 
function (Request $request, Response $response)
{
    $json = [ 'success' => true, 'code' => $response->getStatusCode() ];
    return $response->withJson( $json );
})->add( new \AuthApi\Middleware\Security() );

$app->post('/api/private/post', 
function (Request $request, Response $response)
{
    $json = [ 'success' => true, 'code' => $response->getStatusCode() ];
    return $response->withJson( $json );
})->add( new \AuthApi\Middleware\Security() );

/**
 * Run slim
 */
$app->run();