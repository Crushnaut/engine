<?php
/*************** 
CLASS LAYOUT
***************/
	class layout
	{	protected $content;	// as page
		protected $database;	// as DBinterface
		protected $user;	// as user
		protected $mainmenu;	// as menu
		protected $usermenu;	// as menu
		protected $submenu;		// as submenu
		private $categoryid;
		private $contentid;
		
		function __construct()
		{	require_once("lib/constants.php");
			
			// Start database
			$this->database = new DBinterface();
			
			// Set Category and Content IDs
			$this->set_categoryid($this->grab_GET("catid", 6));				// CONSTANT DEFAULT PAGE ID
			$this->set_contentid($this->grab_GET("contentid", 0));
			
			// If there is no current content selected, get id from default page id assigned to category
			if ($this->get_contentid() == 0)
			{	// If a page is not selected, then grab default page from category
				$query = "SELECT * FROM categories WHERE id = {$this->get_categoryid()}";
				$result = $this->database->query($query);
				$activeCat = mysql_fetch_array($result);
				$this->set_contentid($activeCat['default_page_id']);
			}
			
			$this->user = new user();
			$this->mainmenu = new menu("main", $this->get_categoryid());
			if ($this->user->get_level() > 0)
			{	$this->usermenu = new menu("user", $this->get_categoryid());
			} else
			{	$this->usermenu = new menu("guest", $this->get_categoryid());
			}
			$this->submenu = new submenu("rightmenu", $this->get_categoryid(), $this->get_contentid());
			
			$this->initializePage();
			
			//$this->database->disconnect();
		}
		
		private function grab_GET($index="id", $defaultvalue="0")
		{	$return = $defaultvalue;
			if (isset($_GET[$index]))
			{	$return = $_GET[$index];
			}
			return $return;
		}
		
		protected function initializePage()
		{	// Get current page
			$query = "SELECT * FROM contents WHERE id = {$this->get_contentid()}";
			$result = $this->database->query($query);
			
			// If page does not exist display error
			if (!($page = mysql_fetch_array($result)))
			{	die('This page does not exist.');
			}
			 
			// Display page based on userLVL
			if ($page['level'] > $this->user->get_level())
			{	die('You do not have permission to access this page.');
			} 
			
			// visible?
			if (!($page['visible']))
			{	die('This page have been marked as invisible.');
			}
			
			// Create page object
			$this->content = new $page['type']($page, $this->user);
		}
		
		protected function get_categoryid()
		{	return $this->categoryid;
		}
		
		protected function get_contentid()
		{	return $this->contentid;
		}
		
		protected function set_categoryid($cat)
		{	$this->categoryid = $cat;
		}
		
		protected function set_contentid($id)
		{	$this->contentid = $id;
		}
		
		function generateContent()
		{	$HTML = $this->content->generateHTML();
			echo $HTML;
		}
		
		function generateMainMenu()
		{	$HTML = $this->mainmenu->generateHTML($this->user->get_level());
			echo $HTML;
		}
		
		function generateUserMenu()
		{	$HTML = $this->usermenu->generateHTML($this->user->get_level());
			echo $HTML;
		}
		
		function generateSubMenu()
		{	$HTML = $this->submenu->generateHTML($this->user->get_level());
			echo $HTML;
		}
	}
	
/*************** 
CLASS USER
***************/	
	class user
	{	private $id;
		private $firstname;
		private $lastname;
		private $username;
		private $level;
		private $email;
		private $hash;
		
		function __construct()
		{	// start session and set user access level
			session_start();
			if (isset($_SESSION['level']))
			{	// Already active
				$this->set_id($_SESSION['id']);
				$this->set_firstname($_SESSION['firstname']);
				$this->set_lastname($_SESSION['lastname']);
				$this->set_username($_SESSION['username']);
				$this->set_level($_SESSION['level']);
				$this->set_email($_SESSION['email']);
				$this->set_hash($_SESSION['hash']);
			} else
			{	// Defaults
				$this->set_id(0);
				$this->set_firstname("Guest");
				$this->set_lastname("Account");
				$this->set_username("guest");
				$this->set_level(0);
				$this->set_email("");
				$this->set_hash("");
			}
		}
		
		function get_id()
		{	return $this->id;
		}
		
		function get_firstname()
		{	return $this->firstname;
		}
		
		function get_lastname()
		{	return $this->lastname;
		}
		
		function get_username()
		{	return $this->username;
		}
		
		function get_level()
		{	return $this->level;
		}
		
		function get_email()
		{	return $this->email;
		}
		
		function get_hash()
		{	return $this->hash;
		}
		
		function set_id($newid)
		{	$this->id = $newid;
		}
		
		function set_firstname($newFirstname)
		{	$this->firstname = $newFirstname;
		}
		
		function set_lastname($newLastname)
		{	$this->lastname = $newLastname;
		}
		
		function set_username($newUsername)
		{	$this->username = $newUsername;
		}
		
		function set_level($newLevel)
		{	$this->level = $newLevel;
		}
		
		function set_email($newEmail)
		{	$this->email = $newEmail;
		}
		
		function set_hash($newHash)
		{	$this->hash = $newHash;
		}
	}
	
/*************** 
CLASS PAGE
***************/
	class page
	{	private $id;
		private $owner_id;
		private $category_id;
		private $title;
		private $date_created;
		private $date_lastedit;
		private $type;
		private $content;
		private $visible;
		private $level;
		protected $user;
		protected $database;
		
		function __construct($dbResultArray, $theuser)
		{	// Initialize Properties
			$this->set_id($dbResultArray['id']);
			$this->set_owner_id($dbResultArray['owner_id']);
			$this->set_category_id($dbResultArray['category_id']);
			$this->set_title($dbResultArray['title']);
			$this->set_date_created($dbResultArray['date_created']);
			$this->set_date_lastedit($dbResultArray['date_lastedit']);
			$this->set_type($dbResultArray['type']);
			$this->set_content($dbResultArray['content']);
			$this->set_visible($dbResultArray['visible']);
			$this->set_level($dbResultArray['level']);
			
			// Carry over user
			$this->user = $theuser;
			
			// Start a new database connection
			$this->database = new DBinterface();
		}
		
		function generateHTML()
		{	$HTML = "";
			//$HTML .= "<h1>" . $this->get_title() . "</h1>";
			$HTML .= stripslashes($this->get_content());
			return $HTML;
		}
		
		protected function grab_GET($index="id", $defaultvalue="0")
		{	$return = $defaultvalue;
			if (isset($_GET[$index]))
			{	$return = $_GET[$index];
			}
			return $return;
		}
		
		function set_id($newid)
		{	$this->id = $newid;
		}
		
		function set_owner_id($newowner_id)
		{	$this->owner_id = $newowner_id;
		}
		
		function set_category_id($newcategory_id)
		{	$this->category_id = $newcategory_id;
		}
		
		function set_title($newtitle)
		{	$this->title = $newtitle;
		}
		
		function set_date_created($newdate_created)
		{	$this->date_created = $newdate_created;
		}
		
		function set_date_lastedit($newlast_edit)
		{	$this->date_lastedit = $newlast_edit;
		}
		
		function set_type($newtype)
		{	$this->type = $newtype;
		}
		
		function set_content($newcontent)
		{	$this->content = $newcontent;
		}
		
		function set_visible($newvisible)
		{	$this->visible = $newvisible;
		}
		
		function set_level($newlevel)
		{	$this->level = $newlevel;
		}
		
		function get_id()
		{	return $this->id;
		}
		
		function get_owner_id()
		{	return $this->owner_id;
		}
		
		function get_category_id()
		{	return $this->category_id;
		}
		
		function get_title()
		{	return $this->title;
		}
		
		function get_date_created()
		{	return $this->date_created;
		}
		
		function get_date_lastedit()
		{	return $this->date_lastedit;
		}
		
		function get_type()
		{	return $this->type;
		}
		
		function get_content()
		{	return $this->content;
		}
		
		function get_visible()
		{	return $this->visible;
		}
		
		function get_level()
		{	return $this->level;
		}
	}

