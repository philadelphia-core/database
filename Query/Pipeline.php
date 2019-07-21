<?php 

  namespace PhiladelPhia\Database\Query;

  trait Pipeline
  {
    public function count(...$args) 
    { 
      if (!empty($args))
      {
        $this->parse_args($args);
      }
      if (!empty($args) && is_array($args))
      {
        $sql = implode(",", $args);
        $this->select = "COUNT({$sql})";
      }
      else 
      {
        $this->select = "COUNT(*)";
      }
      $this->count = true;
      $this->types = self::SELECT;
      return $this->autoRun();
		}
    
		public function avg(string $field) 
		{
      $this->select = "AVG({$field})";
      $this->types = self::SELECT;
			return $this->autoRun();
		}
		
		public function max(string $field) 
		{
      $this->select = "MAX({$field})";
      $this->types = self::SELECT;
			return $this->autoRun();
    }

    protected function __variant(string $field, int $inc=1, array $bonus=null, bool $bool) {
      $bool = $bool ? "+" : "-";
      $variant = [$field => "{$field} {$bool} {$inc}"];

      if ($bonus) 
      {
        $variant = array_merge($variant, $bonus);
      } 

      foreach($variant as $key => $value)
			{
        if ($key === $field) 
        {
          $this->fields[] = "{$field} = {$value}"; 
          continue;
        }

				$this->fields[] = $field . "=:" . $field;
      }
      
			$this->values = $body;
			$this->types = self::UPDATE;
			return $this->autoRun();
    }
    
    public function increment(string $field, int $inc=1, array $bonus=null) 
		{
      return $this->__variant($field, $inc, $bonus, true);
		}

		public function decrement(string $field, int $dec, array $bonus) 
		{
      return $this->__variant($field, $inc, $bonus, false);
		}
  }