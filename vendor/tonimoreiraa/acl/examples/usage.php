<?php
use ACL\ACL;

// cria suposições de objeto
$user = new stdClass();
$user->id = 1;

$company = new stdClass();
$company->id = 2;

// seta objeto a ser verificado
$GLOBALS['ACL_OBJECTS_TO_VERIFY'] = [$user];

// seta local do arquivo de acl
$GLOBALS['ACL_FILE_LOCATION'] = __DIR__.'/../files/ACL.json';

$acl = new ACL();

// seta permissão
$acl->setPermission($user, 'company_control', ACL::MODIFY);

// seta permissão em cima de objeto
$acl->setPermissionOnTarget($user, $company,ACL::MODIFY, 'company_control');

// procura permissão
$acl->getPermission('company_control');

// pega permissão em cima de objeto
$acl->getPermissionOnTarget($company, 'company_control');