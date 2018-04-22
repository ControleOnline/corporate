<?php

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Corporate;

use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use User\Model\UserModel;
use User\Model\UserModel;
use Company\Model\CompanyModel;

class Module {

    public function onBootstrap(MvcEvent $e) {
        $eventManager = $e->getApplication()->getEventManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);
        $userModel = new UserModel();
        $userModel->initialize($e->getApplication()->getServiceManager());
        $uri = $e->getRequest()->getUri()->getPath();

        $companyModel = new CompanyModel();
        $companyModel->initialize($e->getApplication()->getServiceManager());
        $companyDomain = $companyModel->getCompanyDomain();


        if ($companyDomain->getDomainType() === 'cfcc' && $userModel->loggedIn() && \Core\Helper\Url::isRedirectUrl($uri) && !$userModel->getUserCompany()->getEnabled()) {
            $response = $e->getResponse();
            $response->getHeaders()->addHeaderLine('Location', '/corporate/conference');
            $response->setStatusCode(302);
            $response->sendHeaders();
            exit;
        }
    }

    public function getConfig() {
        return include __DIR__ . '/config/module.config.php';
    }

    public function getAutoloaderConfig() {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }

}
