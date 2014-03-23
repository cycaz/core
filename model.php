<?php
class TMC_Model extends TMC_Object {
    protected $_itemObjectName = 'TMC_Object';
    
    protected $_cacheExpire = 108000;
    protected $_cachePath = 'files/_cache/database';    
    protected $_cacheFields = array();
    
    protected $_table;
    protected $_where;
    protected $_fields = "*";
    protected $_limit = "";
    protected $_order;
    protected $_direction = "ASC";    
    protected $_filter = array();
    protected $_join = array();
    
    protected $_query;
    protected $_hasData = false;
    
    public function __construct($tableName='') {
        if(!empty($tableName)) $this->_table = $tableName;
    }
    
    public function from($tableName) {
        $this->_table = $tableName;
    }
    
    public function setTable($tableName,$tableAlias="") {
        $this->_table = $tableName." AS ".$tableAlias;
        return $this;
    }
    
    public function getTable() {
        return $this->_table;
    }
    
    public function addFilter($filter) {
        if(is_array($filter)) {
            $this->_filter = array_merge($this->filter,$filter);    
        } else {
            $this->_filter[] = $filter;
        }
        return $this;
    }    
    
    public function addWhere($where) {
        if(is_array($where)) {
            $this->_where = array_merge($this->_where,$where);    
        } else {
            $this->_where[] = $where;
        }
        return $this;
    }    
    
    public function addJoin($join) {
        if(is_array($join)) {
            $this->_join = array_merge($this->_join,$join);    
        } else {
            $this->_join[] = $join;
        }
        return $this;
    }
    
    public function setLimit($data) {
        $this->_limit = $data;
        return $this;
    }

    public function setFields($data) {
        $this->_fields = $data;
        return $this;
    }

    public function setOrder($data) {
        $this->_order = $data;
        return $this;
    }

    public function setDirection($data) {
        $this->_direction = $data;
        return $this;
    }
    
    public function getCountOld() {
        echo $this->_table;
        $rs = TMC::getDB()->query("SELECT COUNT(*) as cnt FROM {$this->_table}")->fetch_assoc();
        $return = $rs['cnt'];        
        return $return;
    }
    
    public function getCount() {
        if(!$this->hasCount()) {
            $query  = "SELECT COUNT(*) as cnt";
            $query .= " FROM ".$this->_table." ";
            if($this->_join && sizeof($this->_join) > 0) $query.= implode(" ",$this->_join);
            $query  .= " WHERE 1=1 ";
            if($this->_filter && sizeof($this->_filter) > 0) $query.= " AND ".implode(" AND ",$this->_filter);
            if($this->_where && sizeof($this->_where) > 0) $query.= " OR ".implode(" OR ",$this->_where);
            $this->_query = $query;
            $q = TMC::getDB()->query($query);
            if($q && $q->num_rows > 0) {
                $r = $q->fetch_object();
                $this->setData('count',$r->cnt);
            } else {
                $this->setData('count',0);
            }
        }
        return $this->getData('count');
    }
    
    public function getCollection() {
        $query  = "SELECT {$this->_fields} FROM {$this->_table} ";
        if($this->_join && sizeof($this->_join) > 0) $query.= implode(" ",$this->_join);
        $query  .= " WHERE 1=1 ";
        if($this->_filter && sizeof($this->_filter) > 0) $query.= " AND ".implode(" AND ",$this->_filter);
        if($this->_where && sizeof($this->_where) > 0) $query.= " OR ".implode(" OR ",$this->_where);
        if($this->_order) $query .= " ORDER BY {$this->_order} {$this->_direction}";
        if($this->_limit) $query .= " LIMIT {$this->_limit}";
        $this->_query = $query;
        $q = TMC::getDB()->query($this->_query);
        if($q && $q->num_rows > 0) {
            $rs = array();
            while($r = $q->fetch_object($this->_itemObjectName)) {
                $rs[] = $r;
            }
            $this->setItems($rs);
        }
        return $this;
    }
    
    
    public function getFields() {
        if($this->getTable() && sizeof($this->_cacheFields) <= 0) {            
            $cachePath = BP.DS.$this->_cachePath.DS.$this->getTable();
            $fields = array();
            if(TMC::isLocal() || !file_exists($cachePath) || filemtime($cachePath) < (time()-$this->_cacheExpire))  {
                $q = TMC::getDB()->query('DESCRIBE '.$this->getTable());
                if($q && $q->num_rows > 0) {
                    while($r = $q->fetch_object('TMC_Object')) {
                        $fields[$r->getField()] = $r;
                    }
                    $this->_cacheFields = $fields;
                    $fh = fopen($cachePath,'w');
                    fwrite($fh,serialize($fields));
                    fclose($fh);
                }
            }
            if(file_exists($cachePath))
                $this->_cacheFields = unserialize(file_get_contents($cachePath));
        }
        return $this->_cacheFields;
    }
    
