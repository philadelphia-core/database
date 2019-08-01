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

    public static function __callStatic($name, $arguments)
    {
      $instance = new static;
      $builder = new Builder;
      $builder->model = get_class($instance);
      $builder->table = $instance->table ?? get_class($instance);
      return $builder->{$name}($arguments);
    }

    public static function find($id) 
    {
      return self::$builder->find($id);
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

      if (!property_exists($this, 'id')) {
        return self::$builder->insert((array)$this);
      }

      $id = $this->id;
      unset($this->id);

      return self::$builder
                          ->where('id', $id)
                          ->update((array)$this);
    }

    public function delete() {

    }

    public function destroy()
    {

    }

  }