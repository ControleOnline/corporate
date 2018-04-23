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
        $this->_view->setVariable('forceNotLoggedInLayout', true);
        if (!$this->_userModel->loggedIn()) {
            return \Core\Helper\View::redirectToLogin($this->_renderer, $this->getResponse(), $this->getRequest(), $this->redirect());
        } else {

            $companymodel = new \Company\Model\CompanyModel();
            $companymodel->initialize($this->serviceLocator);
            $cnpj = $companymodel->getLoggedPeopleCompany()->getDocument()[0]->getDocument();

            $data = Api::nvGet('SociosTK', array(
                        'documento' => $cnpj
            ));
            $company = Api::nvGet('PessoasEmpresasTk', array(
                        'documento' => $cnpj
            ));
            /*
             * Verificar data de fundação
             */

            if ($company['CADASTRAIS'] && $company['CADASTRAIS']['DATA_ABERTURA']) {
                $data_abertura = new \DateTime(implode('-', array_reverse(explode('/', $company['CADASTRAIS']['DATA_ABERTURA']))));
                $hoje = new \DateTime(date('Y-m-d'));
                $intervalo = $hoje->diff($data_abertura);
                $intervalo->y;
                if ($intervalo->y < 1) {
                    return $this->redirectTo('/corporate/conference-fundation-date');
                } else {
                    if (1 === 'z') {
                        /*
                         * CNAE em blacklist
                         */
                        return $this->redirectTo('/corporate/conference-cnae');
                    } else {
                        if (1 === 'z') {
                            /*
                             * Ações judiciais
                             */
                            return $this->redirectTo('/corporate/judicial-actions');
                        } else {
                            if ($data && $data['CPF']) {
                                $document = $this->_userModel->getLoggedUserPeople()->getDocument()[0]->getDocument();
                                foreach ($data['CPF'] AS $key => $cpf) {
                                    if ($document == $cpf) {
                                        /*
                                         * É socio
                                         */
                                        $is_business_partner = true;
                                        $_key = $key;
                                    } elseif ($data['CARGO_SOCIO'][$key] == 'SOCIO ADMINISTRADOR') {
                                        /*
                                         * Existe outro sócio que pode assinar
                                         */
                                        $have_business_partner = true;
                                    }
                                }
                                if ($is_business_partner) {
                                    if (count($data['CPF']) > 1 && $have_business_partner) {
                                        /*
                                         * Existem outros socios
                                         */
                                        return $this->redirectTo('/corporate/conference-business-partner');
                                    } elseif ($data['CARGO_SOCIO'][$_key] == 'SOCIO ADMINISTRADOR') {
                                        if (1 === 'z') {
                                            /*
                                             * Cliente publicamente exposto
                                             */
                                            return $this->redirectTo('/corporate/conference-ppe');
                                        } else {
                                            if (1 === 'z') {
                                                /*
                                                 * Existem CNPJ´s de filiais
                                                 */
                                                return $this->redirectTo('/corporate/conference-affiliateds');
                                            } else {
                                                $company = $companymodel->getLoggedPeopleCompany();
                                                $company->setEnabled(true);
                                                $this->getEntityManager()->persist($company);
                                                $this->getEntityManager()->flush($company);
                                                return $this->redirectTo('/user/profile');
                                            }
                                        }
                                    } else {
                                        /*
                                         * A empresa foi aberta a menos de 1 ano
                                         */
                                        return $this->redirectTo('/corporate/conference-fundation-date');
                                    }
                                } else {
                                    /*
                                     * Procuração
                                     */
                                    return $this->redirectTo('/corporate/create-procuration');
                                }
                            } else {
                                /*
                                 * Procuração
                                 */
                                return $this->redirectTo('/corporate/create-procuration');
                            }
                        } else {
                            echo '<pre>';
                            print_r($data);
                            echo '</pre>';
                        }
                    }
                }
            }
            exit;
        }
    }

    public function judicialActionsAction() {
        $this->_view->setTerminal(true);
        $this->_view->setVariable('forceNotLoggedInLayout', true);
        return;
    }

    public function conferenceCnaeAction() {
        $this->_view->setTerminal(true);
        $this->_view->setVariable('forceNotLoggedInLayout', true);
        return;
    }

    public function conferenceAffiliatedsAction() {
        $this->_view->setTerminal(true);
        $this->_view->setVariable('forceNotLoggedInLayout', true);
        return;
    }

    public function conferencePpeAction() {
        $this->_view->setTerminal(true);
        $this->_view->setVariable('forceNotLoggedInLayout', true);
        return;
    }

    public function conferenceFundationDateAction() {
        $this->_view->setTerminal(true);
        $this->_view->setVariable('forceNotLoggedInLayout', true);
        return;
    }

    public function createProcurationAction() {
        if (ErrorModel::getErrors()) {
            return $this->_view;
        }
        if (!$this->_userModel->loggedIn()) {
            return \Core\Helper\View::redirectToLogin($this->_renderer, $this->getResponse(), $this->getRequest(), $this->redirect());
        } else {
            $this->_view->setVariable('forceNotLoggedInLayout', true);
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
            $this->_view->setTerminal(true);
            $this->_view->setVariable('forceNotLoggedInLayout', true);
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
            $this->_view->setVariable('forceNotLoggedInLayout', true);
            return $this->_view;
        }
    }

    public function redirectTo($url) {
        $this->redirect()->toUrl($this->_renderer->basePath($url));
        $this->getResponse()->sendHeaders();
        exit;
    }

}
