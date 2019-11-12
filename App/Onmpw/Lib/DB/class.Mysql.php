<?php

namespace Lib\DB;

use Inter\DB\IMysql;
use PDOException;
use PDO;

class Mysql extends BaseDB implements IMysql
{

    //修改部分
    public static $_instance; //静态属性，存储实例对象

    private $startTrans = false; //是否开启事务处理

    private $transLink;

    /**
     * 私有化构造函数，使用单例模式
     * @param string $config
     */
    private function __construct($config = '')
    {
        $this->config($config);
    }

    /**
     * 实例化对象
     * @access public static
     * @param string $options
     * @return Mysql
     */
    public static function Instance($options = '')
    {
        if (self::$_instance instanceof self) {
            return self::$_instance;
        }
        self::$_instance = new self($options);
        return self::$_instance;
    }

    public function getLinkId()
    {
        $this->parseConnect(false);
        return $this->link;
    }

    public function getLinks()
    {
        return $this->_links;
    }

    /**
     * 执行sql语句
     * @param string $sql
     * @access public
     * @return bool|mixed
     */
    public function sql($sql = '')
    {
        if (empty($sql)) return false;
        //判断是查询操作抑或是更新操作
        if (preg_match("/^\s*(SELECT|select\s)\s+/i", $sql)) {
            return $this->query($sql);
        } else {
            return $this->execute($sql);
        }
    }

    /**
     * 执行查询语句
     *
     * @param string $sql
     * @param bool $getSql
     * @return array
     */
    protected function query($sql, $getSql = false)
    {
        $result = $this->executeSql($sql,$getSql,false);
        if (false === $result){
            return [];
        } else {
            $fetchResult = $this->PDOStatement->fetchAll(\PDO::FETCH_ASSOC);
            $this->affectNum = count($fetchResult);
            return $fetchResult;
        }
    }

    /**
     * 执行增删改的语句
     *
     * @param string $sql
     * @param bool $getSql
     * @return mixed
     */
    protected function execute($sql, $getSql = false)
    {
        $result = $this->executeSql($sql,$getSql,true);
        if ($result === false) {
            return 0;
        } else {
            $this->affectNum = $this->PDOStatement->rowCount();
            if (preg_match("/^\s*(INSERT\s+INTO|REPLACE\s+INTO)\s+/i", $sql)) {
                $this->lastInsId = $this->link->lastInsertId();
            }
            return $this->affectNum;
        }
    }

    /**
     * 执行sql
     *
     * @param $sql
     * @param $getSql
     * @param $master
     * @return bool
     */
    private function executeSql($sql,$getSql,$master)
    {
        $this->parseConnect($master);
        if (empty($this->link)){
            return false;
        }

        if($getSql){
            $this->sql = $sql;
        }

        if (!empty($this->bind)) {
            $that = $this;
            $sql = strtr($this->sql, array_map(function ($val) use ($that) {
                return addslashes($val);
            }, $this->bind));
        }
//        if ($getSql) return $this->sql;

         // 释放上次执行的结果
        if (!empty($this->PDOStatement)){
            $this->free();
        }

         // 准备一条预处理语句
        $this->PDOStatement = $this->link->prepare($sql);
        if (false === $this->PDOStatement){
            return false;
        }

        // 绑定参数
        foreach ($this->bind as $key => $val) {
            if (is_array($val)) {
                $this->PDOStatement->bindValue($key, $val[0], $val[1]);
            } else {
                $this->PDOStatement->bindValue($key, $val);
            }
        }

        // 释放绑定的参数变量
        $this->bind = array();

        return $this->PDOStatement->execute();

    }

    /**
     * 绑定参数
     * @param string $key
     * @param mixed $val
     */
    private function bindParams($key, $val)
    {
        $this->bind[":" . $key] = $val;
    }

    /**
     * 解析绑定的参数,如果参数不为空则合并参数
     * @param array $bind
     */
    private function parseBind($bind = array())
    {
        if (is_array($bind)) {
            $this->bind = array_merge($this->bind, $bind);
        }
    }

