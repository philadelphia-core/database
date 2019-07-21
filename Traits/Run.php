<?php 
  
  namespace PhiladelPhia\Database\Traits;

  use PDO;

  trait Run {
    
    protected function run_select(bool $many) {
      $select = $this->dbh->__select($this);

      if ($this->count) {
        return $select->fetchColumn();
      }

      if ($many && !($select->rowCount() > 0))
      {
        return [];
      }

      if (!empty($this->model))
      {
        // var_dump($this->model);
        $select->setFetchMode(PDO::FETCH_CLASS|PDO::FETCH_PROPS_LATE, $this->model);
        // $select->setFetchMode(PDO::FETCH_CLASS|PDO::FETCH_PROPS_LATE, $this->model."::class", [$this->table]);
      }
      
      // clear to properties this instance object.
      self::resetProperties($this);

      if (!$many) {
        return $select->fetch() ?: [];
      }

      return $select->fetchAll();
    }

    protected function run_insert(bool $many) {
      $insert = $many 
                      ? $this->dbh->__insertMany($this) 
                      : $this->dbh->__insert($this);
      
      // clear to properties this instance object.
      self::resetProperties($this);

      return (object) [
        'id' => $insert
      ];
    }

    protected function run_update() {      
      $update = $this->dbh->__update($this);
      
      // clear to properties this instance object.
      self::resetProperties($this);
      
      return $update;
    }

    protected function run_delete() {
      $delete = $this->dbh->__delete($this);

      // clear to properties this instance object.
      self::resetProperties($this);

      return $delete;
    }
  }