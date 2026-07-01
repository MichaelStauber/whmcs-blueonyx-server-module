<?php

/**
 * BlueOnyx API
 *
 * BlueOnyx 5210R, 5211R, 5212R API v2 interface module for WHMCS
 *
 * @package   BlueOnyx base-api.mod
 * @author    Michael Stauber
 * @copyright Copyright (c) 2014-2025 Michael Stauber, SOLARSPEED.NET
 * @link      http://www.solarspeed.net
 * @license   http://devel.blueonyx.it/pub/BlueOnyx/licenses/SUN-modified-BSD-License.txt
 * @version   3.0
 *
 * @info      Creation of this module was sponsored by VIRTBIZ Internet Services: http://www.virtbiz.com
 *
 */

define('BLUEONYX_DEBUG', true);

function blueonyx_MetaData() {
    return [
        'DisplayName' => 'BlueOnyx APIv2',
        'APIVersion' => '2.0',
        'RequiresServer' => true,
        'DefaultNonSSLPort' => '9092',
        'DefaultSSLPort' => '9092',
        'TestConnection' => 'blueonyx_TestConnection',
        'Fields' => [
            'clientSecret' => [
                'FriendlyName' => 'Client-Secret',
                'Type' => 'password',
                'Size' => '80',
                'Description' => 'Client-Secret as defined on the BlueOnyx node.',
                'Default' => ''
            ]
        ]
    ];
}

function blueonyx_debug_log($functionName, $request, $response) {
    if (defined('BLUEONYX_DEBUG') && BLUEONYX_DEBUG) {
        logModuleCall(
            'blueonyx',
            $functionName,
            $request,
            $response,
            null,
            []
        );
        error_log("[BlueOnyx Debug][$functionName] Request: " . print_r($request, true));
        error_log("[BlueOnyx Debug][$functionName] Response: " . print_r($response, true));
    }
}

function blueonyx_ConfigOptions() {
    $configarray = array(
        // #1
        "WHM Package Name" => array( 
            "Type" => "text", 
            "Size" => "25",
        ),
        // #2
        "username" => array (
            "FriendlyName" => "UserName",
            "Type" => "text",
            "Size" => "15",
            "Description" => "",
            "Default" => "",
        ),
        // #3
        "password" => array (
            "FriendlyName" => "Password",
            "Type" => "password",
            "Size" => "15",
            "Description" => "",
            "Default" => "",
        ),
        // #4
        "disk" => array (
            "FriendlyName" => "Disk Space",
            "Type" => "text",
            "Description" => "MB",
            "Size" => "6",
            "Default" => "200",
        ),
        // #5
        "users" => array (
            "FriendlyName" => "Maximum Users",
            "Type" => "text",
            "size" => "4",
            "Description" => "",
            "Default" => "25",
        ),
        // #6
        "auto-dns" => array (
            "FriendlyName" => "Automatic DNS",
            "Type" => "yesno",
            "Description" => "Use automatic DNS",
        ),
        // #7
        "PHP" => array (
            "FriendlyName" => "PHP",
            "Type" => "dropdown",
            "Options" => "suPHP,FPM",
            "Description" => "",
            "Default" => "FPM",
        ),
        // #8
        "ssi" => array (
            "FriendlyName" => "SSI",
            "Type" => "yesno",
            "Description" => "Allow Server Side Includes",
        ),
        // #9
        "PHPVersion" => array (
            "FriendlyName" => "PHP Version",
            "Type" => "dropdown",
            "Options" => "PHPOS,PHP56,PHP70,PHP71,PHP72,PHP73,PHP74,PHP80,PHP81,PHP82,PHP83,PHP84,PHP85,PHP86,PHP90,PHP91,PHP92,PHP93",
            "Description" => "Only if PHP method 'suPHP' or 'FPM' is used and a matching PHP PKG is installed on the BlueOnyx server. Defaults to OS provided PHP.",
            "Default" => "PHPOS",
        ),
        // #10
        "cgi" => array (
            "FriendlyName" => "CGI",
            "Type" => "yesno",
            "Description" => "CGI/Perl enabled",
        ),
        // #11
        "MySQL" => array (
            "FriendlyName" => "MySQL Databases",
            "Type" => "dropdown",
            "Options" => "0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20",
            "Description" => "# of allowed MySQL/MariaDB databases",
            "Default" => "1",
        ),
        // #12    
        "ftp" => array (
            "FriendlyName" => "FTP for non-siteAdmin",
            "Type" => "yesno",
            "Description" => "Regular users can use FTP, too.",
        ),
        // #13
        "forwardemail" => array (
            "FriendlyName" => "Email Forwarding",
            "Type" => "yesno",
            "Description" => "Forward emails to siteAdmin to clients WHMCS email-address.",
        ),
        // #14
        "shell" => array (
            "FriendlyName" => "Shell Access",
            "Type" => "dropdown",
            "Options" => "None,Chrooted (SFTP SCP RSYNC),Chrooted (Shell SFTP SCP RSYNC),Full Shell Access",
            "Description" => "",
            "Default" => "None",
        ),
        // #15
        "subdomains" => array (
            "FriendlyName" => "Sub-Domains",
            "Type" => "yesno",
            "Description" => "Enable subdomains.",
        ),
        // #16
        "comments" => array (
            "FriendlyName" => "Notes",
            "Type" => "textarea",
            "Rows" => "3",
            "Cols" => "50",
            "Description" => "Enter notes here",
            "Default" => "",
        ),
        // #17
        "subdomainsMax" => array (
            "FriendlyName" => "# of subdomains",
            "Type" => "text",
            "Description" => "",
            "Size" => "3",
            "Default" => "1",
        ),
    );
    return $configarray;
}

function generateRandomString($length = 8) {
    $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $randomString;
}

