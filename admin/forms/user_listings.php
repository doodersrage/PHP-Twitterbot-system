<?PHP


// display user listing
function user_listing() {
  global $dbh, $twitter_users, $twit_usr_cnt;
  
  $_SESSION['linked_tem'] = '';
  
 $op = '<form id="form1" name="form1" method="post" action="">
  <table border="0" cellspacing="0" cellpadding="0" align="center" class="user_tbl">
	<tr>
	  <th></th>
	  <th></th>
	  <th></th>
	  <th></th>
	  <th></th>
	  <th></th>
	  <th></th>
	  <th></th>
	  <th></th>
	</tr>';
	
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
  $twit_usr_cnt = $row['cnt'];
  // create page links
  $pageLnks = array();
  $pageCnt = 0;
  for($i = 0;$i <= $tweetCnt;$i += 20){
	  $pageCnt++;
	  $pageLnks[] = '<a href="?page='.$pageCnt.'">'.$pageCnt.'</a>';
  }
  $pageLnksOp = implode(' | ',$pageLnks);
  
  // assign page limiter
  if(empty($_GET['page']) || $_GET['page'] == 1){
	  $startCnt = 0;
  } else {
	  $startCnt = ($_GET['page']-1)*20;
  }
  
  $sql_query = "SELECT
				  id
			   FROM
				  twitter_users
				WHERE
					sid = ? 
				LIMIT ".$startCnt.",20;";
					
  $values = array(
				  $_SESSION['user_id'],
				  );
  
  $stmt = $dbh->prepare($sql_query);					 
  $result = $stmt->execute($values);
  
  while ($row = $result->fetchRow(MDB2_FETCHMODE_ASSOC)) {
  
	  $twitter_users->id = $row['id'];
	  $twitter_users->sid = $_SESSION['user_id'];
	  $twitter_users->get();
	  // get tweets count
	  $sql_query = "SELECT
					  count(*) as cnt
				   FROM
					  twitter_user_check
				   WHERE
				   	  userid = ?
				  AND
					  sid = ? 
					  ;";
	  $values = array(
					  $twitter_users->id,
					  $_SESSION['user_id'],
					  );
	  
	  $stmt = $dbh->prepare($sql_query);					 
	  $resultCnt = $stmt->execute($values);
	  
	  $rowCnt = $resultCnt->fetchRow(MDB2_FETCHMODE_ASSOC);
	
	  $op .= '<tr>
		  <td colspan="8">
		  <div class="user-block">
			<div class="user-name"><strong>User:</strong> <a href="?mode=edit_user&userid='.$twitter_users->id.'">'.$twitter_users->username.'</a></div>
			<div class="twitter-account"><a target="_blank" href="http://www.twitter.com/'.$twitter_users->username.'"><img src="images/twittericonsm.png" width="50" height="50" alt="view twitter account" style="display:block" /></a></div>
			<div class="send-tweet"><a href="?mode=send_tweet&userid='.$twitter_users->id.'"><img src="images/tweetit.jpg" width="87" height="74" alt="tweet it" /></a></div>
			<div class="settings-stats">
			<strong>Settings &amp; Stats</strong><br />
			<a href="?mode=edit_terms&userid='.$twitter_users->id.'">Twitter Terms ('.cnt_srch_trms($twitter_users->id).')</a><br />
			<a href="?mode=edit_tweets&userid='.$twitter_users->id.'">Stored Tweets ('.cnt_tweets($twitter_users->id).')</a><br />
			<a href="?mode=sent_tweets&userid='.$twitter_users->id.'">Sent Tweets ('.number_format($rowCnt['cnt']).')</a><br />
			<a href="?mode=retweets&userid='.$twitter_users->id.'">Mentions ('.cnt_retweets($twitter_users->id).')</a></div>
			<div class="account-status"><strong>Account Status</strong><br />
			<label>Enabled:</label><input type="hidden" name="cur_users[]" value="'.$twitter_users->id.'"><input name="user_status['.$twitter_users->id.']" type="checkbox" value="1" '.($twitter_users->enabled == 1 ? 'checked' : '').' /><br />
			<label>Delete:</label><input name="delete[]" type="checkbox" value="'.$twitter_users->id.'" />
			</div>
		  </div>
		  <div class="clear"></div></td>
		</tr>';
  }
  
  $op .= '<tr>
	  <td colspan="8" align="right">
	  Page: '.$pageLnksOp.'
	  </td>
	</tr>
	<tr>
	  <td colspan="8" align="center">
		<input type="submit" name="button" id="button" value="Apply Changes" />
	  </td>
	</tr>
  </table>
  </form>';
  
return $op;
}

?>