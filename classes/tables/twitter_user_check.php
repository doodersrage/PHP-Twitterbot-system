<?PHP

class twitter_user_check {
	public $id;
	public $userid;
	public $username;
	public $tweet_id;
	public $tweet;
	public $sent;
	public $sid;
	public $profile_image;
	
  function assign_vars() {
	$this->id  = $_POST['id'];
	$this->username  = $_POST['username'];
	$this->tweet_id = $_POST['tweet_id'];
	$this->tweet = $_POST['tweet'];
	$this->userid = $_POST['userid'];
	$this->sent = $_POST['sent'];
	$this->sid = $_POST['sid'];
	$this->profile_image = $_POST['profile_image'];
  }
  
  function update() {
	  global $dbh;

	  $sql_query = "UPDATE
	  					twitter_user_check
					SET
						username = ?,
						tweet_id = ?,
						tweet = ?,
						profile_image = ?
					WHERE
						id = ?
					AND
						sid = ?
				  ;";

	  $values = array(
					  $this->username,
					  $this->tweet_id,
					  $this->tweet,
					  $this->profile_image,
					  $this->id,
					  $this->sid,
					  );
	  
	  $stmt = $dbh->prepare($sql_query);
	  $stmt->execute($values);
  }

  function post() {
	  global $dbh;

	  $sql_query = "INSERT 
				  INTO 
					  twitter_user_check
					  (
						username,
						tweet_id,
						tweet,
						profile_image,
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
					  $this->username,
					  $this->tweet_id,
					  $this->tweet,
					  $this->profile_image,
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
						username,
						tweet_id,
						tweet,
						profile_image,
						userid,
						sent,
						sid
					FROM 
						twitter_user_check
					WHERE
						id = ?
					AND 
						sid = ?
					LIMIT 1;";

		$values = array(
						$this->id,
						$this->sid,
						);
		
		$stmt = $dbh->prepare($sql_query);					 
		$result = $stmt->execute($values);
		
		$row = $result->fetchRow(MDB2_FETCHMODE_ASSOC);
		
		$this->id = $row['id'];
		$this->username = $row['username'];
		$this->tweet_id = $row['tweet_id'];
		$this->tweet = $row['tweet'];
		$this->profile_image = $row['profile_image'];
		$this->userid = $row['userid'];
		$this->sent = $row['sent'];
		$this->sid = $row['sid'];

		// clear result set
		$result->free();
		
		// reset DB conn
		db_check_conn();
	
  }

}

?>