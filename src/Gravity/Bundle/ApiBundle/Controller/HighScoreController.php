<?php

namespace Gravity\Bundle\ApiBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Gravity\Bundle\ApiBundle\Entity\HighScore;

/**
 * @Route("/api/score")
 * 
 * @author Darren Karstens <darrenkarstens@gmail.com>
 */
class HighScoreController extends UserController
{
	
   /**
     * Lists the top three scores as well as the current user's score for all levels 
     * where a high score has been set. Scores are grouped by map id and are in 
     * score order (quickest first). A myscore of 1 indicates a score for the current user.
     * 
     * Example
     * 
     * Request: [GET] /api/score/list?userAccount=:userAccount
     * 
     * Response:
     * 
     * {
	 *	"maps": [ 
	 *		[
	 *			{ "myscore": "0", "score": 1000, "mapId": 0, "scoreTitle": "Player1" },
	 *			{ "myscore": "0", "score": 1200, "mapId": 0, "scoreTitle": "Player2" },
	 *			{ "myscore": "0", "score": 1560, "mapId": 0, "scoreTitle": "Player3" },
	 *			{ "myscore": "1", "score": 2500, "mapId": 0, "scoreTitle": "CurrentPlayer" }
	 *		],
	 *		[
     *			{ "myscore": "0", "score": 4200, "mapId": 1, "scoreTitle": "Player1" },
     *		],
	 *	]
	 * }
     * 
     * 
	 * @Route("/list")
     * @Method({"GET"})
	 */
    public function listAction() {
		$scores = array();
		$levels = $this->getAvailableLevels();
		foreach ($levels as $mapId) {
			$score = $this->getTopThreeScores($mapId);
			$usersScore = $this->getUsersScore($mapId);
			if (is_array($usersScore)) {
				$score = array_merge($score, $usersScore);
			}
			$scores [] = $score;
		}
		return new JsonResponse(array('maps'=>$scores));
	}
	
	/**
	 * 
	 * Sets a score for the current user. If an existing quicker score exists then
	 * no change is made.
	 * 
	 * Example
	 * 
	 * Request: [POST] /api/score/set?userAccount=:userAccount&mapId=:mapId&score=:score
	 * 
	 * Response:
	 * 
	 * if a quicker score exists then..
	 *	{
	 *		result: "nochange"
	 *	}
	 * 
	 * ...otherwise...
	 *	{
	 *		result: "newrecord"
	 *	}
	 * 
	 * 
	 * @Route("/set")
     * @Method({"POST"})
	 */
    public function setAction(Request $request) {
		$mapId = $request->get("mapId");
		if (!is_numeric($mapId)) { 
			return $this->create404Response(); 
		}
		$score = $request->get("score");
		if (!$score) { 
			return $this->create404Response(); 
		}
		// check to see if they already have a score for this map
		foreach ($this->user->getScores() as $userScore) {
			/** @var $userScore HighScore **/
			if ($userScore->getMapId() == $mapId) {
				if ($userScore->getScore() > $score) {
					// new high score so override old one
					$userScore->setScore($score);
					$this->getDoctrine()->getManager()->persist($userScore);
					$this->getDoctrine()->getManager()->flush();
					return new JsonResponse(array('result'=>'newrecord'));
				} else {
					// didnt beat old high score
					return new JsonResponse(array('result'=>'nochange'));
				}
			}
		}
		// no score for this map so create a new one
		$userScore = $this->createHighScore();
		$userScore->setMapId($mapId);
		$userScore->setScore($score);
		$userScore->setUserAccount($this->user);
		$this->getDoctrine()->getManager()->persist($userScore);
		$this->getDoctrine()->getManager()->flush();
		return new JsonResponse(array('result'=>'newrecord'));
	}

	/**
	 * Creates and returns a new HighScore instance
	 * 
	 * @return \Gravity\Bundle\ApiBundle\Entity\HighScore
	 */
	protected function createHighScore() {
		return new HighScore();
	}
	
	/**
	 * Sets the title that will be shown for the current user when their score is
	 * listed on the high score table.
	 * 
	 * Example
	 * 
	 * Request: [POST] /api/score/set_title?userAccount=:userAccount&scoreTitle=:scoreTitle
	 * 
	 * Response:
	 *	{
	 *		result: "saved"
	 *	}
	 * 
	 * @Route("/set_title")
     * @Method({"POST"})
	 */
	public function setScoreTitle(Request $request) {
		$scoreTitle = $request->get("scoreTitle");
		if (!$scoreTitle) { 
			return $this->create404Response();
		}
		
		$this->user->setScoreTitle($scoreTitle);
		$this->getDoctrine()->getManager()->persist($this->user);
		$this->getDoctrine()->getManager()->flush();
		return new JsonResponse(array('result'=>'saved'));
	}
	
	/**
	 * Returns a list of all map ids for levels that have had times set
	 * 
	 * @return int[]
	 */
	protected function getAvailableLevels() {
		$em = $this->getDoctrine()->getManager();
		$query = $em->createQuery("select s.mapId from Gravity\Bundle\ApiBundle\Entity\HighScore s group by s.mapId");
		return array_column($query->getResult(),'mapId');
	}

	/**
	 * Returns the score for the given level set by the requesting user
	 * 
	 * @param int $mapId
	 * @return array
	 */
	protected function getUsersScore($mapId) {
		$em = $this->getDoctrine()->getManager();
		$query = $em->createQuery("select 1 as myscore, s.score, s.mapId, u.scoreTitle from Gravity\Bundle\ApiBundle\Entity\HighScore s "
			. "left join s.userAccount u "
			. "where s.mapId = :mapId and u.accountName = :accountName "
			. "order by s.score asc");
		$query->setParameter('mapId', $mapId);
		$query->setParameter('accountName', $this->user->getAccountName());
		return $query->getResult();
	}
	
	/**
	 * Returns the top three scores for the given map (quickest first)
	 * 
	 * @param int $mapId
	 * @return array
	 */
	protected function getTopThreeScores($mapId) {
		$em = $this->getDoctrine()->getManager();
		$query = $em->createQuery("select 0 as myscore, s.score, s.mapId, u.scoreTitle from Gravity\Bundle\ApiBundle\Entity\HighScore s "
			. "left join s.userAccount u "
			. "where s.mapId = :mapId "
			. "order by s.score asc");
		$query->setParameter('mapId', $mapId);
		$query->setMaxResults(3);
		return $query->getResult();
	}
}