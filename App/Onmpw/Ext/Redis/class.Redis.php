<?php
/**
 * ���ߣ�����
 * ���˲��ͣ����䲩��
 * ����url��www.onmpw.com
 * ************
 * Redis�� PHP��װ�Ĳ���Redis�� 
 * ************
 */
class Redis{
    
    const Arrays = '*';   //RESP Arrays����
    const Bulk = '$';     //RESP Bulk Strings ����
    const Integer = ':';  //RESP ��������
    const Simple = '+';   //RESP Simple Strings����
    const Errors = '-';   //RESP Errors ��������
    
    const crlf = "\r\n";
    
    private $handle;
    
    private $host;
    private $port;
    private $quiet_fail;
    private $timeout;
    private $commands = array();
    private $result = true; //Ĭ��ִ�н������ȷ��
    private $setError_func = false; //�Ƿ�ʹ���Զ�������������������Ϣ
    private $used_command = null;
    private $errinfo = ''; //������Ϣ
    
    private $connect_timeout = 3;
    
    public function __construct($host,$port,$quiet_fail = false,$timeout = 60){
        if($host && $port){
            $this->connect($host,$port,$quiet_fail,$timeout);
        }
    }
    /**
     * ����Redis����
     * 
     * @param string $host  ������ַ
     * @param number $port  ����˿�
     * @param string $quiet_fail   �Ƿ����������쳣��Ϣ
     * @param number $timeout  ���ö�ȡ��Դ��ʱʱ��
     */
    private function connect($host = '127.0.0.1',$port = 6379,$quiet_fail = false,$timeout = 60){
        $this->host = $host;
        $this->port = $port;
        $this->quiet_fail = $quiet_fail;
        $this->timeout = $timeout;
        $this->handle = fsockopen($host,$port,$errno,$errstr,$this->connect_timeout);
        if($this->quiet_fail){
            $this->handle = @fsockopen($host,$port,$errno,$errstr,$this->connect_timeout);
            if(!$this->handle){
                $this->handle = false;
            }
        }else{
            $this->handle = fsockopen($host,$port,$errno,$errstr,$this->connect_timeout);
        }
        if(is_resource($this->handle)){
            stream_set_timeout($this->handle, $this->timeout);
        }
    }
    
    /**
     * �������ӷ���������
     */
    public function reconnect(){
        $this->__destruct();
        $this->connect($this->host,$this->port,$this->quiet_fail,$this->timeout);
    }
   
    /**
     * ���췢�������
     * @return Redis
     */
    public function command(){
        if(!$this->handle){
            return $this;
        }
        
        $args = func_get_args();
        $cmdlen = count($args);
        $command = '*'.$cmdlen.self::crlf;
        foreach($args as $v){
            $command .= '$'.strlen($v).self::crlf.$v.self::crlf;
        }
        $this->commands[] = $command;
        return $this;
    }
    
    /**
     * ִ�������
     * 
     * @return int
     */
    public function exec(){
        $count = sizeof($this->commands);
        if($count < 1){
            return false;
        }
        if($this->setError_func){
            $this->used_command = str_replace(self::crlf,'\\r\\n',implode(';', $this->commands));
        }
        $command = implode(self::crlf, $this->commands).self::crlf;
        fwrite($this->handle,$command);
        $this->commands = array();
        return $count;
        
    }
    
    /**
     * �õ��������
     * @return boolean
     */
    public function result(){
        $result = false;
        $char = fgetc($this->handle);
        switch($char){
            case self::Simple:
                $result = $this->Simple_result();
                break;
            case self::Bulk:
                $result = $this->Bulk_result(); 
                break;
            case self::Arrays:
                $result = $this->Arrays_result();
                break;
            case self::Errors:
                $result = $this->Errors_result();
                break;
            case self::Integer:
                $result = $this->Integer_result();
                break;
            
        }
        return $result;
    }
    
    /**
     * ����Simple Strings ������Ӧ������
     * 
     * @return string
     */
    private function Simple_result(){
        
       return trim(fgets($this->handle));
       
    }
    
    /**
     * ���� Bulk Strings ���͵�����
     * @return boolean|unknown
     */
    private function Bulk_result(){
        
        $result = trim( fgets($this->handle) );
        
        if($result == -1){
            $this->errinfo = 'Nothing Replied';
            return false;
        }
        
        $result = $this->read_bulk_result($result);
        
        return $result;
        
    }
    
    /**
     * ���� Arrays ���͵�����
     * @return boolean|multitype:NULL
     */
    private function Arrays_result(){
        $size = trim(fgets($this->handle));
        if($size === -1){
            $this->errinfo = 'Nothing Replied';
            return false;
        }
        $result = array();
        for($i=0; $i<$size; $i++){
            $r = trim(fgets($this->handle));
            if($r === -1){
                return false;
            }
            $result [] = $this->read_bulk_result($r);
        }
        return $result; 
        
    }
    
    
    /**
     * ����RESP Integer ��������
     * 
     * @return string
     */
    private function Integer_result(){
        return intval(trim(fgets($this->handle)));
    }
    
    /**
     * ��������
     * @return boolean
     */
    private function Errors_result(){
        $this->result = false;
        $err = fgets($this->handle);
        if($this->setError_func){
            call_user_func($this->setError_func,$this->used_command."Error Info:".$err);
        }
        $this->errinfo = $err;
        return false;
        
    }
    private function read_bulk_result($r){
        $result = null;
        $read = 0;
        $size = (strlen($r)>1 && substr($r,0,1) == self::Bulk) ? substr($r, 1) : $r;
        while($read < $size){
            $readsize = ($size - $read) > 1024 ? 1024 : $size-$read;
            
            $result .= fread($this->handle,$readsize);
            $read += $readsize;
        }
        
        fgets($this->handle);
        
        return $result;
    }
    
    /**
     * ��������
     */
    public function __destruct(){
        if(is_resource($this->handle)){
            fclose($this->handle);
        }
    }
    /**
     * ���ô�������
     * @param unknown $function
     */
    public function setError_func($function){
        $this->setError_func = $function;
    }
    
    public function get_errinfo(){
        return $this->errinfo;
    }
}

$obj = new Redis('192.168.144.133',6379);
$obj->command('get','mykey','hello')->exec();
var_dump($obj->result());
echo $obj->get_errinfo();