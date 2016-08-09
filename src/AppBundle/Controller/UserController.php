<?php

namespace AppBundle\Controller;

use BackendBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints\Email;

class UserController extends Controller
{
    public function newAction(Request $request)
    {
        $helpersService = $this->get('app.helpers');

        $json = $request->get('json');//Por POST
        $params = json_decode($json);

        $data = array(
            'status' => 'error',
            'code' => 400,
            'msg' => 'User not created'
        );

        if($json){

            $createdAt = new \DateTime('now');
            $image = null;
            $role = 'user';
            $email = isset($params->email) ? $params->email : null;
            $name = isset($params->name) ? ctype_alpha($params->name) : null;
            $surname = isset($params->surname) ? ctype_alpha($params->surname) : null;
            $password = isset($params->password) ? $params->password : null;

            $emailContraint = new Email();
            $emailContraint->message = 'Email not valid';
            $validateEmail = $this->get('validator')->validate($email, $emailContraint);

            if($email && count($validateEmail) == 0 && $password && $name && $surname){
                $user = new User();
                $user->setImage($createdAt);
                $user->setImage($image);
                $user->setRole($role);
                $user->setEmail($email);
                $user->setName($name);
                $user->setSurname($surname);

                $pwd = hash('sha256', $password);
                $user->setPassword($pwd);

                $em = $this->getDoctrine()->getManager();
                $issetUser = $em->getRepository('BackendBundle:User')->findBy(array('email' => $email));

                if(count($issetUser) == 0){
                    $em->persist($user);
                    $em->flush();

                    $data = array(
                        'status' => 'success',
                        'code' => 200,
                        'msg' => 'New User created'
                    );
                }else{
                    $data = array(
                        'status' => 'error',
                        'code' => 400,
                        'msg' => 'User not created, duplicated'
                    );
                }
            }
        }

        return $helpersService->parseJson($data);
    }

    public function editAction(Request $request)
    {
        $helpersService = $this->get('app.helpers');

        $hash = $request->get('authorization');
        $authCheck = $helpersService->authCheck($hash);

        if($authCheck){

            $identity = $helpersService->authCheck($hash, true);

            $em = $this->getDoctrine()->getManager();
            $user = $em->getRepository('BackendBundle:User')->findOneBy(array(
                'id' => $identity->sub
            ));

            $json = $request->get('json');//Por POST
            $params = json_decode($json);

            $data = array(
                'status' => 'error',
                'code' => 400,
                'msg' => 'User not updated'
            );

            if($json) {

                $createdAt = new \DateTime('now');
                $image = null;
                $role = 'user';
                $email = isset($params->email) ? $params->email : null;
                $name = isset($params->name) ? $params->name : null;
                $surname = isset($params->surname) ? $params->surname : null;
                $password = isset($params->password) ? $params->password : null;

                $emailContraint = new Email();
                $emailContraint->message = 'Email not valid';
                $validateEmail = $this->get('validator')->validate($email, $emailContraint);

                if ($email && count($validateEmail) == 0 && $name && $surname) {
//                    $user = new User();
                    $user->setImage($createdAt);
                    $user->setImage($image);
                    $user->setRole($role);
                    $user->setEmail($email);
                    $user->setName($name);
                    $user->setSurname($surname);

                    if($password != null){
                        $pwd = hash('sha256', $password);
                        $user->setPassword($pwd);
                    }

                    $em = $this->getDoctrine()->getManager();
                    $issetUser = $em->getRepository('BackendBundle:User')->findBy(array('email' => $email));

                    if (count($issetUser) == 0 || $identity->email == $email) {
                        $em->persist($user);
                        $em->flush();

                        $data = array(
                            'status' => 'success',
                            'code' => 200,
                            'msg' => 'User updated'
                        );
                    } else {
                        $data = array(
                            'status' => 'error',
                            'code' => 400,
                            'msg' => 'User not updated'
                        );
                    }
                }
            }else{
                $data = array(
                    'status' => 'error',
                    'code' => 400,
                    'msg' => 'Authorization not valid'
                );
            }
        }

        return $helpersService->parseJson($data);
    }
}
