<?php
namespace AuthApi\Middleware;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

/**
 * 
 */
class Security
{
    /**
     * Check security and create a new response if not logged in
     * @param Request $request
     * @param Response $response
     * @param callable $next
     */
    public function __invoke(Request $request, Response $response, callable $next)
    {
        ob_start();
        session_start();

        // get body and signature
        $body = $request->getParsedBody();
        $signature = $body['signature'];
        $apikey = $body['key'];

        // our sample keys
        $secrets = [
            APIKEY => APISECRET
        ];

        // remove signature
        unset($body['signature']);
        $secretkey = isset($secrets[$apikey]) ? $secrets[$apikey] : null;

        // re-generate the hash
        $match = hash_hmac('sha384', json_encode($body), $secretkey);

        // do sender signature and re-generated hash match?
        $authorized = $match === $signature;
        if (!$authorized)
        {
            $json = [
                'success' => false, 
                'message' => 'You are not authorized to perform this action',
                'code' => 401
            ];
            return $response->withJson($json, 401);
        }
        
        return $next($request, $response);
    }
}