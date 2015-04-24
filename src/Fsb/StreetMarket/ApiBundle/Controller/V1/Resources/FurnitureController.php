<?php

namespace Fsb\StreetMarket\ApiBundle\Controller\V1\Resources;

use DateTime;
use Exception;

use Doctrine\ORM\ORMException;

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
        $lastModificationDate = null;
        $data = array();

        $em = $this->getDoctrine()->getManager();
        $furnitures = $em->getRepository('FsbStreetMarketCoreBundle:Furniture')->findAllLatestActive();

        $lastFurniture = end($furnitures);

        if ($lastFurniture) {
            $lastModificationDate = $lastFurniture->getTookAt();
        }

        if ($_format === 'xml') {
            $data = $furnitures;
        } else {
            $data['furnitures'] = $furnitures;
        }

        return $this->generateResponse($data, $statusCode, $success, $_format, array('list'), $lastModificationDate);
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
     *     requirements={},
     *     parameters={
     *         {
     *             "name"="created_at",
     *             "dataType"="DateTime",
     *             "required"=true,
     *             "format"="yyyy-MM-ddTHH:mm:SSZ",
     *             "description"="Date when the furniture has been created"
     *         },
     *         {
     *             "name"="title",
     *             "dataType"="string",
     *             "required"=true,
     *             "description"="Title of the furniture"
     *         },
     *         {
     *             "name"="took_at",
     *             "dataType"="DateTime",
     *             "required"=true,
     *             "format"="yyyy-MM-ddTHH:mm:SSZ",
     *             "description"="Date when the furniture has been taken"
     *         },
     *         {
     *             "name"="latitude",
     *             "dataType"="float",
     *             "required"=true,
     *             "description"="Latitude of the place where the furniture has been taken"
     *         },
     *         {
     *             "name"="longitude",
     *             "dataType"="float",
     *             "required"=true,
     *             "description"="Longitude of the place where the furniture has been taken"
     *         }
     *     },
     *     output="array"
     * )
     */
    public function createAction($_format)
    {
        $statusCode = 201;
        $success = true;
        $data = array();

        $request = $this->getRequest();

        try {
            $createdAt  = new DateTime($request->request->get('created_at'));
        }
        catch (Exception $e) {
            $createdAt  = false;
        }

        $title          = $request->request->get('title');

        try {
            $tookAt     = new DateTime($request->request->get('took_at'));
        }
        catch (Exception $e) {
            $tookAt     = false;
        }

        $latitude       = floatval($request->request->get('latitude'));
        $longitude      = floatval($request->request->get('longitude'));

        if (!$createdAt) {
            $success = false;
            $data['errors'][] = 'Undefined or unvalid parameter: took_at';
        }

        if (!$title) {
            $success = false;
            $data['errors'][] = 'Undefined parameter: title';
        }

        if (!$tookAt) {
            $success = false;
            $data['errors'][] = 'Undefined or unvalid parameter: took_at';
        }

        if (!$latitude || $latitude === 0) {
            $success = false;
            $data['errors'][] = 'Unvalid or unvalid parameter: latitude';
        }

        if (!$longitude || $longitude === 0) {
            $success = false;
            $data['errors'][] = 'Undefined or unvalid parameter: longitude';
        }

        if ($success) {
            $furniture = new Furniture();
            $furniture->setCreatedAt($createdAt);
            $furniture->setUpdatedAt($createdAt);
            $furniture->setIsHidden(true);
            $furniture->setTitle($title);
            // Force hide it until its picture is uploaded [REQUEST /{id}/upload]
            $furniture->setTookAt($tookAt);
            $furniture->setLatitude($latitude);
            $furniture->setLongitude($longitude);

            $em = $this->getDoctrine()->getManager();

            try {
                $em->persist($furniture);
                $em->flush();

                if ($_format === 'xml') {
                    $data = $furniture;
                } else {
                    $data['furniture'] = $furniture;
                }
            }
            catch (ORMException $ORME) {
                $this->get('logger')->error($ORME->getMessage());

                $statusCode = 500;
                $data['message'] = 'An error has occured. Please try again later.';
            }
        } else {
            $statusCode = 400;
            $data['message'] = 'Bad request: unvalid parameters.';
        }

        return $this->generateResponse($data, $statusCode, $success, $_format, array('detail'));
    }

    /**
     * Find one furniture by ID
     *
     * @ApiDoc(
     *     section="Furnitures",
     *     description="Find one furniture by ID",
     *     statusCodes={
     *         200="Returned when successful",
     *         404="Returned when the furniture is not found",
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
        $lastModificationDate = null;
        $data = array();

        $em = $this->getDoctrine()->getManager();

        $furniture = $em->getRepository('FsbStreetMarketCoreBundle:Furniture')->findOneActive($id);

        if ($furniture) {
            $lastModificationDate = $furniture->getTookAt();

            if ($_format === 'xml') {
                $data = $furniture;
            } else {
                $data['furniture'] = $furniture;
            }
        } else {
            $statusCode = 404;
            $success = false;
            $data['message'] = 'This furniture does not exists';
        }

        return $this->generateResponse($data, $statusCode, $success, $_format, array('full'), $lastModificationDate);
    }

    /**
     * Update a furniture
     *
     * @ApiDoc(
     *     section="Furnitures",
     *     description="Update a furniture",
     *     method="put",
     *     statusCodes={
     *         200="Returned when successful",
     *         404="Returned when the furniture is not found",
     *         400="Returned in case of bad request (missing mandatory parameters)"
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
     *             "name"="updated_at",
     *             "dataType"="DateTime",
     *             "required"=false,
     *             "format"="yyyy-MM-ddTHH:mm:SSZ",
     *             "description"="Date when the furniture has been updated"
     *         },
     *         {
     *             "name"="title",
     *             "dataType"="string",
     *             "required"=false,
     *             "description"="Title of the furniture"
     *         },
     *         {
     *             "name"="took_at",
     *             "dataType"="DateTime",
     *             "required"=false,
     *             "format"="yyyy-MM-ddTHH:mm:SSZ",
     *             "description"="Date when the furniture has been taken"
     *         },
     *         {
     *             "name"="latitude",
     *             "dataType"="float",
     *             "required"=false,
     *             "description"="Latitude of the place where the furniture has been taken"
     *         },
     *         {
     *             "name"="longitude",
     *             "dataType"="float",
     *             "required"=false,
     *             "description"="Longitude of the place where the furniture has been taken"
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

        $request = $this->getRequest();
        $em = $this->getDoctrine()->getManager();
        $furniture = $em->getRepository('FsbStreetMarketCoreBundle:Furniture')->findOneActive($id);


        if ($furniture) {
            $title          = $request->request->get('title');

            try {
                $updatedAt  = new DateTime($request->request->get('updated_at'));
            }
            catch (Exception $e) {
                $updatedAt  = false;
            }

            try {
                $tookAt     = new DateTime($request->request->get('took_at'));
            }
            catch (Exception $e) {
                $tookAt     = false;
            }

            $latitude       = floatval($request->request->get('latitude'));
            $longitude      = floatval($request->request->get('longitude'));

            if ($updatedAt) {
                $furniture->setUpdatedAt($updatedAt);
            }

            if ($title) {
                $furniture->setTitle($title);
            }

            if ($tookAt) {
                $furniture->setTookAt($tookAt);
            }

            if ($latitude) {
                $furniture->setLatitude($latitude);
            }

            if ($longitude) {
                $furniture->setLongitude($longitude);
            }

            try {
                $em->persist($furniture);
                $em->flush();

                if ($_format === 'xml') {
                    $data = $furniture;
                } else {
                    $data['furniture'] = $furniture;
                }
            }
            catch (ORMException $ORME) {
                $this->get('logger')->error($ORME->getMessage());

                $statusCode = 500;
                $data['message'] = 'An error has occured. Please try again later.';
            }
        } else {
            $statusCode = 404;
            $data['message'] = 'Bad request: unvalid parameters.';
        }

        return $this->generateResponse($data, $statusCode, $success, $_format, array('detail'));
    }

    /**
     * Upload the picture of a furniture by ID
     *
     * @ApiDoc(
     *     section="Furnitures",
     *     description="Upload the picture of a furniture by ID",
     *     method="put",
     *     statusCodes={
     *         501="Returned because the method is not yet implemented"
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
    public function uploadAction($id, $_format)
    {
        $statusCode = 501;
        $success = false;
        $data = array(
            'message' => 'This method is not yet implemented.'
        );

        // TODO
        return $this->generateResponse($data, $statusCode, $success, $_format);
    }

    /**
     * Delete a furniture by ID
     *
     * @ApiDoc(
     *     section="Furnitures",
     *     description="Delete a furniture by ID",
     *     method="delete",
     *     statusCodes={
     *         200="Returned when successful",
     *         404="Returned when the furniture is not found"
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
        $statusCode = 204;
        $success = true;
        $data = array();

        $em = $this->getDoctrine()->getManager();
        $furniture = $em->getRepository('FsbStreetMarketCoreBundle:Furniture')->findOneActive($id);

        if ($furniture) {
            $furniture->setRemovedAt(new DateTime());

            try {
                $em->persist($furniture);
                $em->flush();

                if ($_format === 'xml') {
                    $data = $furniture;
                } else {
                    $data['furniture'] = $furniture;
                }
            }
            catch (ORMException $ORME) {
                $this->get('logger')->error($ORME->getMessage());

                $statusCode = 500;
                $data['message'] = 'An error has occured. Please try again later.';
            }
        } else {
            $statusCode = 404;
            $success = false;
            $data['message'] = 'This furniture does not exists';
        }

        return $this->generateResponse($data, $statusCode, $success, $_format, array('detail'));
    }
}