/*************** 
CLASS DBINTERFACE
***************/
	class DBinterface
	{	private $connectionID;
	
		function __construct($connection=0)
		{	if ($connection)
			{	$this->set_connectionID($connection);
			} 
			else
			{	$this->connect();
			}
		}
		
		function query($query)
		{	// mysql query
			$result = mysql_query($query, $this->get_connectionID());
			// check query
			if (!$result)
			{	die("Database query failed:" . mysql_error());
			}
			return $result;
		}
		
		function connect()
		{	// Load DB constants
			$server = "localhost";
			$username = "root";
			$password = "qwerty";
			$database = "engine";
			
			// Connect to mysql
			$connection = mysql_connect($server, $username, $password);
			
			// Check connection
			if (!$connection)
			{	die("Database connection failed:" . mysql_error());
			}
			
			// Select Database
			$db_select = mysql_select_db($database, $connection);
			
			// Check selection
			if (!$db_select)
			{	die("Database selection failed:" . mysql_error());
			}
			
			// Set parameter
			$this->set_connectionID($connection);
		}
		
		function disconnect()
		{	// Close Database Connection
			mysql_close($this->get_connectionID());
			$this->set_connectionID(0);
		}
		
		function set_connectionID($newID)
		{	$this->connectionID = $newID;
		}
		
		function get_connectionID()
		{	return $this->connectionID;
		}
	}
	
/*************** 
CLASS MENU
***************/
	class menu
	{	protected $database;	// as DBinterface
		protected $menuname;
		protected $categoryid;
		protected $numItems;
	
		function __construct($newmenuname, $categoryid)
		{	$this->database = new DBinterface();
			$this->set_menuname($newmenuname);
			$this->set_categoryid($categoryid);
		}
		
		// SET
		function set_menuname($newmenuname)
		{	$this->menuname = $newmenuname;
		}
		
		function set_categoryid($newcategoryid)
		{	$this->categoryid = $newcategoryid;
		}
		
		function set_numItems($newValue)
		{	$this->numItems = $newValue;
		}
		
		// GET
		function get_menuname()
		{	return $this->menuname;
		}
		
		function get_categoryid()
		{	return $this->categoryid;
		}
		
		function get_numItems()
		{	return $this->numItems;
		}
	
		function generateHTML($userlevel)
		{	
			// create query			
			$query  = $this->generateQuery();
			
			// query
			$countresult = $this->database->query($query);
			$result = $this->database->query($query);
			// count menu items
			$counter = 0;
			while ($cat = mysql_fetch_array($countresult))
			{	if ($userlevel >= $cat['level'])
				{	$counter++;
				}
			}
			$this->set_numItems($counter);
			$counter = 0;
			// output menu HTML
			$HTML = "";
			while ($cat = mysql_fetch_array($result))
			{	// ensure access to menu is allowed
				if ($userlevel >= $cat['level'])
				{	$counter++;
					$HTML .= $this->generateMenuItem($cat, $counter);
				}
			}
			return $HTML;
		}
		
		function generateMenuItem($cat, $counter)
		{	$HTML = "";
			$HTML .= "<a href='index.php?catid={$cat['id']}' ";
			// set active tab
			if ($this->get_categoryid() == $cat['id'])
			{	$HTML .= "id='activeTab' ";
			}
			if ($counter == 1)
			{	$HTML .= "class='menufront' ";
			}
			if ($counter == $this->get_numItems())
			{	$HTML .= "class='menuback' ";
			}
			$HTML .= ">{$cat['title']}</a>";
			return $HTML;
		}
		
		function generateQuery()
		{	$query  = "SELECT * ";
			$query .= "FROM categories ";
			$query .= "WHERE visible = 1 && menu_owner = '{$this->get_menuname()}' ";
			$query .= "ORDER BY position, id ASC;";
			
			return $query;
		}
	}
	
/***************
SUB MENU
***************/
	class submenu extends menu
	{	private $contentid;
		
		function __construct($newmenuname, $categoryid, $contentid)
		{	parent::__construct($newmenuname, $categoryid);
			$this->set_contentid($contentid);
		}
		
		function set_contentid($newcontentid)
		{	$this->contentid = $newcontentid;
		}
		
		function get_contentid()
		{	return $this->contentid;
		}
		
		function generateMenuItem($cat, $counter)
		{	$HTML = "";
			$HTML .= "<a href='index.php?catid={$this->get_categoryid()}&contentid={$cat['id']}' ";
			// set active tab
			if ($this->get_contentid() == $cat['id'])
			{	$HTML .= "id='activeTab' ";
			}
			if ($counter == 1)
			{	$HTML .= "class='menufront' ";
			}
			if ($counter == $this->get_numItems())
			{	$HTML .= "class='menuback' ";
			}
			$HTML .= ">{$cat['title']}</a>";
			return $HTML;
		}
		
		function generateQuery()
		{	$query = "SELECT * FROM contents WHERE (category_id = {$this->get_categoryid()} && visible = 1 && menuvisible = 1) ORDER BY menuposition, id ASC;";
					
			return $query;
		}
	}
