<?php


namespace AppBundle\Services;

use Doctrine\ORM\EntityManager;
use Firebase\JWT\JWT;

class JWTAuth
{
    private $manager;

    private $key;


    /**
     * JWTAuth constructor.
     *
     * @param EntityManager $manager
     */
    public function __construct(EntityManager $manager)
    {
        $this->manager = $manager;
        $this->key = 'clave-secreta';
    }

    public function signup($email, $password, $getHash = null)
    {
        $user = $this->manager->getRepository('BackendBundle:User')->findOneBy(array(
            'email' => $email,
            'password' => $password
        ));

        $signup = false;
        if(is_object($user)){
            $signup = true;
        }

        if($signup){

            $token = array(
                'sub' => $user->getId(),
                'email' => $user->getEmail(),
                'name' => $user->getName(),
                'surname' => $user->getSurname(),
                'password' => $user->getPassword(),
                'image' => $user->getImage(),
                'iat' => time(),
                'exp' => time() + (7 * 24 * 60 * 60)
            );

            $jwt = JWT::encode($token, $this->key);
            $decoded = JWT::decode($jwt, $this->key, array('HS256'));

            if($getHash){
                return $jwt;//hash cifrado
            }else{
                return $decoded;
            }
        }else{
            return array('status' => 'error', 'data' => 'Login failed');
        }
    }

    public function checkToken($jwt, $getIdentity = false)
    {
        $auth = false;

        try{
            $decoded = JWT::decode($jwt, $this->key, array('HS256'));
        }catch(\UnexpectedValueException $e){
            $auth = false;
        }catch(\DomainException $e){
            $auth = false;
        }

        if(isset($decoded->sub)){
            $auth = true;
        }else{
            $auth = false;
        }

        if($getIdentity){
            return $decoded;
        }else{
            return $auth;
        }
    }
}