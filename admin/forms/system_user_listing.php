<?PHP

// display system user listings
function system_user_listing() {
  global $dbh, $message, $system_users;
  
  $message = 'Deleting any user from here will delete the user along with any linked twitter accounts, search terms, and tweets. Be careful!';
  
 $op = '<form id="form1" name="form1" method="post" action="">
  <table border="0" cellspacing="0" cellpadding="0" align="center" class="user_tbl">
	<tr>
	  <th>e-mail</th>
	  <th>added</th>
	  <th>last login</th>
	  <th>last ip</th>
	  <th>user level</th>
	  <th>Delete</th>
	</tr>';
	
	// get tweet count
  $sql_query = "SELECT
				  count(*) as cnt
			   FROM
				  system_users
				;";
				
  
  $stmt = $dbh->prepare($sql_query);					 
  $result = $stmt->execute();
  $row = $result->fetchRow(MDB2_FETCHMODE_ASSOC);
  $tweetCnt = $row['cnt'];
  // create page links
  $pageLnks = array();
  $pageCnt = 0;
  for($i = 0;$i <= $tweetCnt;$i += 20){
	  $pageCnt++;
	  $pageLnks[] = '<a href="?mode=view_system_users&page='.$pageCnt.'">'.$pageCnt.'</a>';
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
				  system_users
				LIMIT ".$startCnt.",20;";
  
  $stmt = $dbh->prepare($sql_query);					 
  $result = $stmt->execute();
  
  while ($row = $result->fetchRow(MDB2_FETCHMODE_ASSOC)) {
  
	  $system_users->id = $row['id'];
	  $system_users->get();
	
	  $op .= '<tr>
		  <td><a href="?mode=edit_system_users&id='.$system_users->id.'">'.$system_users->email.'</a></td>
		  <td>'.date("F j, Y, g:i a",strtotime($system_users->added)).'</td>
		  <td>'.($system_users->last_login != '' ? date("F j, Y, g:i a",strtotime($system_users->last_login)) : '').'</td>
		  <td>'.$system_users->last_ip.'</td>
		  <td>'.$system_users->user_level.'</td>
		  <td align="center"><input name="delete[]" type="checkbox" value="'.$system_users->id.'" /></td>
		</tr>';
  }
  
  $op .= '<tr>
	  <td colspan="6" align="right">
	  Page: '.$pageLnksOp.'
	  </td>
	</tr>
	<tr>
	  <td colspan="6" align="center">
		<input type="submit" name="button" id="button" value="Apply Changes" />
	  </td>
	</tr>
  </table>
  </form>';
  
return $op;
}

?>