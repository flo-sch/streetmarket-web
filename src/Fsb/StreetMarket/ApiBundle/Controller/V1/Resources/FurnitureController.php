<?php

namespace Fsb\StreetMarket\ApiBundle\Controller\V1\Resources;

use DateTime;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use Fsb\StreetMarket\ApiBundle\Controller\RestController;
use Fsb\StreetMarket\CoreBundle\Entity\Furniture;

/**
 * Furniture controller.
 *
 */
class FurnitureController extends RestController
{
    /**
     * List all furnitures
     *
     * @ApiDoc(
     *     section="Furnitures",
     *     description="List all furnitures",
     *     statusCodes={
     *         200="Returned when successful"
     *     },
     *     output="array"
     * )
     */
    public function listAction($_format)
    {
        $statusCode = 200;
        $success = true;
        $data = array();

        $em = $this->getDoctrine()->getManager();

        $furnitures = $em->getRepository('FsbStreetMarketCoreBundle:Furniture')->findAllActive();

        return $this->generateJsonResponse(array(
            'furnitures' => $furnitures
        ), $statusCode, $success, $_format, array('list'));
    }

    /**
     * Create a new furniture
     *
     * @ApiDoc(
     *     section="Furnitures",
     *     description="Create a new furniture",
     *     method="post",
     *     statusCodes={
     *         200="Returned when successful",
     *         400="Returned when bad request (missing mandatory parameters)"
     *     },
     *     requirements={
     *         {
     *             "name"="sorting",
     *             "dataType"="integer",
     *             "requirement"="\d+",
     *             "description"="Ordre d'affichage"
     *         }
     *     },
     *     parameters={
     *         {
     *             "name"="created_at",
     *             "dataType"="string",
     *             "required"=false,
     *             "format"="yyyy-MM-dd HH:mm:SS",
     *             "description"="Date de création de of the furniture"
     *         },
     *         {
     *             "name"="nickname",
     *             "dataType"="string",
     *             "required"=true,
     *             "description"="Surnom de of the furniture"
     *         },
     *         {
     *             "name"="birth_at",
     *             "dataType"="string",
     *             "required"=false,
     *             "format"="yyyy-MM-dd HH:mm:SS",
     *             "description"="Date de naissance de of the furniture"
     *         },
     *         {
     *             "name"="music_type",
     *             "dataType"="string",
     *             "required"=true,
     *             "description"="Genre musical"
     *         },
     *         {
     *             "name"="sorting",
     *             "dataType"="integer",
     *             "required"=false,
     *             "description"="Ordre d'affichage"
     *         },
     *         {
     *             "name"="picture",
     *             "dataType"="blob",
     *             "required"=false,
     *             "description"="Photo"
     *         },
     *         {
     *             "name"="presentation",
     *             "dataType"="string",
     *             "required"=false,
     *             "description"="Texte de présentation"
     *         },
     *         {
     *             "name"="facebook_link",
     *             "dataType"="string",
     *             "required"=false,
     *             "description"="Lien facebook de of the furniture"
     *         },
     *         {
     *             "name"="spotify_uri",
     *             "dataType"="string",
     *             "required"=false,
     *             "description"="URI Spotify de of the furniture"
     *         },
     *         {
     *             "name"="deezer_album",
     *             "dataType"="string",
     *             "required"=false,
     *             "description"="ID de l'album Deezer de of the furniture"
     *         }
     *     },
     *     output="array"
     * )
     */
    public function createAction($_format)
    {
        $statusCode = 200;
        $success = true;
        $data = array();

        $em = $this->getDoctrine()->getManager();

        $request = $this->getRequest();

        $createdAt              = DateTime::createFromFormat('Y-m-d H:i:s', substr($request->request->get('created_at'), 0, 19));
        $nickname               = $request->request->get('nickname');
        $birthAt                = DateTime::createFromFormat('Y-m-d H:i:s', substr($request->request->get('birth_at'), 0, 19));
        $musicType              = $request->request->get('music_type');
        $sorting                = $request->request->get('sorting');
        $picture                = null;
        $presentation           = $request->request->get('presentation');
        $facebookLink           = $request->request->get('facebook_link');
        $spotifyUri             = $request->request->get('spotify_uri');
        $deezerAlbum            = $request->request->get('deezer_album');

        $furniture                 = new Furniture();

        if ($nickname) {
            $furniture->setNickname($nickname);
            $furniture->setSlug($this->sanitise($nickname));
        } else {
            $data['message'][] = 'Undefined nickname';
        }

        if ($musicType) {
            $furniture->setMusicType($musicType);
        } else {
            $data['message'][] = 'Undefined music type';
        }

        if ($nickname && $musicType) {
            $furniture->setTookAt($tookAt);

            $em->persist($furniture);
            $em->flush();

            $data['furniture'] = $furniture;
        } else {
            $statusCode = 400;
            $success = false;
        }

        return $this->generateJsonResponse($data, $statusCode, $success, $_format, array('detail'));
    }

