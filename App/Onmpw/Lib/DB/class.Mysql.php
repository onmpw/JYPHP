<?php
namespace Lib\DB;
use Inter\DB\IMysql;
class Mysql implements IMysql{
    
    protected $config = array();
    
    protected $dsn = '';  //���ݿ����� dsn��Ϣ
    
    protected $PDOstatement = null;
    
    protected $transnum = 0;  //����ָ������
    
    protected $lastInsId;  //��¼��������ʱ�������һ����¼��id
    
    protected $affectNum; //��¼Ӱ������ݵ�����
    
    //�޸Ĳ���
    public static $_instance; //��̬���ԣ��洢ʵ������
    
    protected $_links = array();  //�洢���ӱ�ʶ��
    
    protected $link = '';
    
    protected $ignore = array();
    
    protected $sql;
    
    protected $bind = array();  //�󶨲���
    
    protected $options = array();
    
    protected $PDOStatement;
    
    private   $starttrans = false; //�Ƿ���������
    
    private   $translink;
    
    /**
     * ˽�л����캯����ʹ�õ���ģʽ
     */
    private function __construct($config=''){
        $this->config = $this->parseConfig($config);
    }
    
    /**
     * ʵ��������
     * @access public static
     * @return Db
     */
    public static function Instance($options = ''){
        if(self::$_instance instanceof self){
            return self::$_instance;
        }
        self::$_instance = new self($options);
        return self::$_instance;
    }
    public function getLinkId(){
        $this->parseConnect(false);
        return $this->link;
    }
    public function getlinks(){
        return $this->_links;
    }
    /**
     * ִ�в�ѯ���
     * 
     * @param string $sql
     * @param bool $getsql
     * 
     * @return mixed
     */
    protected function query($sql,$getsql = false){
        $this->parseConnect(false);
        /*
         * �ж�������Դ�Ƿ����
         */
        if(!$this->link) return false;
        $this->sql = $sql;
        if(!empty($this->bind)){
            $that = $this;
            $sql = strtr($this->sql, array_map(function($val) use($that){ return addslashes($val);}, $this->bind));
        }
        if($getsql) return $this->sql;
        /*
         * �ͷ��ϴ�ִ�еĽ��
         */
        if(!empty($this->PDOStatement)) $this->free();
        /*
         * ׼��һ��Ԥ�������
         */
        $this->PDOStatement = $this->link->prepare($sql);
        if(false === $this->PDOStatement) return false;
        /*
         * �󶨲���
         */
        foreach($this->bind as $key=>$val){
            if(is_array($val)){
                $this->PDOStatement->bindValue($key,$val[0],$val[1]);
            }else{
                $this->PDOStatement->bindValue($key,$val);
            }
        }
        /*
         * �ͷŰ󶨲����ı���
         */
        $this->bind = array();
        /*
         * ִ�����
         */
        $result = $this->PDOStatement->execute();
        if(false === $result) return false;
        else{
            $result = $this->PDOStatement->fetchAll(\PDO::FETCH_ASSOC);
            $this->affectNum = count($result);
            return $result;
        }
    }
    /**
     * ִ����ɾ�ĵ����
     * 
     * @param string $sql
     * @param bool $getsql
     * 
     * @return mixed
     */
    protected function execute($sql,$getsql = false){
        $this->parseConnect(true);
        if(!$this->link) return false;
        $this->sql = $sql;
        if(!empty($this->bind)){
            $that=$this;
            $sql = strtr($this->sql, array_map(function($val) use($that){ return addslashes($val);}, $this->bind));
        }
        if($getsql) return $this->sql;
        /*
         * �ͷ��ϴ�ִ�еĽ��
         */
        if(!empty($this->PDOStatement)) $this->free();
        /*
         * ׼��һ��Ԥ�������
         */
        $this->PDOStatement = $this->link->prepare($sql);
        if(false === $this->PDOStatement) return false;
        /*
         * �󶨲���
         */
        foreach($this->bind as $key=>$val){
            if(is_array($val)){
                $this->PDOStatement->bindValue($key,$val[0],$val[1]);
            }else{
                $this->PDOStatement->bindValue($key,$val);
            }
        }
        /*
         * �ͷŰ󶨵Ĳ�������
         */
        $this->bind = array();
        $result = $this->PDOStatement->execute();
        if($result === false){
            return false;
        }else{
            $this->affectNum = $this->PDOStatement->rowCount();
            if(preg_match("/^\s*(INSERT\s+INTO|REPLACE\s+INTO)\s+/i", $sql)){
                $this->lastInsId = $this->link->lastInsertId();
            }
            return $this->affectNum;
        }
    }
    /**
     * ִ��sql���
     * @param string $sql
     * @access public
     */
    public function sql($sql=''){
        if(empty($sql)) return false;
        //�ж��ǲ�ѯ�����ֻ��Ǹ��²���
        if(preg_match("/^\s*(SELECT|select\s)\s+/i", $sql)){
            return $this->query($sql);
        }else{
            return $this->execute($sql);
        }
    }
    /**
     * �󶨲���
     * @param string $key
     * @param mixed $val
     */
    private function bindParams($key,$val){
        $this->bind[":".$key] = $val;
    }
    /**
     * �����󶨵Ĳ���,���������Ϊ����ϲ�����
     * @param unknown $bind
     */
    private function parseBind($bind = array()){
        if(is_array($bind)){
            $this->bind = array_merge($this->bind,$bind);
        }
    }
    /**
     * ���뺯��
     * @param array $data
     * @param array $options
     * @return mixed
     */
    protected function insert($data=array(),$options=array()){
        $values = $fields = array();
        $this->parseBind(isset($options['bind'])?$options['bind']:array());
        foreach($data as $key=>$val){
            $fields[] = $key;
            for($i=0;$i<count($this->options['fields']);$i++){
                if($this->options['fields'][$i]['field'] == $key){
                    if(preg_match('/\w*(int|INT)$/i', $this->options['fields'][$i]['type'])){
                        $values[] = ":".$key;
                    }else{
                        $values[] = "':".$key."'";
                    }
                    break;
                }
            }
            
            $this->bindParams($key, $val);
        }
        $sql = "INSERT INTO ".$this->options['table']."(".implode(',', $fields).") VALUES (".implode(',', $values).")";
        return $this->execute($sql);
    }
    /**
     * ���ñ���
     * @param string $table
     * @return Db   ���ص�ǰ����
     */
    public function table($table=''){
        if($table == '') $table = $this->options['table'];
        $this->options['table'] = $table;
        if(!$this->parseFields()) return false;
        $this->close();
        return $this;
    }
    /**
     * �õ����ݿ��е����ݱ�
     * @access public
     * @param string $dbname ָ�����ݿ�
     * @return Ambigous <boolean, string, unknown>
     */
    public function getTables($dbname = ''){
        $sql = !empty($dbname)?"SHOW TABLES FROM ".$dbname:"SHOW TABLES";
        $result = $this->query($sql);
        $tables = array();
        foreach($result as $key=>$val){
            $tables[$key] = current($val);
        }
        return $tables;
    }
    /**
     * �õ����ݿ���Ӱ�������
     * @access public
     * @return int
     */
    public function getRowNum(){
        if(!empty($this->affectNum)) return $this->affectNum;
    }
    /**
     * ����Ҫ��ѯ�ı��ֶΣ����û�����ã���Ĭ�ϲ�ѯ��������ֶ�
     * @param string $field
     * @return Db   ���ص�ǰ����
     */
    public function field($field = ''){
        /* if(!empty($field)){ 
            $f = array();
            foreach($this->options['fields'] as $key=>$val){
                $f[] = $val['field'];
            }
            $field = implode(',', $f);
        } */
        if(!empty($field)) $this->options['field'] = $field;
        return $this;
    }
    /**
     * where ��������
     * @param string $where
     * @return Db
     */
    public function where($where = ''){
        if(is_string($where)) $this->options['where'] = $where;
        elseif(is_array($where)){
            $w = '';
            foreach($where as $key=>$val){
                $w .= $key."=".addslashes($val)." and ";
            }
            $where = rtrim($w,' and');
            $this->options['where'] = $where;
        }
        return $this;
    }
    
