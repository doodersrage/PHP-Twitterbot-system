/*
 Navicat Premium Data Transfer

 Source Server         : localhost
 Source Server Type    : MySQL
 Source Server Version : 50520
 Source Host           : localhost
 Source Database       : twitter

 Target Server Type    : MySQL
 Target Server Version : 50520
 File Encoding         : utf-8

 Date: 02/26/2012 16:56:48 PM
*/

SET NAMES utf8;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
--  Table structure for `retweets`
-- ----------------------------
DROP TABLE IF EXISTS `retweets`;
CREATE TABLE `retweets` (
  `id` bigint(25) NOT NULL,
  `screen_name` varchar(255) NOT NULL,
  `profile_image_url` text,
  `url` text,
  `friends_count` int(11) DEFAULT NULL,
  `followers_count` int(11) DEFAULT NULL,
  `favourites_count` int(11) DEFAULT NULL,
  `listed_count` int(11) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `data` text NOT NULL,
  `userid` int(11) NOT NULL,
  `sid` int(11) NOT NULL,
  `rt_date` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


-- ----------------------------
--  Table structure for `system_users`
-- ----------------------------
DROP TABLE IF EXISTS `system_users`;
CREATE TABLE `system_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(140) NOT NULL,
  `password` varchar(60) NOT NULL,
  `added` datetime NOT NULL,
  `last_login` datetime DEFAULT NULL,
  `last_ip` varchar(15) DEFAULT NULL,
  `user_level` tinyint(1) NOT NULL,
  `options` text,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

-- ----------------------------
--  Records of `system_users`
-- ----------------------------
BEGIN;
INSERT INTO `system_users` VALUES ('1', 'admin@localhost.com', '8df21041378729595058bbf29abad7ef', '2010-07-31 21:05:25', null, null, '1', null);
COMMIT;

-- ----------------------------
--  Table structure for `tweets`
-- ----------------------------
DROP TABLE IF EXISTS `tweets`;
CREATE TABLE `tweets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `modified` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `tweet` varchar(140) NOT NULL,
  `userid` int(11) NOT NULL,
  `sid` int(11) DEFAULT NULL,
  `linked_tem` int(11) DEFAULT NULL,
  `max_uses` bigint(25) DEFAULT '0',
  `uses` bigint(25) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=438 DEFAULT CHARSET=latin1;

-- ----------------------------
--  Table structure for `twitter_terms`
-- ----------------------------
DROP TABLE IF EXISTS `twitter_terms`;
CREATE TABLE `twitter_terms` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userid` int(11) NOT NULL,
  `term` varchar(140) NOT NULL,
  `sid` int(11) DEFAULT NULL,
  `datestart` date DEFAULT NULL,
  `dateend` date DEFAULT NULL,
  `city` varchar(255) DEFAULT NULL,
  `state` varchar(255) DEFAULT NULL,
  `radius` int(11) DEFAULT '0',
  `max_tweets` bigint(25) DEFAULT NULL,
  `tweet_count` bigint(25) DEFAULT NULL,
  `enabled` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=12 DEFAULT CHARSET=latin1;

-- ----------------------------
--  Table structure for `twitter_user_check`
-- ----------------------------
DROP TABLE IF EXISTS `twitter_user_check`;
CREATE TABLE `twitter_user_check` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userid` int(11) NOT NULL,
  `username` varchar(20) DEFAULT NULL,
  `tweet_id` int(11) DEFAULT NULL,
  `tweet` text,
  `sent` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `sid` int(11) DEFAULT NULL,
  `profile_image` text,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=137426 DEFAULT CHARSET=latin1;

-- ----------------------------
--  Table structure for `twitter_users`
-- ----------------------------
DROP TABLE IF EXISTS `twitter_users`;
CREATE TABLE `twitter_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(20) NOT NULL,
  `password` varchar(140) NOT NULL,
  `enabled` int(1) DEFAULT '0',
  `sid` int(11) DEFAULT NULL,
  `api_key` varchar(255) DEFAULT NULL,
  `consumer_key` varchar(255) DEFAULT NULL,
  `consumer_secret` varchar(255) DEFAULT NULL,
  `oauthtoken` varchar(255) DEFAULT NULL,
  `oauthsecret` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=latin1;

-- ----------------------------
--  Table structure for `user_levels`
-- ----------------------------
DROP TABLE IF EXISTS `user_levels`;
CREATE TABLE `user_levels` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(120) NOT NULL,
  `description` text NOT NULL,
  `cost` decimal(10,2) DEFAULT NULL,
  `options` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=latin1;

-- ----------------------------
--  Records of `user_levels`
-- ----------------------------
BEGIN;
INSERT INTO `user_levels` VALUES ('1', 'Admin', 'Users of this level have complete control over all twitter accounts within the system.', '0.00', 'a:7:{s:11:\"user_access\";s:1:\"1\";s:11:\"user_levels\";s:1:\"1\";s:12:\"all_twitters\";s:1:\"1\";s:16:\"twitter_accounts\";s:1:\"0\";s:13:\"twitter_terms\";s:1:\"0\";s:14:\"twitter_tweets\";s:1:\"0\";s:15:\"twitter_retries\";s:1:\"0\";}'), ('2', 'Super User', 'This user is allowed more twitter accounts and more retries for tweet attempts.', '20.00', 'a:4:{s:16:\"twitter_accounts\";s:2:\"20\";s:13:\"twitter_terms\";s:2:\"20\";s:14:\"twitter_tweets\";s:2:\"50\";s:15:\"twitter_retries\";s:2:\"20\";}'), ('3', 'User', 'This user is allowed a few more twitter users and tweets than a free user.', '10.00', 'a:4:{s:16:\"twitter_accounts\";s:1:\"5\";s:13:\"twitter_terms\";s:1:\"5\";s:14:\"twitter_tweets\";s:2:\"40\";s:15:\"twitter_retries\";s:1:\"5\";}'), ('4', 'Free User', 'This user is allowed basic functionality.', '0.00', 'a:4:{s:16:\"twitter_accounts\";s:1:\"1\";s:13:\"twitter_terms\";s:1:\"1\";s:14:\"twitter_tweets\";s:2:\"10\";s:15:\"twitter_retries\";s:1:\"1\";}');
COMMIT;

SET FOREIGN_KEY_CHECKS = 1;
