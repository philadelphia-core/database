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

    function __construct()
    {
      self::$builder = new Builder;
      self::$builder::$DBInstance->setFetchMode(PDO::FETCH_CLASS, 'Model');
      self::$builder->table = $this->table;
    }
    
    public static function __callStatic($name, $arguments)
    {
      $builder = self::$builder;
      return $builder->{$name}($arguments);
    }
  }