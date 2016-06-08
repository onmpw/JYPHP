<?php
/**
 * ������ʱδ�õ�
 */
namespace Lib\DB;

abstract class BaseDB{
    
    protected $config = array();
    
    protected  $link = null;   //����
    
    protected $options = array();
    
    protected $dsn = '';  //���ݿ����� dsn��Ϣ
    
    protected $PDOstatement = null;
    
    protected $transnum = 0;  //����ָ������
    
    protected $sql = '';
    
    protected $bind = array();
    
    protected $lastInsId;  //��¼��������ʱ�������һ����¼��id
    
    protected $affectnum; //��¼Ӱ������ݵ�����
    
    public function __construct($config = ''){
        $this->config($config);
    }
    
    /**
     * �������ݿ�����dsn
     * 
     * @param unknown $config
     */
    abstract protected function parseDsn($config);
    
    /**
     * ���ݿ����Ӻ���
     * 
     * @param string $config
     * 
     * @return \PDO
     * 
     */
    public function connect($config = ''){
        //�ж��Ƿ��Ѿ����� ���û��������ô��������
        if(is_null($this->link)){
            if(empty($config)) $config = $this->config;
            if ($config['use_pdo'] == 'yes') {
                if (empty($this->dsn))
                    $this->dsn = $this->parseDsn($config);
                try {
                    $this->link = new \PDO($this->dsn, $config['user'], $config['password'], $this->options);
                } catch (\PDOException $e) {
                    die($e->getMessage());
                }
            } elseif ($config['use_pdo'] == 'no') {
                try {
                    $this->link = new \mysqli($config['host'], $config['user'], $config['password'], $config['dbname'], $config['port']);
                } catch (\Exception $e) {
                    die($e->getMessage());
                }
            }
        }
        return $this->link;
    }
    
    /**
     * ����������
     * 
     * @access public
     * @return void|boolean
     */
    public function startTransaction(){
        $this->initConnect();
        if(empty($this->link)) return false;
        //�����ǰ����������Ϊ0 ��������
        if($this->transnum == 0){
            $this->link->beginTransaction();
        }
        $this->transnum++; //ָ��������1
        return ;
    }
    
    /**
     * �ع�����
     * 
     * @access public
     * @return boolean
     */
    public function rollBack(){
        if($this->transnum > 0){
            //�������ָ��������0 ���ύ���� ���ҽ�����ָ������Ϊ0
            $res = $this->link->rollBack();
            $this->transnum = 0;
            if(!$res){
                return false;
            }
        }
        return true;
    }
    
    /**
     * �ύ����
     * 
     * @access public
     * @return boolean
     */
    public function commit(){
        if($this->transnum > 0){
            //�������ָ��������0 ���ύ���� ���ҽ�����ָ������Ϊ0
            $res = $this->link->commit();
            $this->transnum = 0;
            if(!$res){
                return false;
            }
        }
        return true;
    }
    
    protected function query($sql,$getsql = false){
        $this->initConnect(); //��ʼ������
        
        if(!$this->link) return false;
        $this->sql = $sql;
        
        if(!empty($this->bind)){
            $that = $this;
            $this->sql = strtr($this->sql,array_map(function($val) use($that){ return '\''.\Common::escapeString($val).'\''; },$this->bind));
        }
        if($getsql) return $this->sql;
        /*
         *���ǰ�εĲ�ѯ��û�ͷ� �����ͷ�
         */
        if(!empty($this->PDOstatement)) $this->free();
        /*
         * ׼��һ��Ԥ�������
        */
        $this->PDOstatement = $this->link->prepare($sql);
        if(false === $this->PDOstatement) return false;
        
        /*
         * �󶨲���
         */
        foreach($this->bind as $key=>$val){
            if(is_array($val)){
                //���$val�����飬���һ��Ԫ����ֵ���ڶ���Ԫ��������
                $this->PDOstatement->bindValue($key,$val[0],$val[1]);
            }else{
                $this->PDOstatement->bindValue($key, $val);
            }
        }
        //�ͷŲ����󶨱���
        $this->bind = array();
        //ִ�����
        $result = $this->PDOstatement->execute();
        
        if(false === $result) return false;
        else{
            $result = $this->PDOstatement->fetchAll(\PDO::FETCH_ASSOC);
            $this->affectnum = count($result);
            return $result;
        }
    }
    