    /**
     * 插入函数
     * @param array $data
     * @param array $options
     * @return mixed
     */
    protected function insert($data = array(), $options = array())
    {
        $values = $fields = array();
        $this->parseBind(isset($options['bind']) ? $options['bind'] : array());
        $this->parseData($data,$fields,$values);
        $sql = "INSERT INTO " . $this->options['table'] . "(" . implode(',', $fields) . ") VALUES (" . implode(',', $values) . ")";
        return $this->execute($sql);
    }

    /**
     * 更新函数
     * @param array $data
     * @param array $options
     * @return mixed
     */
    public function update($data = array(), $options = array())
    {
        $values = $fields = $set = array();
        if (is_array($options)) {
            $options = array_merge($options, $this->options);
            $this->table($options['table']);
        }
        $this->parseBind($options['bind'] ?? array());

        $this->parseData($data,$fields,$values);

        for ($i = 0; $i < count($fields); $i++) {
            $set[] = $fields[$i] . "=" . $values[$i];
        }
        $where = $this->parseWhere();
        $sql = "UPDATE " . $this->options['table'] . " SET " . implode(',', $set) . $where;
        return $this->execute($sql);
    }

    /**
     * 解析数据，并且设置field和value
     * @param $data
     * @param $fields
     * @param $values
     */
    private function parseData($data,&$fields,&$values)
    {
        foreach ($data as $key => $val) {
            $fields[] = $key;

            // 检测字段类型
            for ($i = 0; $i < count($this->options['fields']); $i++) {
                if ($this->options['fields'][$i]['field'] == $key) {
                    if (preg_match('/\w*(int|INT)$/i', $this->options['fields'][$i]['type'])) {
                        $values[] = ":" . $key;
                    } else {
                        $values[] = "':" . $key . "'";
                    }
                    break;
                }
            }
            //绑定参数
            $this->bindParams($key, $val);
        }
    }

    /**
     * 设置表名
     * @param string $table
     * @return bool|Mysql 返回当前对象
     */
    public function table($table = '')
    {
        if ($table == '') $table = $this->options['table'];
        $this->options['table'] = $table;
        if (!$this->parseFields()) return false;
        $this->close();
        return $this;
    }

    /**
     * 得到数据库中的数据表
     * @access public
     * @param string $dbname 指定数据库
     * @return array <boolean, string, unknown>
     */
    public function getTables($dbname = '')
    {
        $sql = !empty($dbname) ? "SHOW TABLES FROM " . $dbname : "SHOW TABLES";
        $result = $this->query($sql);
        $tables = array();
        foreach ($result as $key => $val) {
            $tables[$key] = current($val);
        }
        return $tables;
    }

    /**
     * 得到数据库受影响的行数
     * @access public
     * @return int
     */
    public function getRowNum()
    {
        if (!empty($this->affectNum)) return $this->affectNum;
    }

    /**
     * 设置要查询的表字段，如果没有设置，则默认查询表的所有字段
     * @param string $field
     * @return Mysql 返回当前对象
     */
    public function field($field = '')
    {
        /* if(!empty($field)){ 
            $f = array();
            foreach($this->options['fields'] as $key=>$val){
                $f[] = $val['field'];
            }
            $field = implode(',', $f);
        } */
        if (!empty($field)) $this->options['field'] = $field;
        return $this;
    }

    /**
     * where 条件设置
     * @param string $where
     * @return Mysql
     */
    public function where($where = '')
    {
        if (is_string($where)) $this->options['where'] = $where;
        elseif (is_array($where)) {
            $w = '';
            foreach ($where as $key => $val) {
                $w .= $key . "=" . addslashes($val) . " and ";
            }
            $where = rtrim($w, ' and');
            $this->options['where'] = $where;
        }
        return $this;
    }

    /**
     * 查询多条数据函数
     * @param array $options
     * @return array <mixed, boolean, string, string, unknown>
     */
    public function select($options = array())
    {
        $this->parseBind(isset($options['bind']) ? $options['bind'] : array());
        /*
         * 判断是否有分页
         */
        if (isset($options['page'])) {
            $this->limit($options['page']);
        }
        $sql = $this->buildSql($options);
        $result = $this->query($sql);
        return $result;
    }

    /**
     * 查找单条数据
     * @param array $options
     * @return boolean|unknown
     */
    public function find($options = array())
    {
        $this->parseBind(isset($options['bind']) ? $options['bind'] : array());
        /*
         * 判断是否有分页
        */
        if (isset($options['page'])) {
            $this->limit($options['page']);
        }
        $sql = $this->buildSql($options);
        $result = $this->query($sql);
        if (empty($result)) return false;
        $result = $result[0];
        return $result;
    }

