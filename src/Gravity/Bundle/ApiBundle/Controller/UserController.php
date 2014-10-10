<?php
namespace Gravity\Bundle\ApiBundle\Controller;

use Gravity\Bundle\ApiBundle\Entity\UserAccount;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Gravity\Bundle\ApiBundle\EventListener\UserRequiredController;
use Gravity\Bundle\ApiBundle\EventListener\VersionedAPIController;

/**
 * 
 * @author Darren Karstens <darrenkarstens@gmail.com>
 */
class UserController extends Controller implements UserRequiredController, VersionedAPIController {
	
	/** @var \Gravity\Bundle\ApiBundle\Entity\UserAccount */
	protected $user;

	/** @var int */
	protected $apiVersion;
	
	/**
	 * Creates and returns a Response object with a 404 http status code
	 * 
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	protected function create404Response() {
		$notFound = new Response();
		$notFound->setStatusCode(404);
		return $notFound;
	}

	/**
	 * @param \Gravity\Bundle\ApiBundle\Entity\UserAccount $user
	 */
	public function setUser(UserAccount $user) {
		$this->user = $user;
	}

	/**
	 * Sets the version of the API this request is using
	 * @param int $version
	 */
	public function setAPIVersion($version) {
		$this->apiVersion = $version;
	}
}
	