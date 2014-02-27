<?php
	require_once("lib/classes.php");
	$contentEngine = new layout;
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title><?php echo "philmowatt.com"; ?></title>
	<link href="css/franky.css" rel="stylesheet" type="text/css" />
</head>
<body>
    <div id="container">
        <div id="header">
            <div id="logo">
           		logo
            </div>
            <div id="search">
                <p>Search for keyword or phrase: 
                <label>
                    <input type="text" name="textfield" id="textfield" />
                    <input type="submit" name="button" id="button" value="Submit" />
                </label></p>
            </div>
            	<div id="titlebar">
                	<p><?php echo "PHILMOWATT.COM"; ?></p>
                </div>
        </div>
        <div id="banner">
        	<div id="usernav">
					<?php
                        $contentEngine->generateUserMenu();
                    ?>
            </div>
        </div>
        <div id="firstnav">
			<?php
				$contentEngine->generateMainMenu();
            ?>
        </div>
        <div id="contentcontainer">
            <div id="secondnav">
                <?php
					// Side Menu
					$contentEngine->generateSubMenu();
                ?>
            </div>
            <div id="content">
				<?php
					// Content Generation
					$contentEngine->generateContent();
                ?>
            </div>
        </div>
<div id="footer">
        	<p>This page was lasted editted on April 21st, 2009 by pmowatt.<br />
        	Website design, layout, and maintenence preformed by Phil Mowatt.</p>
        </div>
    </div>
</body>
<?php
	// Close Database Connection
?>
</html>