    /**
     * 新增数据
     * @param array $data
     * @param array $options
     * @return boolean|Ambigous <mixed, boolean, string, string>
     */
    public function add($data = array(), $options = array())
    {
        if (isset($options['table']))
            $this->table($options['table']);
        if (!is_array($data)) return false;
        $res = $this->insert($data, $options);
        return $res;
    }

    /**
     * 一次性插入多条数据，支持不同表的插入
     * 当使用多表插入功能时需要在第二个参数中指定 $options['multitable'] = true
     * 并且$data的格式为
     * array(
     *  '表名1'=>array(array(),array()),
     *  '表名2'=>array(array(),array())
     * )
     * @param array $data
     * @param array $options
     * @return boolean
     */
    public function addMore($data = array(), $options = array())
    {
        if (isset($options['table']))
            $this->table($options['table']);
        if (!is_array($data)) return false;
        /*
         * 开启事务处理多条语句
         */
//         $this->startTransaction();
        foreach ($data as $key => $val) {
            //查看是否是多表插入
            if (isset($options['multitable']) && $options['multitable']) {
                /*
                 * 多表插入，则$key为表名,$val为要插入的数据
                 * 使用递归的方式再次对多条数据进行插入
                 */
                $res = $this->addMore($val, array('table' => $key));
            } else {
                //单表插入
                $res = $this->add($val);
            }
            if (!$res) {
                //如果有一条数据插入失败，则回滚事务，撤销所有的操作
//                 $this->rollback();
                return false;
            }
        }
        //如果所有插入操作无误，则提交事务
        $this->commit();
        return true;
    }

    /**
     * 删除数据函数
     * @param array $options
     * @return Ambigous <mixed, boolean, string, string>
     */
    public function delete($options = array())
    {
        if (isset($options['table'])) {
            $this->table($options['table']);
        }
        $low_priority = isset($options['low_priority']) ? $options['low_priority'] : '';
        $quick = isset($options['quick']) ? $options['quick'] : '';
        $ignore = isset($options['ignore']) ? $options['ignore'] : '';
        $where = $this->parseWhere();
        $order = $this->parseOrder();
        $limit = $this->parselimit();
        $sql = "DELETE FROM {$low_priority} {$quick} {$ignore}" . $this->options['table'] . $where . $order . $limit;
        return $this->execute($sql);
    }

    /**
     * 解析limit函数
     * @return string
     */
    private function parseLimit()
    {
        $limit = '';
        if (isset($this->options['limit']) && !empty($this->options['limit'])) {
            $order = " LIMIT " . $this->options['limit'];
            $this->options['limit'] = '';
        }
        return $limit;
    }

    /**
     * 构建sql语句
     * @param array $options
     * @return string
     */
    public function buildSql($options = array())
    {
        if (is_array($options)) {
            $options = array_merge($options, $this->options);
        }
        $where = $this->parseWhere();
        $order = $this->parseOrder();
        $limit = $this->parseLimit();
        $sql = 'SELECT ' . $options['field'] . ' FROM ' . $options['table'] . ' ' . $where . $order . $limit;
        return $sql;
    }

    /**
     * 选择排列顺序
     * @param string $order
     * @return Mysql
     */
    public function orderBy($order = '')
    {
        $this->options['order'] = $order;
        return $this;
    }

    /**
     * limit设置函数
     * @param string $limit
     * @return Mysql
     */
    public function limit($limit = '')
    {
        if (is_array($limit)) {
            list($page, $listRows) = $limit;
            $page = $page > 0 ? $page : 1;
            $listRows = $listRows > 0 ? $listRows : 20;
            $offset = $listRows * ($page - 1);
            $this->options['limit'] = $offset . "," . $listRows;
        } elseif (is_string($limit)) {
            $this->options['limit'] = $limit;
        }
        return $this;
    }

