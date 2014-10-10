<?php
namespace Gravity\Bundle\ApiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="maps")
 * 
 * @author Darren Karstens <darrenkarstens@gmail.com>
 */
class Map {
	/**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

	/**
	 * @ORM\ManyToOne(targetEntity="UserAccount", inversedBy="maps")
	 * @ORM\JoinColumn(name="user_account_id", referencedColumnName="id")
	 */
    protected $userAccount;
	
	/**
	 * @ORM\Column(type="integer")
	 */
    protected $slot;
	
	/**
	 * @ORM\Column(type="boolean")
	 */
    protected $published;
	
	/**
     * @ORM\Column(type="text")
     */
    protected $mapData;
	
	public function getId() {
		return $this->id;
	}

	public function getSlot() {
		return $this->slot;
	}

	public function getMapData() {
		return $this->mapData;
	}

	public function setId($id) {
		$this->id = $id;
	}

	public function setSlot($slot) {
		$this->slot = $slot;
	}

	public function setMapData($mapData) {
		$this->mapData = $mapData;
	}
	
	/**
	 * @return \Gravity\Bundle\ApiBundle\Entity\UserAccount
	 */
	public function getUserAccount() {
		return $this->userAccount;
	}

	public function setUserAccount($userAccount) {
		$this->userAccount = $userAccount;
	}
	
	public function getPublished() {
		return $this->published;
	}

	public function setPublished($published) {
		$this->published = $published;
	}
}