<?PHP

// edit twitter search term
function edit_term_frm() {
  global $dbh, $message, $twitter_terms;
  
   $sql_query = "SELECT
				  count(*) as cnt
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
  $row = $result->fetchRow(MDB2_FETCHMODE_ASSOC);
  $tweetCnt = $row['cnt'];
  
  if($_SESSION['user_options']['twitter_terms'] == 0 || $tweetCnt < $_SESSION['user_options']['twitter_terms'] || !empty($_GET['id'])) {
   
	$twitter_terms->id = $_GET['id'];
	$twitter_terms->sid = $_SESSION['user_id'];
	$twitter_terms->get();
	
	$op = '
 	<script>
	$(function() {
		$( "#datestart" ).datepicker();
		$( "#dateend" ).datepicker();
	});
	</script>
	<form id="form1" name="form1" method="post" action="?mode=process_term">
	<table border="0" cellspacing="3" cellpadding="3" class="user_tbl" align="center">
	  <tr>
		<th colspan="2">Edit Term</th>
	  </tr>
	  <tr>
		<td>Term:</td>
		<td><input name="term" type="text" size="50" maxlength="70" value="'.htmlentities($twitter_terms->term, ENT_QUOTES).'"></td>
	  </tr>
	<tr>
	  <td>Enabled:</td>
	  <td><input name="enabled" type="checkbox" value="1" '.($twitter_terms->enabled == 1 ? 'checked' : '').' /></td>
	</tr>
	<tr>
	  <td>Start:</td>
	  <td><input type="text" id="datestart" name="datestart" value="'.$twitter_terms->datestart.'"/></td>
	</tr>
	<tr>
	  <td>End:</td>
	  <td><input type="text" id="dateend" name="dateend" value="'.$twitter_terms->dateend.'"/></td>
	</tr>
	<tr>
	  <td>City: (EX: Virginia Beach)</td>
	  <td><input type="text" id="city" name="city" value="'.$twitter_terms->city.'"/></td>
	</tr>
	<tr>
	  <td>State: (EX: VA)</td>
	  <td><input type="text" id="state" name="state" value="'.$twitter_terms->state.'"/></td>
	</tr>
	<tr>
	  <td>Search Radius: (In miles)</td>
	  <td><input type="text" id="radius" name="radius" value="'.$twitter_terms->radius.'"/></td>
	</tr>
	<tr>
	  <td>Max Tweets: (0 = unlimited)</td>
	  <td><input type="text" id="max_tweets" name="max_tweets" value="'.$twitter_terms->max_tweets.'"/></td>
	</tr>
	<tr>
	  <td>Current Tweet Count: </td>
	  <td><input type="text" id="tweet_count" name="tweet_count" value="'.$twitter_terms->tweet_count.'"/></td>
	</tr>
	  <tr>
		<td colspan="2" align="center"><input name="id" type="hidden" value="'.$twitter_terms->id.'"><input name="userid" type="hidden" value="'.$_GET['userid'].'"><input name="Submit" type="submit" value="Submit"></td>
	  </tr>
	</table>
	</form>
  <script>
	$(function() {
		$( "#dateend" ).datepicker("option", "dateFormat", "yy-mm-dd");
		$( "#dateend" ).datepicker("option", "currentText", "'.$twitter_terms->dateend.'");
		$( "#datestart" ).datepicker("option", "dateFormat", "yy-mm-dd");
		$( "#datestart" ).datepicker("option", "currentText", "'.$twitter_terms->datestart.'");
	});
	</script>';
  } else {
	  $message = 'Your account does not allow you to add anymore terms.';
  }
  
return $op;
}

?>