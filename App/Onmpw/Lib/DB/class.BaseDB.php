<?php
/**
 * 该类暂时未用到
 */
namespace Lib\DB;

abstract class BaseDB{
    
    protected $config = array();
    
    protected  $link = null;   //链接
    
    protected $options = array();
    
    protected $dsn = '';  //数据库链接 dsn信息
    
    protected $PDOstatement = null;
    
    protected $transnum = 0;  //事务指令数量
    
    protected $sql = '';
    
    protected $bind = array();
    
    protected $lastInsId;  //记录插入数据时最后插入的一条记录的id
    
    protected $affectnum; //记录影响的数据的条数
    
    public function __construct($config = ''){
        $this->config($config);
    }
    
    /**
     * 解析数据库链接dsn
     * 
     * @param unknown $config
     */
    abstract protected function parseDsn($config);
    
    /**
     * 数据库链接函数
     * 
     * @param string $config
     * 
     * @return \PDO
     * 
     */
    public function connect($config = ''){
        //判断是否已经链接 如果没有链接那么进行链接
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
     * 开启事务处理
     * 
     * @access public
     * @return void|boolean
     */
    public function startTransaction(){
        $this->initConnect();
        if(empty($this->link)) return false;
        //如果当前的事务数量为0 则开启事务
        if($this->transnum == 0){
            $this->link->beginTransaction();
        }
        $this->transnum++; //指令数量加1
        return ;
    }
    
    /**
     * 回滚事务
     * 
     * @access public
     * @return boolean
     */
    public function rollBack(){
        if($this->transnum > 0){
            //如果事务指令数大于0 则提交事务 并且将事务指令数置为0
            $res = $this->link->rollBack();
            $this->transnum = 0;
            if(!$res){
                return false;
            }
        }
        return true;
    }
    
    /**
     * 提交事务
     * 
     * @access public
     * @return boolean
     */
    public function commit(){
        if($this->transnum > 0){
            //如果事务指令数大于0 则提交事务 并且将事务指令数置为0
            $res = $this->link->commit();
            $this->transnum = 0;
            if(!$res){
                return false;
            }
        }
        return true;
    }
    
    protected function query($sql,$getsql = false){
        $this->initConnect(); //初始化链接
        
        if(!$this->link) return false;
        $this->sql = $sql;
        
        if(!empty($this->bind)){
            $that = $this;
            $this->sql = strtr($this->sql,array_map(function($val) use($that){ return '\''.\Common::escapeString($val).'\''; },$this->bind));
        }
        if($getsql) return $this->sql;
        /*
         *如果前次的查询还没释放 则将其释放
         */
        if(!empty($this->PDOstatement)) $this->free();
        /*
         * 准备一条预处理语句
        */
        $this->PDOstatement = $this->link->prepare($sql);
        if(false === $this->PDOstatement) return false;
        
        /*
         * 绑定参数
         */
        foreach($this->bind as $key=>$val){
            if(is_array($val)){
                //如果$val是数组，则第一个元素是值，第二个元素是类型
                $this->PDOstatement->bindValue($key,$val[0],$val[1]);
            }else{
                $this->PDOstatement->bindValue($key, $val);
            }
        }
        //释放参数绑定变量
        $this->bind = array();
        //执行语句
        $result = $this->PDOstatement->execute();
        
        if(false === $result) return false;
        else{
            $result = $this->PDOstatement->fetchAll(\PDO::FETCH_ASSOC);
            $this->affectnum = count($result);
            return $result;
        }
    }
    
    /**
     * 执行sql语句
     * @access protected
     * @param string $sql  将要执行的语句
     * @param string $fetsql  是否只是得到sql语句
     * @return mixed
     */
    protected function execute($sql,$getsql = false){
        $this->initConnect(); //初始化链接
        
        if(!$this->link) return false;
        $this->sql = $sql;
        if(!empty($this->bind)){
            $that = $this;
            $this->sql = strtr($this->sql,array_map(function($val) use($that){ return '\''.\Common::escapeString($val).'\''; },$this->bind));
        }
        if($getsql) return $this->sql;
        /*
         *如果前次的查询还没释放 则将其释放 
         */
        if(!empty($this->PDOstatement)) $this->free();
        /*
         * 准备一条预处理语句
         */
        $this->PDOstatement = $this->link->prepare($sql);
        if(false === $this->PDOstatement) return false;
        
        /*
         * 绑定参数
         */
        foreach($this->bind as $key=>$val){
            if(is_array($val)){
                //如果$val是数组，则第一个元素是值，第二个元素是类型
                $this->PDOstatement->bindValue($key,$val[0],$val[1]);
            }else{
                $this->PDOstatement->bindValue($key, $val);
            }
        }
        //释放参数绑定变量
        $this->bind = array();
        //执行语句
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
     * 向数据库插入数据函数
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
     * 绑定参数
     * @param string $name
     * @param string $val
     */
    protected function bindParam($name,$val){
        $this->bind[':'.$name] = $val;
    }
    
    /**
     * 解析绑定的参数
     * @param array $bind
     */
    protected function parseBind($bind = array()){
        if(is_array($bind)){
            $this->bind = array_merge($this->bind,$bind);
        }
    }
    
    /**
     * 得到最后插入数据的id
     * @return string
     */
    public function lastInsId(){
        return $this->lastInsId;
    }
    /**
     * 得到数据库受影响的行数
     * @access public
     * @return int
     */
    public function getRowNum(){
        if(!empty($this->affectnum)) return $this->affectnum;
    }
    
    /**
     * 初始化数据库链接
     */
    protected function initConnect(){
        $this->connect();
    }
    
    /**
     * 数据库配置
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
     * 析构函数 关闭释放查询并且关闭链接
     */
    public function __destruct(){
        if($this->PDOstatement) $this->free();
        $this->close();
    }
    /**
     * 释放查询
     */
    private function free(){
        $this->PDOstatement = null;
    }
    /**
     * 关闭连接
     */
    public function close(){
        $this->link = null;
    }
}
