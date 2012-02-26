<?PHP
ini_set('display_errors',1);
error_reporting(E_ALL);
require('config.php'); 
  
// load twitter bot class
require(HOME_PATH.'classes/tweetmanage.php');
$tweetmanage = new tweetmanage;

// load twitter bot class
require(HOME_PATH.'classes/TwitterBot.class.php');

// load twitter tables interface
require(HOME_PATH.'classes/load_tables.php');

// query all set and enabled system users
$sql_query = "SELECT
				id
			 FROM
				system_users
			 ;";

$usrs_stmt = $dbh->prepare($sql_query);					 
$usrs_result = $usrs_stmt->execute();

while ($usr_row = $usrs_result->fetchRow(MDB2_FETCHMODE_ASSOC)) {
	
	$system_users->id = $usr_row['id'];
	$system_users->get();
	
	// query all set and enabled twitter users
	$sql_query = "SELECT
					id,
					username,
					password,
					api_key,
					consumer_key,
					consumer_secret,
					oauthtoken,
					oauthsecret
				 FROM
					twitter_users
				 WHERE
					enabled = ? ;";
	
	$values = array(
					'1',
					);
	
	$stmt = $dbh->prepare($sql_query);					 
	$result = $stmt->execute($values);
	
	while ($row = $result->fetchRow(MDB2_FETCHMODE_ASSOC)) {
		// assign twitter connect variables
		$tweetmanage->sid = $system_users->id;
		$tweetmanage->userid = $row['id'];
		$tweetmanage->twitter_user = $row['username'];
		$tweetmanage->twitter_pass = $row['password'];
		$tweetmanage->api_key = $row['api_key'];
		$tweetmanage->consumer_key = $row['consumer_key'];
		$tweetmanage->consumer_secret = $row['consumer_secret'];
		$tweetmanage->oauthtoken = $row['oauthtoken'];
		$tweetmanage->oauthsecret = $row['oauthsecret'];
		
		// query all set search terms for current twitter user
		$sql_query = "SELECT
						id,
						term,
						datestart,
						dateend,
						city,
						radius,
						state
					 FROM
						twitter_terms
					 WHERE
						userid = ? 
					 AND 
					 	enabled = 1
					 AND 
					 	((max_tweets < tweet_count) OR (max_tweets = 0));";
		
		$values = array(
						$row['id'],
						);
		
		$term_stmt = $dbh->prepare($sql_query);					 
		$term_result = $term_stmt->execute($values);
		
		
		while ($term_row = $term_result->fetchRow(MDB2_FETCHMODE_ASSOC)) {
			
			$tweetmanage->city = $term_row['city'];
			$tweetmanage->state = $term_row['state'];
			$tweetmanage->radius = $term_row['radius'];
			$tweetmanage->term_id = $term_row['id'];
			
			if(!empty($term_row['datestart']) || !empty($term_row['dateend'])){
				if(!empty($term_row['datestart'])){
					$start_date = strtotime($term_row['datestart']);
				}
				if(!empty($term_row['datestart'])){
					$end_date = strtotime($term_row['datestart']);
				}
				if(!empty($term_row['datestart']) && empty($term_row['dateend'])){
					if($start_date >= time()){
						post_tweet_call($term_row);
					}
				}
				if(!empty($term_row['dateend']) && empty($term_row['datestart'])){
					if($end_date <= time()){
						post_tweet_call($term_row);
					}
				}
				if(!empty($term_row['datestart']) && !empty($term_row['dateend'])){
					if($start_date >= time() && $end_date <= time()){
						post_tweet_call($term_row);
					}
				}
			} else {
				post_tweet_call($term_row);
			}
		}
		
	}
}

function post_tweet_call($term_row){
	global $tweetmanage;
	
	$tweetmanage->twitter_search_word_id = $term_row['id'];
	$tweetmanage->twitter_search_word = $term_row['term'];
	$tweetmanage->post_tweet();
}

// clear result set
$result->free();

// reset DB conn
db_check_conn();


?>