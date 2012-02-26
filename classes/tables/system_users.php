<?PHP

class system_users {
	public $id;
	public $email;
	public $password;
	public $added;
	public $last_login;
	public $last_ip;
	public $user_level;
	public $required = array(
							 'email',
							 'password',
							 'user_level'
							 );
	
  function __contruct(){
	  $this->clr_vars();
  }
	
  function clr_vars(){
	$this->id = '';
	$this->email = '';
	$this->password  = '';
	$this->added = '';
	$this->last_login = '';
	$this->last_ip = '';
	$this->user_level = '';
  }
	
  function assign_vars() {
	$this->id = $_POST['id'];
	$this->email = $_POST['email'];
	$this->password  = pass_salt($_POST['password']);
	$this->added = $_POST['added'];
	$this->last_login = $_POST['last_login'];
	$this->last_ip = $_POST['last_ip'];
	$this->user_level = $_POST['user_level'];
	$this->options = serialize($_POST['options']);
  }
  
  function update() {
	  global $dbh;

	  $sql_query = "UPDATE
	  					system_users
					SET
						email = ?,
						password = ?,
						user_level = ?
					WHERE
						id = ?
				  ;";

	  $values = array(
					  $this->email,
					  $this->password,
					  $this->user_level,
					  $this->id,
					  );
	  
	  $stmt = $dbh->prepare($sql_query);
	  $stmt->execute($values);
  }
  
  function update_options() {
	  global $dbh;

	  $sql_query = "UPDATE
	  					system_users
					SET
						email = ?,
						options = ?
					WHERE
						id = ?
				  ;";

	  $values = array(
					  $this->email,
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
					  system_users
					  (
						email,
						password,
						user_level,
						added
					   )
				  VALUES 
					  (
					   ?,
					   ?,
					   ?,
					   NOW()
					   )
				  ;";

	  $values = array(
					  $this->email,
					  $this->password,
					  $this->user_level
					  );
	  
	  $stmt = $dbh->prepare($sql_query);
	  $stmt->execute($values);
  }

  function get() {
		global $dbh;
		
		$sql_query = "SELECT 
						id,
						email,
						password,
						added,
						last_login,
						last_ip,
						user_level,
						options
					FROM 
						system_users
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
		$this->email = $row['email'];
		$this->password = $row['password'];
		$this->added = $row['added'];
		$this->last_login = $row['last_login'];
		$this->last_ip = $row['last_ip'];
		$this->user_level = $row['user_level'];
		$this->options = unserialize($row['options']);

		// clear result set
		$result->free();
		
		// reset DB conn
		db_check_conn();
	
  }
  
  function login_user(){
		global $dbh, $user_levels;
		
		$sql_query = "SELECT 
						id
					FROM 
						system_users
					WHERE
						email = ?
					AND
						password = ?
					LIMIT 1;";

		$values = array(
						$this->email,
						pass_salt($this->password)
						);
		
		$stmt = $dbh->prepare($sql_query);					 
		$result = $stmt->execute($values);
		
		$row = $result->fetchRow(MDB2_FETCHMODE_ASSOC);
		
		if($row['id'] > 0){
			
			$this->id = $row['id'];
			$this->get();
			$user_levels->id = $this->user_level;
			$user_levels->get();
			
			$_SESSION['user_logged_in'] = 1;
			$_SESSION['user_id'] = $this->id;
			$_SESSION['user_level'] = $this->user_level;
			$_SESSION['user_options'] = $user_levels->options;
			
		}
		
		// clear result set
		$result->free();
		
		// reset DB conn
		db_check_conn();
	
  }
  
}

?>