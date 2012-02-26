<?PHP
// returns a list of retweets for a selected user
function get_retweets() {
  global $dbh, $retweets;
  
  $op = '<form id="form1" name="form1" method="post" action="">
  <table border="0" cellspacing="0" cellpadding="0" align="center" class="user_tbl">
	<tr>
	  <th>#</th>
	  <th>&nbsp;</th>
	  <th>Handle</th>
	  <th>Name</th>
	  <th>Tweet</th>
	  <th>Date</th>
	  <th>URL</th>
	  <th>Friends</th>
	  <th>Followers</th>
	  <th>Favorites</th>
	  <th>Listed</th>
	  <th>Delete</th>
	</tr>';
	// get tweet count
  $sql_query = "SELECT
				  count(*) as cnt
			   FROM
				  retweets
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
  $terms_cnt = $row['cnt'];
  // create page links
  $pageLnks = array();
  $pageCnt = 0;
  for($i = 0;$i < $tweetCnt;$i += 20){
	  $pageCnt++;
	  $pageLnks[] = '<a href="?mode=retweets&userid='.$_GET['userid'].'&page='.$pageCnt.'">'.$pageCnt.'</a>';
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
				  retweets
			   WHERE
			       userid = ?
				AND
					sid = ?
				ORDER BY rt_date DESC
			   LIMIT ".$startCnt.",20;";
				
  $values = array(
				  $_GET['userid'],
				  $_SESSION['user_id'],
				  );
  
  $stmt = $dbh->prepare($sql_query);					 
  $result = $stmt->execute($values);
  
  $cur_tweet = 0;
  while ($row = $result->fetchRow(MDB2_FETCHMODE_ASSOC)) {
  
	  $retweets->id = $row['id'];
	  $retweets->get();
	  $cur_tweet++;
	  
	  $op .= '<tr>
		  <td>'.$cur_tweet.'</td>
		  <td><a target="_blank" href="http://www.twitter.com/'.$retweets->screen_name.'"><img src="'.$retweets->profile_image_url.'" alt="'.$retweets->screen_name.' profile image" /></a></td>
		  <td align="center"><a target="_blank" href="http://www.twitter.com/'.$retweets->screen_name.'">'.$retweets->screen_name.'</a></td>
		  <td align="center">'.$retweets->name.'</td>
		  <td align="center">'.$retweets->data['text'].'</td>
		  <td align="center">'.$retweets->rt_date.'</td>
		  <td align="center">'.($retweets->url != NULL ? '<a href="'.$retweets->url.'">'.$retweets->url.'</a>' : '').'</td>
		  <td align="center">'.$retweets->friends_count.'</td>
		  <td align="center">'.$retweets->followers_count.'</td>
		  <td align="center">'.$retweets->favourites_count.'</td>
		  <td align="center">'.$retweets->listed_count.'</td>
		  <td align="center"><input name="delete[]" type="checkbox" value="'.$twitter_terms->id.'" /></td>
		</tr>';
  }
  
  $op .= '<tr>
	  <td colspan="12" align="right">
	  Page: '.$pageLnksOp.'
	  </td>
	</tr>
	<tr>
	  <td colspan="12" align="center"><label>
		<input type="submit" name="button" id="button" value="Apply Changes" />
	  </label></td>
	</tr>
  </table>
  </form>';
 
return $op;
}

?>