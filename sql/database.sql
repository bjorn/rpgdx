-- phpMyAdmin SQL Dump
-- version 2.6.1-pl2
-- http://www.phpmyadmin.net
-- 
-- Host: localhost
-- Generation Time: Aug 15, 2005 at 12:03 PM
-- Server version: 4.0.24
-- PHP Version: 4.3.11
-- 
-- Database: `indierp_main`
-- 

-- --------------------------------------------------------

-- 
-- Table structure for table `phpbb_reads`
-- 

CREATE TABLE `phpbb_reads` (
  `read_topic_id` mediumint(8) NOT NULL default '0',
  `read_user_id` mediumint(8) NOT NULL default '0',
  `read_time` int(11) NOT NULL default '0'
) ENGINE=MyISAM;

-- --------------------------------------------------------

-- 
-- Table structure for table `rpgdx_article_types`
-- 

CREATE TABLE `rpgdx_article_types` (
  `type_id` int(11) NOT NULL auto_increment,
  `type_title` text NOT NULL,
  `type_long_title` text NOT NULL,
  PRIMARY KEY  (`type_id`)
) ENGINE=MyISAM;

-- --------------------------------------------------------

-- 
-- Table structure for table `rpgdx_articles`
-- 

CREATE TABLE `rpgdx_articles` (
  `article_id` int(11) NOT NULL auto_increment,
  `user_id` int(11) NOT NULL default '0',
  `article_type` int(11) NOT NULL default '0',
  `article_summary` text,
  `article_url` text,
  `article_created` datetime default NULL,
  `article_title` text,
  `article_bbcode_uid` varchar(10) NOT NULL default '',
  PRIMARY KEY  (`article_id`)
) ENGINE=MyISAM;

-- --------------------------------------------------------

-- 
-- Table structure for table `rpgdx_contest_categories`
-- 

CREATE TABLE `rpgdx_contest_categories` (
  `category_id` int(11) NOT NULL auto_increment,
  `category_contest` int(11) NOT NULL default '0',
  `category_name` varchar(64) default NULL,
  `category_value` int(11) default NULL,
  `category_image` varchar(32) default NULL,
  PRIMARY KEY  (`category_id`)
) ENGINE=MyISAM;

-- --------------------------------------------------------

-- 
-- Table structure for table `rpgdx_contest_entries`
-- 

CREATE TABLE `rpgdx_contest_entries` (
  `entry_id` int(11) NOT NULL auto_increment,
  `entry_project` int(11) NOT NULL default '0',
  `entry_contest` int(11) NOT NULL default '0',
  `entry_date` datetime default NULL,
  `entry_disqualified` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`entry_id`)
) ENGINE=MyISAM;

-- --------------------------------------------------------

-- 
-- Table structure for table `rpgdx_contest_votes`
-- 

CREATE TABLE `rpgdx_contest_votes` (
  `vote_id` int(11) NOT NULL auto_increment,
  `vote_entry` int(11) NOT NULL default '0',
  `vote_user` int(11) NOT NULL default '0',
  `vote_category` int(11) NOT NULL default '0',
  PRIMARY KEY  (`vote_id`)
) ENGINE=MyISAM;

-- --------------------------------------------------------

-- 
-- Table structure for table `rpgdx_contests`
-- 

CREATE TABLE `rpgdx_contests` (
  `contest_id` int(11) NOT NULL auto_increment,
  `contest_start` datetime NOT NULL default '0000-00-00 00:00:00',
  `contest_end` datetime NOT NULL default '0000-00-00 00:00:00',
  `contest_name` varchar(64) NOT NULL default '',
  `contest_description` text NOT NULL,
  `contest_status` int(11) NOT NULL default '0',
  PRIMARY KEY  (`contest_id`)
) ENGINE=MyISAM;

-- --------------------------------------------------------

-- 
-- Table structure for table `rpgdx_log`
-- 

CREATE TABLE `rpgdx_log` (
  `log_id` int(11) NOT NULL auto_increment,
  `log_text` text NOT NULL,
  `log_time` datetime default NULL,
  PRIMARY KEY  (`log_id`)
) ENGINE=MyISAM;

-- --------------------------------------------------------

-- 
-- Table structure for table `rpgdx_news`
-- 

CREATE TABLE `rpgdx_news` (
  `news_id` int(11) NOT NULL auto_increment,
  `user_id` int(11) NOT NULL default '0',
  `news_posted` datetime default NULL,
  `news_title` text NOT NULL,
  `news_message` text NOT NULL,
  `news_bbcode_uid` varchar(10) NOT NULL default '',
  PRIMARY KEY  (`news_id`)
) ENGINE=MyISAM;

-- --------------------------------------------------------

-- 
-- Table structure for table `rpgdx_operating_systems`
-- 

CREATE TABLE `rpgdx_operating_systems` (
  `system_id` int(11) NOT NULL auto_increment,
  `system_name` text NOT NULL,
  PRIMARY KEY  (`system_id`)
) ENGINE=MyISAM;