    /**
     * ִ��sql���
     * @access protected
     * @param string $sql  ��Ҫִ�е����
     * @param string $fetsql  �Ƿ�ֻ�ǵõ�sql���
     * @return mixed
     */
    protected function execute($sql,$getsql = false){
        $this->initConnect(); //��ʼ������
        
        if(!$this->link) return false;
        $this->sql = $sql;
        if(!empty($this->bind)){
            $that = $this;
            $this->sql = strtr($this->sql,array_map(function($val) use($that){ return '\''.\Common::escapeString($val).'\''; },$this->bind));
        }
        if($getsql) return $this->sql;
        /*
         *���ǰ�εĲ�ѯ��û�ͷ� �����ͷ� 
         */
        if(!empty($this->PDOstatement)) $this->free();
        /*
         * ׼��һ��Ԥ�������
         */
        $this->PDOstatement = $this->link->prepare($sql);
        if(false === $this->PDOstatement) return false;
        
        /*
         * �󶨲���
         */
        foreach($this->bind as $key=>$val){
            if(is_array($val)){
                //���$val�����飬���һ��Ԫ����ֵ���ڶ���Ԫ��������
                $this->PDOstatement->bindValue($key,$val[0],$val[1]);
            }else{
                $this->PDOstatement->bindValue($key, $val);
            }
        }
        //�ͷŲ����󶨱���
        $this->bind = array();
        //ִ�����
        $result = $this->PDOstatement->execute();
        if(false === $result) return false;
        else{
            $this->affectnum = $this->PDOstatement->rowCount();
            if(preg_match("/^\s*(INSERT\s+INTO|REPLACE\s+INTO)\s+/i", $sql)) {
                $this->lastInsId = $this->link->lastInsertId();
            }
            return $this->affectnum;
        }
        
        
    }
    
    /**
     * �����ݿ�������ݺ���
     * @param array $data
     * @param array $options
     * @return integer
     */
    public function insert($data = array(),$options = array()){
        $values = $fields = array();
        $this->parseBind(isset($options['bind'])?$options['bind']:array());
        foreach($data as $key=>$val){
            $fields[] = trim($key);
            $name = count($this->bind);
            $values[] = ':'.$name;
            $this->bindParam($name,$val);
        }
        $sql = 'INSERT INTO '.$options['table'].'('.implode(',', $fields).') VALUES('.implode(',', $values).')';
        return $this->execute($sql);
        
    }
    
    public function delete($options=array()){
         $this->parseBind(isset($options['bind'])?$options['bind']:array());
         
    }
    
    /**
     * �󶨲���
     * @param string $name
     * @param string $val
     */
    protected function bindParam($name,$val){
        $this->bind[':'.$name] = $val;
    }
    
    /**
     * �����󶨵Ĳ���
     * @param array $bind
     */
    protected function parseBind($bind = array()){
        if(is_array($bind)){
            $this->bind = array_merge($this->bind,$bind);
        }
    }
    
    /**
     * �õ����������ݵ�id
     * @return string
     */
    public function lastInsId(){
        return $this->lastInsId;
    }
    /**
     * �õ����ݿ���Ӱ�������
     * @access public
     * @return int
     */
    public function getRowNum(){
        if(!empty($this->affectnum)) return $this->affectnum;
    }
    
    /**
     * ��ʼ�����ݿ�����
     */
    protected function initConnect(){
        $this->connect();
    }
    
    /**
     * ���ݿ�����
     * 
     * @param string $config
     */
    private function config($config = ''){
        $this->config = array(
            'host' =>   '127.0.0.1',
            'port' =>   '',
            'dbname'=> '',
            'user' => '',
            'password' =>'',
            'prefix'    =>'',
            'charset'   =>'utf8',
            'params'    => array(),
            'use_pdo'   => 'no'
        );
        if(!empty($config)){
            if(is_array($config))
                $this->config = array_merge($this->config,$config);
            if(is_array($this->config['params']))
                $this->options = $this->config['params'] + $this->options;
        }
    }
    
    /**
     * �������� �ر��ͷŲ�ѯ���ҹر�����
     */
    public function __destruct(){
        if($this->PDOstatement) $this->free();
        $this->close();
    }
    /**
     * �ͷŲ�ѯ
     */
    private function free(){
        $this->PDOstatement = null;
    }
    /**
     * �ر�����
     */
    public function close(){
        $this->link = null;
    }
}