function blueonyx_CreateAccount(array $params) {
    try {
        require_once __DIR__ . '/CceApiClient.php';

        $client = new \BlueOnyx\CceApiClient(
            $params['serverhostname'],
            $params['serveraccesshash']
        );

        $client->login($params['serverusername'], $params['serverpassword']);

        $fqdn = $params['domain'];
        $hostname = substr($fqdn, 0, strpos($fqdn, '.'));
        $domain = substr($fqdn, strpos($fqdn, '.') + 1);

        // Validate server IP
        if (empty($params['serverip']) || !filter_var($params['serverip'], FILTER_VALIDATE_IP)) {
            throw new \Exception("Server IP address is missing or invalid");
        }

        // Validate config options with defaults
        $diskMax = is_numeric($params['configoption4']) ? $params['configoption4'] : '200';
        $maxUsers = is_numeric($params['configoption5']) ? $params['configoption5'] : '25';
        $phpVersion = in_array($params['configoption9'], explode(',', 'PHPOS,PHP56,PHP70,PHP71,PHP72,PHP73,PHP74,PHP80,PHP81,PHP82,PHP83,PHP84,PHP85,PHP86,PHP90,PHP91,PHP92,PHP93')) ? $params['configoption9'] : 'PHPOS';
        $mysqlDbs = is_numeric($params['configoption11']) ? $params['configoption11'] : '1';
        $subdomainsMax = is_numeric($params['configoption17']) ? $params['configoption17'] : '1';

        // Step 1: Create the Vsite with main object fields
        $createData = [
            'hostname' => $hostname,
            'domain' => $domain,
            'fqdn' => $fqdn,
            'ipaddr' => $params['serverip'],
            'ipaddrIPv6' => '',
            'createdUser' => 'admin',
            'webAliases' => '',
            'webAliasRedirects' => '1',
            'emailDisabled' => '0',
            'mailAliases' => '',
            'mailCatchAll' => '',
            'volume' => '/home',
            'maxusers' => $maxUsers,
            'dns_auto' => ($params['configoption6'] == 'on') ? '1' : '0',
            'prefix' => '',
            'userPrefixEnabled' => '0',
            'userPrefixField' => '',
            'site_preview' => '0',
        ];

        blueonyx_debug_log('blueonyx_CreateAccount', 'create_data', $createData);

        // Create the Vsite
        $oids = $client->create("Vsite", $createData);
        if (empty($oids)) {
            throw new \Exception("Failed to create Vsite");
        }
        $oid = $oids[0];

        // Get Vsite group name
        $vsiteData = $client->get($oid);
        blueonyx_debug_log('blueonyx_CreateAccount', 'vsite_data', $vsiteData);
        if ($vsiteData === -1) {
            throw new \Exception("Failed to retrieve Vsite object with OID: $oid");
        }
        $groupName = $vsiteData['name'] ?? 'vsite';

        // Step 2: Set Vsite properties
        // Set webAliases and mailAliases if hostname is present
        if (!empty($createData['hostname'])) {
            $domain = $createData['domain'];
            $client->set($oid, '', [
                'webAliases' => "&{$domain}&",
                'mailAliases' => "&{$domain}&"
            ]);
        }

        $client->set($oid, "Disk", ["quota" => $diskMax]);
        $client->set($oid, "PHP", [
            "enabled" => "1",
            "fpm_enabled" => ($params['configoption7'] == 'FPM') ? '1' : '0',
            "suPHP_enabled" => ($params['configoption7'] == 'suPHP') ? '1' : '0',
            "version" => $phpVersion,
        ]);
        $client->set($oid, "SSI", ["enabled" => ($params['configoption8'] == 'on') ? '1' : '0']);
        $client->set($oid, "CGI", ["enabled" => ($params['configoption10'] == 'on') ? '1' : '0']);

        $shellValue = $params['configoption14'] ?: 'None';
        $shellEnabled = $shellValue === 'None' ? '0' : ($shellValue === 'Full Shell Access' ? '3' : '1');
        $client->set($oid, "Shell", ["enabled" => $shellEnabled]);

        $subdomainsEnabled = ($params['configoption15'] == 'on') ? '1' : '0';
        $client->set($oid, "subdomains", [
            "enabled" => $subdomainsEnabled,
            "max_subdomains" => $subdomainsMax,
            "vsite_enabled" => $subdomainsEnabled,
        ]);

        $client->set($oid, "FTPNONADMIN", ["enabled" => ($params['configoption12'] == 'on') ? '1' : '0']);

        // Set MYSQL_Vsite with retry logic
        $maxRetries = 3;
        $retryCount = 0;
        $mysqlSetSuccess = false;
        while ($retryCount < $maxRetries && !$mysqlSetSuccess) {
            try {
                $randomSuffix = generateRandomString(8);
                $mysqlUsername = "{$groupName}_{$randomSuffix}";
                $mysqlPassword = generateRandomString(12);
                $mysqlDbName = "{$groupName}_{$randomSuffix}_db";
                $currentTime = time();

                $client->set($oid, 'MYSQL_Vsite', [
                    'enabled' => ($mysqlDbs > 0) ? '1' : '0',
                    'username' => $mysqlUsername,
                    'pass' => $mysqlPassword,
                    'DB' => $mysqlDbName,
                    'host' => 'localhost',
                    'port' => '3306',
                    'maxDBs' => $mysqlDbs,
                    'hidden' => $currentTime,
                    'create' => $currentTime
                ]);
                $mysqlSetSuccess = true;
            } catch (Exception $e) {
                $retryCount++;
                if ($retryCount === $maxRetries) {
                    throw new \Exception("Failed to set MYSQL_Vsite after $maxRetries attempts: " . $e->getMessage());
                }
                sleep(1);
            }
        }

        // Step 3: Create the siteAdmin user AFTER Vsite is fully created

        // Use client-specified username if provided, otherwise fall back to <groupName>_admin
        $desiredUsername = !empty($params['username']) ? trim($params['username']) : null;
        $username = null;

        if ($desiredUsername) {
            // Check if the desired username is already taken
            $existingUsers = $client->findx("User", ["name" => $desiredUsername]);
            blueonyx_debug_log('blueonyx_CreateAccount', 'check_username', $existingUsers);
            if (empty($existingUsers)) {
                $username = $desiredUsername;
            } else {
                blueonyx_debug_log('blueonyx_CreateAccount', 'username_taken', "Desired username '$desiredUsername' is already taken, falling back to '$groupName'_admin");
            }
        }

        // If username is not set (either not provided or taken), fall back to <groupName>_admin
        if (!$username) {
            $username = "{$groupName}_admin";
        }

        // Use client-specified password if provided, otherwise auto-generate
        $password = !empty($params['password']) ? trim($params['password']) : generateRandomString(12);
        $fullName = $params['clientsdetails']['firstname'] . ' ' . $params['clientsdetails']['lastname'];

        $userData = [
            'volume' => '/home',
            'enabled' => '1',
            'emailDisabled' => '0',
            'description' => '',
            'fullName' => $fullName,
            'ftpDisabled' => '0',
            'localePreference' => 'browser',
            'stylePreference' => 'BlueOnyx',
            'site' => $groupName,
            'name' => $username,
            'sortName' => '',
            'password' => $password,
            'capLevels' => '&siteAdmin&'
        ];

        blueonyx_debug_log('blueonyx_CreateAccount', 'user_data', $userData);

        $userOids = $client->create("User", $userData);
        if (empty($userOids)) {
            throw new \Exception("Failed to create siteAdmin user");
        }
        $userOid = $userOids[0];

        // Set user disk quota (same as Vsite)
        $client->set($userOid, "Disk", ["quota" => $diskMax]);

        // Set user shell access (same as Vsite)
        $client->set($userOid, "Shell", ["enabled" => $shellEnabled]);

        // Set additional user settings
        $client->set($userOid, "SSH", ["GoogleAuthentication" => "0"]);
        $emailSettings = ["aliases" => "&webmaster&"];
        if ($params['configoption13'] == 'on') {
            $emailSettings["forwardEmail"] = $params['clientsdetails']['email'];
            $emailSettings["forwardEnable"] = '1';
            $emailSettings["forwardSave"] = '1';
        }
        $client->set($userOid, "Email", $emailSettings);

        // Step 4: Set Vsite 'prefered_siteAdmin' / web owner
        $client->set($oid, "PHP", ["prefered_siteAdmin" => $username]);
        $client->set($oid, "PHPVsite", ["force_update" => time()]);

        return 'success';
    } catch (Exception $e) {
        blueonyx_debug_log('blueonyx_CreateAccount', 'error', $e->getMessage());
        return 'Error: ' . $e->getMessage();
    }
}

function blueonyx_TerminateAccount(array $params) {
    try {
        require_once __DIR__ . '/CceApiClient.php';

        $client = new \BlueOnyx\CceApiClient(
            $params['serverhostname'],
            $params['serveraccesshash']
        );

        $client->login($params['serverusername'], $params['serverpassword']);

        // Step 1: Find the Vsite by fqdn
        $fqdn = $params['domain'];
        $oids = $client->findx("Vsite", ["fqdn" => $fqdn]);
        blueonyx_debug_log('blueonyx_TerminateAccount', 'findx', $oids);

        if (empty($oids)) {
            throw new \Exception("Vsite not found for domain: $fqdn");
        }
        $oid = $oids[0];

        // Step 2: Get the Vsite's group name (e.g., "site2")
        $vsiteData = $client->get($oid);
        if ($vsiteData === -1) {
            throw new \Exception("Failed to retrieve Vsite object with OID: $oid");
        }
        $groupName = $vsiteData['name'] ?? 'vsite';

        // Step 3: Find all users associated with the Vsite
        $userOids = $client->findx("User", ["site" => $groupName]);
        blueonyx_debug_log('blueonyx_TerminateAccount', 'user_oids', $userOids);

        // Step 4: Delete each user
        foreach ($userOids as $userOid) {
            $client->destroy($userOid);
            blueonyx_debug_log('blueonyx_TerminateAccount', 'destroy_user', "Destroyed user with OID: $userOid");
        }

        // Step 5: Delete the Vsite
        $client->destroy($oid);
        blueonyx_debug_log('blueonyx_TerminateAccount', 'destroy_vsite', "Destroyed Vsite with OID: $oid");

        return 'success';
    } catch (Exception $e) {
        blueonyx_debug_log('blueonyx_TerminateAccount', 'error', $e->getMessage());
        return 'Error: ' . $e->getMessage();
    }
}