/***************
LOGIN PAGE
***************/
	class loginpage extends page
	{	function generateHTML()
		{	$submit = $this->grab_GET("submit", 0);
			if ($this->user->get_level() == 0)
			{	if ($submit)
				{	$HTML = $this->loginuser();
				}
				else
				{	$HTML = $this->displayForm();
				}
			}
			else
			{	$HTML = "Error: You are already logged in.";
			}
			return $HTML;
		}
		
		private function loginuser()
		{	// Grab posts
			$login_username = $this->grab_POST('username', '');
			$login_password = $this->grab_POST('password', '');
			
			$query = "SELECT * FROM users WHERE username = '{$login_username}'";
			$result = $this->database->query($query);
			if ($login_user = mysql_fetch_array($result))
			{	if ($login_user['hash'] == sha1($login_password))
				{	$_SESSION['id'] = $login_user['id'];
					$_SESSION['username'] = $login_user['username'];
					$_SESSION['level'] = $login_user['level'];
					$_SESSION['firstname'] = $login_user['firstname'];
					$_SESSION['lastname'] = $login_user['lastname'];
					$_SESSION['email'] = $login_user['email'];
					$_SESSION['hash'] = $login_user['hash'];
					
					$HTML = "<p>Log in successful.</p>";
					$HTML .= "<p>Please click <a href='index.php?catid=3'>here</a> in order to configure your settings.</p>";
					$HTML .= "<p><a href='index.php'>Return to main site</a></p>";
					
					header("Location: index.php");			
				} else
				{	$HTML = "<p>Log in failed.  Incorrect password.</p>";
					$HTML .= $this->displayForm();
				}
			} else
			{	$HTML = "<p>Username not found in database.</p>";
				$HTML .= $this->displayForm();
			}
			
			return $HTML;
		}
		
		private function grab_POST($index="id", $defaultvalue="0")
		{	$return = $defaultvalue;
			if (isset($_POST[$index]))
			{	$return = $_POST[$index];
			}
			return $return;
		}
		
		private function displayForm()
		{	$HTML = "<p>Please enter your log-in information below.</p>";
			
			$loginForm = new formGenerator("Login", "index.php?catid={$this->get_category_id()}&submit=1");
			$loginForm->add_textbox("Username", "username", 1);
			$loginForm->add_password("Password", "password", 2);
			$loginForm->add_submitbutton("Login", "login", 3);
			
			$HTML .= $loginForm->outputFinalForm();
			return $HTML;
		}
	}
/***************
Logout Page
***************/
	class logoutpage extends page
	{	function generateHTML()
		{	session_destroy();
			$HTML = "<p>You have been successfully logged out.</p><p></p>";
			$HTML .= "<p><a href='index.php'>Return to main site</a></p>";
			return $HTML;
		}
	}
	
/***************
FORM GENERATOR
***************/	
	class formGenerator
	{	private $HTML;
	
		function __construct($formName = "form", $action = "")
		{	$this->set_HTML("<form name='{$formName}' method='post' action='{$action}' ><table>");
		}
		
		function outputFinalForm()
		{	$this->addToHTML("</table></form>");
			return $this->get_HTML();
		}
		
		function add_textbox($question, $name, $index, $defaultValue="")
		{	$this->addToHTML("<tr><td align=right>{$question}:</td><td><input type='text' name='{$name}' id='form{$name}' value='{$defaultValue}' tabindex='{$index}'></td></tr>");
		}
		
		function add_textarea($question, $name, $index, $defaultValue="")
		{	$defaultValue = stripslashes($defaultValue);
			$this->addToHTML("</table><p>{$question}:</p><p><textarea name='{$name}' id='form{$name}' tabindex='{$index}'>{$defaultValue}</textarea></p><table id=form>");
		}
		
		function add_checkbox($question, $name, $index, $defaultValue=0)
		{	$HTML = "<tr><td align=right>{$question}:</td><td><input name='{$name}' type='checkbox' tabindex='{$index}' id='form{$name}' value='1'";
			if ($defaultValue)
			{	$HTML .= "checked='checked'";
			}
			$HTML .= " /></td></tr>";
			$this->addToHTML($HTML);
		}
		
		function add_captcha()
		{
		}
		
		function add_password($question, $name, $index, $defaultValue="")
		{	$this->addToHTML("<tr><td align=right>{$question}:</td><td><input type='password' name='{$name}' id='form{$name}' value='{$defaultValue}' tabindex='{$index}'></td></tr>");
		}
		
		function add_submitButton($buttontext, $name, $index)
		{	$this->addToHTML("<tr><td align=right>&nbsp;</td><td><input type='submit' name='{$name}' id='form{$name}' value='{$buttontext}' tabindex='{$index}'></td></tr>");
		}
		
		private function get_HTML()
		{	return $this->HTML;
		}
		
		private function set_HTML($string)
		{	$this->HTML = $string;
		}
		
		private function addToHTML($string)
		{	$this->set_HTML($this->get_HTML() . $string);
		}
	}
