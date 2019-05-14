<?php

  namespace PhiladelPhia\Database;

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

    public static $instance;

    protected static $sql;
    protected static $select;
    protected static $skip;
    protected static $limit;
    protected static $orderBy;
    protected static $where;
    protected static $fields = [];
    protected static $colonFields = [];
    protected static $values = [];
    protected static $types;
    protected static $omit;
    protected static $as;
    protected static $count;

    public static $operators = [
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
      self::$instance = $instance;
    }


    /**
     * Add to sentence sql the property `AND`
     */
    protected static function addAnd(&$sql)
    {
      return $sql .= " AND ";
    }

    /**
     * Add to sentence sql the property `OR`
     */
    protected static function addOr(&$sql)
    {
      return $sql .= " OR ";
    }

    /**
     * Add to sentence sql the property `NOT`
     */
    protected static function addNot(&$sql)
    {
      return $sql .= " NOT ";
    }

    /**
     * Evaluate a operator, if is valid.
     */
    protected static function invalidOperator($operator) 
    {
      return !in_array(strtolower($operator), self::$operators, true);
    }

    protected static function operator($operator)
    {
      if (self::invalidOperator($operator))
      {
        return $operator;
      }
      else 
      {
        throw new Exceptions("Operator invalid $operator");
      }
    }

    protected static function parse_args(&$in)
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

    protected static function prepare_select()
    {
      $select = null;
      $where = null;

      if (!empty(self::$select))
      {
        $select = self::$select;
      }
      self::$select = $select ?: "*";

      if (!empty(self::$where))
      {
        $where = self::$where;
      }
      self::$where = $where ? " WHERE {$where}" : "";
      
      self::$sql = sprintf(
                            " %s FROM %s %s %s %s %s %s", 
                            self::$select, 
                            self::$table, 
                            self::$as, 
                            self::$where, 
                            self::$orderBy, 
                            self::$limit, 
                            self::$skip);
    }
    
    protected static function prepare_insert()
    {
      self::$fields = implode(",", self::$fields);
      self::$colonFields = implode(",", self::$colonFields);
      self::$sql = sprintf(
                            "%s %s (%s) VALUES (%s)",
                            self::$table,
                            self::$as,
                            self::$fields,
                            self::$colonFields);
    }
    
    protected static function prepare_update()
    {
      $where = null;
      self::$fields = implode(",", self::$fields);

      if (!empty(self::$where))
      {
        $where = self::$where;
      }
      self::$where = $where ? " WHERE {$where}" : "";

      self::$sql = sprintf(
                            "%s %s SET %s %s",
                            self::$table,
                            self::$as,
                            self::$fields,
                            self::$where);
    }

    protected static function prepare_delete() 
    {

    }

    protected static function autoRun()
    {
      $response = null;
      
      switch(self::$types)
      {
        case 0:
          self::prepare_select();
          
          if (self::$omit)
          {
            break;
          }
          
          if (self::$count) {
            $response = self::$instance->__select(self)->fetchColumn();
            $response = (object) array(
              'count' => $response
            ); 
            break;
          }

          $response = self::$instance->__select(self)->fetch();
          
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
          self::prepare_select();
          
          if (self::$omit)
          {
            break;
          }
          
          $response = self::$instance->__select(self);
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
          self::prepare_insert();

          if (self::$omit)
          {
            break;
          }
          
          $response = self::$instance->__insert(self);
          $response = (object) array(
            'id' => $response);
          break;
        case 3:
          self::prepare_insert();
          
          if (self::$omit)
          {
            break;
          }
          
          $response = self::$instance->__insertMany(self);
          $response = (object) array(
            'id' => $response);
          break;
        case 4:
          self::prepare_update();
          
          if (self::$omit)
          {
            break;
          }
          
          $response = self::$instance->__update(self);
          break;
        case 5:
          self::prepare_delete();
          
          if (self::$omit)
          {
            break;
          }
          
          $response = self::$instance->__delete(self);
          $response = (object) array(
            'id'
          );
          break;
      }

      self::resetProperties();
      
      return $response;
    }

    public static function resetProperties()
    {
      self::$sql = null;
      self::$select = null;
      self::$skip = null;
      self::$limit = null;
      self::$orderBy = null;
      self::$where = null;
      self::$fields = [];
      self::$colonFields = [];
      self::$values = [];
      self::$types = null;
      self::$as = null;
      self::$count = null;
    }
  }