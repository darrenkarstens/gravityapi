<?php

namespace Gravity\Bundle\ApiBundle\EventListener;

use Symfony\Component\HttpKernel\Event\FilterControllerEvent;

/**
 * Checks requests from controllers that implement VersionedAPIController for 
 * an apiVersion parameter. Then sets the api version of the controller using 
 * version "1" when no apiVersion parameter is provided.
 * 
 * @author Darren Karstens <darrenkarstens@gmail.com>
 */
class VersionedAPIListener {

	/**
	 * Before handing over to the controller fetch the API version for the request.
	 * 
	 * @param \Symfony\Component\HttpKernel\Event\FilterControllerEvent $event
	 */
    public function onKernelController(FilterControllerEvent $event) {
        $controller = $event->getController();

        if (is_array($controller) && $controller[0] instanceof VersionedAPIController) {
            $apiVersion = $event->getRequest()->query->get('apiVersion');
			if (!$apiVersion) {
				$controller[0]->setAPIVersion(1);
			} else {
				$controller[0]->setAPIVersion($apiVersion);
			}
        }
    }
}