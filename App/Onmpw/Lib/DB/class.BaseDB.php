<?php
/**
 * 该类暂时未用到
 */
namespace Lib\DB;

abstract class BaseDB{

    protected $config = array();

    protected  $link = null;   //链接

    protected $_links = array();  //存储连接标识符

    protected $ignore = array();

    protected $bind = array();  //绑定参数

    protected $dsn;

    protected $options = array();

    protected $PDOStatement;

    protected $sql = '';

    protected $transNum = 0;  //事务指令数量

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
     * @param int $identify
     * @param bool $reconnect
     * @return bool|\mysqli|string|null
     */
    abstract protected function connect($config = '', $identify = 0, $reconnect = false);
    
    /**
     * 数据库配置
     * 
     * @param string $config
     */
    protected function config($config = ''){
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
     * 得到最后插入的数据的id
     */
    public function lastInsId()
    {
        return $this->lastInsId;
    }
    
    /**
     * 析构函数 关闭释放查询并且关闭链接
     */
    public function __destruct(){

    }
}
