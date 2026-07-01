<?php

/**
 * BlueOnyx API CceApiClient.php
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

namespace BlueOnyx;

class CceApiClient {
    private $hostname;
    private $clientSecret;
    private $sessionToken = '';
    private $username = null;
    private $curlHandle = null;
    private $isAuthenticating = false;
    private $tokenExpires = null;
    public $DEBUG = true;

    public function __construct($hostname, $clientSecret) {
        $this->hostname = $hostname;
        $this->clientSecret = trim(preg_replace('/\s+/', '', $clientSecret));
        $this->curlHandle = null;
        $this->sessionToken = null;
    }

    public function __destruct() {
        if ($this->curlHandle !== null) {
            if ($this->sessionToken) {
                try {
                    $this->endkey();
                    $this->log("Session ended with ENDKEY");
                } catch (\Exception $e) {
                    $this->log("Failed to end session: " . $e->getMessage());
                }
            }
            curl_close($this->curlHandle);
            $this->curlHandle = null;
        }
    }

    private function getApiUrl() {
        return "https://" . $this->hostname . ":9092/v2/cce";
    }

    private function log($msg) {
        if ($this->DEBUG) {
            error_log("[CceApiClient] " . $msg);
        }
    }

    private function curlRequest(array $payload) {
        if ($this->isTokenExpired() && !$this->isAuthenticating && $payload['cmd'] !== 'LOGIN') {
            $this->log("Token expired. Reauthenticating...");
            $this->isAuthenticating = true;
            $this->login($this->username);
            $this->isAuthenticating = false;
        }

        if ($this->curlHandle === null) {
            $this->curlHandle = curl_init();
            curl_setopt($this->curlHandle, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($this->curlHandle, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($this->curlHandle, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($this->curlHandle, CURLOPT_FORBID_REUSE, false);
            curl_setopt($this->curlHandle, CURLOPT_FRESH_CONNECT, false);
            curl_setopt($this->curlHandle, CURLOPT_POST, true);
            // Add verbose logging for debugging
            curl_setopt($this->curlHandle, CURLOPT_VERBOSE, true);
            $verboseLog = fopen('php://temp', 'rw+');
            if ($verboseLog === false) {
                $this->log("Failed to open verbose log stream for cURL. Proceeding without verbose logging.");
                $verboseLog = null;
            } else {
                curl_setopt($this->curlHandle, CURLOPT_STDERR, $verboseLog);
            }
        }

        $skipTokenInclusion = ['LOGIN'];

        if (!in_array($payload['cmd'], $skipTokenInclusion) && !empty($this->sessionToken)) {
            $payload['token'] = $this->sessionToken;
        }

        $headers = [
            'Content-Type: application/json',
            'X-Client-Secret: ' . $this->clientSecret
        ];

        $this->log("Request payload for {$payload['cmd']}: " . json_encode($payload));
        curl_setopt($this->curlHandle, CURLOPT_URL, $this->getApiUrl());
        curl_setopt($this->curlHandle, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($this->curlHandle, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($this->curlHandle);
        $error = curl_error($this->curlHandle);

        // Log verbose cURL output if available
        $verboseOutput = '';
        if (is_resource($verboseLog)) {
            rewind($verboseLog);
            $verboseOutput = stream_get_contents($verboseLog);
            $this->log("cURL verbose output for {$payload['cmd']}: " . $verboseOutput);
            fclose($verboseLog);
        }

        if ($response === false) {
            throw new \Exception("cURL error for {$payload['cmd']}: " . $error);
        }

        $this->log("Raw response for {$payload['cmd']}: " . $response);

        $decoded = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception("Invalid JSON response from API for {$payload['cmd']}: " . $response);
        }

        if (isset($decoded['error']) || (isset($decoded['success']) && !$decoded['success'])) {
            $message = $decoded['message'] ?? $decoded['error'] ?? 'Unknown API error';
            if ($payload['cmd'] !== 'LOGIN' && strpos($message, 'Invalid or expired token') !== false && !$this->isAuthenticating) {
                $this->log("Token invalid or expired for {$payload['cmd']}. Reauthenticating...");
                $this->isAuthenticating = true;
                try {
                    $this->login($this->username);
                    $payload['token'] = $this->sessionToken;
                    curl_setopt($this->curlHandle, CURLOPT_POSTFIELDS, json_encode($payload));
                    $response = curl_exec($this->curlHandle);
                    $error = curl_error($this->curlHandle);
                    if ($response === false) {
                        throw new \Exception("cURL error after reauthentication for {$payload['cmd']}: " . $error);
                    }
                    $this->log("Raw response after reauthentication for {$payload['cmd']}: " . $response);
                    $decoded = json_decode($response, true);
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        throw new \Exception("Invalid JSON response after reauthentication for {$payload['cmd']}: " . $response);
                    }
                    if (isset($decoded['error']) || (isset($decoded['success']) && !$decoded['success'])) {
                        $message = $decoded['message'] ?? $decoded['error'] ?? 'Unknown API error';
                        throw new \Exception("API error after reauthentication for {$payload['cmd']}: " . $message);
                    }
                } catch (\Exception $e) {
                    $this->isAuthenticating = false;
                    throw new \Exception("Reauthentication failed for {$payload['cmd']}: " . $e->getMessage());
                }
                $this->isAuthenticating = false;
                // Log verbose output after reauthentication if available
                $verboseOutput = '';
                if (is_resource($verboseLog)) {
                    rewind($verboseLog);
                    $verboseOutput = stream_get_contents($verboseLog);
                    $this->log("cURL verbose output after reauthentication for {$payload['cmd']}: " . $verboseOutput);
                    fclose($verboseLog);
                }
            } else {
                throw new \Exception("API error for {$payload['cmd']}: " . $message);
            }
        }

        return $decoded;
    }

    public function login($username = null, $password = null) {
        $payload = ['cmd' => 'LOGIN'];
        if ($username) {
            $payload['username'] = $username;
            if ($password) {
                $payload['password'] = $password;
            }
        }
        $response = $this->curlRequest($payload);
        $this->log("login() response: " . json_encode($response));
        if (isset($response['data']['token'])) {
            $this->sessionToken = $response['data']['token'];
            $this->username = $username;
            $this->tokenExpires = isset($response['data']['expires']) ? strtotime($response['data']['expires']) : null;
            return $this->sessionToken;
        } elseif (isset($response['data']['sessionId'])) {
            $this->sessionToken = $response['data']['sessionId'];
            $this->username = $username;
            $this->tokenExpires = isset($response['data']['expires']) ? strtotime($response['data']['expires']) : null;
            return $this->sessionToken;
        }
        throw new \Exception("Login failed: " . ($response['message'] ?? 'No token or sessionId received.'));
    }

    private function isTokenExpired() {
        return $this->tokenExpires && time() >= $this->tokenExpires;
    }

    public function create($class, $vars = []) {
        $params = [];
        foreach ($vars as $key => $value) {
            $escapedValue = str_replace('"', '\"', $value);
            $params[] = "$key=\"$escapedValue\"";
        }
        $paramString = implode(' ', $params);
        $cmd = "CREATE $class $paramString";

        $response = $this->curlRequest([
            'cmd' => $cmd,
            'token' => $this->token
        ]);
        $this->log("create() response: " . json_encode($response));

        if (isset($response['status']) && $response['status'] == 201 && !empty($response['data']['oidlist'])) {
            return $response['data']['oidlist'];
        }
        throw new \Exception("Create failed: " . ($response['message'] ?? 'Unknown error'));
    }

    public function destroy($oid) {
        $cmd = "DESTROY $oid";
        $response = $this->curlRequest([
            'cmd' => $cmd,
            'token' => $this->token
        ]);
        $this->log("destroy() response: " . json_encode($response));

        if (isset($response['status']) && $response['status'] == 201) {
            return true;
        }
        throw new \Exception("Destroy failed: " . ($response['message'] ?? 'Unknown error'));
    }

    public function set($oid, $namespace, $vars = []) {
        $params = [];
        foreach ($vars as $key => $value) {
            $escapedValue = str_replace('"', '\"', $value);
            $params[] = "\"$key\" = \"$escapedValue\"";
        }
        $paramString = implode(' ', $params);

        // Only include the dot and namespace if $namespace is non-empty
        $namespacePart = ($namespace !== '') ? " . $namespace" : '';
        $cmd = "SET $oid$namespacePart $paramString";

        $response = $this->curlRequest([
            'cmd' => $cmd,
            'token' => $this->token
        ]);
        $this->log("set() response: " . json_encode($response));

        if (isset($response['status']) && $response['status'] == 201) {
            return 1;
        }
        throw new \Exception("Set failed: " . ($response['message'] ?? 'Unknown error'));
    }

    public function whoami() {
        $response = $this->curlRequest([
            'cmd' => 'WHOAMI'
        ]);
        $this->log("whoami() response: " . json_encode($response));
        if (isset($response['data']['oid']) && is_numeric($response['data']['oid']) && intval($response['data']['oid']) !== -1) {
            return [
                'success' => true,
                'oid' => intval($response['data']['oid'])
            ];
        }
        return [
            'success' => false,
            'message' => 'Invalid or missing OID in whoami response: ' . json_encode($response)
        ];
    }

    public function bye() {
        $response = $this->curlRequest([
            'cmd' => 'BYE'
        ]);
        $this->log("bye() response: " . json_encode($response));
        return isset($response['status']) && in_array($response['status'], [200, 201]);
    }

    public function endkey() {
        $response = $this->curlRequest([
            'cmd' => 'ENDKEY'
        ]);
        $this->log("endkey() response: " . json_encode($response));
        return isset($response['status']) && in_array($response['status'], [200, 201]);
    }

    public function findx($class, $args = []) {
        // Construct the cmd string with args
        $argString = '';
        if (!empty($args)) {
            $params = [];
            foreach ($args as $key => $value) {
                $escapedValue = str_replace('"', '\"', $value);
                $params[] = "$key=\"{$escapedValue}\"";
            }
            $argString = implode(' ', $params);
        }
        $cmd = "FIND $class $argString";

        $response = $this->curlRequest([
            'cmd' => $cmd,
            'token' => $this->token
        ]);
        $this->log("findx() response: " . json_encode($response));

        if (isset($response['status']) && $response['status'] == 201 && !empty($response['data']['oidlist'])) {
            return $response['data']['oidlist'];
        }
        return [];
    }

    public function get($oid, $namespace = "") {
        $namespacePart = ($namespace !== "") ? " . $namespace" : "";
        $cmd = "GET $oid$namespacePart";

        $response = $this->curlRequest([
            'cmd' => $cmd,
            'token' => $this->token
        ]);
        $this->log("get() response: " . json_encode($response));

        if (isset($response['status']) && $response['status'] === 201 && !empty($response['data'])) {
            // Check if 'DATA' key exists, otherwise use the data directly
            $data = isset($response['data']['DATA']) ? $response['data']['DATA'] : $response['data'];
            if (isset($data['oid'])) {
                $data['OID'] = $data['oid'];
                unset($data['oid']);
            }
            $data['NAMESPACE'] = $namespace;
            return $data;
        }
        return -1;
    }

    public function getObject($class, $args = [], $namespace = null) {
        if (!isset($args['oid'])) {
            throw new \Exception("OID is required for getObject");
        }
        $oid = $args['oid'];
        // Use GET <oid> command as per API expectations
        $cmd = "GET $oid";

        $payload = [
            'cmd' => $cmd,
            'token' => $this->token
        ];
        // If a namespace is specified, include it in the command
        if ($namespace !== null && $namespace !== '') {
            $cmd = "GET $oid . $namespace";
            $payload['cmd'] = $cmd;
        }

        $response = $this->curlRequest($payload);
        $this->log("getObject() response: " . json_encode($response));

        if (isset($response['status']) && $response['status'] === 201 && !empty($response['data'])) {
            $data = $response['data'];
            // Normalize field names (e.g., oid to OID)
            if (isset($data['oid'])) {
                $data['OID'] = $data['oid'];
                unset($data['oid']);
            }
            // Add NAMESPACE to the response
            $data['NAMESPACE'] = $namespace ?? '';
            return $data;
        }
        throw new \Exception("Failed to retrieve object: " . ($response['message'] ?? 'Unknown error'));
    }

    public function getAll($class, $vars = []) {
        $response = $this->curlRequest([
            'cmd' => 'GETALL',
            'class' => $class,
            'vars' => $vars
        ]);
        $this->log("getAll() response: " . json_encode($response));
        return $response['data']['objects'] ?? [];
    }

    public function names($oid) {
        $response = $this->curlRequest([
            'cmd' => 'NAMES',
            'oid' => $oid
        ]);
        $this->log("names() response: " . json_encode($response));
        return $response['data']['namespaces'] ?? [];
    }

    public function classes() {
        $response = $this->curlRequest([
            'cmd' => 'CLASSES'
        ]);
        $this->log("classes() response: " . json_encode($response));
        return $response['data'] ?? [];
    }

    public function ping() {
        $response = $this->curlRequest([
            'cmd' => 'PING'
        ]);
        $this->log("ping() response: " . json_encode($response));
        return isset($response['status']) && in_array($response['status'], [200, 202]);
    }
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