    /**
     * 解析表字段
     * @param string $table
     * @return boolean
     */
    public function parseFields($table = '')
    {
        if (empty($table)) $table = $this->options['table'];
        $sql = 'SHOW COLUMNS FROM ' . $table;
        $res = $this->query($sql);
        if (empty($res)) return false;
        $fields = array();
        if (is_array($res)) {
            foreach ($res as $key => $val) {
                array_push($fields, array('field' => $val['Field'], 'isnull' => $val['Null'], 'type' => $val['Type']));
            }
            $this->options['fields'] = $fields;
        }
        $f = [];
        foreach ($this->options['fields'] as $key => $val) {
            $f[] = $val['field'];
        }
        $this->options['field'] = implode(',', $f);
        return true;

    }

    /**
     * 解析where函数
     * @return string
     */
    private function parseWhere()
    {
        $where = '';
        if (isset($this->options['where']) && !empty($this->options['where'])) {
            $where = " WHERE " . $this->options['where'];
            $this->options['where'] = '';
        }
        return $where;
    }

    /**
     * 解析order函数
     * @return string
     */
    private function parseOrder()
    {
        $order = '';
        if (isset($this->options['order']) && !empty($this->options['order'])) {
            $order = " ORDER BY " . $this->options['order'];
            $this->options['order'] = '';
        }
        return $order;
    }

    /**
     * 分配连接
     * @param bool $master 主服务器操作还是从服务器操作
     * @return void
     */
    private function parseConnect($master = true)
    {
        if ($this->config['deploy_type'] == 1) {  //分布式部署
            $this->link = $this->multiConnect($master);
        } else {
            $this->link = $this->connect();
        }

        // 如果开启了事务，那么将连接资源保存起来
        if ($this->startTrans && $master) $this->transLink = $this->link;
        return;
    }

    /**
     * 数据库连接函数
     *
     * @param string $config
     * @param int $identify
     * @param bool $reconnect
     *
     * @return string|boolean|multitype:
     */
    protected function connect($config = '', $identify = 0, $reconnect = false)
    {
        if(!isset($this->_links[$identify])) {
            if (empty($config)) {
                $config = $this->config;
            }

            if ($config['use_pdo'] == 'yes') {
                if (empty($config['dsn'])) {
                    $config = $this->parseDsn($config);
                }
                try {
                    $this->_links[$identify] = new \PDO($config['dsn'], $config['user'], $config['password'], $this->options);
                } catch (\PDOException $e) {
                    if ($reconnect)
                        return "reconnect";
                    else
                        return false;
                }
            } elseif ($config['use_pdo'] == 'no') {
                try {
                    $this->_links[$identify] = new \mysqli($config['host'], $config['user'], $config['password'], $config['dbname'], $config['port']);
                } catch (\Exception $e) {
                    if ($reconnect)
                        return "reconnect";
                    else
                        return false;
                }
            }
        }
        return $this->_links[$identify];
    }

    /**
     * 分布式数据库连接
     * @param bool $master 主服务器操作还是从服务器操作
     * @return mixed
     */
    private function multiConnect($master = false)
    {
        $config['host'] = explode(',', $this->config['host']);
        $config['dbname'] = explode(',', $this->config['dbname']);
        $config['port'] = empty($this->config['port']) ? null : explode(',', $this->config['port']);
        $config['user'] = explode(',', $this->config['user']);
        $config['password'] = explode(',', $this->config['password']);
        $config['dsn'] = explode(',', $this->config['dsn']);
        /*
         * 随机获取一个主服务器的下标
         * 为了保证多个主服务器在其中一个宕机以后，程序自动连接其他的服务器
         * 需要循环获取主服务器下标，如果取出的下标在宕机的服务器表中则继续循环取下标
         * 直到不在宕机列表中，当然如果循环的次数超过一定量，我们可以认为服务器连接出现异常，返回false
         * 
         */
        $count = 0;
        $flag = false;
        do {
            $m = $s = floor(mt_rand(0, $this->config['master_num'] - 1));
            if (!in_array($m, $this->ignore)) {
                $flag = true;
                break;
            }
            $count++;
        } while (count($this->ignore) < $this->config['master_num']);
        if ($flag === false) return false;

        //判断是读还是写
        if ($master) { //$master为true 表示数据更新
            /*
             * 如果事务数量大于0 说明已经开启了事务，接下来的更新操作要在当前连接上进行
             * 如果重新连接可能会连接不同的服务器
             */
            if ($this->transNum > 0) {
                $this->link = $this->transLink;
                return $this->transLink;
            }
            $db = $this->buildConnectDb($config,$m);
        } else { //读操作
            /*
             * 判断是否是读写分离
             */
            if ($this->config['rw_separate']) {  //读写分离
                $count = 0;
                $flag = false;
                do {
                    $s = floor(mt_rand($this->config['master_num'], count($config['host']) - 1));
                    if (!in_array($s, $this->ignore)) {
                        $flag = true;
                        break;
                    }
                    $count++;
                } while (count($this->ignore) < count($config['host']) - $this->config['master_num']);
                if (false === $flag) return false;
            } else {
                //读写不分离
                $count = 0;
                $flag = false;
                do {
                    $s = floor(mt_rand(0, count($config['host']) - 1));
                    if (!in_array($s, $this->ignore)) {
                        $flag = true;
                        break;
                    }
                    $count++;
                } while (count($this->ignore) < count($config['host']));
                if (false === $flag) return false;
            }
            $db = $this->buildConnectDb($config,$s);
        }

         // 连接数据库
        $identify = $master === true ? $m : $s;
        $res = parent::connect($db, $identify, true);
        if ($res === false) {
            return false;
        } elseif ($res == 'reconnect') {
            array_push($this->ignore, $identify);
            $this->link = $this->multiConnect($master);
        } else {
            return $res;
        }
        return $this->link;
    }

