<?php 

	namespace PhiladelPhia\Database\Query;

	trait Update
	{   
		public function update(array $body)
		{
			foreach($body as $field => $value)
			{
				$this->fields[] = $field . "=:" . $field;
			}
			$this->values = $body;
			$this->types = self::UPDATE;
			return $this->autoRun();
		}

		public function updateOrInsert(array $where, array $body)
		{
			$arr = [];
			foreach($where as $key => $value)
			{
				$arr[] = $key;
				$arr[] = $value; 
			}

			$find = $this->where([$arr])
										->get();
			
			if($find->count > 0)
			{
				$response = $this->where([$arr])
										->update(array_merge($body, $where));
			}
			else 
			{
				$response = $this->insert($body);
			}

			return $response;
		}
	}