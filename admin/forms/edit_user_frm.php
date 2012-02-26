<?PHP

// display edit user form
function edit_user_frm() {
  global $dbh, $message, $twitter_users;
  
	
	// get tweet count
  $sql_query = "SELECT
				  count(*) as cnt
			   FROM
				  twitter_users
				WHERE
					sid = ? ;";
					
  $values = array(
				  $_SESSION['user_id']
				  );
  
  $stmt = $dbh->prepare($sql_query);					 
  $result = $stmt->execute($values);
  $row = $result->fetchRow(MDB2_FETCHMODE_ASSOC);
  $tweetCnt = $row['cnt'];

  if($_SESSION['user_options']['twitter_accounts'] == 0 || $tweetCnt < $_SESSION['user_options']['twitter_accounts'] || !empty($_GET['userid'])) {
	$twitter_users->id = $_GET['userid'];
	$twitter_users->sid = $_SESSION['user_id'];
	$twitter_users->get();
	
	$op = '<form id="form1" name="form1" method="post" action="?mode=process_user">
	<table border="0" cellspacing="3" cellpadding="3" class="user_tbl" align="center">
	  <tr>
		<th colspan="2">Edit User Account</th>
	  </tr>
	  <tr>
		<td>Username:</td>
		<td><input name="username" type="text" size="20" maxlength="30" value="'.htmlentities($twitter_users->username, ENT_QUOTES).'"></td>
	  </tr>
	  <tr>
		<td>Password:</td>
		<td><input name="password" type="text" size="20" maxlength="30" value="'.htmlentities($twitter_users->password, ENT_QUOTES).'"></td>
	  </tr>
	  <tr>
		<td>API key:</td>
		<td><input name="api_key" type="text" size="20" value="'.htmlentities($twitter_users->api_key, ENT_QUOTES).'"></td>
	  </tr>
	  <tr>
		<td>Consumer key:</td>
		<td><input name="consumer_key" type="text" size="20" value="'.htmlentities($twitter_users->consumer_key, ENT_QUOTES).'"></td>
	  </tr>
	  <tr>
		<td>Consumer secret:</td>
		<td><input name="consumer_secret" type="text" size="20" value="'.htmlentities($twitter_users->consumer_secret, ENT_QUOTES).'"></td>
	  </tr>
	  <tr>
		<td>OAuthToken:</td>
		<td><input name="oauthtoken" type="text" size="20" value="'.htmlentities($twitter_users->oauthtoken, ENT_QUOTES).'"></td>
	  </tr>
	  <tr>
		<td>OAuthSecret:</td>
		<td><input name="oauthsecret" type="text" size="20" value="'.htmlentities($twitter_users->oauthsecret, ENT_QUOTES).'"></td>
	  </tr>
	  <tr>
		<td>Enabled:</td>
		<td><input name="enabled" type="checkbox" value="1" '.($twitter_users->enabled == 1 ? 'checked' : '').' /></td>
	  </tr>
	  <tr>
		<td colspan="2" align="center"><input name="id" type="hidden" value="'.$twitter_users->id.'"><input name="Submit" type="submit" value="Submit"></td>
	  </tr>
	</table>
	</form>';
  } else {
	  $message = 'Your account does not allow you to add anymore users.';
  }
 
return $op;
}

?>