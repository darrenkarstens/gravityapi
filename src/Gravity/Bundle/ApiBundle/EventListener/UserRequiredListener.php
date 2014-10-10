<?php

namespace Gravity\Bundle\ApiBundle\EventListener;

use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Doctrine\ORM\EntityManager;
use Gravity\Bundle\ApiBundle\Entity\UserAccount;

/**
 * Listens for requests made by controllers who implement UserRequiredController.
 * When found it attempts to find a UserAccount which matches the provided
 * userAccount request parameter and populates the controller with the found account.
 * If not found a new UserAccount is created and the controller is populated.
 * 
 * @author Darren Karstens <darrenkarstens@gmail.com>
 */
class UserRequiredListener {

	/**
	 * @var \Doctrine\ORM\EntityManager
	 */
	private $entityManager;

	/**
	 * Inject the entity manager into this service.
	 * 
	 * @param \Doctrine\ORM\EntityManager $entityManager
	 */
	public function __construct(EntityManager $entityManager) {
		$this->entityManager = $entityManager;
	}
	
	/**
	 * Before handing over to the controller fetch the userAccount for the request.
	 * 
	 * @param \Symfony\Component\HttpKernel\Event\FilterControllerEvent $event
	 * @throws AccessDeniedHttpException
	 */
    public function onKernelController(FilterControllerEvent $event) {
        $controller = $event->getController();

        if (is_array($controller) && $controller[0] instanceof UserRequiredController) {
            $accountName = $event->getRequest()->query->get('userAccount');
			if (!$accountName) {
				throw new AccessDeniedHttpException('No user account provided!');
			}
			$user = $this->getOrCreateUserAccount($accountName);
			$controller[0]->setUser($user);
        }
    }
	
	/**
	 * Attempts to find a UserAccount in the database with a matching accountName.
	 * If not found then a UserAccount with the accountName is created and commited to the database.
	 * 
	 * @param String $accountName
	 * @return \Gravity\Bundle\ApiBundle\Entity\UserAccount
	 */
	protected function getOrCreateUserAccount($accountName) {
		$rep = $this->entityManager->getRepository('GravityApiBundle:UserAccount');
		$account = $rep->findOneBy(array('accountName'=>$accountName));
		if ($account) {
			return $account;
		}
		$newAccount = $this->createUserAccount();
		$newAccount->setAccountName($accountName);
		$this->entityManager->persist($newAccount);
		$this->entityManager->flush();
		return $newAccount;
	}

	/**
	 * Creates and returns a new UserAccount instance
	 * 
	 * @return \Gravity\Bundle\ApiBundle\Entity\UserAccount
	 */
	protected function createUserAccount() {
		return new UserAccount();
	}
}