    /**
     * ��ѯ�������ݺ���
     * @param unknown $options
     * @return Ambigous <mixed, boolean, string, string, unknown>
     */
    public function select($options = array()){
        $this->parseBind(isset($options['bind'])?$options['bind']:array());
        /*
         * �ж��Ƿ��з�ҳ
         */
        if(isset($options['page'])){
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
    public function find($options = array()){
        $this->parseBind(isset($options['bind'])?$options['bind']:array());
        /*
         * �ж��Ƿ��з�ҳ
        */
        if(isset($options['page'])){
            $this->limit($options['page']);
        }
        $sql = $this->buildSql($options);
        $result = $this->query($sql);
        if($result === false || count($result) == 0) return false;
        $result = $result[0];
        return $result;
    }
    
    /**
     * ��������
     * @param array $data
     * @param array $options
     * @return boolean|Ambigous <mixed, boolean, string, string>
     */
    public function add($data = array(),$options = array()){
        if(isset($options['table'])) 
            $this->table($options['table']);
        if(!is_array($data)) return false;
        $res = $this->insert($data,$options);
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
    public function addMore($data = array(),$options = array()){
        if(isset($options['table']))
            $this->table($options['table']);
        if(!is_array($data)) return false;
        /*
         * ����������������
         */
//         $this->startTransaction();
        foreach($data as $key=>$val){
            //�鿴�Ƿ��Ƕ�����
            if(isset($options['multitable'])&&$options['multitable']){
                /*
                 * �����룬��$keyΪ����,$valΪҪ���������
                 * ʹ�õݹ�ķ�ʽ�ٴζԶ������ݽ��в���
                 */
                $res = $this->addMore($val,array('table'=>$key));
            }else{
                //�������
                $res = $this->add($val);
            }
            if(!$res){  
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
     * ���º���
     * @param unknown $data
     * @param unknown $options
     */
    public function update($data = array(),$options = array()){
        $values = $fields = $set = array();
        if(is_array($options)){ 
            $options = array_merge($options,$this->options);
            $this->table($options['table']);
        }
        $this->parseBind(isset($options['bind'])?$options['bind']:array());
        foreach($data as $key=>$val){
            $fields[] = $key;
            /*
             * ����ֶ�����
             */
            for($i=0;$i<count($this->options['fields']);$i++){
                if($this->options['fields'][$i]['field'] == $key){
                    if(preg_match('/\w*(int|INT)$/i', $this->options['fields'][$i]['type'])){
                        $values[] = ":".$key;
                    }else{
                        $values[] = "':".$key."'";
                    }
                    break;
                }
            }
            //�󶨲���
            $this->bindParams($key, $val);
        }
        for($i=0;$i<count($fields);$i++){
            $set[] = $fields[$i]."=".$values[$i];
        }
        $where = $this->parseWhere();
        $sql = "UPDATE ".$this->options['table']." SET ".implode(',',$set).$where;
        return $this->execute($sql);
    }
    
    /**
     * ɾ�����ݺ���
     * @param array $options
     * @return Ambigous <mixed, boolean, string, string>
     */
    public function delete($options = array()){
        if(isset($options['table'])){
            $this->table($options['table']);
        }
        $low_priority = isset($options['low_priority'])?$options['low_priority']:'';
        $quick = isset($options['quick'])?$options['quick']:'';
        $ignore = isset($options['ignore'])?$options['ignore']:'';
        $where = $this->parseWhere();
        $order = $this->parseOrder();
        $limit = $this->parselimit();
        $sql = "DELETE FROM {$low_priority} {$quick} {$ignore}".$this->options['table'].$where.$order.$limit;
        return $this->execute($sql);
    }
    /**
     * ����limit����
     * @return string
     */
    private function parseLimit(){
        $limit = '';
        if(isset($this->options['limit'])&&!empty($this->options['limit'])){
            $order = " LIMIT ".$this->options['limit'];
            $this->options['limit'] = '';
        }
        return $limit;
    }
    /**
     * ����sql���
     * @param unknown $options
     * @return string
     */
    public function buildSql($options = array()){
        if(is_array($options)){
            $options = array_merge($options,$this->options);
        }
        $where = $this->parseWhere();
        $order = $this->parseOrder();
        $limit = $this->parseLimit();
        $sql = 'SELECT '.$options['field'].' FROM '.$options['table'].' '.$where.$order.$limit;
        return $sql;
    }
    /**
     * ѡ������˳��
     * @param string $order
     * @return Db
     */
    public function orderBy($order = ''){
        $this->options['order'] = $order;
        return $this;
    }
    
    /**
     * limit���ú���
     * @param string $limit
     * @return Db
     */
    public function limit($limit = ''){
        if(is_array($limit)){
            list($page,$listrows) = $limit;
            $page = $page > 0 ? $page : 1;
            $listrows = $listrows>0 ? $listrows : 20;
            $offset = $listrows*($page-1);
            $this->options['limit'] = $offset.",".$listrows;
        }elseif(is_string($limit)){
            $this->options['limit'] = $limit;
        }
        return $this;
    }
    /**
     * �������ֶ�
     * @param string $table
     * @return boolean
     */
    public function parseFields($table = ''){
        if(empty($table)) $table = $this->options['table'];
        $sql = 'SHOW COLUMNS FROM '.$table;
        $res = $this->query($sql);
        if(false === $res) return false;
        $fields = array();
        if(is_array($res)){
            foreach($res as $key=>$val){
                array_push($fields,array('field'=>$val['Field'],'isnull'=>$val['Null'],'type'=>$val['Type']));
            }
            $this->options['fields'] = $fields;
        }
        foreach($this->options['fields'] as $key=>$val){
            $f[] = $val['field'];
        }
        $this->options['field'] = implode(',',$f);
        return true;
        
    }
    /**
     * ����where����
     * @return string
     */
    private function parseWhere(){
        $where = '';
        if(isset($this->options['where'])&&!empty($this->options['where'])){
            $where = " WHERE ".$this->options['where'];
            $this->options['where'] = '';
        }
        return $where;
    }
    /**
     * �õ�����������ݵ�id
     */
    public function lastInsId(){
        return $this->lastInsId;
    }
    /**
     * ����order����
     * @return string
     */
    private function parseOrder(){
        $order = '';
        if(isset($this->options['order'])&&!empty($this->options['order'])){
            $order = " ORDER BY ".$this->options['order'];
            $this->options['order'] = '';
        }
        return $order;
    }
    /**
     * ��������
     * @param string $master   ���������������Ǵӷ���������
     * @return 
     */
    private function parseConnect($master = true){
        if($this->config['deploy_type'] == 1){  //�ֲ�ʽ����
            $this->link = $this->multiConnect($master);
        }else{
            $this->link = $this->connect();
        }
        /*
         * ���������������ô��������Դ��������
         */
        if($this->starttrans&&$master) $this->translink = $this->link;
        return ;
    }
    /**
     * ���ݿ����Ӻ���
     * 
     * @param string $config
     * @param number $identify
     * @param string $reconnect
     * 
     * @return string|boolean|multitype:
     */
    private function connect($config='',$identify = 0,$reconnect = false){
        if(!isset($this->_links[$identify])){
             if(empty($config)){
                 $config = $this->config;
             }
             if(empty($config['dsn'])) $config = $this->parseDsn($config);
             try{
                 $this->_links[$identify] = new \PDO($config['dsn'],$config['user'],$config['password']);
                 var_dump($this->_links);
             }catch(\PDOException $e){
                 if($reconnect)
                    return "reconnect";
                 else
                     return false;
             }
        }
        return $this->_links[$identify];
    }
    /**
     * �ֲ�ʽ���ݿ�����
     * @param string $master   ���������������Ǵӷ���������
     * @return mixed
     */
    private function multiConnect($master = false){
        $config['host'] = explode(',', $this->config['host']);
        $config['dbname'] = explode(',',$this->config['dbname']);
        $config['port'] = empty($this->config['port'])?null:explode(',',$this->config['port']);
        $config['user'] = explode(',',$this->config['user']);
        $config['password'] = explode(',',$this->config['password']);
        $config['dsn'] = explode(',',$this->config['dsn']);
        /*
         * �����ȡһ�������������±�
         * Ϊ�˱�֤�����������������һ��崻��Ժ󣬳����Զ����������ķ�����
         * ��Ҫѭ����ȡ���������±꣬���ȡ�����±���崻��ķ��������������ѭ��ȡ�±�
         * ֱ������崻��б��У���Ȼ���ѭ���Ĵ�������һ���������ǿ�����Ϊ���������ӳ����쳣������false
         * 
         */
        $count = 0;
        $flag = false;
        do{
            $m = floor(mt_rand(0, $this->config['master_num']-1));
            if(!in_array($m,$this->ignore)){ 
                $flag = true;
                break;
            }
            $count++;
        }while(count($this->ignore)<$this->config['master_num']);
        if($flag === false) return false;
        
        //�ж��Ƕ�����д
        if($master){ //$masterΪtrue ��ʾ���ݸ���
            /*
             * ���������������0 ˵���Ѿ����������񣬽������ĸ��²���Ҫ�ڵ�ǰ�����Ͻ���
             * ����������ӿ��ܻ����Ӳ�ͬ�ķ�����
             */
            if($this->transnum > 0){
                $this->link = $this->translink;
                return $this->translink;
            }
            $db = array(
                'host'=>isset($config['host'][$m])?$config['host'][$m]:$config['host'][0],
                'dbname'=>isset($config['dbname'][$m])?$config['dbname'][$m]:$config['dbname'][0],
                'port'=>isset($config['port'][$m])?$config['port'][$m]:$config['port'][0],
                'user'=>isset($config['user'][$m])?$config['user'][$m]:$config['user'][0],
                'password'=>isset($config['password'][$m])?$config['password'][$m]:$config['password'][0],
                'dsn'=>isset($config['dsn'][$m])?$config['dsn'][$m]:$config['dsn'][0],
            );
        }else{ //������
            /*
             * �ж��Ƿ��Ƕ�д����
             */
            if($this->config['rw_seprate']){  //��д����
                $count = 0;
                $flag = false;
                do{
                    $s = floor(mt_rand($this->config['master_num'],count($config['host'])-1));
                    if(!in_array($s, $this->ignore)){
                        $flag = true;
                        break;
                    }
                    $count++;
                }while(count($this->ignore)<count($config['host'])-$this->config['master_num']);
                if(false === $flag) return false;
            }else{
                //��д������
                $count = 0;
                $flag = false;
                do{
                    $s = floor(mt_rand(0, count($config['host'])-1));
                    if(!in_array($s, $this->ignore)){
                        $flag = true;
                        break;
                    }
                    $count++;
                }while(count($this->ignore)<count($config['host']));
                if(false === $flag) return false;
            }
            $db = array(
                'host'=>isset($config['host'][$s])?$config['host'][$s]:$config['host'][0],
                'dbname'=>isset($config['dbname'][$s])?$config['dbname'][$s]:$config['dbname'][0],
                'port'=>isset($config['port'][$s])?$config['port'][$s]:$config['port'][0],
                'user'=>isset($config['user'][$s])?$config['user'][$s]:$config['user'][0],
                'password'=>isset($config['password'][$s])?$config['password'][$s]:$config['password'][0],
                'dsn'=>isset($config['dsn'][$s])?$config['dsn'][$s]:$config['dsn'][0],
            );
        }
        /*
         * �������ݿ�
         */
        $identify = $master===true?$m:$s;
        $res = $this->connect($db,$identify,true);
        if($res === false){
            return false;
        }elseif($res=='reconnect'){
            array_push($this->ignore,$identify);
            $this->link = $this->multiConnect($master);
        }else{
            return $res;
        }
        return $this->link;
    }
    /**
     * ������������
     * @param string $config
     */
    protected function parseConfig($config = ''){
        if(empty($config)){
            $config = $this->config;
        }else{
            $config = array_merge($this->config,$config);
        }
        return $config;
    }
    
    /**
     * ����dsn
     * @param string $config
     */
    protected function parseDsn($config = ''){
        if(empty($config)) $config = $this->config;
        $dsn = array(
            'type'=>$this->config['type'],
            'host'=>$config['host'],
            'dbname'=>$config['dbname'],
            'port'=>$config['port'],
            'user'=>$config['user'],
            'password'=>$config['password'],
        );
        $dsn['dsn'] = $dsn['type'].":dbname={$dsn['dbname']};host={$dsn['host']}";
        if(!empty($config['port'])){
            $dsn['dsn'] = $dsn['dsn'].";port={$config['port']}";
        }
        $dsn['dsn'] = $dsn['dsn'].";charset=utf8";
        return $dsn;
    }
    
    /**
     * ��������
     * @access public
     * @return void|boolean
     */
    public function startTransaction(){
        $this->starttrans = true;
        $this->parseConnect();
        if(empty($this->link)) return false;
        if($this->transnum == 0)
            $this->link->beginTransaction();
        $this->transnum++;
        return ;
    }
    
    /**
     * �ع�����
     * @access public
     * @return boolean
     */
    public function rollback(){
        if($this->transnum > 0){
            //�������ָ��������0 ��ع����� ���ҽ�����ָ������Ϊ0
            $res = $this->link->rollBack();
            $this->transnum = 0;
            $this->starttrans = false;
            if(!$res){
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
    public function commit(){
        if($this->transnum > 0){
            //�������ָ��������0 ���ύ���� ���ҽ�����ָ������Ϊ0
            $res = $this->link->commit();
            $this->transnum = 0;
            $this->starttrans = false;
            if(!$res){
                return false;
            }
        }
        return true;
    }
    /**
     * �ͷŲ�ѯ
     */
    private function free(){
        $this->PDOstatement = null;
    }
    /**
     * �ر�����
     */
    private function close(){
        $this->link = null;
    }
    
}