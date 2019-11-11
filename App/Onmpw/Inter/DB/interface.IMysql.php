<?php
namespace Inter\DB;
interface IMysql{
    /**
     * 新增数据
     * @param array $data
     * @param array $options
     * @return boolean|Ambigous <mixed, boolean, string, string>
     */
    public function add($data = array(),$options = array());
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
    public function addMore($data = array(),$options = array());

    /**
     * 更新函数
     * @param array $data
     * @param array $options
     */
    public function update($data = array(),$options = array());
    /**
     * 删除数据函数
     * @param array $options
     * @return Ambigous <mixed, boolean, string, string>
    */
    public function delete($options = array());

    /**
     * 构建sql语句
     * @param array $options
     * @return string
     */
    public function buildSql($options = array());
    /**
     * 选择排列顺序
     * @param string $order
     * @return Db
    */
    public function orderBy($order = '');
    /**
     * limit设置函数
     * @param string $limit
     * @return Db
    */
    public function limit($limit = '');
    /**
     * 得到最后插入的数据的id
     * @access public
     * @return int
    */
    public function lastInsId();
    /**
     * 开启事务
     * @access public
     * @return void|boolean
    */
    public function startTransaction();
    /**
     * 回滚事务
     * @access public
     * @return boolean
    */
    public function rollback();
    /**
     * 提交事务
     * @access public
     * @return boolean
    */
    public function commit();
    /**
     * 查找单条数据
     * @param array $options
     * @return boolean|unknown
    */
    public function find($options = array());

    /**
     * 查询多条数据函数
     * @param array $options
     * @return array
     */
    public function select($options = array());
    /**
     * where 条件设置
     * @param string $where
     * @return Db
    */
    public function where($where = '');
    /**
     * 设置要查询的表字段，如果没有设置，则默认查询表的所有字段
     * @param string $field
     * @return Db   返回当前对象
    */
    public function field($field = '');
    /**
     * 得到数据库受影响的行数
     * @access public
     * @return int
    */
    public function getRowNum();
    /**
     * 得到数据库中的数据表
     * @access public
     * @param string $dbname 指定数据库
     * @return Ambigous <boolean, string, unknown>
    */
    public function getTables($dbname = '');
    /**
     * 设置表名
     * @param string $table
     * @return Db   返回当前对象
    */
    public function table($table='');
    /**
     * 执行sql语句
     * @param string $sql
     * @access public
    */
    public function sql($sql='');
}