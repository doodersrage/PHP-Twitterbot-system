<?PHP

// edit tweet string
function edit_tweet_frm() {
  global $dbh, $message, $tweets;
  
   $sql_query = "SELECT
				  count(*) as cnt
			   FROM
				  tweets
			   WHERE
			       userid = ?
				AND
					sid = ?
				;";
				
  $values = array(
				  $_GET['userid'],
				  $_SESSION['user_id'],
				  );
  
  $stmt = $dbh->prepare($sql_query);					 
  $result = $stmt->execute($values);
  $row = $result->fetchRow(MDB2_FETCHMODE_ASSOC);
  $tweetCnt = $row['cnt'];
  if($_SESSION['user_options']['twitter_tweets'] == 0 || $tweetCnt < $_SESSION['user_options']['twitter_tweets'] || !empty($_GET['id'])) {
		
	$tweets->id = $_GET['id'];
	$tweets->sid = $_SESSION['user_id'];
	$tweets->get();
	
	// added to allow assigning tweet to a search term
	$sql_query = "SELECT
					id,
					term
				 FROM
					twitter_terms
				 WHERE
					 userid = ?
				  AND
					  sid = ?
				  ;";
				  
	$values = array(
					$_GET['userid'],
					$_SESSION['user_id'],
					);
	
	$stmt = $dbh->prepare($sql_query);					 
	$result = $stmt->execute($values);
	
	$term_dd = '';
	while ($row = $result->fetchRow(MDB2_FETCHMODE_ASSOC)) {
		$term_dd .= '<option value="'.$row['id'].'"'.($row['id'] == $tweets->linked_tem ? ' selected="selected" ' : '').'>'.$row['term'].'</option>';
	}
	  
	$op = '<form id="form1" name="form1" method="post" action="?mode=process_tweet">
	<table border="0" cellspacing="3" cellpadding="3" class="user_tbl" align="center">
	  <tr>
		<th colspan="2">Edit tweet</th>
	  </tr>
	  <tr>
		<td>tweet:</td>
		<td><textarea onkeyup="limitChars(\'tweet\', 140, \'charsop\')" name="tweet" id="tweet" cols="50" rows="3">'.htmlentities($tweets->tweet, ENT_QUOTES).'</textarea></td>
	  </tr>
	  <tr>
		<td>Characters:</td>
		<td id="charsop"></td>
	  </tr>
	  <tr>
		<td>Linked Term:</td>
		<td>
		<select name="linked_tem">
		'.$term_dd.'
		</select>
		</td>
	  </tr>
	  <tr>
		<td>Max Uses: (0 = unlimited)</td>
		<td><input type="text" name="max_tweets" value="'.$tweets->max_uses.'" /></td>
	  </tr>
	  <tr>
		<td>Total Uses:</td>
		<td><input type="text" name="uses" value="'.$tweets->uses.'" /></td>
	  </tr>
	  <tr>
		<td colspan="2" align="center"><input name="id" type="hidden" value="'.$tweets->id.'"><input name="userid" type="hidden" value="'.$_GET['userid'].'"><input name="Submit" type="submit" value="Submit"></td>
	  </tr>
	</table>
	</form>
  <script language="javascript">
	function limitChars(textid, limit, infodiv) {
	  var text = $(\'#\'+textid).val(); 
	  var textlength = text.length;
	  if(textlength > limit) {
		$(\'#\' + infodiv).html(\'You cannot write more then \'+limit+\' characters!\');
		$(\'#\'+textid).val(text.substr(0,limit));
	  return false;
	  } else {
		$(\'#\' + infodiv).html(\'You have \'+ (limit - textlength) +\' characters left.\');
	  return true;
	  }
	}
  </script>';
  } else {
	  $message = 'Your account does not allow you to add anymore tweets.';
  }
  
  
return $op;
}

?>