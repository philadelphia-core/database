<?php

  namespace PhiladelPhia\Database;

  use PDO;
  use PhiladelPhia\App\Exceptions;
  use PhiladelPhia\Database\Interfaces\ManagerInterface;
  use PhiladelPhia\Database\Interfaces\BuilderInterface;

  use PhiladelPhia\Database\Traits\Prepare;
  use PhiladelPhia\Database\Traits\Run;

  use PhiladelPhia\Database\Query\OrderBy;
  use PhiladelPhia\Database\Query\Pipeline;
  use PhiladelPhia\Database\Query\Select;
  use PhiladelPhia\Database\Query\Insert;
  use PhiladelPhia\Database\Query\Update;
  use PhiladelPhia\Database\Query\Delete;
  use PhiladelPhia\Database\Query\Where;
  
  use PhiladelPhia\Helpers\Util;

  class Builder implements BuilderInterface
  {
    use Prepare;
    use Run;
    use Where; 
    use OrderBy; 
    use Pipeline;
    use Select; 
    use Insert; 
    use Update; 
    use Delete;

    const SELECT = 0;
    const INSERT = 1;
    const UPDATE = 2;
    const DELETE = 3;
    const TRUNCATE = 4;
    const RAW = 5;
  

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
      $curr = $in;
      $out;
      while(true)
      {
        if (count($curr) === 1)
        {
          $curr = $curr[0];
          continue;
        }

        $out = [array_values($curr)];
        break;
      }

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

    protected function autoRun(bool $many=false)
    { 
      switch($this->types)
      {
        case self::SELECT:
          $this->prepare_select();
          return $this->run_select($many);
        case self::INSERT:
          $this->prepare_insert();
          return $this->run_insert($many);
        case self::UPDATE:
          $this->prepare_update();
          return $this->run_update();
        case self::DELETE:
          $this->prepare_delete();
          return $this->run_delete();
        case self::TRUNCATE:
          return;
        case self::RAW:
          return $this->dbh->__raw($this);
      }
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