/***************
EDIT DB PAGE
***************/	
	
	class editDBpage extends page
	{	/* This class is intended to be extended, and doesn't actually do anything. 
			Extend this class by writing a new constructor using the following format:
		function __construct($dbResultArray, $theuser)
		{	parent::__construct($dbResultArray, $theuser);
			// All of the following arrays must match 1<=>1
				// Number of elements in arrays
				$this->set_numElements(0);
				// Array of Questions
				$this->set_questions(array());
				// Array of Column Titles, also used for names of form elements
				$this->set_titles(array());
				// Array of default values for form elements
				$this->set_defaultValues(array());
				// Array of types of form elements (0 if no form element needed)
				$this->set_types(array());
				// Array of max lengths
				$this->set_maxLengths(array());
				// Array of min lengths
				$this->set_minLengths(array());
				// Array of (1, 0) to check for duplicates already in the table
				$this->set_check4duplicates(array());
				// Array of (1, 0) whether or not to insert into DB
				$this->set_insert(array());
				// Array of (1, 0) whether or not this element will be Posted
				$this->set_post(array());
				// Array of the various data types used.  This is used in checking input format and initial dataformating																
				// Valid choices are "text", "fulltext", "int", "boolean", "username", "password", "email", "date"
				// if blank no checks or formatting applied
				$this->set_dataType(array());
			// Message displayed after successfully interacting with DB
			$this->set_successMessage("");
			// Name of the table to interact with
			$this->set_tableName("");
			// Name of the form
			$this->set_formName("");
			// Action the form should take
			$this->set_formAction("");
			// Action of the SQL (insert || update)
			$this->set_sqlAction("");
			// If you are UPDATING a DB which line are you updating?
			$this->set_sqlWhere("");
			
		}
			
			You can also overide the following functions to add custom elements:
				- collectPOSTS
				- checkinputs
				- displayForm
				- generateQuery
				- successMessage
		*/
		private $numElements;
		private $questions;
		private $titles;
		private $formType;
		private $maxLengths;
		private $minLengths;
		private $check4duplicates;
		private $insert;
		private $post;
		private $successMessage;
		private $tableName;
		private $formName;
		private $formAction;
		private $sqlAction;
		private $sqlWhere;
		private $dataType;
		
		function __construct($dbResultArray, $theuser)
		{	parent::__construct($dbResultArray, $theuser);
		}
		
		function generateHTML()
		{	$HTML = "<h1>" . $this->get_title() . "</h1>";
			$submit = $this->grab_GET("submit", 0);
			if ($submit)
			{	$HTML .= $this->update();
			}
			else
			{	$HTML .= $this->generateForm();
			}
			
			return $HTML;
		}
		
		private function update()
		{	$postarray = $this->collectPOSTs();
			$HTML = $this->checkinputs($postarray);
			
			/* If the HTML varible is set at this point it is because
				of an error, thus if it is not set it is okay it insert
				the data into the database */
			if (!($HTML))
			{	$HTML .= $this->updateDB($postarray);
			}
			else
			{	$HTML .= $this->generateForm();
			}
			
			return $HTML;
		}
		
		private function updateDB($postarray)
		{	$query = $this->generateQuery($postarray);
			$result = $this->database->query($query);
			$HTML = $this->successMessage();
			return $HTML;
		}
		
		protected function collectPOSTs()
		{	$count = 0;
		
			// third party functions used to prevent XSS attacks
			require_once("/extensions/htmlpurifier-3.1.0rc1-standalone/HTMLPurifier.standalone.php");
		    $purifier = new HTMLPurifier();
			
			while ($this->get_numElements() > $count)
			{	if ($this->get_post($count))
				{	// Apply initial formating to inputs
					if ($this->get_dataType($count) == "boolean")
					{	$values[$count] = $purifier->purify($this->grab_POST($this->get_titles($count), "0"));
					}
					elseif ($this->get_dataType($count) == "int")
					{	$values[$count] = $purifier->purify($this->grab_POST($this->get_titles($count), "0"));
					}
					elseif ($this->get_dataType($count) == "text")
					{	$values[$count] = $purifier->purify($this->grab_POST($this->get_titles($count), ""));
					}
					elseif ($this->get_dataType($count) == "username")
					{	$values[$count] = $purifier->purify(strtolower($this->grab_POST($this->get_titles($count), "")));
					}
					elseif ($this->get_dataType($count) == "fulltext")
					{	$values[$count] = addslashes($purifier->purify($this->grab_POST($this->get_titles($count), "")));
					}
					elseif ($this->get_dataType($count) == "email")
					{	$values[$count] = $purifier->purify($this->grab_POST($this->get_titles($count), ""));
					}
					elseif ($this->get_dataType($count) == "password")
					{	$values[$count] = $purifier->purify($this->grab_POST($this->get_titles($count), ""));
					}
					elseif ($this->get_dataType($count) == "date")
					{	$values[$count] = $purifier->purify($this->grab_POST($this->get_titles($count), ""));
					}
					else
					{	$values[$count] = $this->grab_POST($this->get_titles($count), "");
					}
				}
				$count++;
			}
			return $values;
		}
		
		protected function checkinputs($values)
		{	$HTML = "";
			$count = 0;
			while ($this->get_numElements() > $count)
			{	// Apply formating checkss
				if ($this->get_dataType($count) == "int")
				{	if (strpbrk(strtolower($values[$count]), ("abcdefghijklmnopqrstuvwxyz`~!@#$%^&*()_+{}|:<>?-=[]\;',./" . '"')))
					{	$HTML .= "<p class 'error'>You may only enter a number [0-9] in the textbox {$this->get_titles($count)}.</p>";
					}
				}
				elseif ($this->get_dataType($count) == "text")
				{	$values[$count] = $this->grab_POST($this->get_titles($count), "");
				}
				elseif ($this->get_dataType($count) == "username")
				{	$values[$count] = strtolower($this->grab_POST($this->get_titles($count), ""));
				}
				elseif ($this->get_dataType($count) == "fulltext")
				{	$values[$count] = addslashes($this->grab_POST($this->get_titles($count), ""));
				}
				if ($this->get_maxLengths($count))
				{	if (strlen($values[$count]) > $this->get_maxLengths($count))
					{	$HTML .= "<p class='error'>That {$this->get_titles($count)} is too long.  Max length is {$this->get_maxLengths($count)}</p>";
					}
				}
				if ($this->get_minLengths($count))
				{	if (strlen($values[$count]) < $this->get_minLengths($count))
					{	$HTML .= "<p class='error'>That {$this->get_titles($count)} is not long enough.  Min length is {$this->get_minLengths($count)}</p>";
					}
				}
				if ($this->get_check4duplicates($count))
				{	$query = "SELECT * FROM {$this->get_tableName()} WHERE {$this->get_titles($count)} = '{$values[$count]}';";
					$result = $this->database->query($query);
					if ($check = mysql_fetch_array($result))
					{	$HTML .= "<p class='error'>That {$this->get_titles($count)} is already in use.  Please select a different value.</p>";
					}
				}
				$count++;
			}
			return $HTML;
		}
		
		protected function successMessage()
		{	$HTML = "<p>{$this->get_successMessage()}</p>";
			return $HTML;
		}
		
		protected function generateQuery($values)
		{	if ($this->get_sqlAction() == "insert")
			{	$query = "INSERT INTO ";
				$query .= $this->get_tableName() . " (";
				
				$count = 0;
				while ($this->get_numElements() > $count)
				{	if ($this->get_insert($count))
					{	$query .= $this->get_titles($count);
						$query .= ", ";
					}
					$count++;
				}
				$query = substr($query, 0, (strlen($query)-2));
				$query .= ") VALUES (";
				
				$count = 0;
				while ($this->get_numElements() > $count)
				{	if ($this->get_insert($count))
					{	$query .= "'{$values[$count]}', ";
					}
					$count++;
				}
				$query = substr($query, 0, (strlen($query)-2));
				$query .= ");";
			}
			elseif ($this->get_sqlAction() == "update")
			{	$query = "UPDATE ";
				$query .= $this->get_tableName();
				$query .= " SET ";
				
				$count = 0;
				while ($this->get_numElements() > $count)
				{	if ($this->get_insert($count))
					{	$query .= "{$this->get_titles($count)} = '{$values[$count]}', ";
					}
					$count++;
				}
				$query = substr($query, 0, (strlen($query)-2));
				$query .= " WHERE ";
				$query .= $this->get_sqlWhere();
				$query .= ";";
			}
			else
			{	die("Invalid sqlAction selected");
			}
			return $query; 
		}
		
		protected function generateForm()
		{	$HTML = "";
			$count = 0;
			$form = new formGenerator($this->get_formName(), $this->get_formAction());
			while ($this->get_numElements() > $count)
			{	if ($this->get_formType($count) == "textbox")
				{	$form->add_textbox($this->get_questions($count), $this->get_titles($count), ($count+1), $this->get_defaultValues($count));
				}
				if ($this->get_formType($count) == "password")
				{	$form->add_password($this->get_questions($count), $this->get_titles($count), ($count+1), $this->get_defaultValues($count));
				}
				if ($this->get_formType($count) == "submitbutton")
				{	$form->add_submitButton($this->get_questions($count), $this->get_titles($count), ($count+1));
				}
				if ($this->get_formType($count) == "textarea")
				{	$form->add_textarea($this->get_questions($count), $this->get_titles($count), ($count+1), $this->get_defaultValues($count));
				}
				if ($this->get_formType($count) == "checkbox")
				{	$form->add_checkbox($this->get_questions($count), $this->get_titles($count), ($count+1), $this->get_defaultValues($count));
				} 
				$count++;
			}
			return $form->outputFinalForm();
		}
		
		private function grab_POST($index="id", $defaultvalue="0")
		{	$return = $defaultvalue;
			if (isset($_POST[$index]))
			{	$return = $_POST[$index];
			}
			return $return;
		}
	
	// Getter and Setter Methods	
		protected function set_dataType($newValue)
		{	$this->dataType = $newValue;
		}
		
		protected function get_dataType($index)
		{	return $this->dataType[$index];
		}
		
		protected function set_numElements($newValue)
		{	$this->numElements = $newValue;
		}
	
		protected function get_numElements()
		{	return $this->numElements;
		}
		
		protected function set_questions($newValue)
		{	$this->questions = $newValue;
		}
		
		protected function get_questions($index)
		{	return $this->questions[$index];
		}
		
		protected function set_titles($newValue)
		{	$this->titles = $newValue;
		}
		
		protected function get_titles($index)
		{	return $this->titles[$index];
		}
		
		protected function set_defaultValues($newValue)
		{	$this->defaultValues = $newValue;
		}
		
		protected function get_defaultValues($index)
		{	return $this->defaultValues[$index];
		}
		
		protected function set_formType($newValue)
		{	$this->formType = $newValue;
		}
		
		protected function get_formType($index)
		{	return $this->formType[$index];
		}
		
		protected function set_maxLengths($newValue)
		{	$this->maxLengths = $newValue;
		}
		
		protected function get_maxLengths($index)
		{	return $this->maxLengths[$index];
		}
		
		protected function set_minLengths($newValue)
		{	$this->minLengths = $newValue;
		}
		
		protected function get_minLengths($index)
		{	return $this->minLengths[$index];
		}
		
		protected function set_check4duplicates($newValue)
		{	$this->check4duplicates = $newValue;
		}
		
		protected function get_check4duplicates($index)
		{	return $this->check4duplicates[$index];
		}
		
		protected function set_insert($newValue)
		{	$this->insert = $newValue;
		}
		
		protected function get_insert($index)
		{	return $this->insert[$index];
		}
		
		protected function set_post($newValue)
		{	$this->post = $newValue;
		}
		
		protected function get_post($index)
		{	return $this->post[$index];
		}
		
		protected function set_successMessage($newValue)
		{	$this->successMessage = $newValue;
		}
		
		protected function get_successMessage()
		{	return $this->successMessage;
		}
		
		protected function set_tableName($newValue)
		{	$this->tableName = $newValue;
		}
		
		protected function get_tableName()
		{	return $this->tableName;
		}
		
		protected function set_formName($newValue)
		{	$this->formName = $newValue;
		}
		
		protected function get_formName()
		{	return $this->formName;
		}
		
		protected function set_formAction($newValue)
		{	$this->formAction = $newValue;
		}
		
		protected function get_formAction()
		{	return $this->formAction;
		}

		protected function set_sqlAction($newValue)
		{	$this->sqlAction = $newValue;
		}
		
		protected function get_sqlAction()
		{	return $this->sqlAction;
		}
		
		protected function set_sqlWhere($newValue)
		{	$this->sqlWhere = $newValue;
		}		
		
		protected function get_sqlWhere()
		{	return $this->sqlWhere;
		}
	}
	
