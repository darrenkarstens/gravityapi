<?php
namespace Gravity\Bundle\ApiBundle\EventListener;

/**
 * Describes a controller that tracks the api version a request is using.
 * 
 * @author Darren Karstens <darrenkarstens@gmail.com>
 */
interface VersionedAPIController {
	
	public function setAPIVersion($version);
	
}
