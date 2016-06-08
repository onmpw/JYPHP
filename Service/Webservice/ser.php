<?php
define('APP_PATH',$_SERVER['DOCUMENT_ROOT'].'/App/');
include APP_PATH."Core.php";
Core::_Init();
$soapsers = new SoapServer("http://mobileapp.modernland.hk/Service/Webservice/wsdl/mobileapp.wsdl");
$obj = new Lib\Supserver();
$soapsers->setObject($obj);
$soapsers->handle();