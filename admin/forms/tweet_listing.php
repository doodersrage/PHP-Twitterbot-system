<?PHP

// display tweet listing
function tweet_listing() {
  global $dbh, $tweets, $tweet_cnt, $twitter_terms;

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

  $op = '<form id="form1" name="form1" method="post" action="">
  <table border="0" cellspacing="0" cellpadding="0" align="center" class="user_tbl">
	<tr>
	  <th colspan="2" style="text-align:right">Filter By Search Term:</th>
	  <th colspan="5" style="text-align:left">
		<select name="linked_tem" onChange="document.form1.submit()">
		'.$term_dd.'
		</select>
		</th>
	</tr>
	<tr>
	  <th>#</th>
	  <th>Tweet</th>
	  <th>Linked Term</th>
	  <th>Used</th>
	  <th>Max</th>
	  <th>Edit</th>
	  <th>Delete</th>
	</tr>';
	// get tweet count
  $values = array(
				  $_GET['userid'],
				  $_SESSION['user_id'],
				  );
  $sql_query = "SELECT
				  count(*) as cnt
			   FROM
				  tweets
			   WHERE
			       userid = ?
				AND
					sid = ?
				";
  if(!empty($_SESSION['linked_tem'])){
	  $sql_query .= "AND linked_tem = ? ";
	  $values[] = $_SESSION['linked_tem'];
  }
  $sql_query .= " ;";
  
  $stmt = $dbh->prepare($sql_query);					 
  $result = $stmt->execute($values);
  $row = $result->fetchRow(MDB2_FETCHMODE_ASSOC);
  $tweetCnt = $row['cnt'];
  $tweet_cnt = $row['cnt'];
  // create page links
  $pageLnks = array();
  $pageCnt = 0;
  for($i = 0;$i <= $tweetCnt;$i += 20){
	  $pageCnt++;
	  $pageLnks[] = '<a href="?mode=edit_tweets&userid='.$_GET['userid'].'&page='.$pageCnt.'">'.$pageCnt.'</a>';
  }
  $pageLnksOp = implode(' | ',$pageLnks);
  
  // assign page limiter
  if(empty($_GET['page']) || $_GET['page'] == 1){
	  $startCnt = 0;
  } else {
	  $startCnt = ($_GET['page']-1)*20;
  }
  
  // print tweets
  $values = array(
				  $_GET['userid'],
				  $_SESSION['user_id'],
				  );
  $sql_query = "SELECT
				  id
			   FROM
				  tweets
			   WHERE
			       userid = ?
				AND
					sid = ?
				";
  if(!empty($_SESSION['linked_tem'])){
	  $sql_query .= "AND linked_tem = ? ";
	  $values[] = $_SESSION['linked_tem'];
  }
  $sql_query .= " ORDER BY id
				LIMIT ".$startCnt.",20;";
  
  $stmt = $dbh->prepare($sql_query);					 
  $result = $stmt->execute($values);
  
  $cur_tweet = $startCnt;
  while ($row = $result->fetchRow(MDB2_FETCHMODE_ASSOC)) {
  
	  $tweets->id = $row['id'];
	  $tweets->sid = $_SESSION['user_id'];
	  $tweets->get();
	  $cur_tweet++;
	  
	  $twitter_terms->id = $tweets->linked_tem;
	  $twitter_terms->sid = $_SESSION['user_id'];
	  $twitter_terms->get();
	  
	  $op .= '<tr>
		  <td>'.$cur_tweet.'</td>
		  <td>'.$tweets->tweet.'</td>
		  <td>'.$twitter_terms->term.'</td>
		  <td>'.$tweets->uses.'</td>
		  <td>'.$tweets->max_uses.'</td>
		  <td><a href="?mode=edit_selected_tweet&id='.$tweets->id.'&userid='.$tweets->userid.'"><img alt="edit image" src="images/pencil-icon.gif" /></a></td>
		  <td align="center"><input name="delete[]" type="checkbox" value="'.$tweets->id.'" /></td>
		</tr>';
  }
  
  $op .= '<tr>
	  <td colspan="7" align="right">
	  Page: '.$pageLnksOp.'
	  </td>
	</tr>
	<tr>
	  <td colspan="7" align="center"><label>
		<input type="submit" name="button" id="button" value="Apply Changes" />
	  </label></td>
	</tr>
  </table>
  </form>';
 
return $op;
}

?>