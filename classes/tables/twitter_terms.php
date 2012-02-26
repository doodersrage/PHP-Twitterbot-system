<?PHP

class twitter_terms {
	public $id;
	public $userid;
	public $term;
	public $sid;
	public $datestart;
	public $dateend;
	public $city;
	public $state;
	public $radius;
	public $max_tweets;
	public $tweet_count;
	public $enabled;
	
  function assign_vars() {
	$this->id  = $_POST['id'];
	$this->term  = $_POST['term'];
	$this->userid = $_POST['userid'];
	$this->sid = $_POST['sid'];
	$this->datestart = $_POST['datestart'];
	$this->dateend = $_POST['dateend'];
	$this->city = $_POST['city'];
	$this->state = $_POST['state'];
	$this->radius = $_POST['radius'];
	$this->max_tweets = $_POST['max_tweets'];
	$this->tweet_count = $_POST['tweet_count'];
	$this->enabled = $_POST['enabled'];
  }
  
  function update_count() {
	  global $dbh;

	  $sql_query = "UPDATE
	  					twitter_terms
					SET
						tweet_count = tweet_count + 1
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
					  $this->sid,
					  );
	  
	  $stmt = $dbh->prepare($sql_query);
	  $stmt->execute($values);
  }
  
  function update() {
	  global $dbh;

	  $sql_query = "UPDATE
	  					twitter_terms
					SET
						term = ?,
						datestart = ?,
						dateend = ?,
						city = ?,
						state = ?,
						radius = ?,
						max_tweets = ?,
						tweet_count = ?,
						enabled = ?
					WHERE
						id = ?
					AND
						userid = ?
					AND
						sid = ?
				  ;";

	  $values = array(
					  $this->term,
					  $this->datestart,
					  $this->dateend,
					  $this->city,
					  $this->state,
					  $this->radius,
					  $this->max_tweets,
					  $this->tweet_count,
					  $this->enabled,
					  $this->id,
					  $this->userid,
					  $this->sid,
					  );
	  
	  $stmt = $dbh->prepare($sql_query);
	  $stmt->execute($values);
  }

  function post() {
	  global $dbh;

	  $sql_query = "INSERT 
				  INTO 
					  twitter_terms
					  (
						term,
						datestart,
						dateend,
						city,
						state,
						radius,
						max_tweets,
						tweet_count,
						enabled,
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
					   ?,
					   ?,
					   ?,
					   ?,
					   ?,
					   ?
					   )
				  ;";

	  $values = array(
					  $this->term,
					  $this->datestart,
					  $this->dateend,
					  $this->city,
					  $this->state,
					  $this->radius,
					  $this->max_tweets,
					  $this->tweet_count,
					  $this->enabled,
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
						term,
						datestart,
						dateend,
						city,
						state,
						radius,
						max_tweets,
						tweet_count,
						enabled,
						userid
					FROM 
						twitter_terms
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
		$this->term = $row['term'];
		$this->datestart = $row['datestart'];
		$this->dateend = $row['dateend'];
		$this->city = $row['city'];
		$this->state = $row['state'];
		$this->radius = $row['radius'];
		$this->max_tweets = $row['max_tweets'];
		$this->tweet_count = $row['tweet_count'];
		$this->enabled = $row['enabled'];
		$this->userid = $row['userid'];
		$this->sid = $row['sid'];

		// clear result set
		$result->free();
		
		// reset DB conn
		db_check_conn();
	
  }

}

?>