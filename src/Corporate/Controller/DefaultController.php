<?php

namespace Corporate\Controller;

use Core\Helper\Api;
use Core\Model\ErrorModel;
use Corporate\Model\CorporateModel;
use Zend\Session\Container;

class DefaultController extends \Core\Controller\CompanyController {

    /**
     * @var \Zend\Session\Container
     */
    protected $_session;

    public function __construct() {
        $this->_session = new Container('corporate');
        parent::__construct();
    }

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


            if ($this->params()->fromQuery('debug')) {
                echo '<pre>';
                print_r($data);
                echo '</pre>';
                echo '<pre>';
                print_r($company);
                echo '</pre>';
                /*
                  $c = Api::nvGet('PessoasLigadasTK', array(
                  'documento' => '03449275000103'//$cnpj
                  ));
                  echo '<pre>';
                  print_r($c);
                  echo '</pre>';
                 */
                die();
            }

            /*
             * Verificar data de fundação
             */
            if ($company['CADASTRAIS'] && $company['CADASTRAIS']['DATA_ABERTURA'] && $company['CADASTRAIS']['CNAE'] && $data && $data['CPF']) {
                $data_abertura = new \DateTime(implode('-', array_reverse(explode('/', $company['CADASTRAIS']['DATA_ABERTURA']))));
                $hoje = new \DateTime(date('Y-m-d'));
                $intervalo = $hoje->diff($data_abertura);
                $intervalo->y;
                if ($intervalo->y < 1) {
                    /*
                     * Empresa com menos de 1 ano
                     */
                    return $this->redirectTo('/corporate/conference-fundation-date');
                } else
                /*
                 * @todo Preciso da lista de CNAEs que não poderão ser operados
                 */
                if ($company['CADASTRAIS'] && $company['CADASTRAIS']['CNAE'] && in_array($company['CADASTRAIS']['CNAE'], array('0000'))) {
                    /*
                     * CNAE em blacklist
                     */
                    return $this->redirectTo('/corporate/conference-cnae');
                } else
                /*
                 * @todo De onde retirar esta informação?
                 */
                if (1 === 'z') {
                    /*
                     * Ações judiciais
                     */
                    return $this->redirectTo('/corporate/judicial-actions');
                } else {
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
                    /*
                     * Verificar se já temos a procuração
                     */

                    $corporateModel = new CorporateModel();
                    $corporateModel->initialize($this->serviceLocator);
                    $procuration = $corporateModel->getProcuration($companymodel->getLoggedPeopleCompany(), $this->_userModel->getLoggedUserPeople());
                    if ($is_business_partner || $procuration) {
                        /*
                         * @todo Verificar se já respondeu sobre a quantidade de socios
                         */
                        if (!$this->_session->conference_business_partner && count($data['CPF']) > 1 && $have_business_partner && $is_business_partner && $data['CARGO_SOCIO'][$_key] == 'SOCIO ADMINISTRADOR') {
                            /*
                             * Existem outros socios
                             */
                            return $this->redirectTo('/corporate/conference-business-partner');
                        } else
                        /*
                         * @todo Verificar se já temos a procuração
                         */
                        if ($data['CARGO_SOCIO'][$_key] == 'SOCIO ADMINISTRADOR' || $procuration) {
                            /*
                             * @todo Verificar também se já temos o formulário PPE preenchido
                             */
                            if (1 === 'z') {
                                /*
                                 * Cliente publicamente exposto
                                 */
                                return $this->redirectTo('/corporate/conference-ppe');
                            } else
                            /*
                             * @todo Verificar se já respondeu essas perguntas
                             */
                            if (1 === 'z') {
                                /*
                                 * Existem CNPJ´s de filiais
                                 */
                                return $this->redirectTo('/corporate/conference-affiliateds');
                            } else {
                                $companymodel->enablePeople($companymodel->getLoggedPeopleCompany());
                                return $this->redirectTo('/user/profile');
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
                }
            } else {
                /*
                 * Não encontramos nenhum dado na Nova Vida. O que fazer?
                 */
                echo 'Não encontramos nenhum dado na Nova Vida. O que fazer?';
                echo '<pre>';
                print_r($data);
                print_r($company);
                echo '</pre>';
                exit;
            }
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
            $this->_view->setTerminal(true);
            $this->_view->setVariable('forceNotLoggedInLayout', true);
            return $this->_view;
        }
    }

    public function businessPartnerSelectorAction() {
        $this->_view->setTerminal(true);
        $this->_view->setVariable('forceNotLoggedInLayout', true);
        return $this->_view;
    }

    public function conferenceBusinessPartnerAction() {
        if (ErrorModel::getErrors()) {
            return $this->_view;
        }
        $params = $this->params()->fromPost();
        if (!$this->_userModel->loggedIn()) {
            return \Core\Helper\View::redirectToLogin($this->_renderer, $this->getResponse(), $this->getRequest(), $this->redirect());
        } else if ($params && $params['alone']) {
            /*
             * Assina sozinho
             */
            $this->_session->conference_business_partner = true;
            return $this->redirectTo('/corporate/conference');
        } elseif (isset($params['alone'])) {
            /*
             * Assina em conjunto
             */
            return $this->redirectTo('/corporate/business-partner-selector');
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
