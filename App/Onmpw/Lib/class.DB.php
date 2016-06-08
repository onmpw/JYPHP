<?php
namespace Lib;

class DB{
    
    private $config = array();
    
    private static $instance = array();
    private static $_instance = null;
    
    private function __construct(){}
    
    /**
     * 
     * @param array $config   ���ݿ�������Ϣ
     * @return multitype:
     */
    public static function getInstance($config = array()){
        $md5 = md5(serialize($config));
        if(!isset(self::$instance[$md5])){
            $options = self::parseConfig($config);
            /*
             * ���� mysqli  �����������Ϊmysqli ת��Ϊmysql
             */
            if('mysqli' == $options['type']) $options['type']   =   'mysql';
            
            $class = 'Lib\\DB\\'.ucwords(strtolower($options['type']));
            if(class_exists($class)){
                self::$instance[$md5] = $class::Instance($options);
            }else{
                die($class." is not exists!");
            }
            
        }
        self::$_instance = self::$instance[$md5];
        return self::$_instance;
    }
    
    private static function parseConfig($config){
        if(!empty($config)){
            $config = array_change_key_case($config);
            $config = array(
                'type' => $config['db_type'],
                'host'  => $config['db_host'],
                'user'  => $config['db_user'],
                'password'=> $config['db_password'],
                'dbname' => $config['db_dbname'],
                'port'  => $config['db_port'],
                'charset'       =>  isset($config['db_charset'])?$config['db_charset']:'utf8',
                'use_pdo'   =>$config['use_pdo'],
                'slave_no'=>$config['slave_no'],    //ָ���ӷ����������ж�����
                'master_num'=>$config['master_num'],    //��������������
                'deploy_type'=>$config['deploy_type'],   //���ݿⲿ��ʽ��1 ��ʾ���ӷ���   0 ��ʾ��һ������
                'rw_seprate'=>$config['rw_seprate'],    //��д�Ƿ����
            );
        }else{
            $config = array(
                'type' => \Common::C('DB_TYPE'),
                'host'  => \Common::C('DB_HOST'),
                'user'  => \Common::C('DB_USER'),
                'password'=> \Common::C('DB_PASSWORD'),
                'dbname' => \Common::C('DB_DBNAME'),
                'port'  => \Common::C('DB_PORT'),
                'charset' => \Common::C('DB_CHARSET') ,
                'use_pdo' => \Common::C('USE_PDO'),
                'slave_no'=> \Common::C('SLAVE_NO'),    //ָ���ӷ����������ж�����
                'master_num'=>\Common::C('MASTER_NUM'),    //��������������
                'deploy_type'=>\Common::C('DEPLOY_TYPE'),   //���ݿⲿ��ʽ��1 ��ʾ���ӷ���   0 ��ʾ��һ������
                'rw_seprate'=>\Common::C('RW_SEPRATE'),    //��д�Ƿ����
            );
        }
        return $config;
    }
}