function blueonyx_SuspendAccount(array $params) {
    try {
        require_once __DIR__ . '/CceApiClient.php';

        $client = new \BlueOnyx\CceApiClient(
            $params['serverhostname'],
            $params['serveraccesshash']
        );

        $client->login($params['serverusername'], $params['serverpassword']);

        $fqdn = $params['domain'];
        $oids = $client->findx("Vsite", ["fqdn" => $fqdn]);
        blueonyx_debug_log('blueonyx_SuspendAccount', 'findx', $oids);

        if (empty($oids)) {
            throw new \Exception("Vsite not found for domain: $fqdn");
        }

        $oid = $oids[0];
        $success = $client->set($oid, "", ["suspend" => "1"]);
        blueonyx_debug_log('blueonyx_SuspendAccount', 'set', $success);

        if ($success !== 1) {
            throw new \Exception("Failed to suspend Vsite");
        }

        return 'success';
    } catch (Exception $e) {
        blueonyx_debug_log('blueonyx_SuspendAccount', 'error', $e->getMessage());
        return 'Error: ' . $e->getMessage();
    }
}

function blueonyx_UnsuspendAccount(array $params) {
    try {
        require_once __DIR__ . '/CceApiClient.php';

        $client = new \BlueOnyx\CceApiClient(
            $params['serverhostname'],
            $params['serveraccesshash']
        );

        $client->login($params['serverusername'], $params['serverpassword']);

        $fqdn = $params['domain'];
        $oids = $client->findx("Vsite", ["fqdn" => $fqdn]);
        blueonyx_debug_log('blueonyx_UnsuspendAccount', 'findx', $oids);

        if (empty($oids)) {
            throw new \Exception("Vsite not found for domain: $fqdn");
        }

        $oid = $oids[0];
        $success = $client->set($oid, "", ["suspend" => "0"]);
        blueonyx_debug_log('blueonyx_UnsuspendAccount', 'set', $success);

        if ($success !== 1) {
            throw new \Exception("Failed to unsuspend Vsite");
        }

        return 'success';
    } catch (Exception $e) {
        blueonyx_debug_log('blueonyx_UnsuspendAccount', 'error', $e->getMessage());
        return 'Error: ' . $e->getMessage();
    }
}

function blueonyx_ChangePassword(array $params) {
    try {
        require_once __DIR__ . '/CceApiClient.php';

        $client = new \BlueOnyx\CceApiClient(
            $params['serverhostname'],
            $params['serveraccesshash']
        );

        $client->login($params['serverusername'], $params['serverpassword']);

        // Step 1: Find the Vsite by fqdn
        $fqdn = $params['domain'];
        $oids = $client->findx("Vsite", ["fqdn" => $fqdn]);
        blueonyx_debug_log('blueonyx_ChangePassword', 'findx_vsite', $oids);
        if (empty($oids)) {
            throw new \Exception("Vsite not found for domain: $fqdn");
        }
        $oid = $oids[0];

        // Step 2: Get the Vsite group name to find associated users
        $vsiteData = $client->get($oid);
        blueonyx_debug_log('blueonyx_ChangePassword', 'get_vsite', $vsiteData);
        if ($vsiteData === -1) {
            throw new \Exception("Failed to retrieve Vsite object with OID: $oid");
        }
        $groupName = $vsiteData['name'] ?? 'vsite';

        // Step 3: Find all users associated with the Vsite
        $userOids = $client->findx("User", ["site" => $groupName]);
        blueonyx_debug_log('blueonyx_ChangePassword', 'findx_users', $userOids);
        if (empty($userOids)) {
            throw new \Exception("No users found for Vsite: $fqdn");
        }

        // Step 4: Filter users to find site admins (capLevels contains 'siteAdmin' or '&siteAdmin&')
        $siteAdmins = [];
        foreach ($userOids as $userOid) {
            $userData = $client->get($userOid);
            blueonyx_debug_log('blueonyx_ChangePassword', 'get_user_' . $userOid, $userData);
            if (isset($userData['capLevels']) && (strpos($userData['capLevels'], 'siteAdmin') !== false || strpos($userData['capLevels'], '&siteAdmin&') !== false)) {
                $siteAdmins[] = [
                    'oid' => $userOid,
                    'username' => $userData['name']
                ];
            }
        }

        if (empty($siteAdmins)) {
            throw new \Exception("No site admin users found for Vsite: $fqdn");
        }

        // Step 5: Determine the correct site admin
        $siteAdmin = null;
        $userOid = null;

        if (count($siteAdmins) === 1) {
            // Only one site admin, use it directly
            $siteAdmin = $siteAdmins[0]['username'];
            $userOid = $siteAdmins[0]['oid'];
        } else {
            // Multiple site admins, check prefered_siteAdmin from Vsite's PHP namespace
            $phpData = $client->get($oid, "PHP");
            blueonyx_debug_log('blueonyx_ChangePassword', 'get_php', $phpData);
            if (!isset($phpData['prefered_siteAdmin']) || empty($phpData['prefered_siteAdmin'])) {
                throw new \Exception("Failed to retrieve prefered_siteAdmin for Vsite with multiple site admins: $fqdn");
            }
            $preferedSiteAdmin = $phpData['prefered_siteAdmin'];

            // Find the matching site admin
            foreach ($siteAdmins as $admin) {
                if ($admin['username'] === $preferedSiteAdmin) {
                    $siteAdmin = $admin['username'];
                    $userOid = $admin['oid'];
                    break;
                }
            }

            if (!$siteAdmin) {
                throw new \Exception("Prefered site admin '$preferedSiteAdmin' not found among site admins for Vsite: $fqdn");
            }
        }

        blueonyx_debug_log('blueonyx_ChangePassword', 'selected_site_admin', ['username' => $siteAdmin, 'oid' => $userOid]);

        // Step 6: Update the site admin's password
        $newPassword = !empty($params['password']) ? trim($params['password']) : generateRandomString(12);
        $success = $client->set($userOid, "", ["password" => $newPassword]);
        blueonyx_debug_log('blueonyx_ChangePassword', 'set_password', $success);
        if ($success !== 1) {
            throw new \Exception("Failed to update password for site admin '$siteAdmin'");
        }

        return 'success';
    } catch (Exception $e) {
        blueonyx_debug_log('blueonyx_ChangePassword', 'error', $e->getMessage());
        return 'Error: ' . $e->getMessage();
    }
}

