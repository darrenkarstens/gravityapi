<?php
namespace Gravity\Bundle\ApiBundle\EventListener;

use Gravity\Bundle\ApiBundle\Entity\UserAccount;

/**
 * An interface to describe a controller whos request's require a user
 * 
 * @author Darren Karstens <darrenkarstens@gmail.com>
 */
interface UserRequiredController {

	public function setUser(UserAccount $user);
	
}
