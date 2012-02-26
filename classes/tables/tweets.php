<?PHP

class tweets {
	public $id;
	public $tweet;
	public $modified;
	public $userid;
	public $sid;
	public $linked_tem;
	public $max_uses;
	public $uses;
	
  function assign_vars() {
	$this->id  = $_POST['id'];
	$this->tweet  = $_POST['tweet'];
	$this->userid = $_POST['userid'];
	$this->sid = $_POST['sid'];
	$this->linked_tem = $_POST['linked_tem'];
	$this->max_uses = $_POST['max_uses'];
	$this->uses = $_POST['uses'];
  }
  
  function update() {
	  global $dbh;

	  $sql_query = "UPDATE
	  					tweets
					SET
						tweet = ?,
						linked_tem = ?,
						max_uses = ?,
						uses = ?
					WHERE
						id = ?
					AND
						userid = ?
					AND
						sid = ?
				  ;";

	  $values = array(
					  $this->tweet,
					  $this->linked_tem,
					  $this->max_uses,
					  $this->uses,
					  $this->id,
					  $this->userid,
					  $this->sid
					  );
	  
	  $stmt = $dbh->prepare($sql_query);
	  $stmt->execute($values);
  }
  
  function update_count() {
	  global $dbh;

	  $sql_query = "UPDATE
	  					tweets
					SET
						uses = uses + 1
					WHERE
						id = ?
					AND
						userid = ?
					AND
						sid = ?
				  ;";

	  $values = array(
					  $this->id,
					  $this->userid,
					  $this->sid
					  );
	  
	  $stmt = $dbh->prepare($sql_query);
	  $stmt->execute($values);
  }

  function post() {
	  global $dbh;

	  $sql_query = "INSERT 
				  INTO 
					  tweets
					  (
						tweet,
						linked_tem,
						max_uses,
						uses,
						userid,
						sid
					   )
				  VALUES 
					  (
					   ?,
					   ?,
					   ?,
					   ?,
					   ?,
					   ?
					   )
				  ;";

	  $values = array(
					  $this->tweet,
					  $this->linked_tem,
					  $this->max_uses,
					  $this->uses,
					  $this->userid,
					  $this->sid,
					  );
	  
	  $stmt = $dbh->prepare($sql_query);
	  $stmt->execute($values);
  }

  function get() {
		global $dbh;
		
		$sql_query = "SELECT 
						id,
						tweet,
						linked_tem,
						max_uses,
						uses,
						modified,
						userid,
						sid
					FROM 
						tweets 
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
		$this->tweet = $row['tweet'];
		$this->linked_tem = $row['linked_tem'];
		$this->max_uses = $row['max_uses'];
		$this->uses = $row['uses'];
		$this->modified = $row['modified'];
		$this->userid = $row['userid'];
		$this->sid = $row['sid'];

		// clear result set
		$result->free();
		
		// reset DB conn
		db_check_conn();
	
  }

  function get_random() {
			global $dbh;
		
		$sql_query = "SELECT 
						id,
						tweet
					FROM 
						tweets
					WHERE
						userid = ?
					AND
						sid = ?
					AND
						linked_tem = ?
					AND 
						((max_uses < uses) OR (max_uses = 0))
					ORDER BY RAND()
					LIMIT 1;";

		$values = array(
						$this->userid,
						$this->sid,
						$this->linked_tem,
						);
		
		$stmt = $dbh->prepare($sql_query);					 
		$result = $stmt->execute($values);
		
		$row = $result->fetchRow(MDB2_FETCHMODE_ASSOC);
		
		$this->id = $row['id'];
		$this->tweet = $row['tweet'];

		// clear result set
		$result->free();
		
		// reset DB conn
		db_check_conn();
	
  }

}

?>