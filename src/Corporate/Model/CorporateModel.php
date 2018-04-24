<?php

namespace Corporate\Model;

class CorporateModel extends \Core\Model\CompanyModel {

    /**
     * @return \Core\Entity\People
     */
    public function getCurrentPeopleCompany() {
        if ($this->getErrors()) {
            return;
        }
        return $this->_company_id ? $this->_em->getRepository('\Core\Entity\People')->find($this->_company_id) : null;
    }

    public function getAllCompanies() {
        //return $this->_em->getRepository('\Core\Entity\PeopleCorporate')->findBy(array('company' => $this->getCurrentPeopleCompany()), array('name' => 'ASC'), 100);
    }

    public function addCompanyLink($entity_people, $currentPeopleCompany) {
        $people_employee = new \Core\Entity\CorporatePeople();
        $people_employee->setCompanyId($currentPeopleCompany->getId());
        $people_employee->setCorporate($entity_people);
        $this->_em->persist($people_employee);
        $this->_em->flush($people_employee);
    }

    public function getProcuration(\Core\Entity\People $company, \Core\Entity\People $people) {
        return false;
    }

}
