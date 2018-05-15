<?php
/*----------------------------
-------- ++ simPHP ++ --------
A simple PHP hit and visitor counter
Version: 2.0.1 - Refined by Zillinium
Description:
   this counts both regular and unique views on multiple
   webpages and returns the Loads count, Visitors count and 
   the current users IP Address. 
   The stats can be displayed on any PHP-enabled
   webpage or any HTML page using a GET method. 
   
   INSTALLING INFO 
   - Copy this file to desired path on website
   - Make a counter.txt file in the same path as this
   file, it will be used for the log.
   
   - You can access it from <script> with GET 'show=this'
   - this way the script can be included in .html files also.
   
   EXAMPLE USAGE CODE
    <script type="text/javascript" src="counter.php?show=this"></script>
    
    
   OPTIONAL .HTACCESS .PHP REMOVAL
    Add to .htaccess file 
    
# PHP REMOVAL - HUP.php becomes HUP 
# EXAMPLE : HUP?url=http... over HUP.php?url=
#
RewriteRule ^([^.?]+)$ %{REQUEST_URI}.php [L]
#
# RETURN 404 IF REQUEST IS .PHP
#
RewriteCond %{THE_REQUEST} "^[^ ]* .*?\.php[? ].*$"
RewriteRule .* - [L,R=404]
#

IF YOU USE HTACCESS, This method sometimes has unexpected results when used with other .htaccess RewriteRule's
also the new example usage would become,

EXAMPLE USAGE CODE
    <script type="text/javascript" src="counter?show=this"></script>
    
   For more .htaccess tweaks https://pastebin.com/6FxeZwna
    
    
    
    
   You MUST have read/write permissions on files 
   
Script refined for html support by : 
Zillinium : http://facebook.com/Zillinium
 
Original PHP Script (simPHP) by Ajay: me@ajay.ga
----------------------------*/
/*----------CONFIG----------*/
function realIp()
{
    if (!empty($_SERVER['HTTP_CLIENT_IP']))   //check ip from share internet
    {
      $ip=$_SERVER['HTTP_CLIENT_IP'];
    }
    elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))   //to check ip is pass from proxy
    {
      $ip=$_SERVER['HTTP_X_FORWARDED_FOR'];
    }
    else
    {
      $ip=$_SERVER['REMOTE_ADDR'];
    }
    return $ip;
}
$ip = realIp();

// Relative URL of text file that holds hit info:

$lf_name = "counter.txt";

// Save new log file each month

// 0 = No

// 1 = Yes

$monthly = 0;

// Path to store old files:

// Default for June, 2012:

// oldfiles/6-12.txt

$monthly_path = "oldfiles";

// Count unique hits or total hits:

// 0 = Total hits

// 1 = Unique hits

// 2 = Both unique and total

$type = 2;

// Text to display

// before total hits

$beforeTotalText = "Load : ";

// Before unique hits

$beforeUniqueText = "Visitor : ";

// Before current user IP

$beforeIP = "You are : ";

// Display hits on this page:

// 0 = No

// 1 = Yes

$display = 1;

// Only change this if you are recording both values

// Separator for unique and total hits display - use HTML tags! (line break is default)

$separator = "<br \>";

// Default would output:

// Visits: 10

// Unique Visits: 10

/*--------------------------*/

/*--------BEGIN CODE--------*/

$log_file = dirname(__FILE__) . '/' . $lf_name;

// Check for "?display=true" in URL

