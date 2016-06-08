<?php
namespace Admin\Action;
use Lib\Action;
/**
 * 生成wsdl文件
 * @author Onmpw
 *
 */
class SoapAction extends Action{
    /**
     * 生成user_wsdl文件
     */
    public function user_wsdl(){
        $config = array(
            'service_name'=>"WS_User",
            'class_name' => 'Inter\Webservice\I_WS_User',
            'serverfile' => 'Service/Webservice/WS_User.php',
            'uri'=>"http://mobileapp.modernland.hk",
            'targetNamespace' => 'http://mobileapp.modernland.hk',
        );
        $wd = new \Lib\makeWSDL_Java($config);
        $wd->getWSDL();
    }
    
    public function bids_wsdl(){
        $config = array(
            'service_name'=>"WS_Bids",
            'class_name' => 'Inter\Webservice\I_WS_Bids',
            'serverfile' => 'Service/Webservice/WS_Bids.php',
            'uri'=>"http://mobileapp.modernland.hk",
            'targetNamespace' => 'http://mobileapp.modernland.hk',
        );
        $wd = new \Lib\makeWSDL_Java($config);
        $wd->getWSDL();
    }
    
}