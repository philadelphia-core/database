<?php 

  namespace PhiladelPhia\Database;
  
  use PDO;
  use PhiladelPhia\App\Exceptions;
  use PhiladelPhia\Database\Builder;
  use PhiladelPhia\Database\Interfaces\ManagerInterface;
  use PhiladelPhia\Database\Interfaces\BuilderInterface;

  class Model
  {
    protected static $builder;

    function __construct($table = null)
    {
      self::$builder = new Builder;
      if (!empty($table))
      {
        self::$builder->table = $table;
      }
      else 
      {
        self::$builder->table = $this->table;
      }
    }
    
    public static function __callStatic($name, $arguments)
    {
      $builder = self::$builder;
      $builder->model = self::class;

      return $builder->{$name}($arguments);
    }

    public function save() 
    {
       
    }

    public function delete() {

    }
  }