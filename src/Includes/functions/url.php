<?php
/** Coloca URL_BASE antes
 * @param string $url
 */
function url_base(string $url)
{
    if($url[0] != '/') ($url = '/'.$url);
    return URL_BASE.$url;
}
/** Coloca __WEBROOT__ antes
 * @param string $url
 */
function webroot(string $url)
{
    if($url[0] != '/') ($url = '/'.$url);
    return __WEBROOT__.$url;
}