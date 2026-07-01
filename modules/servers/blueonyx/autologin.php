<?php

/**
 * BlueOnyx API autologin.php
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
 */

// Validate parameters
$serverHostname = isset($_GET['hostname']) ? $_GET['hostname'] : '';
$serverUsername = isset($_GET['username']) ? $_GET['username'] : '';
$serverPassword = isset($_GET['ptoken']) ? $_GET['ptoken'] : '';
$token = isset($_GET['token']) ? $_GET['token'] : '';
$timestamp = isset($_GET['timestamp']) ? (int)$_GET['timestamp'] : 0;
$redirectUrl = isset($_GET['redirect']) ? $_GET['redirect'] : '';

if (!$serverHostname || !$serverUsername || !$serverPassword || !$token || !$timestamp) {
    exit('Missing required parameters');
}

// Validate token
$maxAge = 300; // 5 minutes
$timeDiff = abs(time() - $timestamp);
$expectedToken = md5($serverHostname . $serverUsername . $timestamp);

if ($timeDiff > $maxAge || $token !== $expectedToken) {
    exit('Invalid or expired token');
}

// Decode the base64-encoded password
$decodedPassword = base64_decode($serverPassword);
if ($decodedPassword === false) {
    exit('Invalid password encoding');
}

// Construct the form action URL dynamically
$formAction = "https://{$serverHostname}:81/api/apilogin";

// Use the provided redirect URL if not empty; otherwise, fall back to default
$redirectTarget = !empty($redirectUrl) ? $redirectUrl : '/gui';

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

// Generate the auto-login form
?>
<!DOCTYPE html>
<html>
<head>
    <title>BlueOnyx Auto-Login</title>
</head>
<body>
    <form id="autologin-form" action="<?php echo htmlspecialchars($formAction); ?>" method="post">
        <input type="hidden" name="redirect_target" value="<?php echo htmlspecialchars($redirectTarget); ?>" />
        <input type="hidden" name="username_field" value="<?php echo htmlspecialchars($serverUsername); ?>" />
        <input type="hidden" name="password_field" value="<?php echo htmlspecialchars($decodedPassword); ?>" />
        <input type="hidden" name="secureConnect" id="yes" value="1" />
        <button type="submit">Login to Control Panel</button>
    </form>
    <p>Redirecting to BlueOnyx Control Panel...</p>
    <script>
        console.log('autologin.php script executing');
        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOMContentLoaded fired, submitting form');
            document.getElementById('autologin-form').submit();
        });
        console.log('Attempting immediate form submission');
        document.getElementById('autologin-form').submit();
    </script>
</body>
</html>