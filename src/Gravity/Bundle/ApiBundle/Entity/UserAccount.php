<?php
namespace Gravity\Bundle\ApiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="user_accounts")
 * 
 * @author Darren Karstens <darrenkarstens@gmail.com>
 */
class UserAccount {
	/**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

	/**
     * @ORM\Column(type="string", length=100)
     */
    protected $accountName;
	
	/**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    protected $mapTitle;
	
	/**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    protected $scoreTitle;
	
	/**
	 *
	 * @ORM\OneToMany(targetEntity="Map", mappedBy="userAccount")
	 */
	protected $maps;
	
	/**
	 *
	 * @ORM\OneToMany(targetEntity="HighScore", mappedBy="userAccount")
	 */
	protected $scores;
	
	public function __construct() {
        $this->maps = new \Doctrine\Common\Collections\ArrayCollection();
		$this->scores = new \Doctrine\Common\Collections\ArrayCollection();
    }
	
	public function getId() {
		return $this->id;
	}

	public function getAccountName() {
		return $this->accountName;
	}

	public function setId($id) {
		$this->id = $id;
	}

	public function setAccountName($accountName) {
		$this->accountName = $accountName;
	}
	
	public function getMaps() {
		return $this->maps;
	}

	public function setMaps($maps) {
		$this->maps = $maps;
	}
	
	public function getMapTitle() {
		return $this->mapTitle;
	}

	public function setMapTitle($mapTitle) {
		$this->mapTitle = $mapTitle;
	}
	public function getScores() {
		return $this->scores;
	}
	public function setScores($scores) {
		$this->scores = $scores;
	}
	public function getScoreTitle() {
		return $this->scoreTitle;
	}
	public function setScoreTitle($scoreTitle) {
		$this->scoreTitle = $scoreTitle;
	}
}