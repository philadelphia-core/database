<?php 

	namespace PhiladelPhia\Database\Query;

	use PhiladelPhia\App\Exceptions;

	trait Select
	{
		public function select(...$args)
		{
			$this->parse_args($args);

			$this->select = implode(",", $args);
			return $this;
		}   

		public function selectRaw(String $raw) 
		{
			$this->sql = $raw;
			return $this;
		}

		public function skip(int $n) 
		{
			if (empty($this->limit) && empty($this->take) && empty($this->offset))
			{
				throw new Exceptions(
					"Function skip need spefitly the limit, 
					please used the functions as `take`, `limit`, `offset`");
			}
			
			$this->skip = " OFFSET {$n} ";
			return $this;
		}

		public function limit(int $n)
		{
			$this->limit = " LIMIT {$n} ";
			return $this;
		}

		public function values(...$args)
		{
			$this->types = self::SELECT;
			$this->select = implode(",", $args);
			return $this->autoRun();
		}
		
		public function exists()
		{
			if (empty($this->where))
			{
				throw new Exceptions("Not found sql with sentence WHERE before");  
			}
				
			return (empty($this->first()) ? false : true);
		}
	
		public function doesntExist() 
		{
			if (empty($this->where))
			{
				throw new Exceptions("Not found sql with sentence WHERE before");  
			}

			return (empty($this->first()) ? true : false);
		}

		public function find(?array $args) 
		{
			$Ids = $args[0];

			if(!is_string($Ids) && is_array($Ids) && !empty($Ids))
			{
				foreach($Ids as $id)
				{
					$this->orWhere('id', $id);
				}
			}
			else 
			{
				$this->where('id', $Ids);
			}

			return $this->get();
		}

		public function get() 
		{
			$this->types = self::SELECT;
			return $this->autoRun(true);
		}
		
		public function first()
		{
			$this->types = self::SELECT;
			return $this->autoRun();
		}

	}