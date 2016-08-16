<?php

namespace AppBundle\Controller;

use BackendBundle\Entity\Comment;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class CommentController extends Controller
{
    public function newAction(Request $request)
    {
        $helpers = $this->get('app.helpers');

        $hash = $request->get('authorization');
        $authCheck = $helpers->authCheck($hash);

        if($authCheck) {
            $identity = $helpers->authCheck($hash, true);

            $json = $request->get('json');
            if ($json) {
                $params = json_decode($json);

                $createdAt = new \DateTime('now');
                $userId = (isset($identity->sub)) ? $identity->sub : null;
                $videoId = (isset($params->videoId)) ? $params->videoId : null;
                $body = (isset($params->body)) ? $params->body : null;

                if($userId && $videoId){
                    $em = $this->getDoctrine()->getManager();
                    $user = $em->getRepository('BackendBundle:User')->findOneBy(array(
                        'id' => $userId
                    ));
                    $video = $em->getRepository('BackendBundle:Video')->findOneBy(array(
                        'id' => $videoId
                    ));

                    $comment = new Comment();
                    $comment->setUser($user);
                    $comment->setVideo($video);
                    $comment->setBody($body);
                    $comment->setCreatedAt($createdAt);

                    $em->persist($comment);
                    $em->flush();

                    $data = array(
                        'status' => 'success',
                        'code' => 200,
                        'msg' => 'Comment created success'
                    );
                }else{
                    $data = array(
                        'status' => 'error',
                        'code' => 400,
                        'msg' => 'Comment not created'
                    );
                }
            }else{
                $data = array(
                    'status' => 'error',
                    'code' => 400,
                    'msg' => 'Params are not valid'
                );
            }
        }else{
            $data = array(
                'status' => 'error',
                'code' => 400,
                'msg' => 'Authorization not valid'
            );
        }

        return $helpers->parseJson($data);
    }
}
