<?php

class TMC_Model_Collection extends TMC_Model {
    public function getCollection() {
        $query  = "SELECT ".$this->_fields;
        $query .= " FROM ".$this->_table;
        $query  .= " WHERE 1=1 ";
        if($this->_filter && sizeof($this->_filter) > 0) $query.= " AND ".implode(" AND ",$this->_filter);
        if($this->_where && sizeof($this->_where) > 0) $query.= " OR ".implode(" OR ",$this->_where);
        if($this->_order) $query .= " ORDER BY {$this->_order} {$this->_direction}";
        if($this->_limit) $query .= " LIMIT {$this->_limit}";
        echo $query;
        $q = TMC::getDB()->query($query);        
        if($q && $q->num_rows > 0) {
            $rs = array();
            while($r = $q->fetch_object('TMC_Model')) {
                $rs[] = $r;
            }
            $this->setItems($rs);
            return $this;
        }
        return false;
    }
}