-- --------------------------------------------------------

-- 
-- Table structure for table `rpgdx_programming_languages`
-- 

CREATE TABLE `rpgdx_programming_languages` (
  `language_id` int(11) NOT NULL auto_increment,
  `language_name` text NOT NULL,
  PRIMARY KEY  (`language_id`)
) ENGINE=MyISAM;

-- --------------------------------------------------------

-- 
-- Table structure for table `rpgdx_project_downloads`
-- 

CREATE TABLE `rpgdx_project_downloads` (
  `download_id` int(11) NOT NULL auto_increment,
  `download_url` text NOT NULL,
  `download_title` text NOT NULL,
  `project_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`download_id`)
) ENGINE=MyISAM;

-- --------------------------------------------------------

-- 
-- Table structure for table `rpgdx_project_screenshots`
-- 

CREATE TABLE `rpgdx_project_screenshots` (
  `screenshot_id` int(11) NOT NULL auto_increment,
  `upload_id` int(11) NOT NULL default '0',
  `screenshot_title` text NOT NULL,
  `project_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`screenshot_id`)
) ENGINE=MyISAM;

-- --------------------------------------------------------

-- 
-- Table structure for table `rpgdx_project_statusses`
-- 

CREATE TABLE `rpgdx_project_statusses` (
  `status_id` int(11) NOT NULL auto_increment,
  `status_title` text NOT NULL,
  `status_perc` int(11) default NULL,
  PRIMARY KEY  (`status_id`)
) ENGINE=MyISAM;

-- --------------------------------------------------------

-- 
-- Table structure for table `rpgdx_project_types`
-- 

CREATE TABLE `rpgdx_project_types` (
  `type_id` int(11) NOT NULL auto_increment,
  `type_title` text NOT NULL,
  `type_title_plural` text NOT NULL,
  `type_description` text,
  PRIMARY KEY  (`type_id`)
) ENGINE=MyISAM;

-- --------------------------------------------------------

-- 
-- Table structure for table `rpgdx_projects`
-- 

CREATE TABLE `rpgdx_projects` (
  `project_id` int(11) NOT NULL auto_increment,
  `user_id` int(11) NOT NULL default '0',
  `project_added` datetime default NULL,
  `project_last_update` datetime default NULL,
  `project_name` text,
  `project_contributors` text,
  `project_summary` text,
  `project_description` mediumtext,
  `project_bbcode_uid` varchar(10) NOT NULL default '',
  `project_icon_file` int(11) default NULL,
  `download` text,
  `project_url` text,
  `project_type` int(11) default NULL,
  `language_id` int(11) NOT NULL default '0',
  `progress_id` int(11) NOT NULL default '0',
  `project_allow_review` int(1) NOT NULL default '1',
  PRIMARY KEY  (`project_id`)
) ENGINE=MyISAM;

-- --------------------------------------------------------

-- 
-- Table structure for table `rpgdx_reviews`
-- 

CREATE TABLE `rpgdx_reviews` (
  `review_id` int(11) NOT NULL auto_increment,
  `user_id` int(11) NOT NULL default '0',
  `project_id` int(11) NOT NULL default '0',
  `review_added` datetime default NULL,
  `review_score` tinyint(4) default NULL,
  `review_text` mediumtext,
  `review_bbcode_uid` varchar(10) NOT NULL default '',
  PRIMARY KEY  (`review_id`)
) ENGINE=MyISAM;

-- --------------------------------------------------------

-- 
-- Table structure for table `rpgdx_sessions`
-- 

CREATE TABLE `rpgdx_sessions` (
  `autoid` int(11) NOT NULL auto_increment,
  `sid` varchar(100) NOT NULL default '',
  `data` text NOT NULL,
  `addr` varchar(20) NOT NULL default '',
  `opened` int(14) default NULL,
  `expire` int(14) default NULL,
  PRIMARY KEY  (`autoid`),
  UNIQUE KEY `autoid` (`autoid`)
) ENGINE=MyISAM;

-- --------------------------------------------------------

-- 
-- Table structure for table `rpgdx_themes`
-- 

CREATE TABLE `rpgdx_themes` (
  `theme_id` int(11) NOT NULL auto_increment,
  `theme_name` varchar(32) default NULL,
  `theme_dir` varchar(32) default NULL,
  PRIMARY KEY  (`theme_id`)
) ENGINE=MyISAM;

-- --------------------------------------------------------

-- 
-- Table structure for table `rpgdx_uploads`
-- 

CREATE TABLE `rpgdx_uploads` (
  `upload_id` int(11) NOT NULL auto_increment,
  `upload_ext` varchar(8) NOT NULL default '',
  `upload_type` varchar(16) NOT NULL default '',
  PRIMARY KEY  (`upload_id`)
) ENGINE=MyISAM;
