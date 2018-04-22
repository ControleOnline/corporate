<?php

namespace Corporate\Controller;

use Core\Helper\Api;
use Core\Model\ErrorModel;

class DefaultController extends \Core\Controller\CompanyController {
    /*
     * @todo Arrumar essa permissão
     */

    public function checkPermission() {
        
    }

    public function conferenceAction() {
        if (ErrorModel::getErrors()) {
            return $this->_view;
        }
        $this->_view->forceNotLoggedInLayout = true;
        if (!$this->_userModel->loggedIn()) {
            return \Core\Helper\View::redirectToLogin($this->_renderer, $this->getResponse(), $this->getRequest(), $this->redirect());
        } else {

            $companymodel = new \Company\Model\CompanyModel();
            $companymodel->initialize($this->serviceLocator);
            $cnpj = $companymodel->getLoggedPeopleCompany()->getDocument()[0]->getDocument();

            $data = Api::nvGet('SociosTK', array(
                        'documento' => $cnpj
            ));

            if ($data && $data['CPF']) {
                $document = $this->_userModel->getLoggedUserPeople()->getDocument()[0]->getDocument();
                foreach ($data['CPF'] AS $cpf) {
                    if ($document == $cpf) {
                        /*
                         * É socio
                         */
                        $is_business_partner = true;
                    }
                }

                if ($is_business_partner) {
                    if (count($data['CPF']) > 1) {
                        /*
                         * Existem outros socios
                         */
                        return $this->redirectTo('/corporate/conference-business-partner');
                    } else {
                        /*
                         * Verificar endereço
                         */
                        return $this->redirectTo('/corporate/conference-address');
                    }
                } else {
                    /*
                     * Procuração
                     */
                    return $this->redirectTo('/corporate/create-procuration');
                }
            }
            exit;
        }
    }

    public function createProcurationAction() {
        if (ErrorModel::getErrors()) {
            return $this->_view;
        }        
        if (!$this->_userModel->loggedIn()) {
            return \Core\Helper\View::redirectToLogin($this->_renderer, $this->getResponse(), $this->getRequest(), $this->redirect());
        } else {
            $this->_view->forceNotLoggedInLayout = true;
            return $this->_view;
        }
    }

    public function conferenceBusinessPartnerAction() {
        if (ErrorModel::getErrors()) {
            return $this->_view;
        }        
        if (!$this->_userModel->loggedIn()) {
            return \Core\Helper\View::redirectToLogin($this->_renderer, $this->getResponse(), $this->getRequest(), $this->redirect());
        } else {
            $this->_view->forceNotLoggedInLayout = true;
            return $this->_view;
        }
    }

    public function conferenceAddressAction() {
        if (ErrorModel::getErrors()) {
            return $this->_view;
        }        
        if (!$this->_userModel->loggedIn()) {
            return \Core\Helper\View::redirectToLogin($this->_renderer, $this->getResponse(), $this->getRequest(), $this->redirect());
        } else {
            $this->_view->forceNotLoggedInLayout = true;
            return $this->_view;
        }
    }

    public function redirectTo($url) {
        $this->redirect()->toUrl($this->_renderer->basePath($url));
        $this->getResponse()->sendHeaders();
        exit;
    }

}