    /**
     * 构建连接的db
     * @param $config
     * @param $host
     * @return array
     */
    private function buildConnectDb($config,$host)
    {
        $db = array(
            'host' => isset($config['host'][$host]) ? $config['host'][$host] : $config['host'][0],
            'dbname' => isset($config['dbname'][$host]) ? $config['dbname'][$host] : $config['dbname'][0],
            'port' => isset($config['port'][$host]) ? $config['port'][$host] : $config['port'][0],
            'user' => isset($config['user'][$host]) ? $config['user'][$host] : $config['user'][0],
            'password' => isset($config['password'][$host]) ? $config['password'][$host] : $config['password'][0],
            'dsn' => isset($config['dsn'][$host]) ? $config['dsn'][$host] : $config['dsn'][0],
        );
        return $db;
    }

    /**
     * 解析dsn
     * @param string $config
     * @return array
     */
    protected function parseDsn($config = '')
    {
        if (empty($config)) $config = $this->config;
        $dsn = array(
            'type' => $this->config['type'],
            'host' => $config['host'],
            'dbname' => $config['dbname'],
            'port' => $config['port'],
            'user' => $config['user'],
            'password' => $config['password'],
        );
        $dsn['dsn'] = $dsn['type'] . ":dbname={$dsn['dbname']};host={$dsn['host']}";
        if (!empty($config['port'])) {
            $dsn['dsn'] = $dsn['dsn'] . ";port={$config['port']}";
        }
        $dsn['dsn'] = $dsn['dsn'] . ";charset=utf8";
        return $dsn;
    }

    /**
     * 开启事务
     * @access public
     * @return void|boolean
     */
    public function startTransaction()
    {
        $this->startTrans = true;
        $this->parseConnect();
        if (empty($this->link)) return false;
        if ($this->transNum == 0)
            $this->link->beginTransaction();
        $this->transNum++;
        return;
    }

    /**
     * 回滚事务
     * @access public
     * @return boolean
     */
    public function rollback()
    {
        if ($this->transNum > 0) {
            //如果事务指令数大于0 则回滚事务 并且将事务指令数置为0
            $res = $this->link->rollBack();
            $this->transNum = 0;
            $this->startTrans = false;
            if (!$res) {
                return false;
            }
        }
        return true;
    }

    /**
     * 提交事务
     * @access public
     * @return boolean
     */
    public function commit()
    {
        if ($this->transNum > 0) {
            //如果事务指令数大于0 则提交事务 并且将事务指令数置为0
            $res = $this->link->commit();
            $this->transNum = 0;
            $this->startTrans = false;
            if (!$res) {
                return false;
            }
        }
        return true;
    }

    /**
     * 释放查询
     */
    private function free()
    {
        $this->PDOStatement = null;
    }

    /**
     * 关闭连接
     */
    private function close()
    {
        $this->link = null;
    }

}
