<?PHP

// execute sql statement
function execute_qry($sql_query) {
  global $dbh;

  $stmt = $dbh->prepare($sql_query);
  $stmt->execute();

}

// generate encrypted admin user password
function pass_salt($password){

	$salt = '1sa1t9a5s*@';
	
	$new_pass = md5($salt.$password);

return $new_pass;
}

// checks for empty required fields
function check_req_fld($req_flds){
	global $message;
	
	$op = '';
	
	$err_flds = array();
	
	foreach($req_flds as $cur_fld){
		if(empty($_POST[$cur_fld])) $err_flds[] = $cur_fld; 
	}
	
	if(count($err_flds) > 0) $op = 'These fields were found to be empty on submission. Please check your submission and try again. '.implode(', ',$err_flds);

	$message .= $op;

return $op;
}

// check for the postion of a character within a string
function str_chk($string='',$char='@'){
  $pos_chk = strpos($string,$char);
return $pos_chk;
}

function cnt_srch_trms($userid){
	global $dbh;
	
  $sql_query = "SELECT
				  count(*) as cnt
			   FROM
				  twitter_terms
				WHERE
					sid = ? 
				AND
					userid = ? ;";
					
  $values = array(
				  $_SESSION['user_id'],
				  $userid,
				  );
  
  $stmt = $dbh->prepare($sql_query);					 
  $result = $stmt->execute($values);
  $row = $result->fetchRow(MDB2_FETCHMODE_ASSOC);
  $tweetCnt = $row['cnt'];

return $tweetCnt;
}

function cnt_tweets($userid){
	global $dbh;
	
  $sql_query = "SELECT
				  count(*) as cnt
			   FROM
				  tweets
				WHERE
					sid = ? 
				AND
					userid = ? ;";
					
  $values = array(
				  $_SESSION['user_id'],
				  $userid,
				  );
  
  $stmt = $dbh->prepare($sql_query);					 
  $result = $stmt->execute($values);
  $row = $result->fetchRow(MDB2_FETCHMODE_ASSOC);
  $tweetCnt = $row['cnt'];

return $tweetCnt;
}

function cnt_retweets($userid){
	global $dbh;
	
  $sql_query = "SELECT
				  count(*) as cnt
			   FROM
				  retweets
				WHERE
					sid = ? 
				AND
					userid = ? ;";
					
  $values = array(
				  $_SESSION['user_id'],
				  $userid,
				  );
  
  $stmt = $dbh->prepare($sql_query);					 
  $result = $stmt->execute($values);
  $row = $result->fetchRow(MDB2_FETCHMODE_ASSOC);
  $tweetCnt = $row['cnt'];

return $tweetCnt;
}

function usr_lvls_dd($selected = ''){
  global $dbh, $user_levels;
	
  $sql_query = "SELECT
				  id
			   FROM
				  user_levels;";
  
  $stmt = $dbh->prepare($sql_query);					 
  $result = $stmt->execute();
  
  $options = '';
  while ($row = $result->fetchRow(MDB2_FETCHMODE_ASSOC)) {
	  $user_levels->id = $row['id'];
	  $user_levels->get();
	  
	  $options .= '<option value="'.$user_levels->id.'"'.($user_levels->id == $selected ? ' selected="selected" ' : '').'>'.$user_levels->name.'</option>';
  }
  
return $options;
}

