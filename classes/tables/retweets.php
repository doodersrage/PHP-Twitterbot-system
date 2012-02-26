<?PHP

class retweets {
	public $id;
	public $screen_name;
	public $profile_image_url;
	public $url;
	public $friends_count;
	public $followers_count;
	public $favourites_count;
	public $listed_count;
	public $name;
	public $data;
	public $userid;
	public $sid;
	public $rt_date;
	
  function reset_vars() {
	$this->id = '';
	$this->screen_name = '';
	$this->profile_image_url = '';
	$this->url = '';
	$this->friends_count = '';
	$this->followers_count = '';
	$this->favourites_count = '';
	$this->listed_count = '';
	$this->name = '';
	$this->data = '';
	$this->userid = '';
	$this->sid = '';
	$this->rt_date = '';
  }
	
  function assign_vars() {
	$this->id  = $_POST['id'];
	$this->screen_name  = $_POST['screen_name'];
	$this->profile_image_url = $_POST['profile_image_url'];
	$this->url = $_POST['url'];
	$this->friends_count = $_POST['friends_count'];
	$this->followers_count = $_POST['followers_count'];
	$this->favourites_count = $_POST['favourites_count'];
	$this->listed_count = $_POST['listed_count'];
	$this->name = $_POST['name'];
	$this->data = $_POST['data'];
	$this->userid = $_POST['userid'];
	$this->sid = $_POST['sid'];
	$this->rt_date = $_POST['rt_date'];
  }
  
  function update() {
	  global $dbh;

	  $sql_query = "UPDATE
	  					retweets
					SET
						screen_name = ?,
						profile_image_url = ?,
						url = ?,
						friends_count = ?,
						followers_count = ?,
						favourites_count = ?,
						listed_count = ?,
						name = ?,
						data = ?,
						rt_date = ?
					WHERE
						id = ?
					AND
						userid = ?
					AND
						sid = ?
				  ;";

	  $values = array(
					  $this->screen_name,
					  $this->profile_image_url,
					  $this->url,
					  $this->friends_count,
					  $this->followers_count,
					  $this->favourites_count,
					  $this->listed_count,
					  $this->name,
					  $this->data,
					  $this->rt_date,
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
					  retweets
					  (
						id,
						screen_name,
						profile_image_url,
						url,
						friends_count,
						followers_count,
						favourites_count,
						listed_count,
						name,
						data,
						userid,
						sid,
						rt_date
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
					   ?,
					   ?,
					   ?
					   )
				  ;";

	  $values = array(
					  $this->id,
					  $this->screen_name,
					  $this->profile_image_url,
					  $this->url,
					  $this->friends_count,
					  $this->followers_count,
					  $this->favourites_count,
					  $this->listed_count,
					  $this->name,
					  $this->data,
					  $this->userid,
					  $this->sid,
					  $this->rt_date
					  );
	  
	  $stmt = $dbh->prepare($sql_query);
	  $stmt->execute($values);
  }

  function get() {
		global $dbh;
		
		$sql_query = "SELECT 
						id,
						screen_name,
						profile_image_url,
						url,
						friends_count,
						followers_count,
						favourites_count,
						listed_count,
						name,
						data,
						userid,
						sid,
						rt_date
					FROM 
						retweets 
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
		$this->screen_name = $row['screen_name'];
		$this->profile_image_url = $row['profile_image_url'];
		$this->url = $row['url'];
		$this->friends_count = $row['friends_count'];
		$this->followers_count = $row['followers_count'];
		$this->favourites_count = $row['favourites_count'];
		$this->listed_count = $row['listed_count'];
		$this->name = $row['name'];
		$this->data = unserialize($row['data']);
		$this->userid = $row['userid'];
		$this->sid = $row['sid'];
		$this->rt_date = $row['rt_date'];

		// clear result set
		$result->free();
		
		// reset DB conn
		db_check_conn();
	
  }

}

?>