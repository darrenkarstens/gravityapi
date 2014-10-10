<?php
namespace Gravity\Bundle\ApiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="high_scores")
 * 
 * @author Darren Karstens <darrenkarstens@gmail.com>
 */
class HighScore {
	
	/**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

	/**
	 *
	 * @ORM\ManyToOne(targetEntity="UserAccount")
	 */
	protected $userAccount;

	/**
     * @ORM\Column(type="integer")
     */
	protected $mapId;
	
	/**
     * @ORM\Column(type="integer")
     */
	protected $score;
	
	public function __construct() {
        $this->maps = new \Doctrine\Common\Collections\ArrayCollection();
    }
	
	public function getId() {
		return $this->id;
	}
	public function setId($id) {
		$this->id = $id;
	}
	public function getMapId() {
		return $this->mapId;
	}
	public function getScore() {
		return $this->score;
	}
	public function setMapId($mapId) {
		$this->mapId = $mapId;
	}
	public function setScore($score) {
		$this->score = $score;
	}
	public function getUserAccount() {
		return $this->userAccount;
	}
	public function setUserAccount($userAccount) {
		$this->userAccount = $userAccount;
	}
}