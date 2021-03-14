<?php


namespace tonimoreiraa\essentials\Traits;

use tonimoreiraa\essentials\Company as Core;
use CoffeeCode\DataLayer\DataLayer;

trait Company
{
    /* Seta empresa
     * @param Core $user Usuário com id válido
    */
    public function setCompany(Core $company)
    {
        $this->company_id = $company->id;
    }

    /* Procura empresa
     * @return Core|DataLayer
     */
    public function getCompany(): Core|DataLayer
    {
        $company = new Core();
        $company = $company->findById($this->company_id);
        return $company;
    }
}