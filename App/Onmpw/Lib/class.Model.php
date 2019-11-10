<?php
namespace Lib;

class Model{
    
    protected  $options = array();
    
    protected $tablePrefix = ''; //数据表名前缀
    
    protected $name = '';  //数据表主名称
    
    protected $tbname = ''; //数据表名称
    
    protected $dbname = ''; //数据库名称
    
    protected $db = null;
    
    protected $data = array();   //添加的数据
    
    public function __construct($name = '',$tablePrefix = ''){
        //模型初始化
        $this->__initialize();
        //获得模型名称
        if(!empty($name)){
            if(strpos($name, '.')){
                list($this->dbname,$this->name) = explode('.',$name);
            }else{
                $this->name = $name;
            }
        }elseif(empty($this->name)){
            
            $this->name = $this->getModelName();
        }
        /*
         * 设置表前缀
         */
        if(is_null($tablePrefix)){
            $this->tablePrefix = '';
        }elseif('' != $tablePrefix){
            $this->tablePrefix = $tablePrefix;
        }else{
            $this->tablePrefix = \Common::C('DB_PREFIX');
        }
        //初始化数据库操作
        $this->db();
    }
    
    
    protected function __initialize(){}
    
    /**
     * 得到模型名称
     * @access public
     * @return string
     */
    public function getModelName(){
        if(empty($this->name)){
            $name = get_class($this);
            if($pos = strrpos($name,'\\')){
                $this->name = substr($name,$pos+1);
            }else{
                $this->name = $name;
            }
        }
        return substr($this->name,0,strrpos($this->name,'Model'));
    }

    /**
     * 指定字段查询
     * @access public
     * @param mixed $field 要查询的字段
     * @param boolean $expect 是否排除以上字段查询
     * @return Model
     */
    public function field($field,$expect=false){
        if(empty($field)){
            $fields = $this->getFields();
            $field = $fields?$fields:'*';
        }elseif($expect){
            if(is_string($field)){
                $field = explode(',',$field);
            }
            $fields = $this->getFields();
            $field = $fields ? array_diff($fields,$field) : $field;
        }
        $this->options['field'] = $field;
        return $this;
    }
    /**
     * where 条件分析
     * @access public
     * @param mixed $where 条件表达式
     * @param array $op   where条件的限定
     * @return \Lib\Model
     */
    public function where($where,$op = array()){
        if(is_string($where) && '' != $where){
            /* $where = strpos($where, ',')? array_map(array('\Common','escapeString'), explode(',', $where)):array($where);
            foreach($where as $key=>$val){
                list($k,$v) = strpos($val,'=')?explode('=', $val):false;
                $w[$k]=$v;
            }
            $where = array_filter($w); */
            $map = array();
            $map['_string'] = $where;
            $where = $map;
            
        }elseif(is_array($where)){
            $where = array_map(array('\Common','escapeString'),$where);
        }
        if(isset($this->options['where'])){
            $this->options['where'] =  array_merge($this->options['where'],$where);
        }else{
            $this->options['where'] =  $where;
        }
        if(isset($op['_operate'])) $this->options['_where_logic'] = $op['_operate'];
        return $this;
    }
    
    public function sql($sql = ''){
        if(empty($sql)) return false;
        return $this->db->sql($sql);
    }
    
    public function select_sql($sql){
        if(empty($sql)) return array();
        $op = 'S';
        return $this->db->sql($sql,$op);
    }
    
    public function test($type=''){
        var_dump($this->options[$type]);
    }
    /**
     * limit条件分析
     * @access public
     * @param unknown $offset  起始位置
     * @param string $length   长度
     * @return \Lib\Model
     */
    public function limit($offset,$length = null){
        if(is_null($length) && strpos($offset,',')){
            list($offset,$length) = explode(',',$offset);
        }
        $this->options['limit'] = intval($offset).($length?','.intval($length):'');
        return $this;
    }
    /**
     * 得到数据表名称
     * @access public
     * @return string
     */
    public function getTableName(){
        if(!empty($this->tbname)) return $this->tbname;
        //首先查找数据库中的所有表名称
        $tables =array();
        //得到所有的数据表名称
        $tables = $this->db->getTables($this->dbname);
        $name = false;
        //判断实例化的模型名称是否在得到的数据表中
        for($i = 0; $i<$this->db->getRowNum(); $i++){
            if(strtolower($this->tablePrefix.$this->name) == strtolower($tables[$i])){
                $name = $tables[$i];
            }
        }
        if(false === $name) return false;
        if(!empty($this->dbname)){
            $tbname = $this->dbname.".".$this->tablePrefix.$name;
        }else{
            $tbname = $this->tablePrefix.$name;
        }
        $this->options['table'] = $this->tbname = $tbname;
        return $this->tbname;
    }
    
    /**
     * 获取数据表的字段
     * @access public
     * @return unknown
     */
    public function getFields(){
        if(isset($this->options['table'])){
            if(is_array($this->options['table'])){
                $table = key($this->options['table']);
            }else{
                $table = $this->options['table'];
            }
//             $fields = $this->db->getFields($table);
        }elseif(!empty($this->tbname)){
            $table = $this->tbname;
            $this->options['table']=$table;
        }else{
            $table = $this->getTableName();
            $this->options['table'] = $table;
        }
        $fields = $this->db->getFields($table);
        return $fields;
    }
    
    public function add($data='',$options=array()){
        if(empty($data)){
            if(!empty($this->data)){
                //重置数据
               $data = $this->data;
               $this->data = array(); 
            }else{
                die('error');
                return false;
            }
        }
        //处理数据
//         $data = $this->_parsedata();
        $options = $this->_parseOptions($options);
        $result = $this->db->insert($data,$options);
        if($result !== false && is_numeric($result)){
            $insertId = $this->lastInsId();
            if($insertId) return $insertId;
            return false;
        }
        return $result;
                
        
    }
    
    private function _parseOptions($options = array()){
        
        //得到字段
        $fields = $this->getFields();
        if(is_array($options)){
            $options = array_merge($this->options,$options);
        }
        /*
         * 检测where的条件字段是否在表的字段中
         */
        if(isset($this->options['where']) && is_array($this->options['where'])){
            foreach($this->options['where'] as $key=>$val){
                $key = trim($key);
                if(!in_array($key,array_keys($fields))){
                    die('where error');
                }
            }
        }
        $this->options = array();
        return $options;
    }
    
    public function lastInsId(){
        return $this->db->lastInsId();
    }
    
    /**
     * 开启事务处理
     * 
     * @access public
     * @return void
     */
    public function startTransaction(){
        //开启事务之前 先将之前的提交
        $this->commit();
        $this->db->startTransaction();
        return ;
    }
    
    /**
     * 回滚事务
     * 
     * @access public
     * @return boolean
     */
    public function rollBack(){
        return $this->db->rollBack();
    }
    /**
     * 提交操作
     * 
     * @access public
     * @return boolean
     */
    public function Commit(){
        return $this->db->commit();
    }
    
    /**|
     * 初始化数据库链接
     */
    private function db(){
        if(!empty($this->db)) return $this->db;
        $this->db = DB::getInstance();
        if(!empty($this->name)) $this->getTableName();
    }
    
    public function closeDb(){
        $this->db->close();
    }
    
    
    
}