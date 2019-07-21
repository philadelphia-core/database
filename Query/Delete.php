<?php 

	namespace PhiladelPhia\Database\Query;

	trait Delete
	{
		public function delete() 
		{
			$this->types = self::DELETE;
			return $this->autoRun();
		}
	
	
		public function truncate()
		{
			$this->types = self::TRUNCATE;
			return $this->autoRun();
		}
	}