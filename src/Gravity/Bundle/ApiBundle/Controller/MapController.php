<?php

namespace Gravity\Bundle\ApiBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Gravity\Bundle\ApiBundle\Entity\Map;

/**
 * @Route("/api/map")
 * 
 * @author Darren Karstens <darrenkarstens@gmail.com>
 */
class MapController extends UserController
{
	
   /**
    * Loads a user's custom map and returns it in json format.
    * 
    * Example
    * 
    * Request: [GET] /api/map/load?userAccount=:userAccount&mapId=:mapId
    * 
	* Response:
    *	[
	*			[4,4,2,4,4,4,4,4,4,4],
	*			[4,4,4,4,4,4,4,4,4,4],
	*			[4,4,4,4,6,6,6,4,4,4]
	*	]
    * 
	* @Route("/load")
    * @Method({"GET"})
	*/
    public function loadAction(Request $request) {
		$mapId = $request->get("mapId");
		if (!$mapId) {
			return $this->create404Response();
		}
		$map = $this->getMap($mapId);
		if (!$map) {
			return $this->create404Response();
		}
		return new JsonResponse(json_decode($map->getMapData()));
	}

	/**
	 * Returns a list of all custom maps that have been published by users. Each 
	 * row in the list array contains the title which the maps are published under
	 * and map ids for the five custom maps slots available.
	 * 
	 * Example
	 * 
	 * Request: [GET] /api/map/list/all?userAccount=:userAccount
	 * 
	 * Response:
	 * 
	 * {
	 *		"list":[
	 *			{"title":"User1 Maps","mapIds":[0,0,0,4,0]}
	 *			{"title":"User2 Maps","mapIds":[0,2,8,0,0]}
	 *		]
	 * }
	 * 
	 * @Route("/list/all")
     * @Method({"GET"})
	 */
    public function listAllAction() {
		$em = $this->getDoctrine()->getManager();
		$query = $em->createQuery("SELECT u, m FROM \Gravity\Bundle\ApiBundle\Entity\UserAccount u JOIN u.maps m WHERE m.published = 1 order by u.mapTitle");
		$userAccounts = $query->getResult();

		$list = array();
		foreach ($userAccounts as $account) {
			$mapIds = array(0,0,0,0,0);
			foreach ($account->getMaps() as $map) {
				if ($map->getPublished()) {
					$mapIds[$map->getSlot()] = $map->getId();
				}
			}
			$list [] = array('title'=>$account->getMapTitle(),'mapIds'=>$mapIds);
		}
		return new JsonResponse(array('list'=>$list));
	}

	/**
	 * Returns a list of all custom maps owned by the current user. Each row in
	 * the maps array contains the title the maps are published under, the id
	 * of the map, the published flag and the slot number.
	 * 
	 * Example
	 * 
	 * Request: [GET] /api/map/list/my?userAccount=:userAccount
	 * 
	 * Response:
	 *	{
	 *		"maps":[
	 *			{"title":"User1 Maps","id":6,"published":1,"slot":0},
	 *			{"title":"User1 Maps","id":7,"published":0,"slot":1}
	 *		]
	 *	}
	 * 
	 * @Route("/list/my")
     * @Method({"GET"})
	 */
    public function listMyAction() {
		$maps = array();
		foreach ($this->user->getMaps() as $map) {
			$maps [] = array('title'=>$this->user->getMapTitle(),'id'=>$map->getId(), 
				'published'=>($map->getPublished() ? 1 : 0),'slot'=>$map->getSlot());
		}

		return new JsonResponse(array('maps'=>$maps));
	}

