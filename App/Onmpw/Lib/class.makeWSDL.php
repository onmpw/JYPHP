<?php
namespace Lib;
use ReflectionClass as ReflectionClassAlias;
use ReflectionException as ReflectionExceptionAlias;
use ReflectionMethod as ReflectionMethodAlias;

class makeWSDL{
    private $service_name = '';
    private $class_name = '';
    private $uri = '';
    private $serverfile = '';
    private $targetNamespace = '';
    public function __construct($config=array() ){
        $this->service_name =  $config['service_name'];
        $this->class_name  = $config['class_name'];
        $this->uri = $config['uri'];
        $this->serverfile = $config['serverfile'];
        $this->targetNamespace = $config['targetNamespace'];
    }
    public function getWSDL(){
        $headerWsdl = "<?xml version='1.0' encoding='UTF-8' standalone='no'?>\r\n";
        $headerWsdl .= "<wsdl:definitions xmlns:soap='http://schemas.xmlsoap.org/wsdl/soap/' xmlns:tns='{$this->targetNamespace}' xmlns:wsdl='http://schemas.xmlsoap.org/wsdl/' xmlns:xsd='http://www.w3.org/2001/XMLSchema' name='{$this->service_name}' targetNamespace='{$this->targetNamespace}'>";
//         $headerWsdl .= "<wsdl:definitions xmlns:soap='http://schemas.xmlsoap.org/wsdl/soap/' xmlns:tns='{$this->targetNamespace}' xmlns:wsdl='http://schemas.xmlsoap.org/wsdl/' xmlns:xsd='http://www.w3.org/2001/XMLSchema' name='{$this->service_name}' targetNamespace='{$this->targetNamespace}'>\r\n";
        $headerWsdl .= "\t<wsdl:types>\r\n\t<xsd:schema targetNamespace='{$this->targetNamespace}'>\r\n";
        try {
            $class = new ReflectionClassAlias($this->class_name);
            $methods = $class->getMethods(ReflectionMethodAlias::IS_PUBLIC);
        }catch (ReflectionExceptionAlias $exception) {
            // todo 处理异常
            throw $exception;
        }

        $eleTemplate = $this->elementTemplate($methods);
        $messTemplate = $this->messageTemplate($methods);
        $portTypeTemplate = $this->portypeTemplate($methods);
        $bindingTemplate = $this->bindingTemplate($methods);
        $serviceTemplate = $this->serviceTemplate();
        $headerWsdl .= $eleTemplate."</xsd:schema>\r\n</wsdl:types>\r\n".$messTemplate.$portTypeTemplate.$bindingTemplate.$serviceTemplate."</wsdl:definitions>";
        $fp = fopen(SERVICE_PATH.'Webservice/wsdl/'.$this->service_name.".wsdl","w");
//         $fp = fopen(SERVICE_PATH.'Webservice/'.$this->service_name.".wsdl","w");
        fwrite($fp,$headerWsdl);
        fclose($fp);
    }
    
    private function elementTemplate($methods/*$name,$type="string"*/){
        $template = "";
        for($i = 0;$i<count($methods);$i++){
            $template .= "<xsd:element name='{$methods[$i]->getName()}'>\r\n\t<xsd:complexType>\r\n\t<xsd:sequence><xsd:element name='in' type='xsd:string'/>\r\n\t</xsd:sequence>\r\n\t</xsd:complexType>\r\n\t</xsd:element>\r\n";
        }
        return $template;
    }
    
    private function messageTemplate($methods/* $operate,$paranum=0 */){
        $template = "";
        for ($i = 0; $i < count($methods); $i ++) {
            $paraNum = $methods[$i]->getNumberOfParameters();
            if ($paraNum == 0) {
//                 $paraTemplate = "\t<wsdl:part name='{$methods[$i]->getName()}Request' type='soapenc:string'/>\r\n";
                $paraTemplate = "\t<wsdl:part name='{$methods[$i]->getName()}Request' type='xsd:string'/>\r\n";
            } else {
                $paraTemplate = "";
                for ($j = 1; $j <= $paraNum; $j ++) {
//                     $paratemplate .= "\t<wsdl:part name='{$methods[$i]->getName()}Request' type='soapenc:string'/>\r\n";
                    $paraTemplate .= "\t<wsdl:part name='{$methods[$i]->getName()}Request' type='xsd:string'/>\r\n";
//                     $paratemplate .= "\t<wsdl:part name='{$methods[$i]->getName()}Request{$j}' type='xsd:string'/>\r\n";
                }
            }
            $template .= "<wsdl:message name='{$methods[$i]->getName()}Request'>\r\n" . $paraTemplate . "</wsdl:message>\r\n<wsdl:message name='{$methods[$i]->getName()}Response'>\r\n\t<wsdl:part name='{$methods[$i]->getName()}Response' type='xsd:string'/>\r\n</wsdl:message>\r\n";
        }
        return $template;
    }
    
    private function portypeTemplate($methods){
        $operaTemp= '';
        for($i = 0 ; $i < count($methods) ; $i++){
//             $operaTemp .= "\t<wsdl:operation name='{$methods[$i]->getName()}'>\r\n\t\t<wsdl:input message='impl:{$methods[$i]->getName()}Request'/>\r\n\t\t<wsdl:output message='impl:{$methods[$i]->getName()}Response'/>\r\n\t</wsdl:operation>\r\n";
            $operaTemp .= "\t<wsdl:operation name='{$methods[$i]->getName()}'>\r\n\t\t<wsdl:input message='tns:{$methods[$i]->getName()}Request'/>\r\n\t\t<wsdl:output message='tns:{$methods[$i]->getName()}Response'/>\r\n\t</wsdl:operation>\r\n";
        }
        $template = "<wsdl:portType name='{$this->service_name}'>\r\n{$operaTemp}</wsdl:portType>\r\n";
        return $template;
    }
    private function bindingTemplate($methods){
        $operateTemplate = "";
        for($i = 0 ; $i<count($methods) ; $i++){
//             $operattemplate .= "\t<wsdl:operation name='{$methods[$i]->getName()}'>\r\n\t<soap:operation soapAction='{$this->targetNamespace}/{$methods[$i]->getName()}' />\r\n\t\t<wsdl:input>\r\n\t\t\t<soap:body use='literal' />\r\n\t\t</wsdl:input>\r\n\t\t<wsdl:output>\r\n\t\t\t<soap:body use='literal' />\r\n\t\t</wsdl:output>\r\n\t</wsdl:operation>\r\n";
            $operateTemplate .= "\t<wsdl:operation name='{$methods[$i]->getName()}'>\r\n\t<soap:operation soapAction='' />\r\n\t\t<wsdl:input>\r\n\t\t\t<soap:body use='literal' />\r\n\t\t</wsdl:input>\r\n\t\t<wsdl:output>\r\n\t\t\t<soap:body use='literal' />\r\n\t\t</wsdl:output>\r\n\t</wsdl:operation>\r\n";
        }
        $bindingTemplate = "<wsdl:binding name='{$this->service_name}' type='tns:{$this->service_name}'>\r\n<soap:binding style='document' transport='http://schemas.xmlsoap.org/soap/http' />\r\n{$operateTemplate}</wsdl:binding>\r\n";
        return $bindingTemplate;
    }
    private function serviceTemplate(){
        return "<wsdl:service name='{$this->service_name}'>\r\n\t<wsdl:port binding='tns:{$this->service_name}' name='{$this->service_name}'>\r\n\t\t<soap:address location='{$this->uri}/{$this->serverfile}'/>\r\n\t</wsdl:port>\r\n</wsdl:service>";
    }
}