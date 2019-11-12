<?php

namespace Lib\DB;

use Inter\DB\IMysql;
use PDOException;
use PDO;

class Mysql extends BaseDB implements IMysql
{

    //�޸Ĳ���
    public static $_instance; //��̬���ԣ��洢ʵ������

    private $startTrans = false; //�Ƿ���������

    private $transLink;

    /**
     * ˽�л����캯����ʹ�õ���ģʽ
     * @param string $config
     */
    private function __construct($config = '')
    {
        $this->config($config);
    }

    /**
     * ʵ��������
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
     * ִ��sql���
     * @param string $sql
     * @access public
     * @return bool|mixed
     */
    public function sql($sql = '')
    {
        if (empty($sql)) return false;
        //�ж��ǲ�ѯ�����ֻ��Ǹ��²���
        if (preg_match("/^\s*(SELECT|select\s)\s+/i", $sql)) {
            return $this->query($sql);
        } else {
            return $this->execute($sql);
        }
    }

    /**
     * ִ�в�ѯ���
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
     * ִ����ɾ�ĵ����
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
     * ִ��sql
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

         // �ͷ��ϴ�ִ�еĽ��
        if (!empty($this->PDOStatement)){
            $this->free();
        }

         // ׼��һ��Ԥ�������
        $this->PDOStatement = $this->link->prepare($sql);
        if (false === $this->PDOStatement){
            return false;
        }

        // �󶨲���
        foreach ($this->bind as $key => $val) {
            if (is_array($val)) {
                $this->PDOStatement->bindValue($key, $val[0], $val[1]);
            } else {
                $this->PDOStatement->bindValue($key, $val);
            }
        }

        // �ͷŰ󶨵Ĳ�������
        $this->bind = array();

        return $this->PDOStatement->execute();

    }

    /**
     * �󶨲���
     * @param string $key
     * @param mixed $val
     */
    private function bindParams($key, $val)
    {
        $this->bind[":" . $key] = $val;
    }

    /**
     * �����󶨵Ĳ���,���������Ϊ����ϲ�����
     * @param array $bind
     */
    private function parseBind($bind = array())
    {
        if (is_array($bind)) {
            $this->bind = array_merge($this->bind, $bind);
        }
    }

    /**
     * ���뺯��
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
     * ���º���
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
     * �������ݣ���������field��value
     * @param $data
     * @param $fields
     * @param $values
     */
    private function parseData($data,&$fields,&$values)
    {
        foreach ($data as $key => $val) {
            $fields[] = $key;

            // ����ֶ�����
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
            //�󶨲���
            $this->bindParams($key, $val);
        }
    }

    /**
     * ���ñ���
     * @param string $table
     * @return bool|Mysql ���ص�ǰ����
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
     * �õ����ݿ��е����ݱ�
     * @access public
     * @param string $dbname ָ�����ݿ�
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
     * �õ����ݿ���Ӱ�������
     * @access public
     * @return int
     */
    public function getRowNum()
    {
        if (!empty($this->affectNum)) return $this->affectNum;
    }

    /**
     * ����Ҫ��ѯ�ı��ֶΣ����û�����ã���Ĭ�ϲ�ѯ��������ֶ�
     * @param string $field
     * @return Mysql ���ص�ǰ����
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
     * where ��������
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
     * ��ѯ�������ݺ���
     * @param array $options
     * @return array <mixed, boolean, string, string, unknown>
     */
    public function select($options = array())
    {
        $this->parseBind(isset($options['bind']) ? $options['bind'] : array());
        /*
         * �ж��Ƿ��з�ҳ
         */
        if (isset($options['page'])) {
            $this->limit($options['page']);
        }
        $sql = $this->buildSql($options);
        $result = $this->query($sql);
        return $result;
    }

