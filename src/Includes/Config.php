<?php
// carrega config
$config = file_get_contents(__ROOT__.'/App/Config/Config.json');
$config = json_decode($config);

// banco de dados
define('DATA_LAYER_CONFIG', [
    'driver' => 'pgsql',
    'host' => $config->db->host,
    'port' => $config->db->port,
    'username' => $config->db->username,
    'passwd' => $config->db->password,
    'dbname' => $config->db->dbname,
    'options' => [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
        PDO::ATTR_CASE => PDO::CASE_NATURAL
    ]
]);

// email
$GLOBALS['ADMIN_EMAIL'] = $config->dev_email;
define('__WEBROOT__', $config->webroot);
define('PROJECT_NAME', $config->project_name);