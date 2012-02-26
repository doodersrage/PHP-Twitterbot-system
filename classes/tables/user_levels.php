<?PHP

class user_levels {
	public $id;
	public $name;
	public $description;
	public $cost;
	public $options;
	public $required = array(
							 'name',
							 );
	
  function assign_vars() {
	$this->id  = $_POST['id'];
	$this->name  = $_POST['name'];
	$this->description = $_POST['description'];
	$this->cost = $_POST['cost'];
	$this->options = serialize($_POST['options']);
  }
  
  function update() {
	  global $dbh;

	  $sql_query = "UPDATE
	  					user_levels
					SET
						name = ?,
						description = ?,
						cost = ?,
						options = ?
					WHERE
						id = ?
				  ;";

	  $values = array(
					  $this->name,
					  $this->description,
					  $this->cost,
					  $this->options,
					  $this->id,
					  );
	  
	  $stmt = $dbh->prepare($sql_query);
	  $stmt->execute($values);
  }

  function post() {
	  global $dbh;

	  $sql_query = "INSERT 
				  INTO 
					  user_levels
					  (
						name,
						description,
						cost,
						options
					   )
				  VALUES 
					  (
					   ?,
					   ?,
					   ?,
					   ?
					   )
				  ;";

	  $values = array(
					  $this->name,
					  $this->description,
					  $this->cost,
					  $this->options
					  );
	  
	  $stmt = $dbh->prepare($sql_query);
	  $stmt->execute($values);
  }

  function get() {
		global $dbh;
		
		$sql_query = "SELECT 
						id,
						name,
						description,
						cost,
						options
					FROM 
						user_levels
					WHERE
						id = ?
					LIMIT 1;";

		$values = array(
						$this->id
						);
		
		$stmt = $dbh->prepare($sql_query);					 
		$result = $stmt->execute($values);
		
		$row = $result->fetchRow(MDB2_FETCHMODE_ASSOC);
		
		$this->id = $row['id'];
		$this->name = $row['name'];
		$this->description = $row['description'];
		$this->cost = $row['cost'];
		$this->options = unserialize($row['options']);

		// clear result set
		$result->free();
		
		// reset DB conn
		db_check_conn();
	
  }

  function get_all() {
		global $dbh;
		
		$sql_query = "SELECT 
						id,
						name,
						description,
						cost,
						options
					FROM 
						user_levels
					;";

		$stmt = $dbh->prepare($sql_query);					 
		$result = $stmt->execute();
		
		while($row = $result->fetchRow(MDB2_FETCHMODE_ASSOC)){
			$rows[] = $row;
		}

		// clear result set
		$result->free();
		
		// reset DB conn
		db_check_conn();
  
  return $rows;
  }

}

?>