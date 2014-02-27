-- phpMyAdmin SQL Dump
-- version 3.2.0.1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Feb 22, 2014 at 01:50 AM
-- Server version: 5.1.36
-- PHP Version: 5.3.0

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- Database: `engine`
--

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE IF NOT EXISTS `categories` (
  `id` int(6) NOT NULL AUTO_INCREMENT,
  `position` int(2) NOT NULL,
  `menu_owner` varchar(20) NOT NULL,
  `title` varchar(20) NOT NULL,
  `default_page_id` int(6) NOT NULL,
  `level` int(8) NOT NULL,
  `visible` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id` (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=13 ;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `position`, `menu_owner`, `title`, `default_page_id`, `level`, `visible`) VALUES
(1, 1, 'guest', 'Login', 1, 0, 1),
(2, 2, 'guest', 'Register', 2, 0, 1),
(3, 1, 'user', 'UserCP', 16, 1, 1),
(4, 2, 'user', 'AdminCP', 20, 2, 1),
(5, 3, 'user', 'Logout', 3, 1, 1),
(6, 1, 'main', 'Home', 6, 0, 1),
(7, 2, 'main', 'Projects', 7, 0, 1),
(8, 3, 'main', 'Resume', 12, 0, 1),
(9, 4, 'main', 'Links', 14, 0, 1),
(10, 5, 'main', 'Test', 10, 0, 0),
(11, 5, 'main', 'Test', 10, 0, 0),
(12, 6, 'main', 'Test2', 11, 1, 0);

-- --------------------------------------------------------

--
-- Table structure for table `contents`
--

CREATE TABLE IF NOT EXISTS `contents` (
  `id` int(6) NOT NULL AUTO_INCREMENT,
  `owner_id` int(6) NOT NULL,
  `category_id` int(6) NOT NULL,
  `title` varchar(20) NOT NULL,
  `date_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `date_lastedit` datetime NOT NULL,
  `type` varchar(20) NOT NULL,
  `content` text NOT NULL,
  `visible` tinyint(1) NOT NULL,
  `level` int(8) NOT NULL,
  `menuvisible` tinyint(1) NOT NULL,
  `menuposition` int(2) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=28 ;

--
-- Dumping data for table `contents`
--

INSERT INTO `contents` (`id`, `owner_id`, `category_id`, `title`, `date_created`, `date_lastedit`, `type`, `content`, `visible`, `level`, `menuvisible`, `menuposition`) VALUES
(1, 1, 1, 'Login', '2010-05-24 22:05:37', '2010-05-20 23:36:15', 'loginpage', '', 1, 0, 0, 0),
(2, 1, 2, 'Register', '2010-05-20 23:36:15', '2010-05-20 23:36:15', 'registeruser', '', 1, 0, 0, 0),
(3, 1, 5, 'Logout', '2010-05-20 23:37:17', '2010-05-20 23:37:17', 'logoutpage', '', 1, 1, 0, 0),
(4, 1, 6, 'Personal Blog', '2010-05-24 23:02:26', '2010-05-25 03:02:26', 'page', 'Personal Blog goes here.  This is a test message.', 1, 0, 1, 2),
(5, 1, 6, 'Development Blog', '2010-05-20 23:44:16', '2010-05-21 15:28:29', 'page', 'Site Development Blog goes here.', 1, 0, 1, 3),
(6, 1, 6, 'Welcome Page', '2010-06-01 23:13:02', '2010-06-02 03:13:02', 'page', '<h1>Welcome</h1>\r\n\r\n<p>This is the personal website of Phil Mowatt, otherwise known as philmowatt.com.  Directly below you will find general information about this site including it''s goals and basic navigational information.  Enjoy your stay.</p>\r\n\r\n<h2>About</h2>\r\n<p>My name is Phil Mowatt, and this is my personal homepage, philmowatt.com.  I am a self taught web designer who has been creating webpages since 1998.  I started creating pages using Microsoft Frontpage, and the Homestead site builder.  I eventually advanced to directly writing HTML, but I was still creating static webpages.  These pages were difficult to maintain, and so in 2004 I created the first instance of philmowatt.com in order to teach myself PhP and to create a dynamic website.  Over the last six years I have created four separate &quot;content generators&quot; culminating in the engine which runs this site.</p>\r\n<p>This site achieves the goals that I originally set out to accomplish when creating the original philmowatt.com.  Firstly, I have learned a great deal about PhP and now consider it my favorite programming language, and the language I am most proficient with.  Secondly, I have created a dynamic site which can be maintained without writing a single line of HTML; however, more advanced users can create plug-ins, style sheets, and layouts.  Now that these goals have been achieved I have set out to accomplish a new set of goals:</p>\r\n<ul><li>Connect with people who share my interests who may add to the content I have created or find their own use for it.</li>\r\n<li>Provide a portfolio for potential employers to review when determining their interest in hiring me.</li>\r\n<li>Create a place where I can practice and develop my skills as a web developer.</li>\r\n<li>Generate an income using the skills I have developed in web development over the last twelve years.</li>\r\n</ul><h2>Navigation</h2>\r\n<p>The information on this site is organized into a number of categories for ease of navigation.  These categories can be accessed through the main menu and are as follows.</p>\r\n<p><strong>Personal:</strong> contains pages containing information directly related to my person, such as, a small profile, a personal blog, links to my various social networking pages, and a form to directly contact me.</p>\r\n<p><strong>Projects:</strong> houses information about projects of various stages of completion organized into a number of sub-categories.  This category also contains a blog which I update with news related to the development of this site and my other projects.</p>\r\n<p><strong>Employment:</strong> holds a my classic resumes as well as a web based resume.  You can also find information on hiring me to create a website.</p>\r\n<p><strong>Links:</strong> this final category simply contains connections to other websites which I frequent or find interesting which I wished to share with \r\nthe rest of the world.</p>', 1, 0, 0, 1),
(7, 1, 7, 'Websites', '2010-06-01 18:57:47', '2010-06-01 22:57:47', 'page', '<p>The following is a list of websites that I have created, along with a brief description of that page.</p>\r\n<h2>Completed Sites</h2>\r\n<p></p>\r\n<h2>Incomplete Sites</h2>\r\n<p>-=NSF=- Clan Page: I created this page in 2001 for my Counter-Strike clan, Northern Special Forces.  This page represents the most ambitious flash webpage that I have ever worked on.  This clan disbanded before I was able to finish the page.  Since then I have used flash to create a number of simple animations, and to work on vector graphic projects.</p>\r\n<p>Full Frontal band page: this page was created for a band named &quot;Full Frontal&quot; in 2008.  I created a CSS page for this site, but never received any content for the pages.</p>', 1, 0, 1, 0),
(8, 1, 7, 'RPG Games', '2010-06-01 19:12:38', '2010-06-01 23:12:38', 'page', '<p>Below are links to various RPG related projects I have been working on.  I enjoy playing pen and paper RPGs with my friends as either the Game Master, or a player.  The published systems that I most often use are Dungeons and Dragons (3.5 and 4E) and various Whitewolf games (Vampires, Werewolf, Mage, Scion).</p>\r\n<h2>Dungeons and Dragons</h2>\r\n<p>Eyes of the Lich Queen: this is the current campaign that I am DMing for five of my friends.  The original story is a published story written for DnD 3.5, which I have updated to be used for DnD 4E.  This page contains information about the player characters, story summaries, and information others can use to run this story.</p>\r\n<p>Upcoming Campaign: I am currently planning the follow up campaign to Eyes of the Lich Queen.  The same group of friends have expressed interest in playing in another campaign with me as the DM.  Whether or not this story will directly related to the events of Eyes of the Lich Queen this story will take place in the world of Eberron.</p>\r\n<h2>World of Darkness</h2>\r\n<p>New World of Scion: this is a project which I have been working on to modify the rules of Scion to more resemble the rules of the New World of Darkness which White Wolf released after the release of Scion.</p>\r\n<h2>Other</h2>\r\n<p>Phil''s d100: this is an ambitious project to create a role playing rule system based on a d100 mechanic.  The goals of this project are to create a simple, yet realistic RPG system.  The project advanced a great deal at first; however, now I feel that the goals of the project have started to conflict with each other.  Although the system is realistic, I do not feel it is simple thus this project has been put on hold.</p>', 1, 0, 1, 0),
(9, 1, 7, 'Writing', '2010-05-27 00:30:43', '2010-05-27 04:30:43', 'page', '<h2>The Orange Ball</h2>\r\n<h3>Description</h3>\r\n<p>I wrote this after someone passed along the attached image.  I wrote this to pass some time.</p>\r\n<p><img src="img/orangeball.jpg" width="450" height="337" alt="orangeball.jpg" /></p>\r\n<h3>The story</h3>\r\n<p>Bill walked into the open plaza of the train station. As he entered he was hit by a breeze which was carried by one of the passing subway cars. He knew this because of the smell the air carried. It was strong. Not quite the smell of mildew, and not quite the smell of burning rubber; but, a healthy combination of the two. </p>\r\n<p>&quot;While riding the train, please allow all passengers to depart before boarding the car,&quot; chimed a voice over the PA. He had heard it thousands of times before. In fact, he heard it every morning and every morning he wondered to himself who on this planet would be stupid enough to actually require that advice.</p>\r\n<p>Most mornings Bill was in a hurry, but this morning he was running early. He stopped at the top of the stairs leading down to the train platforms, and lit a cigarette. He leaned on the railing over-looking the scurrying people below. Above, was an orange sphere, less than two meters in diameter. It was frosted with an orange lacquer that dulled the intensity of the light inside. Installed when the building was constructed it was supposed to give the place an up class artsy feel.</p>\r\n<p>Bill was neither artsy, nor up class. He graduated from university with a degree in applied mathematics. Out of university he landed a job in data analysis for a South Korean bio-medical science firm. In his spare time he liked to tinker with the odds and ends he could find around his house, in the trash, and at swap meets. This is probably why he failed to see what was so artistic about this sphere.</p>\r\n<p>Large glass sphere - $50 at a swap meet; Orange lacquer - $10 at a hardware store; four xenon high intensity light bulbs - $40 at an electronics store. &quot;And the bugger probably sold the damn thing for a fortune too,&quot; Bill was talking to himself again.</p>\r\n<p>&quot;That he did. Hiro Miaki was the guy’s name, I think. Sold it to the company that put this place together for $60,000.&quot; It was Ruddy. He was one of Bill''s co-workers. Together, they figured out which was the most efficient way to order and calculate results from the experimental data gathered by some scientists.</p>\r\n<p>&quot;It''s a shame that the art we do everyday doesn''t net us that much money,&quot; replied Bill. &quot;I really do not get it. What makes this damn thing so special?&quot; Bill recited his list of supplies and their prices as the two headed down the stairs to the subway platform. &quot;I could make one of those, but no one would ever pay me a year’s salary for it.&quot;</p>\r\n<p>&quot;Well let’s just face it. Some people could shit in a bag and sell it for a million bucks on eBay. The rest of us just have to scrape by.&quot; Ruddy had a frank way of putting things. Normally, that frankness was enough to extinguish the stray thoughts in Bills head; however, this level of frankness just brought up even more images.</p>\r\n<p>&quot;I can see it now. Britney Spears, pinching a tird that resembles the Virgin Mary. The next day she is made the leader of some new religious movement that worships her excrement’s.&quot; The subway platform was packed at this hour, just as it was every morning. The men allowed the people disembarking to depart before stepping aboard the train. The doors slid shut behind them.</p>\r\n<p>&quot;Things have been stranger. In ancient times men used to read the livers of birds looking for the word of God.&quot;</p>\r\n<p>&quot;They ever find anything.&quot;</p>\r\n<p>&quot;They thought they did.&quot;</p>\r\n<p>&quot;What could they have possibly found?&quot;</p>\r\n<p>&quot;A way to power?&quot;</p>\r\n<p>&quot;Touché!&quot;</p>', 1, 0, 1, 0),
(10, 1, 7, 'Images', '2010-05-21 15:35:12', '2010-05-21 15:35:12', 'page', 'Image editing projects go here.', 1, 0, 1, 0),
(11, 1, 7, 'CSS', '2010-05-21 15:34:55', '2010-05-21 15:34:55', 'page', 'CSS projects go here.', 1, 0, 1, 0),
(12, 1, 8, 'IT Resume', '2010-05-21 15:37:51', '2010-05-21 15:37:51', 'page', 'IT Resume goes here.', 1, 0, 1, 0),
(13, 1, 8, 'Cust. Service Resume', '2010-05-21 15:37:51', '2010-05-21 15:37:51', 'page', 'Customer Service Resume goes here.', 1, 0, 1, 0),
(14, 1, 9, 'Blogs', '2010-05-21 15:39:37', '2010-05-21 15:39:37', 'page', 'Links to my favourite blogs go here.', 1, 0, 1, 0),
(15, 1, 9, 'Other Sites', '2010-05-21 15:39:21', '2010-05-21 15:39:21', 'page', 'Links to other sites I like go here.', 1, 0, 1, 0),
(16, 1, 3, 'UserCP', '2010-05-22 18:15:18', '2010-05-22 18:15:18', 'page', 'This is where the user cp welcome page goes.', 1, 1, 0, 0),
(17, 1, 3, 'Edit Profile', '2010-05-22 18:15:18', '2010-05-22 18:15:18', 'editprofile', '', 1, 1, 1, 1),
(18, 1, 4, 'View Users', '2010-05-23 01:48:17', '2010-05-23 01:48:17', 'displayusers', '', 1, 2, 1, 2),
(19, 1, 4, 'Edit User', '2010-05-23 01:48:17', '2010-05-23 01:48:17', 'edituser', '', 1, 5, 0, 0),
(20, 1, 4, 'AdminCP', '2010-05-23 01:50:49', '2010-05-23 01:50:49', 'page', 'This is where the Admin CP welcome page goes.', 1, 2, 0, 0),
(21, 1, 4, 'View Categories', '2010-05-23 02:12:50', '2010-05-23 02:12:50', 'displaycategories', '', 1, 2, 1, 4),
(22, 1, 4, 'Edit Category', '2010-05-23 02:12:50', '2010-05-23 02:12:50', 'editcategory', '', 1, 9, 0, 0),
(23, 1, 4, 'View Pages', '2010-05-23 02:14:42', '2010-05-23 02:14:42', 'displaypages', '', 1, 2, 1, 6),
(24, 1, 4, 'Edit Page', '2010-05-23 02:14:42', '2010-05-23 02:14:42', 'editpage', '', 1, 9, 0, 0),
(25, 1, 4, 'New User', '2010-05-24 19:35:49', '2010-05-24 19:35:49', 'newuser', '', 1, 5, 1, 1),
(26, 1, 4, 'New Category', '2010-05-24 19:35:49', '2010-05-24 19:35:49', 'newcategory', '', 1, 5, 1, 3),
(27, 1, 4, 'New Page', '2010-05-24 19:36:53', '2010-05-24 19:36:53', 'newpage', '', 1, 5, 1, 5);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `id` int(6) NOT NULL AUTO_INCREMENT,
  `username` varchar(20) NOT NULL,
  `firstname` varchar(20) NOT NULL,
  `lastname` varchar(20) NOT NULL,
  `email` varchar(50) NOT NULL,
  `hash` varchar(40) NOT NULL,
  `level` int(8) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=4 ;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `firstname`, `lastname`, `email`, `hash`, `level`) VALUES
(1, 'root', 'root', 'administrator', 'admin@philmowatt.com', 'b1b3773a05c0ed0176787a4f1574ff0075f7521e', 9),
(2, 'pmowatt', 'philip', 'mowatt', 'philmowatt@gmail.com', 'b1b3773a05c0ed0176787a4f1574ff0075f7521e', 1),
(3, 'test', 'test', 'test', 'test@test.com', 'b1b3773a05c0ed0176787a4f1574ff0075f7521e', 1);
