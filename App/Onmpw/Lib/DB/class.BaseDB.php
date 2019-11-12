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
    
    protected $affectNum; //��¼Ӱ������ݵ�����
    
    private function __construct($config = ''){
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
    protected function connect($config = ''){
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

    }
}