if ($_GET['display'] == "true") {

	// Show include() info

	die("<pre>&#60;? include(\"" . dirname(__FILE__) . '/' . basename(__FILE__) . "\"); ?&#62;</pre>");

} else {

	// Get visitor IP

	$uIP = realIp();

	// Check for "hits.txt" file

	if (file_exists($log_file)) {

		// Get contents of log file

		$log = file_get_contents($log_file);

		if ($monthly) {

			// Check if today is first day of month

			// Also check if prev month log file exists already

			$prev_name = $monthly_path . '/' . date("n-Y", strtotime("-1 month")) . '.txt';

			if (date('j') == 1 && !file_exists($prev_name)) {

				// If it is first day of month,

				// move previous log file to subdir and create new file

				// Ensure that monthly dir exists

				if (!file_exists($monthly_path)) {

					mkdir($monthly_path);

				}

				copy($log_file, $prev_name);

				// Write new data based on config

				if ($type == 0) {

					// Total hits

					$toWrite = "1";

					$info = $beforeTotalText . "1";

				} else if ($type == 1) {

					// Unique hits

					$toWrite = "1;" . $uIP . ",";

					$info = $beforeUniqueText . "1";

				} else if ($type == 2) {

					// Unique and total

					$toWrite = "1;1;" . $uIP . ",";

					$info = $beforeTotalText . "1" . $separator . $beforeUniqueText . "1" . $separator . $beforeIP . $ip;

				}

				write_logfile($toWrite, $info);

			} else {

				// Still same month as before, so just increment counters

				// What to do depends on type from config

				if ($type == 0) {

					// Total hits

					// Create info to write to log file and info to show

					$toWrite = intval($log) + 1;

					$info = $beforeTotalText . $toWrite;

				} else if ($type == 1) {

					// Separate log file into hits and IPs

					$hits = reset(explode(";", $log));

					$IPs = end(explode(";", $log));

					$IPArray = explode(",", $IPs);

					// Check for visitor IP in list of IPs

					if (array_search($uIP, $IPArray, true) === false) {

						// IP doesnt' exist so increase hits and include IP

						$hits = intval($hits) + 1;

						$toWrite = $hits . ";" . $IPs . $uIP . ",";

					} else {

						// If IP exists don't change anything

						$toWrite = $log;

					}

					// Info to show

					$info = $beforeUniqueText . $hits;

				} else if ($type == 2) {

					// Both total hits and unique hits

					// Separate log file into regular hits, unique hits, and IPs

					$pieces = explode(";", $log);

					$totalHits = $pieces[0];

					$uniqueHits = $pieces[1];

					$IPs = $pieces[2];

					$IPArray = explode(",", $IPs);

					// Always increase regular hits, regardless of IP

					$totalHits = intval($totalHits) + 1;

					// Search for visitor IP in list of IPs

					if (array_search($uIP, $IPArray, true) === false) {

						// IP doesn't exist so increase unique hits and append IP

						$uniqueHits = intval($uniqueHits) + 1;

						$toWrite = $totalHits . ";" . $uniqueHits . ";" . $IPs . $uIP . ",";

					} else {

						// If IP already exists just keep unique hits unchanged

						$toWrite = $totalHits . ";" . $uniqueHits . ";" . $IPs;

					}

					// Info to show

					$info = $beforeTotalText . $totalHits . $separator . $beforeUniqueText . $uniqueHits . $separator . $beforeIP . $ip;

				}

				write_logfile($toWrite, $info);

			}

		} else {

			// What to do depends on type from config

			if ($type == 0) {

				// Total hits

				// Create info to write to log file and info to show

				$toWrite = intval($log) + 1;

				$info = $beforeTotalText . $toWrite;

			} else if ($type == 1) {

				// Separate log file into hits and IPs

				$hits = reset(explode(";", $log));

				$IPs = end(explode(";", $log));

				$IPArray = explode(",", $IPs);

				// Check for visitor IP in list of IPs

				if (array_search($uIP, $IPArray, true) === false) {

					// IP doesnt' exist so increase hits and include IP

					$hits = intval($hits) + 1;

					$toWrite = $hits . ";" . $IPs . $uIP . ",";

				} else {

					// If IP exists don't change anything

					$toWrite = $log;

				}

				// Info to show

				$info = $beforeUniqueText . $hits;

			} else if ($type == 2) {

				// Both total hits and unique hits

				// Separate log file into regular hits, unique hits, and IPs

				$pieces = explode(";", $log);

				$totalHits = $pieces[0];

				$uniqueHits = $pieces[1];

				$IPs = $pieces[2];

				$IPArray = explode(",", $IPs);

				// Always increase regular hits, regardless of IP

				$totalHits = intval($totalHits) + 1;

				// Search for visitor IP in list of IPs

				if (array_search($uIP, $IPArray, true) === false) {

					// IP doesn't exist so increase unique hits and append IP

					$uniqueHits = intval($uniqueHits) + 1;

					$toWrite = $totalHits . ";" . $uniqueHits . ";" . $IPs . $uIP . ",";

				} else {

					// If IP already exists just keep unique hits unchanged

					$toWrite = $totalHits . ";" . $uniqueHits . ";" . $IPs;

				}

				// Info to show

				$info = $beforeTotalText . $totalHits . $separator . $beforeUniqueText . $uniqueHits . $separator . $beforeIP . $ip;

			}

			write_logfile($toWrite, $info);

		}

	} else {

		// If "hits.txt" doesn't exist, create it

		$fp = fopen($log_file, "w");

		fclose($fp);

		// Write file according to config above

		if ($type == 0) {

			$toWrite = "1";

			$info = $beforeTotxalText . "1";

		} else if ($type == 1) {

			$toWrite = "1;" . $uIP . ",";

			$info = $beforeUniqueText . "1";

		} else if ($type == 2) {

			$toWrite = "1;1;" . $uIP . ",";

			$info = $beforeTotalText . "1" . $separator . $beforeUniqueText . "1" . $separator . $beforeIP . $ip;

		}

		write_logfile($toWrite, $info);

	}

}

/**

* Writes given data to the logfile and echoes data if the option

* 	 says so in config

* Requires: A string of data to write to the file and a string

* of data to print

*/

function write_logfile($data, $output) {

	global $log_file;

	// Put $toWrite in log file

	file_put_contents($log_file, $data);

	// Display info if is set in config

	if ($display == 1) {

		echo $output;

	}

}
$outCode = '<div id="show">'. $info . '</div>';
if(isset($_GET['show']) && $_GET['show']=='this') $outCode = "document.write('$outCode');";
echo $outCode; 

?>
