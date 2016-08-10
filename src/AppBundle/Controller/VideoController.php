<?php

namespace AppBundle\Controller;

use BackendBundle\Entity\Video;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class VideoController extends Controller
{
    public function newAction(Request $request)
    {
        $helpers = $this->get('app.helpers');

        $hash = $request->get('authorization');
        $authCheck = $helpers->authCheck($hash);

        if($authCheck){
            $identity = $helpers->authCheck($hash, true);

            $json = $request->get('json');
            if($json){
                $params = json_decode($json);
                $createdAt = new \DateTime('now');
                $updatedAt = new \DateTime('now');
                $image = null;
                $videoPath = null;

                $userId = ($identity->sub != null) ? $identity->sub : null;
                $title = isset($params->title) ? $params->title : null;
                $description = isset($params->description) ? $params->description : null;
                $status = isset($params->status) ? $params->status : null;

                if($userId != null && $title != null) {
                    $em = $this->getDoctrine()->getManager();
                    $user = $em->getRepository('BackendBundle:User')->findOneBy(array(
                        'id' => $userId
                    ));

                    $video = new Video();
                    $video->setUser($user);
                    $video->setTitle($title);
                    $video->setDescription($description);
                    $video->setStatus($status);
                    $video->setCreatedAt($createdAt);
                    $video->setUpdatedAt($updatedAt);

                    $em->persist($video);
                    $em->flush();

                    $video = $em->getRepository('BackendBundle:Video')->findOneBy(array(
                        'user' => $user,
                        'title' => $title,
                        'status' => $status,
                        'createdAt' => $createdAt
                    ));

                    $data = array(
                        'status' => 'success',
                        'code' => 200,
                        'data' => $video
                    );

                }else{
                    $data = array(
                        'status' => 'error',
                        'code' => 400,
                        'msg' => 'Video not created'
                    );
                }
            }else{
                $data = array(
                    'status' => 'error',
                    'code' => 400,
                    'msg' => 'Video not created, params failed'
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

    /**
     * @param Request $request
     * @param null $id
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function editAction(Request $request, $id = null)
    {
        $helpers = $this->get('app.helpers');

        $hash = $request->get('authorization');
        $authCheck = $helpers->authCheck($hash);

        if($authCheck){
            $identity = $helpers->authCheck($hash, true);

            $json = $request->get('json');
            if($json){
                $params = json_decode($json);
                $updatedAt = new \DateTime('now');
                $image = null;
                $videoPath = null;

                $userId = ($identity->sub != null) ? $identity->sub : null;
                $title = isset($params->title) ? $params->title : null;
                $description = isset($params->description) ? $params->description : null;
                $status = isset($params->status) ? $params->status : null;

                if($userId != null && $title != null) {
                    $em = $this->getDoctrine()->getManager();

                    $videoId = $id;
                    $video = $em->getRepository('BackendBundle:Video')->findOneBy(array(
                        'id' => $videoId
                    ));

                    if(isset($identity->sub) && $identity->sub == $video->getUser()->getId()){
                        $video->setTitle($title);
                        $video->setDescription($description);
                        $video->setStatus($status);
                        $video->setUpdatedAt($updatedAt);

                        $em->persist($video);
                        $em->flush();

                        $data = array(
                            'status' => 'success',
                            'code' => 200,
                            'msg' => 'Video updated success'
                        );
                    }else{
                        $data = array(
                            'status' => 'error',
                            'code' => 400,
                            'msg' => 'Video updated error, you not owner'
                        );
                    }


                }else{
                    $data = array(
                        'status' => 'error',
                        'code' => 400,
                        'msg' => 'Video updated error'
                    );
                }
            }else{
                $data = array(
                    'status' => 'error',
                    'code' => 400,
                    'msg' => 'Video not updated, params failed'
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

    public function uploadAction(Request $request, $id)
    {
        $helpers = $this->get('app.helpers');

        $hash = $request->get('authorization');
        $authCheck = $helpers->authCheck($hash);

        if($authCheck) {
            $identity = $helpers->authCheck($hash, true);
            $json = $request->get('json');

            $videoId = $id;
            $em = $this->getDoctrine()->getManager();
            $video = $em->getRepository('BackendBundle:Video')->findOneBy(array(
                'id' => $videoId
            ));

            if($videoId && isset($identity->sub) && $identity->sub == $video->getUser()->getId()){
                $file = $request->files->get('image');
                $fileVideo = $request->files->get('video');

                if($file && !empty($file)){
                    $ext = $file->guessExtension();

                    if(in_array($ext, array('jpeg', 'jpg', 'png'))) {
                        $fileName = time() . '.' . $ext;
                        $pathOfFile = 'uploads/video_images/video_' . $videoId;
                        $file->move($pathOfFile, $fileName);

                        $video->setImage($fileName);

                        $em->persist($video);
                        $em->flush();

                        $data = array(
                            'status' => 'success',
                            'code' => 200,
                            'msg' => 'Image file for video uploaded'
                        );
                    }else{
                        $data = array(
                            'status' => 'error',
                            'code' => 400,
                            'msg' => 'Format image not valid'
                        );
                    }
                }else{
                    if($fileVideo && !empty($fileVideo)){
                        $ext = $fileVideo->guessExtension();

                        if(in_array($ext, array('mp4', 'avi'))) {

                            $fileName = time() . '.' . $ext;
                            $pathOfFile = 'uploads/video_files/video_' . $videoId;
                            $fileVideo->move($pathOfFile, $fileName);

                            $video->setVideoPath($fileName);

                            $em->persist($video);
                            $em->flush();

                            $data = array(
                                'status' => 'success',
                                'code' => 200,
                                'msg' => 'Video file uploaded'
                            );
                        }else{
                            $data = array(
                                'status' => 'error',
                                'code' => 400,
                                'msg' => 'Format video not valid'
                            );
                        }
                    }
                }

            }else{
                $data = array(
                    'status' => 'error',
                    'code' => 400,
                    'msg' => 'Video updated error, you not owner'
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