function blueonyx_ChangePackage(array $params) {
    try {
        require_once __DIR__ . '/CceApiClient.php';

        $client = new \BlueOnyx\CceApiClient(
            $params['serverhostname'],
            $params['serveraccesshash']
        );

        $client->login($params['serverusername'], $params['serverpassword']);

        // Step 1: Find the Vsite by fqdn
        $fqdn = $params['domain'];
        $oids = $client->findx("Vsite", ["fqdn" => $fqdn]);
        blueonyx_debug_log('blueonyx_ChangePackage', 'findx_vsite', $oids);
        if (empty($oids)) {
            throw new \Exception("Vsite not found for domain: $fqdn");
        }
        $oid = $oids[0];

        // Step 2: Get the Vsite group name to find associated users
        $vsiteData = $client->get($oid);
        blueonyx_debug_log('blueonyx_ChangePackage', 'get_vsite', $vsiteData);
        if ($vsiteData === -1) {
            throw new \Exception("Failed to retrieve Vsite object with OID: $oid");
        }
        $groupName = $vsiteData['name'] ?? 'vsite';

        // Step 3: Find all users associated with the Vsite
        $userOids = $client->findx("User", ["site" => $groupName]);
        blueonyx_debug_log('blueonyx_ChangePackage', 'findx_users', $userOids);
        if (empty($userOids)) {
            throw new \Exception("No users found for Vsite: $fqdn");
        }

        // Step 4: Filter users to find site admins (capLevels contains 'siteAdmin' or '&siteAdmin&')
        $siteAdmins = [];
        foreach ($userOids as $userOid) {
            $userData = $client->get($userOid);
            blueonyx_debug_log('blueonyx_ChangePackage', 'get_user_' . $userOid, $userData);
            if (isset($userData['capLevels']) && (strpos($userData['capLevels'], 'siteAdmin') !== false || strpos($userData['capLevels'], '&siteAdmin&') !== false)) {
                $siteAdmins[] = [
                    'oid' => $userOid,
                    'username' => $userData['name']
                ];
            }
        }

        if (empty($siteAdmins)) {
            throw new \Exception("No site admin users found for Vsite: $fqdn");
        }

        // Step 5: Determine the correct site admin
        $siteAdmin = null;
        $userOid = null;

        if (count($siteAdmins) === 1) {
            // Only one site admin, use it directly
            $siteAdmin = $siteAdmins[0]['username'];
            $userOid = $siteAdmins[0]['oid'];
        } else {
            // Multiple site admins, check prefered_siteAdmin from Vsite's PHP namespace
            $phpData = $client->get($oid, "PHP");
            blueonyx_debug_log('blueonyx_ChangePackage', 'get_php', $phpData);
            if (!isset($phpData['prefered_siteAdmin']) || empty($phpData['prefered_siteAdmin'])) {
                throw new \Exception("Failed to retrieve prefered_siteAdmin for Vsite with multiple site admins: $fqdn");
            }
            $preferedSiteAdmin = $phpData['prefered_siteAdmin'];

            // Find the matching site admin
            foreach ($siteAdmins as $admin) {
                if ($admin['username'] === $preferedSiteAdmin) {
                    $siteAdmin = $admin['username'];
                    $userOid = $admin['oid'];
                    break;
                }
            }

            if (!$siteAdmin) {
                throw new \Exception("Prefered site admin '$preferedSiteAdmin' not found among site admins for Vsite: $fqdn");
            }
        }

        blueonyx_debug_log('blueonyx_ChangePackage', 'selected_site_admin', ['username' => $siteAdmin, 'oid' => $userOid]);

        // Step 6: Validate config options with defaults
        $diskMax = is_numeric($params['configoption4']) ? $params['configoption4'] : '200';
        $maxUsers = is_numeric($params['configoption5']) ? $params['configoption5'] : '25';
        $phpVersion = in_array($params['configoption9'], explode(',', 'PHPOS,PHP56,PHP70,PHP71,PHP72,PHP73,PHP74,PHP80,PHP81,PHP82,PHP83,PHP84,PHP85,PHP86,PHP90,PHP91,PHP92,PHP93')) ? $params['configoption9'] : 'PHPOS';
        $mysqlDbs = is_numeric($params['configoption11']) ? $params['configoption11'] : '1';
        $subdomainsMax = is_numeric($params['configoption17']) ? $params['configoption17'] : '1';

        // Step 7: Update Vsite main object
        $success = $client->set($oid, "", [
            'maxusers' => $maxUsers,
            'dns_auto' => ($params['configoption6'] == 'on') ? '1' : '0',
        ]);
        blueonyx_debug_log('blueonyx_ChangePackage', 'set_main', $success);
        if ($success !== 1) {
            throw new \Exception("Failed to update Vsite main properties");
        }

        // Step 8: Update Vsite namespace properties
        $success = $client->set($oid, "Disk", ["quota" => $diskMax]);
        blueonyx_debug_log('blueonyx_ChangePackage', 'set_disk', $success);
        if ($success !== 1) {
            throw new \Exception("Failed to update Disk quota");
        }

        $success = $client->set($oid, "PHP", [
            "enabled" => "1",
            "fpm_enabled" => ($params['configoption7'] == 'FPM') ? '1' : '0',
            "suPHP_enabled" => ($params['configoption7'] == 'suPHP') ? '1' : '0',
            "version" => $phpVersion,
            "prefered_siteAdmin" => $siteAdmin
        ]);
        blueonyx_debug_log('blueonyx_ChangePackage', 'set_php', $success);
        if ($success !== 1) {
            throw new \Exception("Failed to update PHP settings");
        }

        $success = $client->set($oid, "SSI", ["enabled" => ($params['configoption8'] == 'on') ? '1' : '0']);
        blueonyx_debug_log('blueonyx_ChangePackage', 'set_ssi', $success);
        if ($success !== 1) {
            throw new \Exception("Failed to update SSI settings");
        }

        $success = $client->set($oid, "CGI", ["enabled" => ($params['configoption10'] == 'on') ? '1' : '0']);
        blueonyx_debug_log('blueonyx_ChangePackage', 'set_cgi', $success);
        if ($success !== 1) {
            throw new \Exception("Failed to update CGI settings");
        }

        $shellValue = $params['configoption14'] ?: 'None';
        $shellEnabled = $shellValue === 'None' ? '0' : ($shellValue === 'Full Shell Access' ? '3' : '1');
        $success = $client->set($oid, "Shell", ["enabled" => $shellEnabled]);
        blueonyx_debug_log('blueonyx_ChangePackage', 'set_shell', $success);
        if ($success !== 1) {
            throw new \Exception("Failed to update Shell settings");
        }

        $subdomainsEnabled = ($params['configoption15'] == 'on') ? '1' : '0';
        $success = $client->set($oid, "subdomains", [
            "enabled" => $subdomainsEnabled,
            "max_subdomains" => $subdomainsMax,
            "vsite_enabled" => $subdomainsEnabled,
        ]);
        blueonyx_debug_log('blueonyx_ChangePackage', 'set_subdomains', $success);
        if ($success !== 1) {
            throw new \Exception("Failed to update Subdomains settings");
        }

        $success = $client->set($oid, "FTPNONADMIN", ["enabled" => ($params['configoption12'] == 'on') ? '1' : '0']);
        blueonyx_debug_log('blueonyx_ChangePackage', 'set_ftpnonadmin', $success);
        if ($success !== 1) {
            throw new \Exception("Failed to update FTPNONADMIN settings");
        }

        // Update MySQL settings (maxDBs only, preserve existing credentials)
        $success = $client->set($oid, "MYSQL_Vsite", [
            "enabled" => ($mysqlDbs > 0) ? '1' : '0',
            "maxDBs" => $mysqlDbs
        ]);
        blueonyx_debug_log('blueonyx_ChangePackage', 'set_mysql', $success);
        if ($success !== 1) {
            throw new \Exception("Failed to update MySQL settings");
        }

        // Step 9: Update site admin user settings to match Vsite package
        $success = $client->set($userOid, "Disk", ["quota" => $diskMax]);
        blueonyx_debug_log('blueonyx_ChangePackage', 'set_user_disk', $success);
        if ($success !== 1) {
            throw new \Exception("Failed to update user Disk quota");
        }

        $success = $client->set($userOid, "Shell", ["enabled" => $shellEnabled]);
        blueonyx_debug_log('blueonyx_ChangePackage', 'set_user_shell', $success);
        if ($success !== 1) {
            throw new \Exception("Failed to update user Shell settings");
        }

        // Step 10: Force PHP update
        $success = $client->set($oid, "PHPVsite", ["force_update" => time()]);
        blueonyx_debug_log('blueonyx_ChangePackage', 'set_phpvsite', $success);
        if ($success !== 1) {
            throw new \Exception("Failed to force PHP update");
        }

        return 'success';
    } catch (Exception $e) {
        blueonyx_debug_log('blueonyx_ChangePackage', 'error', $e->getMessage());
        return 'Error: ' . $e->getMessage();
    }
}