/***************
Register User
***************/
	class registeruser extends editDBpage
	{	function __construct($dbResultArray, $theuser)
		{	parent::__construct($dbResultArray, $theuser);
			// All of the following arrays must match 1<=>1
				// Number of elements in arrays
				$this->set_numElements(9);
				// Array of Questions
				$this->set_questions(array("First Name", "Last Name", "User Name", "Password", "Confirm Password", "E-mail", "Hash", "Register", "Level"));
				// Array of Column Titles, also used for names of form elements
				$this->set_titles(array("firstname", "lastname", "username", "password", "cpassword", "email", "hash", "register", "level"));
				// Array of default values for form elements
				$this->set_defaultValues(array("", "", "", "", "", "", "", "", ""));
				// Array of formType of form elements (0 if no form element needed)
				$this->set_formType(array("textbox", "textbox", "textbox", "password", "password", "textbox", "", "submitbutton", ""));
				// Array of max lengths
				$this->set_maxLengths(array(20, 20, 20, 20, 0, 40, 0, 0, 0));
				// Array of min lengths
				$this->set_minLengths(array(1, 1, 1, 1, 0, 1, 0, 0, 0));
				// Array of (1, 0) to check for duplicates already in the table
				$this->set_check4duplicates(array(0, 0, 1, 0, 0, 0, 0, 0, 0));
				// Array of (1, 0) whether or not to insert into DB
				$this->set_insert(array(1, 1, 1, 0, 0, 1, 1, 0, 1));
				// Array of (1, 0) whether or not this element will be Posted
				$this->set_post(array(1, 1, 1, 1, 1, 1, 0, 0, 0));
				// Array of the various data types used.  This is used in checking input format and initial dataformating
				// Valid choices are "text", "fulltext", "int", "boolean", "username", "password", "email"
				// if blank no checks or formatting applied
				$this->set_dataType(array("text", "text", "username", "password", "password", "email", "", "", "int"));
			// Message displayed after successfully interacting with DB
			$this->set_successMessage("You have successfully registered.");
			// Name of the table to interact with
			$this->set_tableName("users");
			// Name of the form
			$this->set_formName("register");
			// Action the form should take
			$this->set_formAction("index.php?catid={$this->get_category_id()}&submit=1");
			// Action of the SQL (insert || update)
			$this->set_sqlAction("insert");
			// If you are UPDATING a DB which line are you updating?
			$this->set_sqlWhere("");
		}
		
		protected function generateQuery($array)
		{	// Set HASH
			$array[6] = sha1($array[3]);
			$query = parent::generateQuery($array);
			return $query;
		}
		
		protected function checkInputs($array)
		{	// Set Level
			$array[8] = 1;
			$HTML = parent::checkInputs($array);
			if (!($array[3] == $array[4]))
			{	$HTML .= "<p class='error'>Passwords do not match.</p>";
			}
			return $HTML;
		}
	}
	
/***************
EDIT PROFILE
***************/
	class editprofile extends editDBpage
	{	function __construct($dbResultArray, $theuser)
		{	parent::__construct($dbResultArray, $theuser);
			// All of the following arrays must match 1<=>1
				// Number of elements in arrays
				$this->set_numElements(7);
				// Array of Questions
				$this->set_questions(array("First Name", "Last Name", "Password", "Confirm Password", "E-mail", "Hash", "Update"));
				// Array of Column Titles, also used for names of form elements
				$this->set_titles(array("firstname", "lastname", "password", "cpassword", "email", "hash", "update"));
				// Array of default values for form elements
				$this->set_defaultValues(array($this->user->get_firstname(), $this->user->get_lastname(), "", "", $this->user->get_email(), "", ""));
				// Array of formType of form elements (0 if no form element needed)
				$this->set_formType(array("textbox", "textbox", "password", "password", "textbox", "", "submitbutton"));
				// Array of max lengths
				$this->set_maxLengths(array(20, 20, 20, 0, 40, 0, 0));
				// Array of min lengths
				$this->set_minLengths(array(1, 1, 0, 0, 1, 0, 0));
				// Array of (1, 0) to check for duplicates already in the table
				$this->set_check4duplicates(array(0, 0, 0, 0, 0, 0, 0));
				// Array of (1, 0) whether or not to insert into DB
				$this->set_insert(array(1, 1, 0, 0, 1, 0, 0));
				// Array of (1, 0) whether or not this element will be Posted
				$this->set_post(array(1, 1, 1, 1, 1, 0, 0));
				// Array of the various data types used.  This is used in checking input format and initial dataformating																
				// Valid choices are "text", "fulltext", "int", "boolean", "username", "password", "email"
				// If blank no checks or formatting applied
				$this->set_dataType(array("text", "text", "password", "password", "email", "", ""));
			// Message displayed after successfully interacting with DB
			$this->set_successMessage("You have successfully updated your profile.  These changes will come into effect next time you login.");
			// Name of the table to interact with
			$this->set_tableName("users");
			// Name of the form
			$this->set_formName("update");
			// Action the form should take
			$this->set_formAction("index.php?catid={$this->get_category_id()}&contentid={$this->get_id()}&submit=1");
			// Action of the SQL (insert || update)
			$this->set_sqlAction("update");
			// If you are UPDATING a DB which line are you updating?
			$this->set_sqlWhere("id = {$this->user->get_id()}");
		}
		
		protected function generateQuery($array)
		{	// Set HASH if the password has been changed
			if ($array[2])
			{	$array[5] = sha1($array[2]);
				$this->set_insert(array(1, 1, 0, 0, 1, 1, 0));
			}
			$query = parent::generateQuery($array);
			return $query;
		}
		
		protected function checkInputs($array)
		{	$HTML = parent::checkInputs($array);
			if (!($array[2] == $array[3]))
			{	$HTML .= "<p class='error'>Passwords do not match.</p>";
			}
			return $HTML;
		}
	}
		
