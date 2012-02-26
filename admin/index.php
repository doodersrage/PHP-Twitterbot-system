<?PHP
//ini_set('display_errors',1);
//error_reporting(E_ALL);

require('../config.php'); 
require(HOME_PATH.'admin/fpdf/fpdf.php');

// start user session
session_start();

// initialize global variables
$message = '';
$twit_usr_cnt = 0;
$tweet_cnt = 0;
$terms_cnt = 0;

// load twitter tables interface
require(HOME_PATH.'classes/load_tables.php');
// load commonly used functions functions
require(HOME_PATH.'admin/functions/common.php');
// load page section functions
require(HOME_PATH.'admin/functions/sections.php');
// load twitter bot class
require(HOME_PATH.'classes/TwitterBot.class.php');
// load page section handler
require(HOME_PATH.'admin/functions/page_procs.php');

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Twitter Multi-Bot Management System</title>

<!-- CSS -->
<link href="style/css/transdmin.css" rel="stylesheet" type="text/css" media="screen" />
<!--[if IE 6]><link rel="stylesheet" type="text/css" media="screen" href="style/css/ie6.css" /><![endif]-->
<!--[if IE 7]><link rel="stylesheet" type="text/css" media="screen" href="style/css/ie7.css" /><![endif]-->

<!-- JavaScripts-->
<link type="text/css" href="style/css/dot-luv/jquery-ui-1.8.7.custom.css" rel="stylesheet" />	
<script type="text/javascript" src="style/js/jquery-1.4.4.min.js"></script>
<script type="text/javascript" src="style/js/jquery-ui-1.8.7.custom.min.js"></script>
<script type="text/javascript" src="style/js/jNice.js"></script>
</head>

<body>
	<div id="wrapper">
    	<!-- h1 tag stays for the logo, you can use the a tag for linking the index page -->
    	<h1><a href="#"><span>Transdmin Light</span></a></h1>
        
        <!-- You can name the links with lowercase, they will be transformed to uppercase by CSS, we prefered to name them with uppercase to have the same effect with disabled stylesheet -->
        <ul id="mainNav">
        	<?PHP
            if($_SESSION['user_logged_in'] == 1){
			?>
			<li><a href="index.php"<?PHP if($_GET['mode'] == '') echo ' class="active" '; ?>>DASHBOARD</a></li> <!-- Use the "active" class for the active menu item  -->
        	<li><a href="?mode=edit_user_options"<?PHP if($_GET['mode'] == 'edit_user_options') echo ' class="active" '; ?>>ACCOUNT OPTIONS</a></li>
        	<?PHP if($_SESSION['user_options']['user_access'] == 1) echo '<li><a href="?mode=view_system_users"'.($_GET['mode'] == 'view_system_users' ? ' class="active" ' : '').'>MANAGE USERS</a></li>'; ?>
        	<?PHP if($_SESSION['user_options']['user_levels'] == 1) echo '<li><a href="?mode=view_user_levels"'.($_GET['mode'] == 'view_user_levels' ? ' class="active" ' : '').'>USER LEVELS</a></li>'; ?>
        	<li class="logout"><a href="?mode=logoff">LOGOUT</a></li>
        	<?PHP
			}
			?>
        </ul>
        <!-- // #end mainNav -->
        
        <div id="containerHolder">
			<div id="container">
        		<div id="sidebar">
                	<ul class="sideNav">
                        <?PHP
						if($_SESSION['user_logged_in'] == 1){
							switch($_GET['mode']){
							case 'edit_terms':
								if($_SESSION['user_options']['twitter_terms'] == 0 || $terms_cnt < $_SESSION['user_options']['twitter_terms']) echo '<li><a href="?mode=add_term&userid='.$_GET['userid'].'"><img src="images/plus.png" alt="add me"/> Add Term</a></li>';
							break;
							case 'edit_tweets':
								if($_SESSION['user_options']['twitter_tweets'] == 0 || $tweet_cnt < $_SESSION['user_options']['twitter_tweets']) echo '<li><a href="?mode=new_tweet&userid='.$_GET['userid'].'"><img src="images/plus.png" alt="add me"/> New Tweet</a></li>';
							break;
							case 'view_system_users':
								echo '<li><a href="?mode=add_system_users"><img src="images/plus.png" alt="add me"/> Add User</a></li>';
							break;
							case 'view_user_levels':
								echo '<li><a href="?mode=add_user_levels"><img src="images/plus.png" alt="add me"/> Add Level</a></li>';
							break;
							case '':
								if($_SESSION['user_options']['twitter_accounts'] == 0 || $twit_usr_cnt < $_SESSION['user_options']['twitter_accounts']) echo '<li><a href="?mode=create_user"><img src="images/plus.png" alt="add me"/> New Account</a></li>';
							break;
							default:
							break;
							}
						}
						?>
                    </ul>
                    <!-- // .sideNav -->
                </div>    
                <!-- // #sidebar -->
                
                <!-- h2 stays for breadcrumbs -->
<!--                <h2><a href="#">Dashboard</a> &raquo; <a href="#" class="active">Print resources</a></h2>
-->                
                <div id="main">
				<?PHP
				if(!empty($message)) {
					echo '<div id="dialog" title="Attention!" style="display:none;text-align:left;">'.$message.'</div>';
					echo '<script>
						$(function() {
							$( "#dialog" ).dialog();
						});
						</script>';
				}
                echo $output;
                ?>
                </div>
                <!-- // #main -->
                
                <div class="clear"></div>
            </div>
            <!-- // #container -->
        </div>	
        <!-- // #containerHolder -->
        
        <p id="footer">
    </div>
    <!-- // #wrapper -->
</body>
</html>
