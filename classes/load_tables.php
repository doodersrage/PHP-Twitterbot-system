<?PHP

require(HOME_PATH.'classes/tables/tweets.php');
$tweets = new tweets;
require(HOME_PATH.'classes/tables/twitter_terms.php');
$twitter_terms = new twitter_terms;
require(HOME_PATH.'classes/tables/twitter_user_check.php');
$twitter_user_check = new twitter_user_check;
require(HOME_PATH.'classes/tables/twitter_users.php');
$twitter_users = new twitter_users;
require(HOME_PATH.'classes/tables/system_users.php');
$system_users = new system_users;
require(HOME_PATH.'classes/tables/user_levels.php');
$user_levels = new user_levels;
require(HOME_PATH.'classes/tables/retweets.php');
$retweets = new retweets;

?>