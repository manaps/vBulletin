<?php
/*======================================================================*\
|| #################################################################### ||
|| # vBulletin 4.2.5
|| # ---------------------------------------------------------------- # ||
|| # All PHP code in this file is �2000-2019 vBulletin Solutions Inc. # ||
|| # This file may not be redistributed in whole or significant part. # ||
|| # ---------------- VBULLETIN IS NOT FREE SOFTWARE ---------------- # ||
|| #        www.vbulletin.com | www.vbulletin.com/license.html        # ||
|| #################################################################### ||
\*======================================================================*/

/*-------------------------------------------------------*\
| ****** NOTE REGARDING THE VARIABLES IN THIS FILE ****** |
+---------------------------------------------------------+
| If you get any errors while attempting to connect to    |
| MySQL, you will need to email your webhost because we   |
| cannot tell you the correct values for the variables    |
| in this file.                                           |
\*-------------------------------------------------------*/

$helpers = new \vBulletin\Helpers();

/* #### DATABASE TYPE  ####
This is the type of the database server on which your vBulletin database will be located.
The only valid option atm is mysqli, for slave support add _slave i.e. mysqli_slave.  */
$config['Database']['dbtype'] = $helpers->env('DB_TYPE', 'mysqli');

/* #### DATABASE NAME  ####
This is the name of the database where your vBulletin will be located.
This must be created by your webhost. */
$config['Database']['dbname'] = $helpers->env('DB_NAME', 'forum');

/* #### TABLE PREFIX  ####
Prefix that your vBulletin tables have in the database. */
$config['Database']['tableprefix'] = $helpers->env('DB_TABLE_PREFIX', '');

/* #### MASTER DATABASE SERVER NAME AND PORT  ####
This is the hostname or IP address and port of the database server.
If you are unsure of what to put here, leave the default values.
Note: If you are using IIS 7+ and MySQL is on the same machine, you
need to use 127.0.0.1 instead of localhost. */
$config['MasterServer']['servername'] = $helpers->env('DB_MASTER_SERVER_NAME', 'localhost');
$config['MasterServer']['port'] = $helpers->env('DB_MASTER_SERVER_PORT', 3306);

/* #### MASTER DATABASE USERNAME & PASSWORD  ####
This is the username and password you use to access MySQL.
These must be obtained through your webhost. */
$config['MasterServer']['username'] = $helpers->env('DB_MASTER_SERVER_USER', 'root');
$config['MasterServer']['password'] = $helpers->env('DB_MASTER_SERVER_PASS', 'xxxx');

/* #### MASTER DATABASE PERSISTENT CONNECTIONS  ####
This option allows you to turn persistent connections to MySQL on or off.
The difference in performance is negligible for all but the largest boards.
If you are unsure what this should be, leave it off. (0 = off; 1 = on). */
$config['MasterServer']['usepconnect'] = $helpers->env('DB_MASTER_SERVER_PERSISTENT', 0);

/* #### SLAVE DATABASE CONFIGURATION  ####
If you have multiple database backends, this is the information for your slave
server. If you are not 100% sure you need to fill in this information,
do not change any of the values here. */
$config['SlaveServer']['servername'] = $helpers->env('DB_SLAVE_SERVER_NAME', '');
$config['SlaveServer']['port'] = $helpers->env('DB_SLAVE_SERVER_PORT', 3306);
$config['SlaveServer']['username'] = $helpers->env('DB_SLAVE_SERVER_USER', '');
$config['SlaveServer']['password'] = $helpers->env('DB_SLAVE_SERVER_PASS', '');
$config['SlaveServer']['usepconnect'] = $helpers->env('DB_SLAVE_SERVER_PERSISTENT', 0);

/* #### MySQLI OPTIONS #### 
When using MySQL 4.1+, MySQLi should be used to connect to the database.
If you need to set the default connection charset because your database
is using a charset other than latin1, you can set the charset here.
If you don't set the charset to be the same as your database, you
may receive collation errors.  Ignore this setting unless you
are sure you need to use it. */
//$config['Mysqli']['charset'] = 'utf8';
/* Optionally, PHP can be instructed to set connection parameters by reading from the
file named in 'ini_file'. Please use a full path to the file.
Example: $config['Mysqli']['ini_file'] = 'c:\program files\MySQL\MySQL Server 4.1\my.ini'; */
$config['Mysqli']['ini_file'] = '';