    /**
     * Find one furniture by ID
     *
     * @ApiDoc(
     *     section="Furnitures",
     *     description="Find one furniture by ID",
     *     statusCodes={
     *         200="Returned when successful",
     *         404="Returned when not found"
     *     },
     *     requirements={
     *         {
     *             "name"="id",
     *             "dataType"="integer",
     *             "requirement"="\d+",
     *             "description"="ID of the furniture"
     *         }
     *     },
     *     parameters={
     *         {
     *             "name"="id",
     *             "dataType"="integer",
     *             "required"=true,
     *             "description"="ID of the furniture"
     *         }
     *     },
     *     output="array"
     * )
     */
    public function detailAction($id, $_format)
    {
        $statusCode = 200;
        $success = true;
        $data = array();

        $em = $this->getDoctrine()->getManager();

        $furniture = $em->getRepository('FsbStreetMarketCoreBundle:Furniture')->findOneActive($id);

        if ($furniture) {
            $data['furniture'] = $furniture;
        } else {
            $statusCode = 404;
            $success = false;
            $data['message'] = 'This furniture does not exists';
        }

        return $this->generateJsonResponse($data, $statusCode, $success, $_format, array('full'));
    }

    /**
     * Update an furniture
     *
     * @ApiDoc(
     *     section="Furnitures",
     *     description="Update an furniture",
     *     method="put",
     *     statusCodes={
     *         200="Returned when successful",
     *         400="Returned when bad request (missing mandatory parameters)"
     *     },
     *     requirements={
     *         {
     *             "name"="id",
     *             "dataType"="integer",
     *             "requirement"="\d+",
     *             "description"="ID of the furniture"
     *         }
     *     },
     *     parameters={
     *         {
     *             "name"="id",
     *             "dataType"="integer",
     *             "required"=true,
     *             "description"="ID de of the furniture"
     *         },
     *         {
     *             "name"="title",
     *             "dataType"="string",
     *             "required"=true,
     *             "description"="Title of the furniture"
     *         },
     *         {
     *             "name"="took_at",
     *             "dataType"="string",
     *             "required"=true,
     *             "format"="yyyy-MM-dd HH:mm:SS",
     *             "description"="Date when the furniture has been taken"
     *         }
     *     },
     *     output="array"
     * )
     */
    public function updateAction($id, $_format)
    {
        $statusCode = 200;
        $success = true;
        $data = array();

        $em = $this->getDoctrine()->getManager();
        $furniture = $em->getRepository('FsbStreetMarketCoreBundle:Furniture')->findOneActive($id);

        $request = $this->getRequest();

        if ($furniture) {
            $updatedAt              = DateTime::createFromFormat('Y-m-d H:i:s', substr($request->request->get('updated_at'), 0, 19));
            $nickname               = $request->request->get('nickname');
            $birthAt                = DateTime::createFromFormat('Y-m-d H:i:s', substr($request->request->get('birth_at'), 0, 19));
            $musicType              = $request->request->get('music_type');
            $sorting                = $request->request->get('sorting');
            $picture                = null;
            $presentation           = $request->request->get('presentation');
            $facebookLink           = $request->request->get('facebook_link');
            $spotifyUri             = $request->request->get('spotify_uri');
            $deezerAlbum            = $request->request->get('deezer_album');

            if ($updatedAt) {
                $furniture->setUpdatedAt($updatedAt);
            }

            if ($nickname) {
                $furniture->setNickname($nickname);
                $furniture->setSlug($this->sanitise($nickname));
            }

            if ($birthAt) {
                $furniture->setBirthAt($birthAt);
            }

            if ($musicType) {
                $furniture->setMusicType($musicType);
            }

            if ($sorting) {
                $furniture->setSorting($sorting);
            }

            if ($picture) {
                $furniture->setPicture($picture);
            }

            if ($presentation) {
                $furniture->setPresentation($presentation);
            }

            if ($facebookLink) {
                $furniture->setFacebookLink($facebookLink);
            }

            if ($spotifyUri) {
                $furniture->setSpotifyUri($spotifyUri);
            }

            if ($deezerAlbum) {
                $furniture->setDeezerAlbum($deezerAlbum);
            }

            $em->persist($furniture);
            $em->flush();

            $data['furniture'] = $furniture;
        } else {
            $statusCode = 404;
            $success = false;
            $data['message'] = 'This furniture does not exists';
        }

        return $this->generateJsonResponse($data, $statusCode, $success, $_format, array('detail'));
    }

    /**
     * Delete an furniture by ID
     *
     * @ApiDoc(
     *     section="Furnitures",
     *     description="Delete an furniture by ID",
     *     method="delete",
     *     statusCodes={
     *         200="Returned when successful",
     *         404="Returned when not found"
     *     },
     *     requirements={
     *         {
     *             "name"="id",
     *             "dataType"="integer",
     *             "requirement"="\d+",
     *             "description"="ID of the furnituree"
     *         }
     *     },
     *     parameters={
     *         {
     *             "name"="id",
     *             "dataType"="integer",
     *             "required"=true,
     *             "description"="ID of the furnituree"
     *         }
     *     },
     *     output="array"
     * )
     */
    public function deleteAction($id, $_format)
    {
        $statusCode = 200;
        $success = true;
        $data = array();

        $em = $this->getDoctrine()->getManager();
        $furniture = $em->getRepository('FsbStreetMarketCoreBundle:Furniture')->findOneActive($id);

        if ($furniture) {
            $data['furniture'] = $furniture;

            $furniture->setRemovedAt(new DateTime());
            $em->persist($furniture);
            $em->flush();
        } else {
            $statusCode = 404;
            $success = false;
            $data['message'] = 'This furniture does not exists';
        }

        return $this->generateJsonResponse($data, $statusCode, $success, $_format, array('detail'));
    }
}
