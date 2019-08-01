<?php 

  namespace PhiladelPhia\Database\Traits;

  trait Prepare {
    
    protected function prepare_select()
    {
      $select = null;
      $where = null;

      if (!empty($this->select))
      {
        $select = $this->select;
      }
      $this->select = $select ?: "*";

      if (!empty($this->where))
      {
        $where = $this->where;
      }

      $this->where = $where ? " WHERE {$where}" : "";
      return $this->sql = sprintf(
                            " %s FROM %s %s %s %s %s %s", 
                            $this->select, 
                            $this->table, 
                            $this->as, 
                            $this->where, 
                            $this->orderBy, 
                            $this->limit, 
                            $this->skip);
    }
    
    protected function prepare_insert()
    {
      $this->fields = implode(",", $this->fields);
      $this->colonFields = implode(",", $this->colonFields);
      return $this->sql = sprintf(
                            " %s %s (%s) VALUES (%s)",
                            $this->table,
                            $this->as,
                            $this->fields,
                            $this->colonFields);
    }
    
    protected function prepare_update()
    {
      $where = null;
      $this->fields = implode(",", $this->fields);

      if (!empty($this->where))
      {
        $where = $this->where;
      }
      $this->where = $where ? " WHERE {$where}" : "";

      return $this->sql = sprintf(
                            " %s %s SET %s %s",
                            $this->table,
                            $this->as,
                            $this->fields,
                            $this->where);
    }

    protected function prepare_delete() 
    {
      $where = null;
      
      if (!empty($this->where)) 
      {
        $where = $this->where;
      }

      $this->where = $where ? "WHERE {$where}" : "";
      
      return $this->sql = sprintf(
                            " FROM %s %s %s ", 
                            $this->table, 
                            $this->as, 
                            $this->where);
    }

  }