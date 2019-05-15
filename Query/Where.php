<?php 

  namespace PhiladelPhia\Database\Query;
  
  use PhiladelPhia\App\Exceptions;

	trait Where
	{
		protected function __where($args) {
      foreach($args as $key => $value) 
      {
        if ($key > 0 && $key < count($args))
        {
          $this->addAnd($this->where);
        }
        
        if (count($value) == 2)
        {
          list($field, $val) = $value;
          $this->where .= sprintf(
                                  "%s = %s",
                                  $field,
                                  self::$instance->quote($val));
          continue;
        }
        else if (count($value) == 3) 
        {
          list($field, $op, $val) = $value;
          self::$where .= sprintf(
                                  "%s %s %s",
                                  $field,
                                  self::operator($op),
                                  self::$instance->quote($val));
          continue;
        }
      }
      return self;
    }

    public static function where(...$args)
    {
      self::parse_args($args);

      if (!empty(self::$where))
      {
        self::addAnd(self::$where);
      }
      return self::__where($args);
    }

    public static function orWhere(...$args)
    {
      self::parse_args($args);

      if (!empty(self::$where))
      {
        self::addOr(self::$where);
      }

      return self::__where($args);
    }

    protected static function __between(string $field, array $between, bool $not=false)
    {
      $not = $not ? "NOT" : "";
      self::$where .= "{$field} {$not} BETWEEN {$between[0]} AND {$between[1]}";
      return self;
    }

    public static function whereBetween(string $field, array $between) 
    {
      if (!empty(self::$where))
      {
        self::addAnd(self::$where);
      }

      return self::__between($field, $between);
    }
    
    public static function orWhereBetween(string $field, array $between) 
    {
      if (!empty(self::$where))
      {
        self::addOr(self::$where);
      }

      return self::__between($field, $between);
    }
    
    public static function whereNotBetween(string $field, array $between)
    {
      if (!empty(self::$where))
      {
        self::addAnd(self::$where);
      }

      return self::__between($field, $between, true);
    }

    public static function orWhereNotBetween(string $field, array $between)
    {
      if (!empty(self::$where))
      {
        self::addOr(self::$where);
      }

      return self::__between($field, $between, true);
    }

    protected static function __whereIn(string $field, array $in, bool $not=false) 
    {
      $not = $not ? "NOT" : "";
      $in = implode(",", $in);
      self::$where .= "{$field} {$not} IN ({$in})";
    }
    
    public static function whereIn(string $field, array $in)
    {
      if (!empty(self::$where))
      {
        self::addAnd(self::$where);
      }
      
      return self::__whereIn($field, $in);
    }
    
    public static function orWhereIn(string $field, array $in)
    {
      if (!empty(self::$where))
      {
        self::addOr(self::$where);
      }
      
      return self::__whereIn($field, $in);
    }

    public static function whereNotIn(string $field, array $in)
    {
      if (!empty(self::$where))
      {
        self::addAnd(self::$where);
      }
      
      return self::__whereIn($field, $in, true);
    }
    
    public static function orWhereNotIn(string $field, array $in)
    {
      if (!empty(self::$where))
      {
        self::addOr(self::$where);
      }
      
      return self::__whereIn($field, $in, true);
    }

    protected static function __whereNull(string $field, bool $not=false)
    {
      $not = $not ? "NOT" : "";
      self::$where .= "{$field} IS {$not} NULL";
    }

    public static function whereNull(string $field) 
    {
      if (!empty(self::$where))
      {
        self::addAnd(self::$where);
      }

      return self::__whereNull($field);
    }

    public static function orWhereNull(string $field)
    {
      if (!empty(self::$where))
      {
        self::addOr(self::$where);
      }

      return self::__whereNull($field);
    }

    public static function whereNotNull(string $field)
    {
      if (!empty(self::$where))
      {
        self::addAnd(self::$where);
      }

      return self::__whereNull($field, true);
    }

    public static function orWhereNotNull(string $field)
    {
      if (!empty(self::$where))
      {
        self::addOr(self::$where);
      }

      return self::__whereNull($field, true);
    }

    protected static function __whereColumn($args) 
    {
      foreach($args as $key => $value) 
      {
        if ($key > 0 && $key < count($args))
        {
          self::addAnd(self::$where);
        }

        if (count($value) == 2)
        {
          list($field, $val) = $value;
          self::$where .= "{$field} = {$val}";
          continue;
        }
        else if (count($value) == 3) 
        {
          list($field, $op, $field) = $value;
          self::$where .= sprintf(
                                  "%s %s %s",
                                  $field,
                                  self::operator($op),
                                  $field
          );
          continue;
        }
      }

      return self;
    }

		public static function whereColumn(...$args)
		{
      self::parse_args($args);

      if (!empty(self::$where))
      {
        self::addAnd(self::$where);
      }
      
      return self::__whereColumn($args);
    }
    
    public static function orWhereColumn(...$args)
    {
      self::parse_args($args);

      if (!empty(self::$where))
      {
        self::addOr(self::$where);
      }
      
      return self::__whereColumn($args);
    }

		public static function whereTime()
		{

		}

		public static function whereDay()
		{

		}

		public static function whereMonth() 
		{

		}

		public static function whereYear() 
		{

		}

		public static function whereDate(string $date) 
		{
      if (!empty(self::$where))
      {
        self::addAnd(self::$where);
      }

      $date = date("Y-m-d", strtotime($date)); 

      // self::$where .= ""

      return self;
		}

		public static function whereRaw() 
		{

		}

		public static function having()
		{

		}
	}