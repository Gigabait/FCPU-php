<?php
/*
 * FoxCaves PHP Uploader © 2012 David Todd
 * Public download can be found at: http://unps-gama.tk/FoxCaves-PHP-Uploader.php.src and also GitHub: https://github.com/alopexc0de/FCPU-php
 * 
 * FoxCav.es is property of Doridian Draconia and all credit goes to him for designing the site and for helping me debug this script
 * 
 * This PHP script is released under a Creative Commons Attribution-NonCommercial-ShareAlike 3.0 Unported license
 * http://creativecommons.org/licenses/by-nc-sa/3.0/
 */
empty($_SERVER['SHELL']) && die('Run again from the command line'); //only allow this to be run from the command line

//initalize all variables
$filepath = ''; //Full absolute path to the file (relative paths may work) put this here if you just wanna upload from pictures Eg. /home/user/Pictures or C:\Users\user\Pictures or whatever the hell it is on a mac
$filename = ''; //full name of the file, including extension (you can name your file here, but you'd have to edit the file every time you uploaded)
$line = ''; //Used to hold user input for a short amount of time
$thefile = ''; //This is the pure binary data from the file
$old = ''; //Used when reading file into thefile
$link =''; //Used to display the full link at the end
$result = "\r\n"; //print newline when reading the response from server as a spacing buffer at the beginning
$user = ''; //username for foxcaves put it here if you want to skip logging in all the time
$pass = ''; //password for foxcaves put it here if you want to skip logging in all the time (this is plaintext so store at your own risk)

echo "Press ^C (<CTRL>+<C>) anytime during this script is operation if you feel you have made a mistake\r\n";
//if the filepath, filename, username, or password are empty, prompt for values.

//Take input from user
if ($user == ''){
	echo "Username: ";
	$user = fopen ("php://stdin","r");
	$line = fgets($user);
	if(trim($line) == ''){
		die("Please give your username\r\n");
	}
	$user = trim($line);
	echo "Your username is: ".$user."\n";
}
if ($pass == ''){	
	echo "Password: ";
	$pass = fopen ("php://stdin","r");
	$line = fgets($pass);
	if(trim($line) == ''){
		die("Please give your password\r\n");
	}
	$pass = trim($line);
	echo "Your password is: ".$pass."\n";
}
if ($filepath == ''){
	echo "Please type the absolute path to the file with the trailing slash: ";
	$filepath = fopen ("php://stdin","r");
	$line = fgets($filepath);
	if(trim($line) == ''){
		die("Please give the absolute path to the file with the trailing slash\r\n");
	}
	$filepath = trim($line);
	echo "The path to your file is: ".$filepath."\n";
}
if ($filename == ''){
	echo "Please type the full filename including the extension: ";
	$filename = fopen ("php://stdin","r");
	$line = fgets($filename);
	if(trim($line) == ''){
		die("Please give the full filename of what you wish to upload with the extension\r\n");
	}
	$filename = trim($line);
	echo "Your filename is: ".$filename."\n";
}
echo "This is your file you're uploading".$filepath.$filename."\nDo you accept it? (yes/no): ";
$line = fopen ("php://stdin","r");
	$line = fgets($line);
	if(trim($line) != 'yes'){
		if(trim($line) != 'no'){
			die("It appears your choice was invalid. Please try again");
		}
		die("It appears you didn't want to upload that particular file. Please try again");
	}
//end input from user

//read the entire contents of the file into a variable (I have a feeling this is going to eat ram for large files, but there's a 200MB limit for most users)
$old = fopen($filepath.$filename,  "r"); 
while(!feof($old)) 
{ 
    $thefile  .=  fgets($old);  
} 

//build the header to send to the server
$header = "PUT /create?".$filename ." HTTP/1.0\r\n"; //create script on server using HTTP/1.0
$header .= "X-Foxscreen-User: ". $user ."\r\n"; //username header
$header .= "X-Foxscreen-Password: ". $pass ."\r\n"; //password header
$header .= "Host: foxcav.es\r\n"; //host
$header .= "Content-Length: " . strlen($thefile)."\r\n\r\n"; //length in bytes of the file

echo "\r\n\r\nThis is what the header should look like:\r\n".$header; //show the constructed header for debugging if something goes wrong

//open connection to the server here
$fp = fsockopen("ssl://foxcav.es", 443, $errno, $errstr, 60); //hardcoded to foxcaves because that's what this script is for
    if(!$fp)
    {
		return "unable to connect"; //general error - should only happen if server is offline or something
    }
    else
    {
		fputs ($fp, $header.$thefile); //push the connection and the header to the server, then append the raw data of the file after the header
		while (!feof($fp)) // while the connection to the server is open...
		{	
			$result .= fread ($fp, 1024); //read server reply into result
		}
		fclose($fp); //close connection to server
    }
echo $result."\r\n"; //print reply from the server
$link = explode("\n", $result); //seperate the lines of the server reply
echo "\r\nYour final uploaded link is:\r\nhttp://foxcav.es/".$link[12]."\r\n\r\n"; 
//newline then show the base (http://foxcav.es/) append the 12th line of the server reply (which will always be the link unless the upload fails in some way, but it should be error proof enough) 
//and append two more new lines for nicer formatting of output
/*
 * SUCESSFUL UPLOAD EXAMPLE:
 * 
 * HTTP/1.1 200 OK
 * Server: nginx/1.3.7
 * Date: Fri, 12 Oct 2012 00:23:33 GMT
 * Content-Type: text/plain
 * Content-Length: 15
 * Connection: close
 * X-XSS-Protection: 0
 * X-Frame-Options: sameorigin
 * X-Powered-By: Lua 5.1, LuaJIT 2.0.0
 * Strict-Transport-Security: max-age=315360000; includeSubdomains
 *
 * view/5RliUtHoE5
 *
 * Your final uploaded link is:
 * http://foxcav.es/view/5RliUtHoE5
 *
 */
?>
<a rel="license" href="http://creativecommons.org/licenses/by-nc-sa/3.0/deed.en_US"><img alt="Creative Commons License" style="border-width:0" src="http://i.creativecommons.org/l/by-nc-sa/3.0/88x31.png" /></a><br /><span xmlns:dct="http://purl.org/dc/terms/" property="dct:title">FoxCaves PHP Uploader</span> by <a xmlns:cc="http://creativecommons.org/ns#" href="http://unps-gama.tk" property="cc:attributionName" rel="cc:attributionURL">David Todd</a> is licensed under a <a rel="license" href="http://creativecommons.org/licenses/by-nc-sa/3.0/deed.en_US">Creative Commons Attribution-NonCommercial-ShareAlike 3.0 Unported License</a>.
