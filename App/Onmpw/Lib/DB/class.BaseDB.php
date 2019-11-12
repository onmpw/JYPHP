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
    
    protected $affectNum; //记录影响的数据的条数
    
    private function __construct($config = ''){
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
    protected function connect($config = ''){
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

    }
}
