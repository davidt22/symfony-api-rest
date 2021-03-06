<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints\Email;

class DefaultController extends Controller
{
    /**
     * @param Request $request
     *
     * @return Response
     */
    public function indexAction(Request $request)
    {
        // replace this example code with whatever you need
        return $this->render('default/index.html.twig', [
            'base_dir' => realpath($this->getParameter('kernel.root_dir').'/..'),
        ]);
    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function pruebasAction(Request $request)
    {
        $helpersService = $this->get('app.helpers');

        $hash = $request->get('authorization');
        $check = $helpersService->authCheck($hash);

        var_dump($check);
//        $em = $this->getDoctrine()->getManager();
//        $users = $em->getRepository('BackendBundle:User')->findAll();
//        return $helpersService->parseJson($users);
    }

    public function loginAction(Request $request)
    {
        $helpersService = $this->get('app.helpers');
        $jwatAuthService = $this->get('app.jwt_auth');
        $json = $request->get('json');

        if($json){
            $params = json_decode($json);

            $email = isset($params->email) ? $params->email : null;
            $password = isset($params->password) ? $params->password : null;
            $getHash = isset($params->getHash) ? $params->getHash : null;

            $emailContraint = new Email();
            $emailContraint->message = 'Email not valid';

            $validateEmail = $this->get('validator')->validate($email, $emailContraint);

            $pwd = hash('sha256', $password);

            if(count($validateEmail) == 0 && $password != null){

                if($getHash == null || $getHash == 'false'){
                    $signup = $jwatAuthService->signup($email, $pwd);
                }else{
                    $signup = $jwatAuthService->signup($email, $pwd, true);
                }

                return new JsonResponse($signup);
            }else{
                return $helpersService->parseJson(array('status' => 'error', 'data' => 'Login not valid'));
            }
        }else{
            return $helpersService->parseJson(array('status' => 'error', 'data' => 'Send Json with POST'));
        }

    }

}