    /**
     * ���ҵ�������
     * @param array $options
     * @return boolean|unknown
     */
    public function find($options = array())
    {
        $this->parseBind(isset($options['bind']) ? $options['bind'] : array());
        /*
         * �ж��Ƿ��з�ҳ
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
     * ��������
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
     * һ���Բ���������ݣ�֧�ֲ�ͬ��Ĳ���
     * ��ʹ�ö����빦��ʱ��Ҫ�ڵڶ���������ָ�� $options['multitable'] = true
     * ����$data�ĸ�ʽΪ
     * array(
     *  '����1'=>array(array(),array()),
     *  '����2'=>array(array(),array())
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
         * ����������������
         */
//         $this->startTransaction();
        foreach ($data as $key => $val) {
            //�鿴�Ƿ��Ƕ�����
            if (isset($options['multitable']) && $options['multitable']) {
                /*
                 * �����룬��$keyΪ����,$valΪҪ���������
                 * ʹ�õݹ�ķ�ʽ�ٴζԶ������ݽ��в���
                 */
                $res = $this->addMore($val, array('table' => $key));
            } else {
                //�������
                $res = $this->add($val);
            }
            if (!$res) {
                //�����һ�����ݲ���ʧ�ܣ���ع����񣬳������еĲ���
//                 $this->rollback();
                return false;
            }
        }
        //������в�������������ύ����
        $this->commit();
        return true;
    }

    /**
     * ɾ�����ݺ���
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
     * ����limit����
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
     * ����sql���
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
     * ѡ������˳��
     * @param string $order
     * @return Mysql
     */
    public function orderBy($order = '')
    {
        $this->options['order'] = $order;
        return $this;
    }

    /**
     * limit���ú���
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
     * �������ֶ�
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
     * ����where����
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
     * ����order����
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
     * ��������
     * @param bool $master ���������������Ǵӷ���������
     * @return void
     */
    private function parseConnect($master = true)
    {
        if ($this->config['deploy_type'] == 1) {  //�ֲ�ʽ����
            $this->link = $this->multiConnect($master);
        } else {
            $this->link = $this->connect();
        }

        // ���������������ô��������Դ��������
        if ($this->startTrans && $master) $this->transLink = $this->link;
        return;
    }

    /**
     * ���ݿ����Ӻ���
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
     * �ֲ�ʽ���ݿ�����
     * @param bool $master ���������������Ǵӷ���������
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
         * �����ȡһ�������������±�
         * Ϊ�˱�֤�����������������һ��崻��Ժ󣬳����Զ����������ķ�����
         * ��Ҫѭ����ȡ���������±꣬���ȡ�����±���崻��ķ��������������ѭ��ȡ�±�
         * ֱ������崻��б��У���Ȼ���ѭ���Ĵ�������һ���������ǿ�����Ϊ���������ӳ����쳣������false
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

        //�ж��Ƕ�����д
        if ($master) { //$masterΪtrue ��ʾ���ݸ���
            /*
             * ���������������0 ˵���Ѿ����������񣬽������ĸ��²���Ҫ�ڵ�ǰ�����Ͻ���
             * ����������ӿ��ܻ����Ӳ�ͬ�ķ�����
             */
            if ($this->transNum > 0) {
                $this->link = $this->transLink;
                return $this->transLink;
            }
            $db = $this->buildConnectDb($config,$m);
        } else { //������
            /*
             * �ж��Ƿ��Ƕ�д����
             */
            if ($this->config['rw_separate']) {  //��д����
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
                //��д������
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

         // �������ݿ�
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
     * �������ӵ�db
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
     * ����dsn
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
     * ��������
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
     * �ع�����
     * @access public
     * @return boolean
     */
    public function rollback()
    {
        if ($this->transNum > 0) {
            //�������ָ��������0 ��ع����� ���ҽ�����ָ������Ϊ0
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
     * �ύ����
     * @access public
     * @return boolean
     */
    public function commit()
    {
        if ($this->transNum > 0) {
            //�������ָ��������0 ���ύ���� ���ҽ�����ָ������Ϊ0
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
     * �ͷŲ�ѯ
     */
    private function free()
    {
        $this->PDOStatement = null;
    }

    /**
     * �ر�����
     */
    private function close()
    {
        $this->link = null;
    }

}
