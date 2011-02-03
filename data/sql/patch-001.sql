-- phpMyAdmin SQL Dump
-- version 2.11.8.1deb5+lenny6
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Feb 03, 2011 at 08:46 PM
-- Server version: 5.0.51
-- PHP Version: 5.3.3-0.dotdeb.1

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- Database: `phpoton`
--

-- --------------------------------------------------------

--
-- Table structure for table `answers`
--

CREATE TABLE IF NOT EXISTS `answers` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `twitter_id` int(11) NOT NULL,
  `status_id` bigint(20) NOT NULL,
  `answer` varchar(140) NOT NULL,
  `question_id` int(11) NOT NULL,
  `receive_dt` datetime NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=22 ;

-- --------------------------------------------------------

--
-- Table structure for table `auth`
--

CREATE TABLE IF NOT EXISTS `auth` (
  `id` int(11) NOT NULL auto_increment,
  `username` varchar(50) NOT NULL,
  `password` char(40) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

-- --------------------------------------------------------

--
-- Table structure for table `questions`
--

CREATE TABLE IF NOT EXISTS `questions` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `question` varchar(140) NOT NULL,
  `answer` varchar(50) NOT NULL,
  `twitter_id` int(11) default NULL,
  `fullname` varchar(50) NOT NULL,
  `create_dt` datetime NOT NULL,
  `tweet_dt` datetime default NULL,
  `wonat` datetime default NULL,
  `status` enum('moderation','pending','active','done','notapproved') NOT NULL default 'moderation',
  `winning_answer_id` int(11) default NULL,
  `timelimit` int(11) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=7 ;

-- --------------------------------------------------------

--
-- Table structure for table `scoreboard`
--

CREATE TABLE IF NOT EXISTS `scoreboard` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `twitter_id` int(11) NOT NULL,
  `score_points` int(11) NOT NULL default '0',
  `score_time` int(11) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=9 ;

-- --------------------------------------------------------

--
-- Table structure for table `status`
--

CREATE TABLE IF NOT EXISTS `status` (
  `id` int(11) NOT NULL,
  `since_id` bigint(11) NOT NULL,
  `since_dm_id` bigint(20) NOT NULL,
  `sleeptime` int(11) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `tweeps`
--

CREATE TABLE IF NOT EXISTS `tweeps` (
  `id` int(11) NOT NULL,
  `screen_name` varchar(50) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
