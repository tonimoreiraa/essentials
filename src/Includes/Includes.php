<?php
// constantes e váriaveis globais
define('URL_BASE', ($_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['SERVER_NAME'].':'.$_SERVER['SERVER_PORT']) ?? '');

$root = str_replace('/vendor/tonimoreiraa/essentials/src/Includes', '', __DIR__);
$root = str_replace('\vendor\tonimoreiraa\essentials\src\Includes', '', $root);
define('__ROOT__', $root);
define('__ESSENTIALS_ROOT__', str_replace('/src/Includes', '', __DIR__));

// le configuração
require_once (__DIR__.'/Config.php');

// carrega funções
$functions_basedir = __DIR__.'/functions';
$functions_dir = dir($functions_basedir);
while($function_dir = $functions_dir->read()){
    $file = $functions_basedir.'/'.$function_dir;
    if(pathinfo($function_dir, PATHINFO_EXTENSION) == 'php' && is_readable($file)){
        require_once $file;
    }
}

$stream = new \Monolog\Handler\StreamHandler(__ROOT__.'/App/Log/log-'.date('Y-m-d').'.log', \Monolog\Logger::DEBUG);
$stream->setFormatter(new \Monolog\Formatter\LineFormatter("\n[%datetime%] [%level_name%]: %message% %context% %extra%", 'd/m/Y H:i:s'));

$telegram = new \Monolog\Handler\TelegramBotHandler(
    '1533146893:AAFJ1_wcn0FanxC044nhG3wt9s0bERj70x4',
    '@meus_projetos',
    \Monolog\Logger::WARNING
);
$telegram->setParseMode('HTML');
$telegram->setFormatter(new \Monolog\Formatter\LineFormatter("<b>[".PROJECT_NAME."]\n[%level_name%]</b>\n%message%\n\n<b>Detalhes:</b>\n%context%\n\n<b>Extra:</b>\n%extra%"));

$GLOBALS['log'] = new \Monolog\Logger('log');
$GLOBALS['log']->pushHandler($stream);
$GLOBALS['log']->pushHandler($telegram);