/***************
new category
***************/
	class newcategory extends editDBpage
	{	function __construct($dbResultArray, $theuser)	
		{	parent::__construct($dbResultArray, $theuser);
			// All of the following arrays must match 1<=>1
				// Number of elements in arrays
				$this->set_numElements(7);
				// Array of Questions
				$this->set_questions(array("Title", "Position", "Menu Owner", "Default Page ID", "Visible?", "Level", "Create"));
				// Array of Column Titles, also used for names of form elements
				$this->set_titles(array("title", "position", "menu_owner", "default_page_id", "visible", "level", "create"));
				// Array of default values for form elements
				$this->set_defaultValues(array("", "", "", "", "", "", ""));
				// Array of formType of form elements (0 if no form element needed)
				$this->set_formType(array("textbox", "textbox", "textbox", "textbox", "checkbox", "textbox", "submitbutton"));
				// Array of max lengths
				$this->set_maxLengths(array(20, 2, 20, 6, 1, 8, 0));
				// Array of min lengths
				$this->set_minLengths(array(1, 1, 1, 1, 1, 1, 0));
				// Array of (1, 0) to check for duplicates already in the table
				$this->set_check4duplicates(array(0, 0, 0, 0, 0, 0, 0));
				// Array of (1, 0) whether or not to insert into DB
				$this->set_insert(array(1, 1, 1, 1, 1, 1, 0));
				// Array of (1, 0) whether or not this element will be Posted
				$this->set_post(array(1, 1, 1, 1, 1, 1, 0));
				// Array of the various data types used.  This is used in checking input format and initial dataformating																
				// Valid choices are "text", "fulltext", "int", "boolean", "username", "password", "email"
				// if blank no checks or formatting applied
				$this->set_dataType(array("text", "int", "text", "int", "boolean", "int", ""));
			// Message displayed after successfully interacting with DB
			$this->set_successMessage("You have successfully created a new category.");
			// Name of the table to interact with
			$this->set_tableName("categories");
			// Name of the form
			$this->set_formName("newcat");
			// Action the form should take
			$this->set_formAction("index.php?catid={$this->get_category_id()}&contentid={$this->get_id()}&submit=1");
			// Action of the SQL (insert || update)
			$this->set_sqlAction("insert");
			// If you are UPDATING a DB which line are you updating?
			$this->set_sqlWhere("");
		}
	}

/***************
new user
***************/
	class newuser extends editDBpage
	{	function __construct($dbResultArray, $theuser)
		{	parent::__construct($dbResultArray, $theuser);
			// All of the following arrays must match 1<=>1
				// Number of elements in arrays
				$this->set_numElements(9);
				// Array of Questions
				$this->set_questions(array("First Name", "Last Name", "User Name", "Password", "Confirm Password", "E-mail", "Level", "Hash", "Register"));
				// Array of Column Titles, also used for names of form elements
				$this->set_titles(array("firstname", "lastname", "username", "password", "cpassword", "email", "level", "hash", "register"));
				// Array of default values for form elements
				$this->set_defaultValues(array("", "", "", "", "", "", "", "", ""));
				// Array of formType of form elements (0 if no form element needed)
				$this->set_formType(array("textbox", "textbox", "textbox", "password", "password", "textbox", "textbox", "", "submitbutton"));
				// Array of max lengths
				$this->set_maxLengths(array(20, 20, 20, 20, 0, 40, 8, 0, 0));
				// Array of min lengths
				$this->set_minLengths(array(1, 1, 1, 1, 0, 1, 1, 0, 0));
				// Array of (1, 0) to check for duplicates already in the table
				$this->set_check4duplicates(array(0, 0, 1, 0, 0, 0, 0, 0, 0));
				// Array of (1, 0) whether or not to insert into DB
				$this->set_insert(array(1, 1, 1, 0, 0, 1, 1, 1, 0));
				// Array of (1, 0) whether or not this element will be Posted
				$this->set_post(array(1, 1, 1, 1, 1, 1, 1, 0, 0));
				// Array of the various data types used.  This is used in checking input format and initial dataformating																
				// Valid choices are "text", "fulltext", "int", "boolean", "username", "password", "email"
				// if blank no checks or formatting applied
				$this->set_dataType(array("text", "text", "username", "password", "password", "email", "int", "", ""));
			// Message displayed after successfully interacting with DB
			$this->set_successMessage("You have successfully created a new user.");
			// Name of the table to interact with
			$this->set_tableName("users");
			// Name of the form
			$this->set_formName("register");
			// Action the form should take
			$this->set_formAction("index.php?catid={$this->get_category_id()}&contentid={$this->get_id()}&submit=1");
			// Action of the SQL (insert || update)
			$this->set_sqlAction("insert");
			// If you are UPDATING a DB which line are you updating?
			$this->set_sqlWhere("");
		}
		
		protected function generateQuery($array)
		{	// Set HASH
			$array[7] = sha1($array[3]);
			$query = parent::generateQuery($array);
			return $query;
		}
		
		protected function checkInputs($array)
		{	$HTML = parent::checkInputs($array);
			if (!($array[3] == $array[4]))
			{	$HTML .= "<p class='error'>Passwords do not match.</p>";
			}
			return $HTML;
		}
	}

