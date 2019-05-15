<?php 

  namespace PhiladelPhia\Database\Query;
  
  use PhiladelPhia\App\Exceptions;

	trait Where
	{
    private function __where($args) 
    {
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
                                  self::$DBInstance->quote($val));
          continue;
        }
        else if (count($value) == 3) 
        {
          list($field, $op, $val) = $value;
          $this->where .= sprintf(
                                  "%s %s %s",
                                  $field,
                                  $this->operator($op),
                                  self::$DBInstance->quote($val));
          continue;
        }
      }
      return $this;
    }

    public function where(...$args)
    {
      $this->parse_args($args);

      if (!empty($this->where))
      {
        $this->addAnd($this->where);
      }
      return $this->__where($args);
    }

    public function orWhere(...$args)
    {
      $this->parse_args($args);

      if (!empty($this->where))
      {
        $this->addOr($this->where);
      }

      return $this->__where($args);
    }

    private function __between(string $field, array $between, bool $not=false)
    {
      $not = $not ? "NOT" : "";
      $this->where .= "{$field} {$not} BETWEEN {$between[0]} AND {$between[1]}";
      return $this;
    }

    public function whereBetween(string $field, array $between) 
    {
      if (!empty($this->where))
      {
        $this->addAnd($this->where);
      }

      return $this->__between($field, $between);
    }
    
    public function orWhereBetween(string $field, array $between) 
    {
      if (!empty($this->where))
      {
        $this->addOr($this->where);
      }

      return $this->__between($field, $between);
    }
    
    public function whereNotBetween(string $field, array $between)
    {
      if (!empty($this->where))
      {
        $this->addAnd($this->where);
      }

      return $this->__between($field, $between, true);
    }

    public function orWhereNotBetween(string $field, array $between)
    {
      if (!empty($this->where))
      {
        $this->addOr($this->where);
      }

      return $this->__between($field, $between, true);
    }

    private function __whereIn(string $field, array $in, bool $not=false) 
    {
      $not = $not ? "NOT" : "";
      $in = implode(",", $in);
      $this->where .= "{$field} {$not} IN ({$in})";
      return $this;
    }
    
    public function whereIn(string $field, array $in)
    {
      if (!empty($this->where))
      {
        $this->addAnd($this->where);
      }
      
      return $this->__whereIn($field, $in);
    }
    
    public function orWhereIn(string $field, array $in)
    {
      if (!empty($this->where))
      {
        $this->addOr($this->where);
      }
      
      return $this->__whereIn($field, $in);
    }

    public function whereNotIn(string $field, array $in)
    {
      if (!empty($this->where))
      {
        $this->addAnd($this->where);
      }
      
      return $this->__whereIn($field, $in, true);
    }
    
    public function orWhereNotIn(string $field, array $in)
    {
      if (!empty($this->where))
      {
        $this->addOr($this->where);
      }
      
      return $this->__whereIn($field, $in, true);
    }

    private function __whereNull(string $field, bool $not=false)
    {
      $not = $not ? "NOT" : "";
      $this->where .= "{$field} IS {$not} NULL";
      return $this;
    }

    public function whereNull(string $field) 
    {
      if (!empty($this->where))
      {
        $this->addAnd($this->where);
      }

      return $this->__whereNull($field);
    }

    public function orWhereNull(string $field)
    {
      if (!empty($this->where))
      {
        $this->addOr($this->where);
      }

      return $this->__whereNull($field);
    }

    public function whereNotNull(string $field)
    {
      if (!empty($this->where))
      {
        $this->addAnd($this->where);
      }

      return $this->__whereNull($field, true);
    }

    public function orWhereNotNull(string $field)
    {
      if (!empty($this->where))
      {
        $this->addOr($this->where);
      }

      return $this->__whereNull($field, true);
    }

    private function __whereColumn($args) 
    {
      foreach($args as $key => $value) 
      {
        if ($key > 0 && $key < count($args))
        {
          $this->addAnd($this->where);
        }

        if (count($value) == 2)
        {
          list($field, $val) = $value;
          $this->where .= "{$field} = {$val}";
          continue;
        }
        else if (count($value) == 3) 
        {
          list($field, $op, $field) = $value;
          $this->where .= sprintf(
                                  "%s %s %s",
                                  $field,
                                  $this->operator($op),
                                  $field
          );
          continue;
        }
      }

      return $this;
    }

		public function whereColumn(...$args)
		{
      $this->parse_args($args);

      if (!empty($this->where))
      {
        $this->addAnd($this->where);
      }
      
      return $this->__whereColumn($args);
    }
    
    public function orWhereColumn(...$args)
    {
      $this->parse_args($args);

      if (!empty($this->where))
      {
        $this->addOr($this->where);
      }
      
      return $this->__whereColumn($args);
    }

		public function whereTime()
		{

		}

		public function whereDay()
		{

		}

		public function whereMonth() 
		{

		}

		public function whereYear() 
		{

		}

		public function whereDate(string $date) 
		{
      if (!empty($this->where))
      {
        $this->addAnd($this->where);
      }

      $date = date("Y-m-d", strtotime($date)); 

      // self::$where .= ""

      return $this;
		}

		public static function whereRaw() 
		{

		}

		public static function having()
		{

		}
	}