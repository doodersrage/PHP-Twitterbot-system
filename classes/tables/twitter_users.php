<?PHP

class twitter_users {
	public $id;
	public $username;
	public $password;
	public $enabled;
	public $sid;
	public $api_key;
	public $consumer_key;
	public $consumer_secret;
	public $oauthtoken;
	public $oauthsecret;
	
  function assign_vars() {
	$this->id  = $_POST['id'];
	$this->username  = $_POST['username'];
	$this->userid = $_POST['userid'];
	$this->enabled = $_POST['enabled'];
	$this->sid = $_POST['sid'];
	$this->api_key = $_POST['api_key'];
	$this->consumer_key = $_POST['consumer_key'];
	$this->consumer_secret = $_POST['consumer_secret'];
	$this->oauthtoken = $_POST['oauthtoken'];
	$this->oauthsecret = $_POST['oauthsecret'];
  }
  
  function update() {
	  global $dbh;

	  $sql_query = "UPDATE
	  					twitter_users
					SET
						username = ?,
						password = ?,
						enabled = ?,
						api_key = ?,
						consumer_key = ?,
						consumer_secret = ?,
						oauthtoken = ?,
						oauthsecret = ?
					WHERE
						id = ?
					AND
						sid = ?
				  ;";

	  $values = array(
					  $this->username,
					  $this->password,
					  $this->enabled,
					  $this->api_key,
					  $this->consumer_key,
					  $this->consumer_secret,
					  $this->oauthtoken,
					  $this->oauthsecret,
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
					  twitter_users
					  (
						username,
						password,
						enabled,
						api_key,
						consumer_key,
						consumer_secret,
						oauthtoken,
						oauthsecret,
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
					   ?
					   )
				  ;";

	  $values = array(
					  $this->username,
					  $this->password,
					  $this->enabled,
					  $this->api_key,
					  $this->consumer_key,
					  $this->consumer_secret,
					  $this->oauthtoken,
					  $this->oauthsecret,
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
						password,
						enabled,
						api_key,
						consumer_key,
						consumer_secret,
						oauthtoken,
						oauthsecret,
						sid
					FROM 
						twitter_users
					WHERE
						id = ?
					AND 
						sid = ?
					LIMIT 1;";

		$values = array(
						$this->id,
						$this->sid
						);
		
		$stmt = $dbh->prepare($sql_query);					 
		$result = $stmt->execute($values);
		
		$row = $result->fetchRow(MDB2_FETCHMODE_ASSOC);
		
		$this->id = $row['id'];
		$this->username = $row['username'];
		$this->password = $row['password'];
		$this->enabled = $row['enabled'];
		$this->api_key = $row['api_key'];
		$this->consumer_key = $row['consumer_key'];
		$this->consumer_secret = $row['consumer_secret'];
		$this->oauthtoken = $row['oauthtoken'];
		$this->oauthsecret = $row['oauthsecret'];
		$this->sid = $row['sid'];

		// clear result set
		$result->free();
		
		// reset DB conn
		db_check_conn();
	
  }

}

?>