<?PHP

// view a listing of sent tweets
function sent_tweets_listing() {
  global $dbh, $twitter_user_check;
  
  // set session vars to handle pagination
  if(isset($_POST['filterstart'])) $_SESSION['filterstart'] = $_POST['filterstart'];
  if(isset($_POST['filterend'])) $_SESSION['filterend'] = $_POST['filterend'];
  if(isset($_POST['linked_tem'])) $_SESSION['linked_tem'] = $_POST['linked_tem'];

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
  
  $term_dd = '<option value=""></option>';
  while ($row = $result->fetchRow(MDB2_FETCHMODE_ASSOC)) {
	  $term_dd .= '<option value="'.$row['id'].'"'.($row['id'] == $_SESSION['linked_tem'] ? ' selected="selected" ' : '').'>'.$row['term'].'</option>';
  }
	
	// get tweet count
  $values = array(
				  $_GET['userid'],
				  $_SESSION['user_id'],
				  );
  $sql_query = "SELECT
				  count(*) as cnt
			   FROM
				  twitter_user_check tuc
			   INNER JOIN
			      tweets ts
			   ON
			      ts.id = tuc.tweet_id
			   WHERE
			   		tuc.userid = ?
				AND
					tuc.sid = ? 
				";
  if(!empty($_SESSION['filterstart'])){
	$sql_query .= " AND tuc.sent >= ? ";
	$values[] = date('Y-m-d', strtotime($_SESSION['filterstart']));
  }
  if(!empty($_SESSION['filterend'])){
	$sql_query .= " AND tuc.sent <= ? ";
	$values[] = date('Y-m-d', strtotime($_SESSION['filterend']));
  }
  if(!empty($_SESSION['linked_tem'])){
	  $sql_query .= " AND ts.linked_tem = ? ";
	  $values[] = $_SESSION['linked_tem'];
  }
  $sql_query .= " ;";				
  
  $stmt = $dbh->prepare($sql_query);					 
  $result = $stmt->execute($values);
  $row = $result->fetchRow(MDB2_FETCHMODE_ASSOC);
  $tweetCnt = $row['cnt'];
  
 $op = '
 	<script>
	$(function() {
		$( "#filterstart" ).datepicker();
		$( "#filterend" ).datepicker();
	});
	</script>
  <form id="form1" name="form1" method="post" action="">
  <table border="0" cellspacing="0" cellpadding="0" align="center" class="user_tbl">
	<tr>
	  <th colspan="5">Filter By Date</th>
	</tr>
	<tr>
	  <th colspan="2" style="text-align:right;">Start:</th>
	  <th style="text-align:left;"><input type="text" id="filterstart" name="filterstart" value="'.$_SESSION['filterstart'].'"/></th>
	  <th style="text-align:right;">End:</th>
	  <th style="text-align:left;"><input type="text" id="filterend" name="filterend" value="'.$_SESSION['filterend'].'"/></th>
	</tr>
	<tr>
	  <th colspan="3" style="text-align:right">Filter By Search Term:</th>
	  <th colspan="2" style="text-align:left">
		<select name="linked_tem" onChange="document.form1.submit()">
		'.$term_dd.'
		</select>
		</th>
	</tr>
	<tr>
	  <th colspan="5"><input type="submit" value="Submit"/></th>
	</tr>
	<tr>
	  <th colspan="3" style="text-align:left">Tweets Found: '.number_format($tweetCnt).'</th>
	  <th colspan="2" style="text-align:right">Export to <a href="'.SITE_DOMAIN.'admin/index.php?mode=sent_tweets_pdf&userid='.$_GET['userid'].'" target="_blank">PDF</a> | <a href="'.SITE_DOMAIN.'admin/index.php?mode=sent_tweets_csv&userid='.$_GET['userid'].'" target="_blank">CSV</a></th>
	</tr>
	<tr>
	  <th width="20">#</th>
	  <th>&nbsp;</th>
	  <th>Username</th>
	  <th>Tweet</th>
	  <th>Sent</th>
	</tr>';
  // create page links
  $pageLnks = array();
  $pageCnt = 0;
  for($i = 0;$i <= $tweetCnt;$i += 20){
	  $pageCnt++;
	  $pageLnks[] = '<a href="?mode=sent_tweets&page='.$pageCnt.'&userid='.$_GET['userid'].'">'.$pageCnt.'</a>';
  }
  $pageLnksOp = implode(' | ',$pageLnks);
  
  // assign page limiter
  if(empty($_GET['page']) || $_GET['page'] == 1){
	  $startCnt = 0;
  } else {
	  $startCnt = ($_GET['page']-1)*20;
  }
  
  $values = array(
				  $_GET['userid'],
				  $_SESSION['user_id'],
				  );
  $sql_query = "SELECT
				  tuc.id
			   FROM
				  twitter_user_check tuc
			   INNER JOIN
			      tweets ts
			   ON
			      ts.id = tuc.tweet_id
 			   WHERE
			   		tuc.userid = ?
				AND
					tuc.sid = ? 
				";
  if(!empty($_SESSION['filterstart'])){
	$sql_query .= " AND tuc.sent >= ? ";
	$values[] = date('Y-m-d', strtotime($_SESSION['filterstart']));
  }
  if(!empty($_SESSION['filterend'])){
	$sql_query .= " AND tuc.sent <= ? ";
	$values[] = date('Y-m-d', strtotime($_SESSION['filterend']));
  }
  if(!empty($_SESSION['linked_tem'])){
	  $sql_query .= " AND ts.linked_tem = ? ";
	  $values[] = $_SESSION['linked_tem'];
  }
  $sql_query .= " ORDER BY tuc.sent DESC
			   LIMIT ".$startCnt.",20;";
 
  $stmt = $dbh->prepare($sql_query);					 
  $result = $stmt->execute($values);
  
  $cur_tweet = $startCnt;
  while ($row = $result->fetchRow(MDB2_FETCHMODE_ASSOC)) {
  
	  $twitter_user_check->id = $row['id'];
	  $twitter_user_check->sid = $_SESSION['user_id'];
	  $twitter_user_check->get();
	  $cur_tweet++;
	  
	  $op .= '<tr>
		  <td>'.$cur_tweet.'</td>
		  <td>'.($twitter_user_check->profile_image != '' ? '<a target="_blank" href="http://www.twitter.com/'.$twitter_user_check->username.'"><img src="'.$twitter_user_check->profile_image.'" alt="'.$twitter_user_check->username.' profile image" /></a>' : '&nbsp;').'</td>
		  <td><a target="_blank" href="http://www.twitter.com/'.$twitter_user_check->username.'">'.$twitter_user_check->username.'</a></td>
		  <td>'.$twitter_user_check->tweet.'</td>
		  <td>'.date("F j, Y, g:i a",strtotime($twitter_user_check->sent)).'</td>
		</tr>';
  }
  
  $op .= '<tr>
	  <td colspan="6" align="right">
	  Page: '.$pageLnksOp.'
	  </td>
	</tr>
  </table>
  </form>';
  
return $op;
}

?>