   /**
     * Saves the user's custom map sent in json format.
     * 
     * Example
     * 
     * Request: [POST] /api/map/save?userAccount=:userAccount&slot=:slot
     *			[POST] map = 
     *					[
	 *						[4,4,2,4,4,4,4,4,4,4],
	 *						[4,4,4,4,4,4,4,4,4,4],
	 *						[4,4,4,4,6,6,6,4,4,4]
	 *					]
     * 
	 * Response:
	 *	{
	 *		result: "Success"
	 *	}
     * 
	 * @Route("/save")
     * @Method({"POST"})
	 */
    public function saveAction(Request $request)
    {
		$em = $this->getDoctrine()->getManager();
		$map = $request->get("map");
		$slot = $request->get("slot");
		if (!$map || !is_numeric($slot)) {
			return $this->create404Response();
		}
		$mapJson = json_decode($map);
		if ($mapJson === null) {
			return $this->create404Response();
		}
		/** @var $map \Gravity\Bundle\ApiBundle\Entity\Map */
		foreach ($this->user->getMaps() as $existingMap) {
			if ($existingMap->getSlot() == $slot) {
				$existingMap->setMapData(json_encode($mapJson));
				$em->persist($existingMap);
				$em->flush();
				return new JsonResponse(array('result'=>'Success'));
			}
		}
		$newMap = $this->createMap();
		$newMap->setMapData(json_encode($mapJson));
		$newMap->setSlot($slot);
		$newMap->setPublished(false);
		$newMap->setUserAccount($this->user);
		$em->persist($newMap);
		$em->flush();
		return new JsonResponse(array('result'=>'Success'));
    }

	/**
	 * Creates and returns a new Map instance
	 * 
	 * @return \Gravity\Bundle\ApiBundle\Entity\Map
	 */
	protected function createMap() {
		return new Map();
	}
		
	/**
	 * Publishes one of the user's maps making it available to other users. A 
	 * title will also need to be provided to be shown on the public map list.
	 * 
	 * Example
	 * 
	 * Request: [POST] /api/map/publish?userAccount=:userAccount&mapId=:mapId&title=:title
	 * 
	 * Response:
	 *	{
	 *		result: "Success"
	 *	}
	 * 
	 * @Route("/publish")
     * @Method({"POST"})
	 */
    public function publishAction(Request $request) {
		$mapId = $request->get("mapId");
		if (!$mapId) {
			return $this->create404Response();
		}
		$title = $request->get("title");
		if (!$title) {
			return $this->create404Response();
		}
		$map = $this->getMap($mapId);
		if (!$map) {
			return $this->create404Response();
		}
		// check the given map id belongs to the user account
		if ($map->getUserAccount()->getAccountName() != $this->user->getAccountName()) {
			return $this->create404Response();
		}
		$em = $this->getDoctrine()->getManager();
		$map->getUserAccount()->setMapTitle($title);
		$map->setPublished(true);
		$em->persist($map);
		$em->flush();
		return new JsonResponse(array('result'=>'Success'));
	}

	/**
	 * Removes a user's map from the public map list.
	 * 
	 * Example
	 * 
	 * Request: [POST] /api/map/unpublish?userAccount=:userAccount&mapId=:mapId
	 * 
	 * Response:
	 *	{
	 *		result: "Success"
	 *	}
	 * 
 	 * @Route("/unpublish")
     * @Method({"POST"})
 	 */
    public function unpublishAction(Request $request) {
		$mapId = $request->get("mapId");
		if (!$mapId) {
			return $this->create404Response();
		}
		$map = $this->getMap($mapId);
		if (!$map) {
			return $this->create404Response();
		}
		// check the given map id belongs to the user account
		if ($map->getUserAccount()->getAccountName() != $this->user->getAccountName()) { 
			return $this->create404Response();
		}
		$em = $this->getDoctrine()->getManager();
		$map->setPublished(false);
		$em->persist($map);
		$em->flush();
		return new JsonResponse(array('result'=>'Success'));
	}
	
	/**
	 * Loads and returns a map from the database
	 * 
	 * @param type $mapId
	 * @return \Gravity\Bundle\ApiBundle\Entity\Map
	 */
	protected function getMap($mapId) {
		$rep = $this->getDoctrine()->getRepository('GravityApiBundle:Map');
		$map = $rep->find($mapId);
		return $map;
	}
	
}
