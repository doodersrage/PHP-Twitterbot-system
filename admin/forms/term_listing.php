<?PHP

// display term listing
function term_listing() {
  global $dbh, $twitter_terms, $terms_cnt;
  
  $op = '<form id="form1" name="form1" method="post" action="">
  <table border="0" cellspacing="0" cellpadding="0" align="center" class="user_tbl">
	<tr>
	  <th>#</th>
	  <th>Term</th>
	  <th>Enabled</th>
	  <th>Start</th>
	  <th>End</th>
	  <th>Location</th>
	  <th>Radius (mi)</th>
	  <th>Edit</th>
	  <th>Delete</th>
	</tr>';
	// get tweet count
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
  $terms_cnt = $row['cnt'];
  // create page links
  $pageLnks = array();
  $pageCnt = 0;
  for($i = 0;$i <= $tweetCnt;$i += 20){
	  $pageCnt++;
	  $pageLnks[] = '<a href="?mode=edit_terms&userid='.$_GET['userid'].'&page='.$pageCnt.'">'.$pageCnt.'</a>';
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
  
  $cur_tweet = 0;
  while ($row = $result->fetchRow(MDB2_FETCHMODE_ASSOC)) {
  
	  $twitter_terms->id = $row['id'];
	  $twitter_terms->sid = $_SESSION['user_id'];
	  $twitter_terms->get();
	  $cur_tweet++;
	  
	  $op .= '<tr>
		  <td>'.$cur_tweet.'</a></td>
		  <td>'.$twitter_terms->term.'</td>
		  <td>'.($twitter_terms->enabled == 1 ? 'Yes' : 'No').'</td>
		  <td>'.($twitter_terms->datestart != '' ? date('Y-m-d', strtotime($twitter_terms->datestart)) : '').'</td>
		  <td>'.($twitter_terms->dateend != '' ? date('Y-m-d', strtotime($twitter_terms->dateend)) : '').'</td>
		  <td>'.$twitter_terms->city.', '.$twitter_terms->state.'</td>
		  <td>'.$twitter_terms->radius.'</td>
		  <td><a href="?mode=edit_selected_term&id='.$twitter_terms->id.'&userid='.$twitter_terms->userid.'"><img alt="edit image" src="images/pencil-icon.gif" /></a></td>
		  <td align="center"><input name="delete[]" type="checkbox" value="'.$twitter_terms->id.'" /></td>
		</tr>';
  }
  
  $op .= '<tr>
	  <td colspan="9" align="right">
	  Page: '.$pageLnksOp.'
	  </td>
	</tr>
	<tr>
	  <td colspan="9" align="center"><label>
		<input type="submit" name="button" id="button" value="Apply Changes" />
	  </label></td>
	</tr>
  </table>
  </form>';
 
return $op;
}

?>