/* #### FORCE SQL MODE #### 
// Allows the forcing of a MySQL mode.
You only need to modify this value if vBulletin recommends it. */
//$config['Database']['set_sql_mode'] = '';

/* #### TECHNICAL EMAIL ADDRESS  ####
If any database errors occur, they will be emailed to the address specified here.
Leave this blank to not send any emails when there is a database error. */
$config['Database']['technicalemail'] = $helpers->env('TECHNICAL_EMAIL', '');

/* #### PATH TO ADMIN & MODERATOR CONTROL PANELS  ####
This setting allows you to change the name of the folders that the admin and
moderator control panels reside in. You may wish to do this for security purposes.
Please note that if you change the name of the directory here, you will still need
to manually change the name of the directory on the server. */
$config['Misc']['admincpdir'] = $helpers->env('ADMINCP_DIR', 'admincp');
$config['Misc']['modcpdir'] = $helpers->env('MODCP_DIR', 'modcp');
/* Prefix that all vBulletin cookies will have
Keep this short and only use numbers and letters, i.e. 1-9 and a-Z */
$config['Misc']['cookieprefix'] = $helpers->env('COOKIE_PREFIX', 'vb');

/* #### FULL PATH TO FORUMS DIRECTORY  ####
On a few systems it may be necessary to input the full path to your forums directory
for vBulletin to function normally. You can ignore this setting unless vBulletin
tells you to fill this in. Do not include a trailing slash!
Example Unix:
  $config['Misc']['forumpath'] = '/home/users/public_html/forums';
Example Win32:
  $config['Misc']['forumpath'] = 'c:\program files\apache group\apache\htdocs\vb3';  */
$config['Misc']['forumpath'] = $helpers->env('FORUM_PATH', '');

/* #### USERS WITH ADMIN LOG VIEWING PERMISSIONS  ####
The users specified here will be allowed to view the admin log in the control panel.
Users must be specified by *ID number* here. To obtain a user's ID number,
view their profile via the control panel. If this is a new installation, leave
the first user created will have a user ID of 1. Seperate each userid with a comma. */
$config['SpecialUsers']['canviewadminlog'] = $helpers->env('ADMIN_LOG_VIEW_USERS', '1');

/* #### USERS WITH ADMIN LOG PRUNING PERMISSIONS  ####
The users specified here will be allowed to remove ("prune") entries from the admin
log. See the above entry for more information on the format. */
$config['SpecialUsers']['canpruneadminlog'] = $helpers->env('ADMIN_LOG_PRUNE_USERS', '1');

/* #### USERS WITH QUERY RUNNING PERMISSIONS  ####
The users specified here will be allowed to run queries from the control panel.
See the above entries for more information on the format.
Please note that the ability to run queries is quite powerful. You may wish
to remove all user IDs from this list for security reasons. */
$config['SpecialUsers']['canrunqueries'] = $helpers->env('RUN_QUERIES_USERS', '');

/* #### UNDELETABLE / UNALTERABLE USERS  ####
The users specified here will not be deletable or alterable from the control panel by any users.
To specify more than one user, separate userids with commas. */
$config['SpecialUsers']['undeletableusers'] = $helpers->env('UNDELETABLE_USERS', '');

/* #### SUPER ADMINISTRATORS ******
The users specified below will have permission to access the administrator permissions
page, which controls the permissions of other administrators. */
$config['SpecialUsers']['superadministrators'] = $helpers->env('SUPER_ADMIN_USERS', '1');

/* #### DATASTORE CACHE CONFIGURATION  ####
Here you can configure different methods for caching datastore items.
vB_Datastore_Filecache - to use includes/datastore/datastore_cache.php
vB_Datastore_APCu - to use APCu (which replaces APC)
vB_Datastore_XCache - to use XCache (not available for PHP 7+)
vB_Datastore_Memcache - to use one or more Memcache servers, more configuration below.
vB_Datastore_Redis - to use one or more Redis servers, more configuration options below. */
// $config['Datastore']['class'] = 'vB_Datastore_Filecache';

