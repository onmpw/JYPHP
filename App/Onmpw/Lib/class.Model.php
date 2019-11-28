<?php

namespace Lib;

use Common;

class Model
{

    protected $options = array();

    protected $tablePrefix = ''; //���ݱ���ǰ׺

    protected $name = '';  //���ݱ�������

    protected $tbName = ''; //���ݱ�����

    protected $dbName = ''; //���ݿ�����

    protected $db = null;

    protected $data = array();   //��ӵ�����

    public function __construct($name = '', $tablePrefix = '')
    {
        //ģ�ͳ�ʼ��
        $this->__initialize();
        //���ģ������
        if (!empty($name)) {
            if (strpos($name, '.')) {
                list($this->dbName, $this->name) = explode('.', $name);
            } else {
                $this->name = $name;
            }
        } elseif (empty($this->name)) {

            $this->name = $this->getModelName();
        }
        /*
         * ���ñ�ǰ׺
         */
        if (is_null($tablePrefix)) {
            $this->tablePrefix = '';
        } elseif ('' != $tablePrefix) {
            $this->tablePrefix = $tablePrefix;
        } else {
            $this->tablePrefix = Common::C('DB_PREFIX');
        }
        //��ʼ�����ݿ����
        $this->db();
    }


    protected function __initialize()
    {
    }

    /**
     * �õ�ģ������
     * @access public
     * @return string
     */
    public function getModelName()
    {
        if (empty($this->name)) {
            $name = get_class($this);
            if ($pos = strrpos($name, '\\')) {
                $this->name = substr($name, $pos + 1);
            } else {
                $this->name = $name;
            }
        }
        return substr($this->name, 0, strrpos($this->name, 'Model'));
    }

    /**
     * ָ���ֶβ�ѯ
     * @access public
     * @param mixed $field Ҫ��ѯ���ֶ�
     * @param boolean $expect �Ƿ��ų������ֶβ�ѯ
     * @return Model
     */
    public function field($field, $expect = false)
    {
        if (empty($field)) {
            $fields = $this->getFields();
            $field = $fields ? $fields : '*';
        } elseif ($expect) {
            if (is_string($field)) {
                $field = explode(',', $field);
            }
            $fields = $this->getFields();
            $field = $fields ? array_diff($fields, $field) : $field;
        }
        $this->options['field'] = $field;
        return $this;
    }

    /**
     * where ��������
     * @access public
     * @param mixed $where �������ʽ
     * @param array $op where�������޶�
     * @return \Lib\Model
     */
    public function where($where, $op = array())
    {
        if (is_string($where) && '' != $where) {
            /* $where = strpos($where, ',')? array_map(array('\Common','escapeString'), explode(',', $where)):array($where);
            foreach($where as $key=>$val){
                list($k,$v) = strpos($val,'=')?explode('=', $val):false;
                $w[$k]=$v;
            }
            $where = array_filter($w); */
            $map = array();
            $map['_string'] = $where;
            $where = $map;

        } elseif (is_array($where)) {
            $where = array_map(array('\Common', 'escapeString'), $where);
        }
        if (isset($this->options['where'])) {
            $this->options['where'] = array_merge($this->options['where'], $where);
        } else {
            $this->options['where'] = $where;
        }
        if (isset($op['_operate'])) $this->options['_where_logic'] = $op['_operate'];
        return $this;
    }

    public function sql($sql = '')
    {
        if (empty($sql)) return false;
        return $this->db->sql($sql);
    }

    public function select_sql($sql)
    {
        if (empty($sql)) return array();
        $op = 'S';
        return $this->db->sql($sql, $op);
    }

    /**
     * limit��������
     * @access public
     * @param int $offset ��ʼλ��
     * @param string $length ����
     * @return Model
     */
    public function limit(int $offset , $length = null)
    {
        if (is_null($length) && strpos($offset, ',')) {
            list($offset, $length) = explode(',', $offset);
        }
        $this->options['limit'] = intval($offset) . ($length ? ',' . intval($length) : '');
        return $this;
    }

    /**
     * �õ����ݱ�����
     * @access public
     * @return string
     */
    public function getTableName()
    {
        if (!empty($this->tbName)) return $this->tbName;
        //�õ����е����ݱ�����
        $tables = $this->db->getTables($this->dbName);
        $name = false;
        //�ж�ʵ������ģ�������Ƿ��ڵõ������ݱ���
        for ($i = 0; $i < $this->db->getRowNum(); $i++) {
            if (strtolower($this->tablePrefix . $this->name) == strtolower($tables[$i])) {
                $name = $tables[$i];
            }
        }
        if (false === $name) return false;
        if (!empty($this->dbName)) {
            $tbName = $this->dbName . "." . $this->tablePrefix . $name;
        } else {
            $tbName = $this->tablePrefix . $name;
        }
        $this->options['table'] = $this->tbName = $tbName;
        return $this->tbName;
    }

    /**
     * ��ȡ���ݱ���ֶ�
     * @access public
     * @return unknown
     */
    public function getFields()
    {
        if (isset($this->options['table'])) {
            if (is_array($this->options['table'])) {
                $table = key($this->options['table']);
            } else {
                $table = $this->options['table'];
            }
//             $fields = $this->db->getFields($table);
        } elseif (!empty($this->tbName)) {
            $table = $this->tbName;
            $this->options['table'] = $table;
        } else {
            $table = $this->getTableName();
            $this->options['table'] = $table;
        }
        $fields = $this->db->getFields($table);
        return $fields;
    }

    public function add($data = '', $options = array())
    {
        if (empty($data)) {
            if (!empty($this->data)) {
                //��������
                $data = $this->data;
                $this->data = array();
            } else {
                die('error');
                return false;
            }
        }
        //��������
//         $data = $this->_parsedata();
        $options = $this->_parseOptions($options);
        $result = $this->db->insert($data, $options);
        if ($result !== false && is_numeric($result)) {
            $insertId = $this->lastInsId();
            if ($insertId) return $insertId;
            return false;
        }
        return $result;


    }

    private function _parseOptions($options = array())
    {

        //�õ��ֶ�
        $fields = $this->getFields();
        if (is_array($options)) {
            $options = array_merge($this->options, $options);
        }
        /*
         * ���where�������ֶ��Ƿ��ڱ���ֶ���
         */
        if (isset($this->options['where']) && is_array($this->options['where'])) {
            foreach ($this->options['where'] as $key => $val) {
                $key = trim($key);
                if (!in_array($key, array_keys($fields))) {
                    die('where error');
                }
            }
        }
        $this->options = array();
        return $options;
    }

    public function lastInsId()
    {
        return $this->db->lastInsId();
    }

    /**
     * ����������
     *
     * @access public
     * @return void
     */
    public function startTransaction()
    {
        //��������֮ǰ �Ƚ�֮ǰ���ύ
        $this->commit();
        $this->db->startTransaction();
        return;
    }

    /**
     * �ع�����
     *
     * @access public
     * @return boolean
     */
    public function rollBack()
    {
        return $this->db->rollBack();
    }

    /**
     * �ύ����
     *
     * @access public
     * @return boolean
     */
    public function Commit()
    {
        return $this->db->commit();
    }

    /**|
     * ��ʼ�����ݿ�����
     */
    private function db() : void
    {
        if (!empty($this->db)) return $this->db;
        $this->db = DB::getInstance();
        if (!empty($this->name)) $this->getTableName();
    }

    public function closeDb()
    {
        if(!is_null($this->db)){
            $this->db->close();
        }
    }


}