    public function getQuery() {
        return $this->_query;
    }
    
    public function setQuery($query) {
        $this->_query = $query;
        return $this;
    }
    
    public function insert() {
        if($this->getTable()) {
            $fields = $this->getFields();
            $query = "INSERT INTO {$this->getTable()} ";
            $data = array();
            if(!$this->hasData('created_time')) $this->setCreatedTime(date('Y-m-d H:i:s'));
            if(!$this->hasData('modified_time')) $this->setModifiedTime(date('Y-m-d H:i:s'));
            foreach(array_keys($fields) as $field) {
                if($this->hasData($field)) {
                    $data[$field] = $this->getData($field);
                } else {
                    $data[$field] = '';
                }
            }
            $query .= "(`".implode("`,`",array_keys($data))."`) VALUES ('".implode("','",array_values($data))."')";
            $q = TMC::getDB()->query($query);
            if($q) {
                $this->setId(TMC::getDB()->insert_id);
                return true;
            } else {
                throw new Exception(TMC::getDB()->error);
            }
        }
        return false;
    }

    public function update($debug=false) {
        if($this->getTable()) {
            $fields = $this->getFields();
            $query = "UPDATE {$this->getTable()} SET ";
            $data = array();
            $primaryKeys = array('id'=>'id');
            #$this->unsetData('created_time');
            $this->setModifiedTime(date('Y-m-d H:i:s'));
            
            foreach($fields as $name => $field) {
                if($field->getKey() !='PRI') {
                    if($this->hasData($field->getField())) {
                        $query .= $name."='".$this->getData($field->getField())."',";
                    }
                } else {
                    $primaryKeys[$name] = $name;
                }
            }
            $query = rtrim($query,",");
            $query .=" WHERE 1=1";
            foreach($primaryKeys as $primaryKey) {
                if($this->hasData($primaryKey)) {
                    $query .= " AND {$primaryKey}='".$this->getData($primaryKey)."'";
                }
            }
            if($this->_filter) $query .= " AND ".implode(" AND ",$this->_filter);
            if($this->_where) $query .= " OR ".implode(" OR ",$this->_where);
            if($debug) {
                die($query);
            }
            $this->_query = $query;
            $q = TMC::getDB()->query($query);
            if($q) {
                return true;
            } else {
                throw new Exception(TMC::getDB()->error);
            }
        }
        return false;
    }
    
    public function query($query) {
        $this->_query = $query;
        return $this;
    }
    
    public function load() {
        $query  = "SELECT {$this->_fields} FROM {$this->_table} ";
        if($this->_join && sizeof($this->_join) > 0) $query.= implode(" ",$this->_join);
        $query  .= " WHERE 1=1 ";
        if($this->_filter && sizeof($this->_filter) > 0) $query.= " AND ".implode(" AND ",$this->_filter);
        if($this->_where && sizeof($this->_where) > 0) $query.= " OR ".implode(" OR ",$this->_where);
        if($this->_order) $query .= " ORDER BY {$this->_order} {$this->_direction}";
        $query .= " LIMIT 1";
        $this->_query = $query;        
        $q = TMC::getDB()->query($this->_query);
        if($q && $q->num_rows > 0) {
            $r = $q->fetch_assoc();
            $this->addData($r);
        }
        return $this;
    }
    
    public function oldLoad() {
        $rs = TMC::getDB()->query($this->_query);
        if(!$rs || $rs->num_rows==0) return null;
        if($rs->num_rows > 0) $this->_hasData = true;
        if($rs->num_rows > 1) {
            $return = array();
            while($r = $rs->fetch_object(get_class(self))) {
                $return[] = $r;
            }
            return $return;
        } else {            
            return $rs->fetch_object(get_class(self));
        }
    }
    
    public function delete() {
        if($this->getId()) {
            $query = "DELETE FROM {$this->_table} WHERE 1=1 ";
            if($this->_filter && sizeof($this->_filter) > 0) $query.= " AND ".implode(" AND ",$this->_filter);
            if($this->_where && sizeof($this->_where) > 0) $query.= " OR ".implode(" OR ",$this->_where);
            TMC::getDB()->query($query);
        }
    }
}  
?>