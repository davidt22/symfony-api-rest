<?php

namespace AppBundle\Controller;

use BackendBundle\Entity\Video;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManager;
use Knp\Component\Pager\Pagination\PaginationInterface;
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

    public function videosAction(Request $request)
    {
        $helpers = $this->get('app.helpers');

        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();
        $dql = 'SELECT v FROM BackendBundle:Video v ORDER BY v.id DESC';

        $query = $em->createQuery($dql);

        $page = $request->query->getInt('page', 1);
        $paginator = $this->get('knp_paginator');
        $itemsPerPage = 6;

        $pagination = $paginator->paginate($query, $page, $itemsPerPage);
        $totalItemsCount = $pagination->getTotalItemCount();

        $data = array(
            'status' => 'success',
            'total_items_count' => $totalItemsCount,
            'page_actual' => $page,
            'items_per_page' => $itemsPerPage,
            'total_pages' => ceil($totalItemsCount / $itemsPerPage),
            'data' => $pagination
        );

        return $helpers->parseJson($data);
    }
}
