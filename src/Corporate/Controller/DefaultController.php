<?php

namespace Corporate\Controller;

use Core\Helper\Api;

class DefaultController extends \Core\Controller\CompanyController {
    /*
     * @todo Arrumar essa permissão
     */

    public function checkPermission() {
        
    }

    public function conferenceAction() {
        echo 'teste';

        $companymodel = new \Company\Model\CompanyModel();
        $companymodel->initialize($this->serviceLocator);
        $cnpj = $companymodel->getLoggedPeopleCompany()->getDocument()[0]->getDocument();

        $data = Api::nvGet('SociosTk', array(
                    'documento' => $cnpj
        ));

        echo '<pre>';
        print_r($data);
        echo '</pre>';

        exit;
    }

}