function blueonyx_ClientArea(array $params) {
    // Determine the requested action and set service call parameters
    $requestedAction = isset($_REQUEST['customAction']) ? $_REQUEST['customAction'] : '';

    try {
        // Default template for the overview tab
        $templateFile = 'templates/overview.tpl';

        // Initialize template variables
        $templateVariables = array();

        // Handle custom actions for the new buttons
        if ($requestedAction == 'manage') {
            $templateFile = 'templates/manage.tpl';
        } elseif ($requestedAction == 'changePhpVersion') {
            $templateFile = 'templates/changePhpVersion.tpl';
        } elseif ($requestedAction == 'generateSsl') {
            $templateFile = 'templates/generateSsl.tpl';
        } elseif ($requestedAction == 'fileManager') {
            $templateFile = 'templates/fileManager.tpl';
        } elseif ($requestedAction == 'phpMyAdmin') {
            $templateFile = 'templates/phpMyAdmin.tpl';
        }

        require_once __DIR__ . '/CceApiClient.php';

        $client = new \BlueOnyx\CceApiClient(
            $params['serverhostname'],
            $params['serveraccesshash']
        );

        $client->login($params['serverusername'], $params['serverpassword']);

        $UserName = $params['username'];

        // Step 1: Find the User to get their Vsite group name
        $oids = $client->findx("User", ["name" => $UserName]);
        blueonyx_debug_log('blueonyx_ClientArea', 'findx_user', $oids);
        if (empty($oids)) {
            throw new \Exception("Failed to retrieve User object for User: $UserName");
        }
        $oid = $oids[0];

        // Step 2: Get the User to find out group name
        $userData = $client->get($oid);
        blueonyx_debug_log('blueonyx_ClientArea', 'get_user', $userData);
        if ($userData === -1) {
            throw new \Exception("Failed to retrieve User object with OID: $oid");
        }
        $groupName = $userData['site'] ?? '';

        // Step 3: Fetch Vsite details
        $vsiteInfo = [
            'name' => $groupName,
            'ip_address' => 'N/A',
            'vsite_over_quota' => 'N/A',
            'disk_limit' => 'N/A',
            'disk_limit_unit' => ''
        ];
        $sslInfo = [
            'enabled' => 'No',
            'expiry' => 'N/A',
            'issuer' => 'N/A'
        ];
        $vsiteOid = null; // Initialize vsiteOid for use in PHP version change

        // Fetch available PHP versions and current PHP version
        $phpInfo = ['current_version' => 'N/A', 'available_versions' => []];
        if (!empty($groupName)) {
            // Find the Vsite object by group name
            $vsiteOids = $client->findx("Vsite", ["name" => $groupName]);
            blueonyx_debug_log('blueonyx_ClientArea', 'findx_vsite', $vsiteOids);
            if (!empty($vsiteOids)) {
                $vsiteOid = $vsiteOids[0];

                // Fetch Vsite general details
                $vsiteData = $client->get($vsiteOid);
                blueonyx_debug_log('blueonyx_ClientArea', 'get_vsite', $vsiteData);
                if ($vsiteData !== -1) {
                    // Fetch Vsite details (adjust property names based on API response)
                    $ipAddress = isset($vsiteData['ipaddr']) ? $vsiteData['ipaddr'] : 'N/A';
                    // Sanitize IP address (allow numbers, dots, colons for IPv6)
                    $vsiteInfo['ip_address'] = preg_replace('/[^0-9.:]/', '', $ipAddress);
                }

                // Fetch Vsite SSL information
                $vsiteSslData = $client->get($vsiteOid, 'SSL');
                blueonyx_debug_log('blueonyx_ClientArea', 'get_vsite_ssl', $vsiteSslData);
                if ($vsiteSslData !== -1) {
                    $sslEnabled = isset($vsiteSslData['enabled']) ? $vsiteSslData['enabled'] : false;
                    $sslInfo['enabled'] = $sslEnabled ? 'Yes' : 'No';
                    if ($sslEnabled) {
                        $sslInfo['expiry'] = isset($vsiteSslData['expires']) ? date('Y-m-d', strtotime($vsiteSslData['expires'])) : 'N/A';
                        $sslInfo['issuer'] = isset($vsiteSslData['orgName']) ? $vsiteSslData['orgName'] : 'N/A';
                    }
                }

                // Fetch Vsite Disk information
                $vsiteDiskData = $client->get($vsiteOid, 'Disk');
                blueonyx_debug_log('blueonyx_ClientArea', 'get_vsite_disk', $vsiteDiskData);
                if ($vsiteDiskData !== -1) {
                    // Quota is in MB (e.g., 5000 for 5GB)
                    $quotaMB = isset($vsiteDiskData['quota']) ? (float)$vsiteDiskData['quota'] : 0;
                    if ($quotaMB >= 1000000) { // 1TB in MB (base-10)
                        $vsiteInfo['disk_limit'] = round($quotaMB / 1000000, 2);
                        $vsiteInfo['disk_limit_unit'] = 'TB';
                    } elseif ($quotaMB >= 1000) { // 1GB in MB (base-10)
                        $vsiteInfo['disk_limit'] = round($quotaMB / 1000, 2);
                        $vsiteInfo['disk_limit_unit'] = 'GB';
                    } else {
                        $vsiteInfo['disk_limit'] = $quotaMB;
                        $vsiteInfo['disk_limit_unit'] = 'MB';
                    }
                    $vsiteInfo['vsite_over_quota'] = isset($vsiteDiskData['vsite_over_quota']) && $vsiteDiskData['vsite_over_quota'] ? 'Yes' : 'No';
                } else {
                    blueonyx_debug_log('blueonyx_ClientArea', 'disk_fetch_failed', 'Failed to fetch Disk namespace for Vsite: ' . $groupName);
                }

                // Get PHP version
                $SystemPHP = $client->getAll("PHP", ["CLASS" => 'PHP']);
                $SystemPHP = reset($SystemPHP);
                $PHP = $SystemPHP['OBJECT'];
                unset($SystemPHP['OBJECT']);
                $availablePHPVersions = ['PHPOS'];

                foreach ($SystemPHP as $NS => $data) {
                    if (($data['enabled'] === '1') && ($data['present'] === '1')) {
                        $availablePHPVersions[] = $NS;
                    }
                }

                $phpInfo = ['current_version' => 'N/A', 'available_versions' => $availablePHPVersions];
                if (!empty($groupName) && !empty($vsiteOids)) {
                    $vsiteOid = $vsiteOids[0];
                    $phpData = $client->get($vsiteOid, 'PHP');
                    blueonyx_debug_log('blueonyx_ClientArea', 'get_vsite_php', $phpData);
                    if ($phpData !== -1 && isset($phpData['version'])) {
                        $phpInfo['current_version'] = $phpData['version'];
                    }
                }
            }
        }

        // Sanitize the group name to prevent URL issues
        $groupName = preg_replace('/[^a-zA-Z0-9_-]/', '', $groupName);

        // Construct the redirect URL for overview login
        if (empty($groupName)) {
            $redirectUrl = "/gui";
        } else {
            $redirectUrl = "/user/userList?group=" . $groupName;
        }

        // Generate the auto-login URL using the helper function
        $whmcsUrl = $GLOBALS['CONFIG']['SystemURL'];
        $loginLink = blueonyx_generateAutoLoginUrl(
            $whmcsUrl,
            $params['serverhostname'],
            $params['username'],
            $params['password'],
            $redirectUrl
        );

        // Prepare template variables
        $templateVariables = array(
            'loginLink' => $loginLink,
            'sslInfo' => $sslInfo,
            'vsiteInfo' => $vsiteInfo,
            'vsiteUsername' => $params['username'],
            'vsitePassword' => $params['password'],
            'serviceid' => $params['serviceid'],
            'phpInfo' => $phpInfo,
        );

        // Handle PHP version change
        if ($requestedAction == 'changePhpVersion') {
            $successMessage = '';
            $errorMessage = '';
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['phpVersion'])) {
                $newPhpVersion = $_POST['phpVersion'];
                if (in_array($newPhpVersion, $phpInfo['available_versions'])) {
                    if ($vsiteOid) {
                        $result = $client->set($vsiteOid, 'PHP', ['version' => $newPhpVersion]);
                        blueonyx_debug_log('blueonyx_ClientArea', 'set_vsite_php', "Set PHP version to $newPhpVersion for Vsite OID: $vsiteOid, Result: $result");
                        if ($result !== -1) {
                            $successMessage = "PHP version successfully changed to $newPhpVersion.";
                            $phpInfo['current_version'] = $newPhpVersion; // Update current version
                        } else {
                            $errorMessage = "Failed to change PHP version to $newPhpVersion.";
                        }
                    } else {
                        $errorMessage = "Unable to change PHP version: Vsite not found.";
                    }
                } else {
                    $errorMessage = "Invalid PHP version selected.";
                }
            }
            // Re-fetch the current PHP version to confirm the change
            if ($vsiteOid) {
                $phpData = $client->get($vsiteOid, 'PHP');
                blueonyx_debug_log('blueonyx_ClientArea', 'get_vsite_php_after_change', $phpData);
                if ($phpData !== -1 && isset($phpData['version'])) {
                    $phpInfo['current_version'] = $phpData['version'];
                }
            }
            $templateVariables['successMessage'] = $successMessage;
            $templateVariables['errorMessage'] = $errorMessage;
            $templateVariables['phpInfo'] = $phpInfo; // Ensure updated phpInfo is passed
            $templateFile = 'templates/changePhpVersion.tpl';
        }

        // Handle Generate SSL Certificate redirect
        if ($requestedAction == 'generateSsl') {
            if (empty($groupName)) {
                $templateFile = 'templates/generateSsl.tpl';
                $templateVariables['errorMessage'] = 'Unable to generate SSL certificate: Vsite group name not found.';
            } else {
                $sslRedirectUrl = "/ssl/letsencryptCert?group=" . $groupName;
                $sslAutoLoginUrl = blueonyx_generateAutoLoginUrl(
                    $whmcsUrl,
                    $params['serverhostname'],
                    $params['username'],
                    $params['password'],
                    $sslRedirectUrl
                );
                blueonyx_debug_log('blueonyx_ClientArea', 'generate_ssl_redirect', "Redirecting to: $sslAutoLoginUrl");
                header("Location: $sslAutoLoginUrl");
                exit;
            }
        }

        // Handle phpMyAdmin redirect
        if ($requestedAction == 'phpMyAdmin') {
            if (empty($groupName)) {
                $templateFile = 'templates/phpMyAdmin.tpl';
                $templateVariables['errorMessage'] = 'Unable to access phpMyAdmin: Vsite group name not found.';
            } else {
                // Construct phpMyAdmin URL via BlueOnyx GUI on port 81
                $phpMyAdminRedirectUrl = "/phpmyadmin/site?group=" . $groupName;
                $phpMyAdminAutoLoginUrl = blueonyx_generateAutoLoginUrl(
                    $whmcsUrl,
                    $params['serverhostname'],
                    $params['username'],
                    $params['password'],
                    $phpMyAdminRedirectUrl
                );
                blueonyx_debug_log('blueonyx_ClientArea', 'phpmyadmin_redirect', "Redirecting to: $phpMyAdminAutoLoginUrl");
                header("Location: $phpMyAdminAutoLoginUrl");
                exit;
            }
        }

        // Handle File Manager redirect
        if ($requestedAction == 'fileManager') {
            if (empty($groupName)) {
                $templateFile = 'templates/fileManager.tpl';
                $templateVariables['errorMessage'] = 'Unable to access File Manager: Vsite group name not found.';
            } else {
                // Construct phpMyAdmin URL via BlueOnyx GUI on port 81
                $fileManagerRedirectUrl = "/ftp/filemanager?group=" . $groupName;
                $fileManagerRedirectUrl = blueonyx_generateAutoLoginUrl(
                    $whmcsUrl,
                    $params['serverhostname'],
                    $params['username'],
                    $params['password'],
                    $fileManagerRedirectUrl
                );
                blueonyx_debug_log('blueonyx_ClientArea', 'filemaneger_redirect', "Redirecting to: $fileManagerRedirectUrl");
                header("Location: $fileManagerRedirectUrl");
                exit;
            }
        }

        return array(
            'tabOverviewReplacementTemplate' => $templateFile,
            'templateVariables' => $templateVariables,
        );
    } catch (Exception $e) {
        logModuleCall(
            'blueonyx',
            __FUNCTION__,
            $params,
            $e->getMessage(),
            $e->getTraceAsString()
        );

        return array(
            'tabOverviewReplacementTemplate' => 'error.tpl',
            'templateVariables' => array(
                'usefulErrorHelper' => $e->getMessage(),
            ),
        );
    }
}