function prnt_tweets_lst_csv(){
  global $dbh, $twitter_user_check, $twitter_users;
  
  // Send Header
  header("Pragma: public");
  header("Expires: 0");
  header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
  header("Content-Type: application/force-download");
  header("Content-Type: application/octet-stream");
  header("Content-Type: application/download");;
  header("Content-Disposition: attachment;filename=".$_SESSION['user_id']."-".$_GET['userid']."-tweet-listing.xls ");
  header("Content-Transfer-Encoding: binary ");

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

  $twitter_users->id = $_GET['userid'];
  $twitter_users->sid = $_SESSION['user_id'];
  $twitter_users->get();

  $op = $twitter_users->username . ' Twitter Tweet Report ' . date("m-d-Y")."\t\n";
  $op .= '#'."\t"
	  .'Username'."\t"
	  .'Tweet'."\t"
	  .'Sent'."\t\n";
  
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
			   ;";
 
  $stmt = $dbh->prepare($sql_query);					 
  $result = $stmt->execute($values);
  
  $cur_tweet = $startCnt;
  while ($row = $result->fetchRow(MDB2_FETCHMODE_ASSOC)) {
  
	  $twitter_user_check->id = $row['id'];
	  $twitter_user_check->sid = $_SESSION['user_id'];
	  $twitter_user_check->get();
	  $cur_tweet++;
	  
	  $op .= $cur_tweet."\t"
		  .$twitter_user_check->username."\t"
		  .$twitter_user_check->tweet."\t"
		  .date("F j, Y, g:i a",strtotime($twitter_user_check->sent))."\t\n";
  }
   $op .= "\n".'Total: '."\t".number_format($tweetCnt)."\t\n";
  echo $op;
  die();
}

class PDF extends FPDF
{
  //Colored table
  function FancyTable($header,$data)
  {
	  global $twitter_user_check;
	  
	  //Colors, line width and bold font
	  $this->SetFillColor(255,0,0);
	  $this->SetTextColor(255);
	  $this->SetDrawColor(128,0,0);
	  $this->SetLineWidth(.2);
	  $this->SetFont('','B');
	  //Header
	  $w=array(15,40,160,60);
	  for($i=0;$i<count($header);$i++)
		  $this->Cell($w[$i],7,$header[$i],1,0,'C',true);
	  $this->Ln();
	  //Color and font restoration
	  $this->SetFillColor(224,235,255);
	  $this->SetTextColor(0);
	  $this->SetFont('');
	  //Data
	  $fill=true;
	  foreach($data as $row)
	  {
		  $twitter_user_check->id = $row['id'];
		  $twitter_user_check->sid = $_SESSION['user_id'];
		  $twitter_user_check->get();
		  $cur_tweet++;
		  
		  $this->Cell($w[0],6,$cur_tweet,'LR',0,'R',$fill);
		  $this->Cell($w[1],6,$twitter_user_check->username,'LR',0,'L',$fill);
		  $this->Cell($w[2],6,substr($twitter_user_check->tweet, 0, 105),'LR',0,'L',$fill);
		  $this->Cell($w[3],6,date("F j, Y, g:i a",strtotime($twitter_user_check->sent)),'LR',0,'R',$fill);
		  $this->Ln();
		  $fill=!$fill;
	  }
	  $this->Cell(array_sum($w),0,'','T');
  }
}

function prnt_tweets_lst_pdf(){
  global $dbh, $twitter_user_check, $twitter_users;
	  
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
				 ;";
   
	$stmt = $dbh->prepare($sql_query);					 
	$result = $stmt->execute($values);
	
	$cur_tweet = $startCnt;
	$rows = $result->fetchAll(MDB2_FETCHMODE_ASSOC);
	
	$twitter_users->id = $_GET['userid'];
	$twitter_users->sid = $_SESSION['user_id'];
	$twitter_users->get();
	
	$pdf=new PDF( 'L', 'mm', 'A4' );
	//Column titles
	$header=array('#','Username','Tweet','Sent');
	//Data loading
	$pdf->SetFont('Arial','',9);
	$pdf->AddPage();
	$pdf->Write( 6, $twitter_users->username . ' Twitter Tweet Report ' . date("m-d-Y"));
	$pdf->ln( 7 );
	$pdf->Write( 6, 'Total Tweets Sent: ' . number_format($tweetCnt));
	$pdf->ln( 7 );
	$pdf->FancyTable($header,$rows);
	$pdf->Output();
}
?>