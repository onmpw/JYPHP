<?php
namespace Inter\DB;
interface IMysql{
    /**
     * ��������
     * @param array $data
     * @param array $options
     * @return boolean|Ambigous <mixed, boolean, string, string>
     */
    public function add($data = array(),$options = array());
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
    public function addMore($data = array(),$options = array());

    /**
     * ���º���
     * @param array $data
     * @param array $options
     */
    public function update($data = array(),$options = array());
    /**
     * ɾ�����ݺ���
     * @param array $options
     * @return Ambigous <mixed, boolean, string, string>
    */
    public function delete($options = array());

    /**
     * ����sql���
     * @param array $options
     * @return string
     */
    public function buildSql($options = array());
    /**
     * ѡ������˳��
     * @param string $order
     * @return Db
    */
    public function orderBy($order = '');
    /**
     * limit���ú���
     * @param string $limit
     * @return Db
    */
    public function limit($limit = '');
    /**
     * �õ�����������ݵ�id
     * @access public
     * @return int
    */
    public function lastInsId();
    /**
     * ��������
     * @access public
     * @return void|boolean
    */
    public function startTransaction();
    /**
     * �ع�����
     * @access public
     * @return boolean
    */
    public function rollback();
    /**
     * �ύ����
     * @access public
     * @return boolean
    */
    public function commit();
    /**
     * ���ҵ�������
     * @param array $options
     * @return boolean|unknown
    */
    public function find($options = array());

    /**
     * ��ѯ�������ݺ���
     * @param array $options
     * @return array
     */
    public function select($options = array());
    /**
     * where ��������
     * @param string $where
     * @return Db
    */
    public function where($where = '');
    /**
     * ����Ҫ��ѯ�ı��ֶΣ����û�����ã���Ĭ�ϲ�ѯ��������ֶ�
     * @param string $field
     * @return Db   ���ص�ǰ����
    */
    public function field($field = '');
    /**
     * �õ����ݿ���Ӱ�������
     * @access public
     * @return int
    */
    public function getRowNum();
    /**
     * �õ����ݿ��е����ݱ�
     * @access public
     * @param string $dbname ָ�����ݿ�
     * @return Ambigous <boolean, string, unknown>
    */
    public function getTables($dbname = '');
    /**
     * ���ñ���
     * @param string $table
     * @return Db   ���ص�ǰ����
    */
    public function table($table='');
    /**
     * ִ��sql���
     * @param string $sql
     * @access public
    */
    public function sql($sql='');
}