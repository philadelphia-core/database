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
      self::$builder->model = get_class($this);
      self::$builder->table  = $this->table ?? get_class($this);
    }
    
    public static function __callStatic($name, $arguments)
    {
      if (property_exists(self, $name))
      {
        return self::$name($arguments);
      }

      return self::$builder->{$name}($arguments);
    }

    public static function find($id) 
    {
      
    }

    public static function create() 
    {

    }

    public function fresh() 
    {

    }

    public function refresh() 
    {

    }

    public function fill() 
    {

    }

    public function save() 
    {
      unset($this->table);

      $array = (array)$this;
      
    }

    public function delete() {

    }

    public function destroy()
    {

    }

  }