/***************
edit user
***************/
	class edituser extends editDBpage
	{	function __construct($dbResultArray, $theuser)
		{	parent::__construct($dbResultArray, $theuser);
			// All of the following arrays must match 1<=>1
				// Number of elements in arrays
				$this->set_numElements(5);
				// Array of Questions
				$this->set_questions(array("First Name", "Last Name", "E-mail", "Level", "Update"));
				// Array of Column Titles, also used for names of form elements
				$this->set_titles(array("firstname", "lastname", "email", "level", "update"));
				// Array of default values for form elements
				$this->set_defaultValues($this->retriveDVfromDB());
				// Array of formType of form elements (0 if no form element needed)
				$this->set_formType(array("textbox", "textbox", "textbox", "textbox", "submitbutton"));
				// Array of max lengths
				$this->set_maxLengths(array(20, 20, 40, 8, 0));
				// Array of min lengths
				$this->set_minLengths(array(1, 1, 1, 1, 0));
				// Array of (1, 0) to check for duplicates already in the table
				$this->set_check4duplicates(array(0, 0, 0, 0, 0));
				// Array of (1, 0) whether or not to insert into DB
				$this->set_insert(array(1, 1, 1, 1, 0));
				// Array of (1, 0) whether or not this element will be Posted
				$this->set_post(array(1, 1, 1, 1, 0));
				// Array of the various data types used.  This is used in checking input format and initial dataformating																
				// Valid choices are "text", "fulltext", "int", "boolean", "username", "password", "email"
				// if blank no checks or formatting applied
				$this->set_dataType(array("text", "text", "email", "int", ""));
			// Message displayed after successfully interacting with DB
			$this->set_successMessage("You have successfully edited a user.");
			// Name of the table to interact with
			$this->set_tableName("users");
			// Name of the form
			$this->set_formName("edituser");
			// Action the form should take
			$this->set_formAction("index.php?catid={$this->get_category_id()}&contentid={$this->get_id()}&id={$this->grab_GET('id')}&submit=1");
			// Action of the SQL (insert || update)
			$this->set_sqlAction("update");
			// If you are UPDATING a DB which line are you updating?
			$this->set_sqlWhere("id = {$this->grab_GET('id')}");
		}
		
		private function retriveDVfromDB()
		{	$userID = $this->grab_GET("id", "");
			$query = "SELECT firstname, lastname, email, level FROM users WHERE id = {$userID};";
			$result = $this->database->query($query);
			return mysql_fetch_array($result);
		}
	}

/***************
edit category
***************/
	class editcategory extends editDBpage
	{	function __construct($dbResultArray, $theuser)
		{	parent::__construct($dbResultArray, $theuser);
			// All of the following arrays must match 1<=>1
				// Number of elements in arrays
				$this->set_numElements(7);
				// Array of Questions
				$this->set_questions(array("Title", "Position", "Menu Owner", "Default Page ID", "Visible?", "Level", "Edit"));
				// Array of Column Titles, also used for names of form elements
				$this->set_titles(array("title", "position", "menu_owner", "default_page_id", "visible", "level", "edit"));
				// Array of default values for form elements
				$this->set_defaultValues($this->retriveDVfromDB());
				// Array of formType of form elements (0 if no form element needed)
				$this->set_formType(array("textbox", "textbox", "textbox", "textbox", "checkbox", "textbox", "submitbutton"));
				// Array of max lengths
				$this->set_maxLengths(array(20, 2, 20, 6, 1, 8, 0));
				// Array of min lengths
				$this->set_minLengths(array(1, 1, 1, 1, 1, 1, 0));
				// Array of (1, 0) to check for duplicates already in the table
				$this->set_check4duplicates(array(0, 0, 0, 0, 0, 0, 0));
				// Array of (1, 0) whether or not to insert into DB
				$this->set_insert(array(1, 1, 1, 1, 1, 1, 0));
				// Array of (1, 0) whether or not this element will be Posted
				$this->set_post(array(1, 1, 1, 1, 1, 1, 0));
				// Array of the various data types used.  This is used in checking input format and initial dataformating																
				// Valid choices are "text", "fulltext", "int", "boolean", "username", "password", "email"
				// if blank no checks or formatting applied
				$this->set_dataType(array("text", "int", "text", "int", "boolean", "int", ""));
			// Message displayed after successfully interacting with DB
			$this->set_successMessage("You have successfully edited a category.");
			// Name of the table to interact with
			$this->set_tableName("categories");
			// Name of the form
			$this->set_formName("editcat");
			// Action the form should take
			$this->set_formAction("index.php?catid={$this->get_category_id()}&contentid={$this->get_id()}&id={$this->grab_GET('id')}&submit=1");
			// Action of the SQL (insert || update)
			$this->set_sqlAction("update");
			// If you are UPDATING a DB which line are you updating?
			$this->set_sqlWhere("id = {$this->grab_GET('id')}");
		}
		
		private function retriveDVfromDB()
		{	$catID = $this->grab_GET("id");
			$query = "SELECT title, position, menu_owner, default_page_id, visible, level FROM categories WHERE id = {$catID};";
			$result = $this->database->query($query);
			return mysql_fetch_array($result);
		}
	}
	
/***************
new page
***************/
	class newpage extends editDBpage
	{	function __construct($dbResultArray, $theuser)
		{	parent::__construct($dbResultArray, $theuser);
			// All of the following arrays must match 1<=>1
				// Number of elements in arrays
				$this->set_numElements(12);
				// Array of Questions
				$this->set_questions(array("Owner ID", "Category ID", "Title", "Date Created", "Date of Last Edit", "Page Type", "Visible?", "Access Level", "Visible in Menu?", "Menu Position", "Content", "Create"));
				// Array of Column Titles, also used for names of form elements
				$this->set_titles(array("owner_id", "category_id", "title", "date_created", "date_lastedit", "type", "visible", "level", "menuvisible", "menuposition", "content", "create"));
				// Array of default values for form elements
				$this->set_defaultValues(array($this->user->get_id(), "", "", "NOW", "NOW", "page", "1", "0", "1", "", "", ""));
				// Array of formType of form elements (0 if no form element needed)
				$this->set_formType(array("textbox", "textbox", "textbox", "", "", "textbox", "checkbox", "textbox", "checkbox", "textbox", "textarea", "submitbutton"));
				// Array of max lengths
				$this->set_maxLengths(array(6, 6, 20, 0, 0, 20, 1, 8, 1, 2, 0, 0));
				// Array of min lengths
				$this->set_minLengths(array(1, 1, 1, 0, 0, 1, 1, 1, 1, 1, 0, 0));
				// Array of (1, 0) to check for duplicates already in the table
				$this->set_check4duplicates(array(0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0));
				// Array of (1, 0) whether or not to insert into DB
				$this->set_insert(array(1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 0));
				// Array of (1, 0) whether or not this element will be Posted
				$this->set_post(array(1, 1, 1, 0, 0, 1, 1, 1, 1, 1, 1, 0));
				// Array of the various data types used.  This is used in checking input format and initial dataformating																
				// Valid choices are "text", "fulltext", "int", "boolean", "username", "password", "email"
				// if blank no checks or formatting applied
				$this->set_dataType(array("int", "int", "text", "date", "date", "text", "boolean", "int", "boolean", "int", "fulltext", ""));
			// Message displayed after successfully interacting with DB
			$this->set_successMessage("You have successfully created a new page.");
			// Name of the table to interact with
			$this->set_tableName("contents");
			// Name of the form
			$this->set_formName("newpage");
			// Action the form should take
			$this->set_formAction("index.php?catid={$this->get_category_id()}&contentid={$this->get_id()}&submit=1");
			// Action of the SQL (insert || update)
			$this->set_sqlAction("insert");
			// If you are UPDATING a DB which line are you updating?
			$this->set_sqlWhere("");
		}
		
		protected function generateQuery($array)
		{	// Set date created and date of last edit
			$array[3] = date("c");
			$array[4] = date("c");
			$query = parent::generateQuery($array);
			return $query;
		}
	}

