<?php
/**
 * ������ʱδ�õ�
 */
namespace Lib\DB;

abstract class BaseDB{

    protected $config = array();

    protected  $link = null;   //����

    protected $_links = array();  //�洢���ӱ�ʶ��

    protected $ignore = array();

    protected $bind = array();  //�󶨲���

    protected $dsn;

    protected $options = array();

    protected $PDOStatement;

    protected $sql = '';

    protected $transNum = 0;  //����ָ������

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
     * @param int $identify
     * @param bool $reconnect
     * @return bool|\mysqli|string|null
     */
    abstract protected function connect($config = '', $identify = 0, $reconnect = false);
    
    /**
     * ���ݿ�����
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
     * �õ�����������ݵ�id
     */
    public function lastInsId()
    {
        return $this->lastInsId;
    }
    
    /**
     * �������� �ر��ͷŲ�ѯ���ҹر�����
     */
    public function __destruct(){

    }
}