function last_blueonyx_ClientArea(array $params) {
    // Determine the requested action and set service call parameters
    $requestedAction = isset($_REQUEST['customAction']) ? $_REQUEST['customAction'] : '';

    try {
        // Default template for the overview tab
        $templateFile = 'templates/overview.tpl';

        // Initialize template variables
        $templateVariables = array();

        // Handle custom actions for the new buttons
        if ($requestedAction == 'manage') {
            $templateFile = 'templates/manage.tpl';
        } elseif ($requestedAction == 'changePhpVersion') {
            $templateFile = 'templates/changePhpVersion.tpl';
        } elseif ($requestedAction == 'generateSsl') {
            $templateFile = 'templates/generateSsl.tpl';
        } elseif ($requestedAction == 'fileManager') {
            $templateFile = 'templates/fileManager.tpl';
        } elseif ($requestedAction == 'phpMyAdmin') {
            $templateFile = 'templates/phpMyAdmin.tpl';
        }

        require_once __DIR__ . '/CceApiClient.php';

        $client = new \BlueOnyx\CceApiClient(
            $params['serverhostname'],
            $params['serveraccesshash']
        );

        $client->login($params['serverusername'], $params['serverpassword']);

        $UserName = $params['username'];

        // Step 1: Find the User to get their Vsite group name
        $oids = $client->findx("User", ["name" => $UserName]);
        blueonyx_debug_log('blueonyx_ClientArea', 'findx_user', $oids);
        if (empty($oids)) {
            throw new \Exception("Failed to retrieve User object for User: $UserName");
        }
        $oid = $oids[0];

        // Step 2: Get the User to find out group name
        $userData = $client->get($oid);
        blueonyx_debug_log('blueonyx_ClientArea', 'get_user', $userData);
        if ($userData === -1) {
            throw new \Exception("Failed to retrieve User object with OID: $oid");
        }
        $groupName = $userData['site'] ?? '';

        // Step 3: Fetch Vsite details
        $vsiteInfo = [
            'name' => $groupName,
            'ip_address' => 'N/A',
            'vsite_over_quota' => 'N/A',
            'disk_limit' => 'N/A',
            'disk_limit_unit' => ''
        ];
        $sslInfo = [
            'enabled' => 'No',
            'expiry' => 'N/A',
            'issuer' => 'N/A'
        ];
        $vsiteOid = null; // Initialize vsiteOid for use in PHP version change

        // Fetch available PHP versions and current PHP version
        $phpInfo = ['current_version' => 'N/A', 'available_versions' => []];
        if (!empty($groupName)) {
            // Find the Vsite object by group name
            $vsiteOids = $client->findx("Vsite", ["name" => $groupName]);
            blueonyx_debug_log('blueonyx_ClientArea', 'findx_vsite', $vsiteOids);
            if (!empty($vsiteOids)) {
                $vsiteOid = $vsiteOids[0];

                // Fetch Vsite general details
                $vsiteData = $client->get($vsiteOid);
                blueonyx_debug_log('blueonyx_ClientArea', 'get_vsite', $vsiteData);
                if ($vsiteData !== -1) {
                    // Fetch Vsite details (adjust property names based on API response)
                    $ipAddress = isset($vsiteData['ipaddr']) ? $vsiteData['ipaddr'] : 'N/A';
                    // Sanitize IP address (allow numbers, dots, colons for IPv6)
                    $vsiteInfo['ip_address'] = preg_replace('/[^0-9.:]/', '', $ipAddress);
                }

                // Fetch Vsite SSL information
                $vsiteSslData = $client->get($vsiteOid, 'SSL');
                blueonyx_debug_log('blueonyx_ClientArea', 'get_vsite_ssl', $vsiteSslData);
                if ($vsiteSslData !== -1) {
                    $sslEnabled = isset($vsiteSslData['enabled']) ? $vsiteSslData['enabled'] : false;
                    $sslInfo['enabled'] = $sslEnabled ? 'Yes' : 'No';
                    if ($sslEnabled) {
                        $sslInfo['expiry'] = isset($vsiteSslData['expires']) ? date('Y-m-d', strtotime($vsiteSslData['expires'])) : 'N/A';
                        $sslInfo['issuer'] = isset($vsiteSslData['orgName']) ? $vsiteSslData['orgName'] : 'N/A';
                    }
                }

                // Fetch Vsite Disk information
                $vsiteDiskData = $client->get($vsiteOid, 'Disk');
                blueonyx_debug_log('blueonyx_ClientArea', 'get_vsite_disk', $vsiteDiskData);
                if ($vsiteDiskData !== -1) {
                    // Quota is in MB (e.g., 5000 for 5GB)
                    $quotaMB = isset($vsiteDiskData['quota']) ? (float)$vsiteDiskData['quota'] : 0;
                    if ($quotaMB >= 1000000) { // 1TB in MB (base-10)
                        $vsiteInfo['disk_limit'] = round($quotaMB / 1000000, 2);
                        $vsiteInfo['disk_limit_unit'] = 'TB';
                    } elseif ($quotaMB >= 1000) { // 1GB in MB (base-10)
                        $vsiteInfo['disk_limit'] = round($quotaMB / 1000, 2);
                        $vsiteInfo['disk_limit_unit'] = 'GB';
                    } else {
                        $vsiteInfo['disk_limit'] = $quotaMB;
                        $vsiteInfo['disk_limit_unit'] = 'MB';
                    }
                    $vsiteInfo['vsite_over_quota'] = isset($vsiteDiskData['vsite_over_quota']) && $vsiteDiskData['vsite_over_quota'] ? 'Yes' : 'No';
                } else {
                    blueonyx_debug_log('blueonyx_ClientArea', 'disk_fetch_failed', 'Failed to fetch Disk namespace for Vsite: ' . $groupName);
                }

                // Get PHP version
                $SystemPHP = $client->getAll("PHP", ["CLASS" => 'PHP']);
                $SystemPHP = reset($SystemPHP);
                $PHP = $SystemPHP['OBJECT'];
                unset($SystemPHP['OBJECT']);
                $availablePHPVersions = ['PHPOS'];

                foreach ($SystemPHP as $NS => $data) {
                    if (($data['enabled'] === '1') && ($data['present'] === '1')) {
                        $availablePHPVersions[] = $NS;
                    }
                }

                $phpInfo = ['current_version' => 'N/A', 'available_versions' => $availablePHPVersions];
                if (!empty($groupName) && !empty($vsiteOids)) {
                    $vsiteOid = $vsiteOids[0];
                    $phpData = $client->get($vsiteOid, 'PHP');
                    blueonyx_debug_log('blueonyx_ClientArea', 'get_vsite_php', $phpData);
                    if ($phpData !== -1 && isset($phpData['version'])) {
                        $phpInfo['current_version'] = $phpData['version'];
                    }
                }
            }
        }

        // Sanitize the group name to prevent URL issues
        $groupName = preg_replace('/[^a-zA-Z0-9_-]/', '', $groupName);

        // Construct the redirect URL for overview login
        if (empty($groupName)) {
            $redirectUrl = "/gui";
        } else {
            $redirectUrl = "/user/userList?group=" . $groupName;
        }

        // Generate the auto-login URL using the helper function
        $whmcsUrl = $GLOBALS['CONFIG']['SystemURL'];
        $loginLink = blueonyx_generateAutoLoginUrl(
            $whmcsUrl,
            $params['serverhostname'],
            $params['username'],
            $params['password'],
            $redirectUrl
        );

        // Prepare template variables
        $templateVariables = array(
            'loginLink' => $loginLink,
            'sslInfo' => $sslInfo,
            'vsiteInfo' => $vsiteInfo,
            'vsiteUsername' => $params['username'],
            'vsitePassword' => $params['password'],
            'serviceid' => $params['serviceid'],
            'phpInfo' => $phpInfo,
        );

        // Handle PHP version change
        if ($requestedAction == 'changePhpVersion') {
            $successMessage = '';
            $errorMessage = '';
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['phpVersion'])) {
                $newPhpVersion = $_POST['phpVersion'];
                if (in_array($newPhpVersion, $phpInfo['available_versions'])) {
                    if ($vsiteOid) {
                        $result = $client->set($vsiteOid, 'PHP', ['version' => $newPhpVersion]);
                        blueonyx_debug_log('blueonyx_ClientArea', 'set_vsite_php', "Set PHP version to $newPhpVersion for Vsite OID: $vsiteOid, Result: $result");
                        if ($result !== -1) {
                            $successMessage = "PHP version successfully changed to $newPhpVersion.";
                            $phpInfo['current_version'] = $newPhpVersion; // Update current version
                        } else {
                            $errorMessage = "Failed to change PHP version to $newPhpVersion.";
                        }
                    } else {
                        $errorMessage = "Unable to change PHP version: Vsite not found.";
                    }
                } else {
                    $errorMessage = "Invalid PHP version selected.";
                }
            }
            // Re-fetch the current PHP version to confirm the change
            if ($vsiteOid) {
                $phpData = $client->get($vsiteOid, 'PHP');
                blueonyx_debug_log('blueonyx_ClientArea', 'get_vsite_php_after_change', $phpData);
                if ($phpData !== -1 && isset($phpData['version'])) {
                    $phpInfo['current_version'] = $phpData['version'];
                }
            }
            $templateVariables['successMessage'] = $successMessage;
            $templateVariables['errorMessage'] = $errorMessage;
            $templateVariables['phpInfo'] = $phpInfo; // Ensure updated phpInfo is passed
            $templateFile = 'templates/changePhpVersion.tpl';
        }

        // Handle Generate SSL Certificate redirect
        if ($requestedAction == 'generateSsl') {
            if (empty($groupName)) {
                $templateFile = 'templates/generateSsl.tpl';
                $templateVariables['errorMessage'] = 'Unable to generate SSL certificate: Vsite group name not found.';
            } else {
                $sslRedirectUrl = "/ssl/letsencryptCert?group=" . $groupName;
                $sslAutoLoginUrl = blueonyx_generateAutoLoginUrl(
                    $whmcsUrl,
                    $params['serverhostname'],
                    $params['username'],
                    $params['password'],
                    $sslRedirectUrl
                );
                blueonyx_debug_log('blueonyx_ClientArea', 'generate_ssl_redirect', "Redirecting to: $sslAutoLoginUrl");
                header("Location: $sslAutoLoginUrl");
                exit;
            }
        }

        return array(
            'tabOverviewReplacementTemplate' => $templateFile,
            'templateVariables' => $templateVariables,
        );
    } catch (Exception $e) {
        logModuleCall(
            'blueonyx',
            __FUNCTION__,
            $params,
            $e->getMessage(),
            $e->getTraceAsString()
        );

        return array(
            'tabOverviewReplacementTemplate' => 'error.tpl',
            'templateVariables' => array(
                'usefulErrorHelper' => $e->getMessage(),
            ),
        );
    }
}

