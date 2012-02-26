<?PHP

// display user level listings
function user_level_listing() {
  global $dbh, $user_levels;
  
 $op = '<form id="form1" name="form1" method="post" action="">
  <table border="0" cellspacing="0" cellpadding="0" align="center" class="user_tbl">
	<tr>
	  <th>Level ID</th>
	  <th>Description</th>
	  <th>Delete</th>
	</tr>';
	
	// get tweet count
  $sql_query = "SELECT
				  count(*) as cnt
			   FROM
				  user_levels
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
	  $pageLnks[] = '<a href="?mode=view_user_levels&page='.$pageCnt.'">'.$pageCnt.'</a>';
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
				  user_levels
				LIMIT ".$startCnt.",20;";
  
  $stmt = $dbh->prepare($sql_query);					 
  $result = $stmt->execute();
  
  while ($row = $result->fetchRow(MDB2_FETCHMODE_ASSOC)) {
  
	  $user_levels->id = $row['id'];
	  $user_levels->get();
	
	  $op .= '<tr>
		  <td><a href="?mode=edit_user_levels&id='.$user_levels->id.'">'.$user_levels->name.'</a></td>
		  <td>'.$user_levels->description.'</td>
		  <td align="center"><input name="delete[]" type="checkbox" value="'.$user_levels->id.'" /></td>
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