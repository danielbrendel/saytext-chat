<?php

/*
    Asatru PHP - routes configuration file

    Add here all your needed routes.

    Schema:
        [<url>, <method>, controller_file@controller_method, opt:unique_identifier]
    Example:
        [/my/route, get, mycontroller@index]
        [/my/route/with/{param1}/and/{param2}, get, mycontroller@another, mycontroller.another]
    Explanation:
        Will call index() in app\controller\mycontroller.php if request is 'get'
        Every route with $ prefix is a special route
*/

return [
    array('/', 'GET', 'index@index'),
    array('/chat/message', 'POST', 'chat@message'),
    array('/chat/fetch', 'POST', 'chat@fetch'),
    array('/chat/online', 'ANY', 'chat@online'),
    array('/chat/users', 'ANY', 'chat@users'),
    array('/chat/clear', 'POST', 'chat@clear'),
    array('/chat/checkname', 'ANY', 'chat@checkname'),
    array('/api/status', 'POST', 'api@status'),
    array('/api/checkname', 'POST', 'api@checkname'),
    array('/api/serverlist', 'POST', 'api@serverlist'),
    array('$404', 'ANY', 'error404@index')
];
