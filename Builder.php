<?php

  namespace PhiladelPhia\Database;

  use PDO;
  use PhiladelPhia\App\Exceptions;
  use PhiladelPhia\Database\Interfaces\ManagerInterface;
  use PhiladelPhia\Database\Interfaces\BuilderInterface;

  use PhiladelPhia\Database\Query\Where;
  use PhiladelPhia\Database\Query\OrderBy;
  use PhiladelPhia\Database\Query\Pipeline;
  use PhiladelPhia\Database\Query\Select;
  use PhiladelPhia\Database\Query\Insert;
  use PhiladelPhia\Database\Query\Update;
  use PhiladelPhia\Database\Query\Delete;
  
  use PhiladelPhia\Helpers\Util;
  
  class Builder implements BuilderInterface
  {
    use Where; 
    use OrderBy; 
    use Pipeline;
    use Select; 
    use Insert; 
    use Update; 
    use Delete;

    public static $DBInstance;
    protected static $instance;
    protected static $__table;

    protected $dbh;
    protected $select;
    protected $skip;
    protected $limit;
    protected $orderBy;
    protected $where;
    protected $fields = [];
    protected $colonFields = [];
    protected $types;
    protected $as;
    protected $count;
    
    public $sql;
    public $values = [];

    public $operators = [
      '=', '<', '>', '<=', '>=', '<>', '!=', '<=>',
      'like', 'like binary', 'not like', 'ilike',
      '&', '|', '^', '<<', '>>',
      'rlike', 'regexp', 'not regexp',
      '~', '~*', '!~', '!~*', 'similar to',
      'not similar to', 'not ilike', '~~*', '!~~*',
    ];

    /**
     * Set instance Database
     */
    public static function setInstanceDatabase(ManagerInterface $instance)
    {
      self::$DBInstance = $instance;
    }

    public function __construct()
    {
      $this->dbh = self::$DBInstance;
      
      if (!isset($this->table))
      {
        $this->table = self::$__table;
      } 
      else 
      {
        self::$__table = $this->table;
      }
    }

    /**
     * Add to sentence sql the property `AND`
     */
    protected function addAnd(&$sql)
    {
      return $sql .= " AND ";
    }

    /**
     * Add to sentence sql the property `OR`
     */
    protected function addOr(&$sql)
    {
      return $sql .= " OR ";
    }

    /**
     * Add to sentence sql the property `NOT`
     */
    protected function addNot(&$sql)
    {
      return $sql .= " NOT ";
    }

    /**
     * Evaluate a operator, if is valid.
     */
    protected function invalidOperator($operator) 
    {
      return in_array(strtolower($operator), $this->operators, true);
    }

    protected function operator($operator)
    {
      if ($this->invalidOperator($operator))
      {
        return $operator;
      }
      else 
      {
        throw new Exceptions("Operator invalid $operator");
      }
    }

    protected function parse_args(&$in)
    {
      if (key_exists(0, $in) 
            && (count($in) <= 1) 
              && is_array($in[0]))
      {
        return $in;
      }
      $out = array_values($in);
      
      if (count($out) > 3) {
        throw new Exceptions("
                          Just can only pass 2 or 3 parameters for the pipeline `where`
                          example
                          `
                            ->where('column', 'other columns')
                            ->where('column', 'operator('=', '>', '<' ...)', 'other columns')
                          `");
      }
      
      return $in = $out;
    }

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
      $this->sql = sprintf(
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
      $this->sql = sprintf(
                            "%s %s (%s) VALUES (%s)",
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

      $this->sql = sprintf(
                            "%s %s SET %s %s",
                            $this->table,
                            $this->as,
                            $this->fields,
                            $this->where);
    }

    protected function prepare_delete() 
    {

    }

    protected function autoRun()
    {
      $response = null;
      
      switch($this->types)
      {
        case 0:
          $this->prepare_select();
          
          if ($this->count) {
            $response = $this->dbh->__select($this)->fetchColumn();
            break;
          }
          
          $response = $this->dbh->__select($this);
          if (!empty($this->model))
          {
            $response->setFetchMode(PDO::FETCH_CLASS|PDO::FETCH_PROPS_LATE, $this->model, [$this->table]);
          }
          
          $response = $response->fetch();

          if (!$response)
          {
            $response = array();
            break;
          }
          break;
        case 1:
          $this->prepare_select();
          
          $response = $this->dbh->__select($this); 

          if (!($response->rowCount() > 0))
          {
            $response = (object) array();
          }

          if (!empty($this->model))
          {
            $response->setFetchMode(PDO::FETCH_CLASS|PDO::FETCH_PROPS_LATE, $this->model, [$this->table]);
          }
          
          $response = $response->fetchAll();
          break;
        case 2:
          $this->prepare_insert();
          
          $response = $this->dbh->__insert($this);
          $response = (object) array(
            'id' => $response);
          break;
        case 3:
          $this->prepare_insert();
          
          $response = $this->dbh->__insertMany($this);
          $response = (object) array(
            'id' => $response);
          break;
        case 4:
          $this->prepare_update();
          
          $response = $this->dbh->__update($this);
          break;
        case 5:
          $this->prepare_delete();
          
          $response = $this->dbh->__delete($this);
          $response = (object) array(
            'id'
          );
          break;
      }

      self::resetProperties($this);
      
      return $response;
    }

    public static function resetProperties(&$self)
    {
      $self->sql          = null;
      $self->select       = null;
      $self->skip         = null;
      $self->limit        = null;
      $self->orderBy      = null;
      $self->where        = null;
      $self->fields       = [];
      $self->colonFields  = [];
      $self->values       = [];
      $self->types        = null;
      $self->as           = null;
      $self->count        = null;
    }
  }