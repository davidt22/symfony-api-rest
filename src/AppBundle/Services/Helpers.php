<?php
namespace AppBundle\Services;

use Monolog\Logger;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Symfony\Component\Serializer\Serializer;

class Helpers
{
    private $jwtAuth;
    /** @var Logger $logger */
    private $logger;

    /**
     * Helpers constructor.
     *
     * @param JWTAuth $JWTAuth
     * @param Logger $logger
     */
    public function __construct(JWTAuth $JWTAuth, Logger $logger)
    {
        $this->jwtAuth = $JWTAuth;
        $this->logger = $logger;
    }

    public function authCheck($hash, $getIdentity = false)
    {
        $auth = false;

        if($hash){
            if(!$getIdentity){
                $checkToken = $this->jwtAuth->checkToken($hash);
                if($checkToken){
                    $auth = true;
                }
            }else{
                $checkToken = $this->jwtAuth->checkToken($hash, true);
                if(is_object($checkToken)){
                    $auth = $checkToken;
                }
            }
        }

        return $auth;
    }

    public function parseJson($data)
    {
        $normalizers = array(new GetSetMethodNormalizer());
        $encoders = array('json' => new JsonEncoder());

        $serializer = new Serializer($normalizers, $encoders);
        $json = $serializer->serialize($data, 'json');

        $response = new Response();
        $response->setContent($json);
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }
}