function blueonyx_AdminLink($params) {
    require_once __DIR__ . '/CceApiClient.php';

    try {
        blueonyx_debug_log('blueonyx_AdminLink', 'start', 'Entering blueonyx_AdminLink with params: ' . json_encode($params));

        $client = new \BlueOnyx\CceApiClient(
            $params['serverhostname'],
            $params['serveraccesshash']
        );

        $token = $client->login($params['serverusername'], $params['serverpassword']);
        blueonyx_debug_log('blueonyx_AdminLink', 'login', 'Token: ' . $token);

        $whoami = $client->whoami();
        blueonyx_debug_log('blueonyx_AdminLink', 'whoami', json_encode($whoami));
        if (!$whoami['success']) {
            throw new \Exception("Session validation failed: " . ($whoami['message'] ?? 'Unknown error'));
        }

        blueonyx_debug_log('blueonyx_AdminLink', 'getObject', 'Attempting to fetch System object');
        $system = $client->getObject("System");
        blueonyx_debug_log('blueonyx_AdminLink', 'getObject(System)', json_encode($system));

        if (!is_array($system) || !isset($system['GUI_PORT'])) {
            throw new \Exception("Failed to retrieve System object: Invalid response structure");
        }

        $gui_port = $system['GUI_PORT'] ?? '81';
        blueonyx_debug_log('blueonyx_AdminLink', 'gui_port', 'GUI port: ' . $gui_port);

        $hostname = $params['serverhostname'];
        $url = "https://$hostname:$gui_port/login";

        blueonyx_debug_log('blueonyx_AdminLink', 'result', 'Generated URL: ' . $url);
        return '<a href="' . $url . '" target="_blank" class="btn btn-default btn-sm">Login to BlueOnyx GUI</a>';
    } catch (Exception $e) {
        blueonyx_debug_log('blueonyx_AdminLink', 'error', $e->getMessage());
        $hostname = $params['serverhostname'];
        $url = "https://$hostname:81/login";
        blueonyx_debug_log('blueonyx_AdminLink', 'fallback', 'Using fallback URL: ' . $url);
        return [
            'adminlink' => '<a href="' . $url . '" target="_blank" class="btn btn-default btn-sm">Login to BlueOnyx GUI (Fallback)</a>',
            'error' => $e->getMessage()
        ];
    }
}

function blueonyx_ServiceSingleSignOn(array $params) {
    try {
        $whmcsUrl = $GLOBALS['CONFIG']['SystemURL'];
        // Use the client's username and password for token generation
        $token = md5($params['serverhostname'] . $params['username'] . time());

        require_once __DIR__ . '/CceApiClient.php';

        $client = new \BlueOnyx\CceApiClient(
            $params['serverhostname'],
            $params['serveraccesshash']
        );

        $client->login($params['serverusername'], $params['serverpassword']);

        $UserName = $params['username'];

        $oids = $client->findx("User", ["name" => $UserName]);
        blueonyx_debug_log('blueonyx_ServiceSingleSignOn', 'findx_user', $oids);
        if (empty($oids)) {
            throw new \Exception("Failed to retrieve User object for User: $UserName");
        }
        $oid = $oids[0];

        // Step 2: Get the User to find out group name:
        $userData = $client->get($oid);
        blueonyx_debug_log('blueonyx_ServiceSingleSignOn', 'get_user', $userData);
        if ($userData === -1) {
            throw new \Exception("Failed to retrieve User object with OID: $oid");
        }
        $groupName = $userData['site'] ?? '';

        // Construct the redirect URL
        if (empty($groupName)) {
            $redirectUrl = "/gui";
        } else {
            $redirectUrl = "/user/userList?group=" . $groupName;
        }

        // Construct the autologin URL with the redirect parameter
        $autologinUrl = $whmcsUrl . '/modules/servers/blueonyx/autologin.php' .
            '?hostname=' . urlencode($params['serverhostname']) .
            '&username=' . urlencode($params['username']) .
            '&timestamp=' . time() .
            '&ptoken=' . urlencode(base64_encode($params['password'])) .
            '&token=' . $token .
            '&redirect=' . urlencode($redirectUrl);

        return array(
            'success' => true,
            'redirectTo' => $autologinUrl,
        );
    } catch (Exception $e) {
        logModuleCall(
            'blueonyx',
            __FUNCTION__,
            $params,
            $e->getMessage(),
            $e->getTraceAsString()
        );

        return array(
            'success' => false,
            'errorMsg' => $e->getMessage(),
        );
    }
}