/***************
edit page
***************/
	class editpage extends editDBpage
	{	function __construct($dbResultArray, $theuser)
		{	parent::__construct($dbResultArray, $theuser);
			// All of the following arrays must match 1<=>1
				// Number of elements in arrays
				$this->set_numElements(11);
				// Array of Questions
				$this->set_questions(array("Owner ID", "Category ID", "Title", "Date of Last Edit", "Page Type", "Visible?", "Access Level", "Visible in Menu?", "Menu Position", "Content", "Update"));
				// Array of Column Titles, also used for names of form elements
				$this->set_titles(array("owner_id", "category_id", "title", "date_lastedit", "type", "visible", "level", "menuvisible", "menuposition", "content", "update"));
				// Array of default values for form elements
				$this->set_defaultValues($this->retriveDVfromDB());
				// Array of formType of form elements (0 if no form element needed)
				$this->set_formType(array("textbox", "textbox", "textbox", "", "textbox", "checkbox", "textbox", "checkbox", "textbox", "textarea", "submitbutton"));
				// Array of max lengths
				$this->set_maxLengths(array(6, 6, 20, 0, 20, 1, 8, 1, 2, 0, 0));
				// Array of min lengths
				$this->set_minLengths(array(1, 1, 1, 0, 1, 1, 1, 1, 1, 0, 0));
				// Array of (1, 0) to check for duplicates already in the table
				$this->set_check4duplicates(array(0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0));
				// Array of (1, 0) whether or not to insert into DB
				$this->set_insert(array(1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 0));
				// Array of (1, 0) whether or not this element will be Posted
				$this->set_post(array(1, 1, 1, 0, 1, 1, 1, 1, 1, 1, 0));
				// Array of the various data types used.  This is used in checking input format and initial dataformating																
				// Valid choices are "text", "fulltext", "int", "boolean", "username", "password", "email"
				// if blank no checks or formatting applied
				$this->set_dataType(array("int", "int", "text", "date", "text", "boolean", "int", "boolean", "int", "fulltext", ""));
			// Message displayed after successfully interacting with DB
			$this->set_successMessage("You have successfully edited a page.");
			// Name of the table to interact with
			$this->set_tableName("contents");
			// Name of the form
			$this->set_formName("editpage");
			// Action the form should take
			$this->set_formAction("index.php?catid={$this->get_category_id()}&contentid={$this->get_id()}&id={$this->grab_GET('id')}&submit=1");
			// Action of the SQL (insert || update)
			$this->set_sqlAction("update");
			// If you are UPDATING a DB which line are you updating?
			$this->set_sqlWhere("id = {$this->grab_GET('id')}");
		}
		
		private function retriveDVfromDB()
		{	$contentID = $this->grab_GET("id");
			$query = "SELECT owner_id, category_id, title, date_lastedit, type, visible, level, menuvisible, menuposition, content FROM contents WHERE id = {$contentID};";
			$result = $this->database->query($query);
			return mysql_fetch_array($result);
		}
		
		protected function generateQuery($array)
		{	// Set date of last edit
			$array[3] = date("c");
			$query = parent::generateQuery($array);
			return $query;
		}
	}

/***************
DISPLAY DB PAGE
***************/
	class displayDBpage extends page
	{	// Designed to be extendable; create your own generateQuery, generateHeadersArray, generateEditLink
	
		function generateHTML()
		{	$HTML = "<h1>" . $this->get_title() . "</h1>";
			$query = $this->generateQuery();
			$result = $this->database->query($query);
			$HTML .= $this->generateTable($result);
			return $HTML;
		}
		
		protected function generateQuery()
		{	$query = "";
			return $query;
		}
		
		private function generateTable($result)
		{	$HTML = "<table>" . "\n";						// Generate opening table tags and header row
			$HTML .= $this->generateHeaders();
			while ($row = mysql_fetch_array($result))		// Display users loop
			{	$HTML .= $this->generateRow($row);
			}
			$HTML .= "</table>" . "\n";
			return $HTML;
		}
		
		private function generateHeaders()
		{	$HTML = "<tr>\n<th>";
			$HTML .= implode("</th>\n<th>", $this->generateHeaderArray());
			$HTML .= "</th>\n</tr>" . "\n";
			return $HTML;
		}
		
		protected function generateHeaderArray()
		{	return array("");
		}

		private function generateRow($row)
		{	$HTML = "<tr>" . "\n";
			$count = 0;
			// divide by two because mysqlfetch adds each value to the array twice, once by #, and once by association
			while ((count($row)/2) > $count)
			{	$HTML .= "<td>{$row[$count]}</td>" . "\n";
				$count++;
			}
			$HTML .= $this->generateEditLink($row);
			$HTML .= "</tr>" . "\n";
			return $HTML;
		}
		
		protected function generateEditLink($row)
		{	$HTML = "" . "\n";
			return $HTML;
		}
	}
	
/***************
display users
***************/
	class displayusers extends displayDBpage
	{	protected function generateQuery()
		{	$query = "SELECT id, username, firstname, lastname, level FROM users;";
			return $query;
		}
		
		protected function generateHeaderArray()
		{	return array("ID", "Username", "First Name", "Last Name", "Level", "Edit Link");
		}
		
		protected function generateEditLink($row)
		{	// NEED A CONSTANT HERE!!!!!!
			$HTML = "<td><a href=index.php?catid={$this->get_category_id()}&contentid=19&id={$row['id']}>Edit</a></td>" . "\n";
			return $HTML;
		}
	}
	
/***************
display pages
***************/
	class displaypages extends displayDBpage
	{	protected function generateQuery()
		{	$query = "SELECT id, title, type, visible, level FROM contents;";
			return $query;
		}
		
		protected function generateHeaderArray()
		{	return array("ID", "Title", "Type", "Visible?", "Level", "Edit Link");
		}
		
		protected function generateEditLink($row)
		{	// NEED A CONSTANT HERE!!!!!!
			$HTML = "<td><a href=index.php?catid={$this->get_category_id()}&contentid=24&id={$row['id']}>Edit</a></td>" . "\n";
			return $HTML;
		}
	}

/***************
display categories
***************/
	class displaycategories extends displayDBpage
	{	protected function generateQuery()
		{	$query = "SELECT id, title, position, menu_owner, default_page_id, visible, level FROM categories;";
			return $query;
		}
		
		protected function generateHeaderArray()
		{	return array("ID", "Title", "Position", "Menu Owner", "Default Page ID", "Visible?", "Level", "Edit Link");
		}
		
		protected function generateEditLink($row)
		{	// NEED A CONSTANT HERE!!!!!!
			$HTML = "<td><a href=index.php?catid={$this->get_category_id()}&contentid=22&id={$row['id']}>Edit</a></td>" . "\n";
			return $HTML;
		}
	}
?> 