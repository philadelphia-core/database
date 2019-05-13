<?php

  namespace PhiladelPhia\Database;

  use PhiladelPhia\App\Exceptions;
  use PhiladelPhia\Database\Interfaces\BuilderInterface;
  use PhiladelPhia\Database\Manager;

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

    protected $sql;
    protected $select;
    protected $skip;
    protected $limit;
    protected $orderBy;
    protected $where;
    protected $fields = [];
    protected $colonFields = [];
    protected $values = [];
    protected $db;
    protected $types;
    protected $omit;
    protected $as;
    protected $count;

    public $operators = [
      '=', '<', '>', '<=', '>=', '<>', '!=', '<=>',
      'like', 'like binary', 'not like', 'ilike',
      '&', '|', '^', '<<', '>>',
      'rlike', 'regexp', 'not regexp',
      '~', '~*', '!~', '!~*', 'similar to',
      'not similar to', 'not ilike', '~~*', '!~~*',
    ];

    /**
     * instance Database
     */
    protected function __construct() 
    {
      $this->db = new Database();
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
      return !in_array(strtolower($operator), $this->operators, true);
    }

    protected function parse_args(&$in)
    {
      if (key_exists(0, $in) 
            && (count($in) <= 1) 
              && is_array($in[0]))
      {
        return $in = $in[0];
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
      
      return $in = [$out];
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
      $this->where = $where ? " WHERE " . $where : "";
      
      $this->sql = " {$this->select} FROM {$this->table} {$this->as} {$this->where} {$this->orderBy} {$this->limit} {$this->skip}";
    }
    
    protected function prepare_insert()
    {
      $this->fields = implode(",", $this->fields);
      $this->colonFields = implode(",", $this->colonFields);
      $this->sql = " {$this->table} {$this->as} ({$this->fields}) VALUES ({$this->colonFields}) ";
    }

    protected function prepare_insertMany()
    {
      $this->fields = implode(",", $this->fields);
      $this->colonFields = implode(",", $this->colonFields);
      $this->sql = " {$this->table} {$this->as} ({$this->fields}) VALUES ({$this->colonFields}) ";
    }
    
    protected function prepare_update()
    {
      $where = null;
      $this->fields = implode(",", $this->fields);

      if (!empty($this->where))
      {
        $where = $this->where;
      }
      $this->where = $where ? " WHERE " . $where : "";

      $this->sql = " {$this->table} {$this->as} SET {$this->fields} {$this->where}";
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
          
          if ($this->omit)
          {
            break;
          }
          
          if ($this->count) {
            $response = $this->db->__select($this)->fetchColumn();
            $response = (object) array(
              'count' => $response
            ); 
            break;
          }

          $response = $this->db->__select($this)->fetch();
          
          if (!$response)
          {
            $response = (object) array(
              'item' => array());
            break;
          }
          
          $response = (object) array(
            'item' => $response);
          break;
        case 1:
          $this->prepare_select();
          
          if ($this->omit)
          {
            break;
          }
          
          $response = $this->db->__select($this);
          if (!$response->rowCount() > 0)
          {
            $response = (object) array(
              'items' => [],
              'count' => 0);
          }
          $response = (object) array(
                      'items' => $response->fetchAll(),
                      'count' => $response->rowCount());
          break;
        case 2:
          $this->prepare_insert();

          if ($this->omit)
          {
            break;
          }
          
          $response = $this->db->__insert($this);
          $response = (object) array(
            'id' => $response);
          break;
        case 3:
          $this->prepare_insertMany();
          
          if ($this->omit)
          {
            break;
          }
          
          $response = $this->db->__insertMany($this);
          $response = (object) array(
            'id' => $response);
          break;
        case 4:
          $this->prepare_update();
          
          if ($this->omit)
          {
            break;
          }
          
          $response = $this->db->__update($this);
          break;
        case 5:
          $this->prepare_delete();
          
          if ($this->omit)
          {
            break;
          }
          
          $response = $this->db->__delete($this);
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
      $self->sql = null;
      $self->select = null;
      $self->skip = null;
      $self->limit = null;
      $self->orderBy = null;
      $self->where = null;
      $self->fields = [];
      $self->colonFields = [];
      $self->values = [];
      $self->types = null;
      $self->as = null;
      $self->count = null;
    }
  }