function old_blueonyx_ServiceSingleSignOn(array $params) {
    try {
        $whmcsUrl = $GLOBALS['CONFIG']['SystemURL'];
        // Use the client's username and password for token generation
        $token = md5($params['serverhostname'] . $params['username'] . time());

        require_once __DIR__ . '/CceApiClient.php';

        $client = new \BlueOnyx\CceApiClient(
            $params['serverhostname'],
            $params['serveraccesshash']
        );

        $client->login($params['serverusername'], $params['serverpassword']);

        $UserName = $params['username'];

        $oids = $client->findx("User", ["name" => $UserName]);
        blueonyx_debug_log('blueonyx_ServiceSingleSignOn', 'findx_user', $oids);
        if (empty($oids)) {
            throw new \Exception("Failed to retrieve User object for User: $UserName");
        }
        $oid = $oids[0];

        // Step 2: Get the User to find out group name:
        $userData = $client->get($oid);
        blueonyx_debug_log('blueonyx_ServiceSingleSignOn', 'get_user', $userData);
        if ($userData === -1) {
            throw new \Exception("Failed to retrieve User object with OID: $oid");
        }
        $groupName = $userData['site'] ?? '';

        // Construct the redirect URL
        if (empty($groupName)) {
            $redirectUrl = "/gui";
        }
        else {
            $redirectUrl = "/user/userList?group=" . $groupName;
        }

        // Construct the autologin URL with the redirect parameter
        $autologinUrl = $whmcsUrl . '/modules/servers/blueonyx/autologin.php' .
            '?hostname=' . urlencode($params['serverhostname']) .
            '&username=' . urlencode($params['username']) .
            '&timestamp=' . time() .
            '&ptoken=' . urlencode(base64_encode($params['password'])) .
            '&token=' . $token .
            '&redirect=' . urlencode($redirectUrl);

        return array(
            'success' => true,
            'redirectTo' => $autologinUrl,
        );
    } catch (Exception $e) {
        logModuleCall(
            'blueonyx',
            __FUNCTION__,
            $params,
            $e->getMessage(),
            $e->getTraceAsString()
        );

        return array(
            'success' => false,
            'errorMsg' => $e->getMessage(),
        );
    }
}

function blueonyx_AdminSingleSignOn(array $params) {
    try {
        $whmcsUrl = $GLOBALS['CONFIG']['SystemURL'];
        $token = md5($params['serverhostname'] . $params['serverusername'] . time());
        $autologinUrl = $whmcsUrl . '/modules/servers/blueonyx/autologin.php' .
            '?hostname=' . urlencode($params['serverhostname']) .
            '&username=' . urlencode($params['serverusername']) .
            '&timestamp=' . time() .
            '&ptoken=' . urlencode(base64_encode($params['serverpassword'])) .
            '&token=' . $token .
            '&redirect=' . urlencode('/vsite/vsiteList');

        return array(
            'success' => true,
            'redirectTo' => $autologinUrl,
        );
    } catch (Exception $e) {
        logModuleCall(
            'blueonyx',
            __FUNCTION__,
            $params,
            $e->getMessage(),
            $e->getTraceAsString()
        );

        return array(
            'success' => false,
            'errorMsg' => $e->getMessage(),
        );
    }
}

function blueonyx_LoginLink(array $params) {
    try {
        $whmcsUrl = $GLOBALS['CONFIG']['SystemURL'];
        $token = md5($params['serverhostname'] . $params['serverusername'] . time());
        $autologinUrl = $whmcsUrl . '/modules/servers/blueonyx/autologin.php' .
            '?hostname=' . urlencode($params['serverhostname']) .
            '&username=' . urlencode($params['serverusername']) .
            '&ptoken=' . urlencode(base64_encode($params['serverpassword'])) .
            '&token=' . $token .
            '&timestamp=' . time() . 
            '&redirect=' . urlencode('/vsite/vsiteList');

        return '<a href="' . htmlspecialchars($autologinUrl) . '" target="_blank">Login to BlueOnyx Control Panel</a>';

        //return array(
        //    'success' => true,
        //    'redirectTo' => $autologinUrl,
        //);
    } catch (Exception $e) {
        logModuleCall(
            'blueonyx',
            __FUNCTION__,
            $params,
            $e->getMessage(),
            $e->getTraceAsString()
        );

        return array(
            'success' => false,
            'errorMsg' => $e->getMessage(),
        );
    }
}

function blueonyx_ClientAreaCustomButtonArray() {
    return [];
}

function blueonyx_buttonOneFunction(array $params) {
    try {
        // Placeholder for custom button logic
    } catch (Exception $e) {
        logModuleCall(
            'provisioningmodule',
            __FUNCTION__,
            $params,
            $e->getMessage(),
            $e->getTraceAsString()
        );
        return $e->getMessage();
    }
    return 'success';
}

function blueonyx_AdminCustomButtonArray() {
    return [ ];
}

function blueonyx_TestConnection(array $params) {
    try {
        require_once __DIR__ . '/CceApiClient.php';

        $client = new \BlueOnyx\CceApiClient(
            $params['serverhostname'],
            $params['serveraccesshash']
        );

        $token = $client->login($params['serverusername'], $params['serverpassword']);
        blueonyx_debug_log('blueonyx_TestConnection', 'login', $token);

        if (!$token) {
            return ['success' => false, 'error' => 'Login failed: No token returned.'];
        }

        $whoami = $client->whoami();
        blueonyx_debug_log('blueonyx_TestConnection', 'whoami', $whoami);

        if ($whoami['success']) {
            return ['success' => true];
        }

        return ['success' => false, 'error' => '"whoami" failed: ' . ($whoami['message'] ?? 'Unknown reason')];
    } catch (Exception $e) {
        blueonyx_debug_log('blueonyx_TestConnection', 'error', $e->getMessage());
        return ['success' => false, 'error' => 'Connection error: ' . $e->getMessage()];
    }
}

function blueonyx_generateAutoLoginUrl($whmcsUrl, $serverHostname, $username, $password, $redirectUrl) {
    $token = md5($serverHostname . $username . time());
    return $whmcsUrl . '/modules/servers/blueonyx/autologin.php' .
        '?hostname=' . urlencode($serverHostname) .
        '&username=' . urlencode($username) .
        '&timestamp=' . time() .
        '&ptoken=' . urlencode(base64_encode($password)) .
        '&token=' . $token .
        '&redirect=' . urlencode($redirectUrl);
}

/*
Copyright (c) 2014-2025 Michael Stauber, SOLARSPEED.NET
Copyright (c) 2014-2025 Team BlueOnyx, BLUEONYX.IT
All Rights Reserved.

1. Redistributions of source code must retain the above copyright 
   notice, this list of conditions and the following disclaimer.

2. Redistributions in binary form must reproduce the above copyright 
   notice, this list of conditions and the following disclaimer in 
   the documentation and/or other materials provided with the 
   distribution.

3. Neither the name of the copyright holder nor the names of its 
   contributors may be used to endorse or promote products derived 
   from this software without specific prior written permission.

THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS 
"AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT 
LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS 
FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE 
COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, 
INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, 
BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; 
LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER 
CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT 
LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN 
ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE 
POSSIBILITY OF SUCH DAMAGE.

You acknowledge that this software is not designed or intended for 
use in the design, construction, operation or maintenance of any 
nuclear facility.
*/
?>