/* #### DATASTORE PREFIX  ####
If you are using a PHP Caching system (APCu, XCache, Memcache) with more
than one set of forums installed on your host, you *may* need to use a prefix
so that they do not try to use the same variable within the cache.
This works in a similar manner to the database table prefix. */
// $config['Datastore']['prefix'] = '';

/* #### MEMCACHE SETTINGS #### */
/*$config['Misc']['memcacheServers'] = array(
	array(
		'server' => '127.0.0.1',
		'port' => 11211
	),
); */
//$config['Misc']['memcacheRetry'] = 15; // Retry time in seconds.
//$config['Misc']['memcacheTimeout'] = 1; // Connect timeout in seconds.
//$config['Misc']['memcachePersistent'] = true; // Persistent connections.

/* #### REDIS SETTINGS #### */
/*$config['Misc']['redisServers'] = array(
	array(
		'addr' => '127.0.0.1',
		'port' => 6379
	),
); */
//$config['Misc']['redisRetry'] = 100; // Retry time in milliseconds.
//$config['Misc']['redisTimeout'] = 3; // Connect timeout in seconds.
//$config['Misc']['redisMaxDelay'] = 10; // Slave out of sync, timeout in seconds.

/* #### IMAGE PROCESSING #### 
Images that exceed either dimension below will not be resized by vBulletin. If you need to resize larger images, alter these settings. */
$config['Misc']['maxwidth'] = $helpers->env('IMAGE_PROCESSING_MAX_WIDTH', 2592);
$config['Misc']['maxheight'] = $helpers->env('IMAGE_PROCESSING_MAX_HEIGHT', 1944);

/* #### ALLOWED PORTS FOR UPLOADING #### 
Other than ports 80 and 443, upload requests through any other ports
will be blocked, unless specified in the array.
Comma separated array of integers, Ex. array(8080, 3128); */
// $config['Misc']['uploadallowedports'] = array();

/* #### CUSTOM FILE LOAD #### 
Define a custom functions file to be imported.
e.g. 'custom' will load ../includes/functions_custom.php' */
// $config['Misc']['Functions'] = 'custom';

/* #### REVERSE PROXY IP ####
If your use a system where the main IP address passed to vBulletin is the address of a proxy server
and the actual 'real' ip address is passed in another http header then you enter the details here 
Enter your known proxy servers here. You can list multiple trusted IPs separated by a comma.
You can also use the * wildcard (at the end of a definition only) or use the keyword 'all' to represent any ip address.*/
//$config['Misc']['proxyiplist'] = '127.0.0.1, 192.168.*, all';
/* If the real IP is passed in a http header variable you can set the name here; */
//$config['Misc']['proxyipheader'] = 'HTTP_X_FORWARDED_FOR';

/* #### Force VB ALT_IP ####
Setting this will force vBulletin to use any internally detected alternative ip as the main ip address.
The core checks three http headers (in order) HTTP_CLIENT_IP, HTTP_CF_CONNECTING_IP, HTTP_X_FORWARDED_FOR.
It will use the first one it finds as IPADDRESS, and the original REMOTE_ADDR as ALT_IP.
Do not use this option unless you understand what you are doing, and the possible consequences. */
//define('USE_VB_ALT_IP', true);

/* #### FORCE URL SCHEME ####
By default, vBulletin will try and work out what URL scheme to use (http or https) by checking the incoming page request.
However, in some circumstances, this can fail. You can force the scheme to 'http://' or 'https://' using the below setting. */
//$config['Misc']['vb_url_scheme'] = 'https://';

/* #### FORCE URL PORT ####
It may sometimes be necessary to force the port as well. Do not use this setting unless you know what you are doing. */
//$config['Misc']['vb_url_port'] = '443';

// DEBUG MODE
if ($helpers->env('DEBUG_MODE') === true) {
    if ($helpers->env('DEBUG_ONLY_IN_ACP') === true) {
        if (defined('IN_CONTROL_PANEL') && IN_CONTROL_PANEL === true) {
            $config['Misc']['debug'] = true;
        }
    } else {
        $config['Misc']['debug'] = true;
    }
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: 17:39, Sat Aug 3rd 2019 : $Revision: 92688 $
|| # $Date: 2017-01-31 07:01:38 -0800 (Tue, 31 Jan 2017) $
|| ####################################################################
\*======================================================================*/
