<?php
/**
 * Created by Bruce Mubangwa on 07 /Nov, 2020 @ 7:52
 */

namespace Https\Target;

class Request
{

    private static $cookie = null;
    private static $cookieFile = null;
    private static $curlOpts = array();
    private static $defaultHeaders = array();
    private static $handle = null;
    private static $jsonOpts = array();
    private static $socketTimeout = null;
    private static $verifyPeer = true;
    private static $verifyHost = true;

    private static $auth = array(
        'user' => '',
        'pass' => '',
        'method' => CURLAUTH_BASIC
    );

    private static $proxy = array(
        'port' => false,
        'tunnel' => false,
        'address' => false,
        'type' => CURLPROXY_HTTP,
        'auth' => array(
            'user' => '',
            'pass' => '',
            'method' => CURLAUTH_BASIC
        )
    );

    /**
     * Set JSON decode mode
     *
     * @param bool $assoc When TRUE, returned objects will be converted into associative arrays.
     * @param integer $depth User specified recursion depth.
     * @param integer $options Bitmask of JSON decode options. Currently only JSON_BIGINT_AS_STRING is supported (default is to cast large integers as floats)
     * @return array
     */
    public static function jsonOpts($assoc = false, $depth = 512, $options = 0)
    {
        return self::$jsonOpts = array($assoc, $depth, $options);
    }

    /**
     * Verify SSL peer
     *
     * @param bool $enabled enable SSL verification, by default is true
     * @return bool
     */
    public static function verifyPeer($enabled)
    {
        return self::$verifyPeer = $enabled;
    }

    /**
     * Verify SSL host
     *
     * @param bool $enabled enable SSL host verification, by default is true
     * @return bool
     */
    public static function verifyHost($enabled)
    {
        return self::$verifyHost = $enabled;
    }

    /**
     * Set a timeout
     *
     * @param integer $seconds timeout value in seconds
     * @return integer
     */
    public static function timeout($seconds)
    {
        return self::$socketTimeout = $seconds;
    }

    /**
     * Set default headers to send on every request
     *
     * @param array $headers headers array
     * @return array
     */
    public static function defaultHeaders($headers)
    {
        return self::$defaultHeaders = array_merge(self::$defaultHeaders, $headers);
    }

    /**
     * Clear all the default headers
     */
    public static function clearDefaultHeaders()
    {
        return self::$defaultHeaders = array();
    }

    /**
     * Set curl options to send on every request
     *
     * @param array $options options array
     * @return array
     */
    public static function curlOpts($options)
    {
        return self::mergeCurlOptions(self::$curlOpts, $options);
    }

    /**
     * @param array $existing_options
     * @param array $new_options
     * @return array
     */
    private static function mergeCurlOptions(&$existing_options, $new_options)
    {
        $existing_options = $new_options + $existing_options;
        return $existing_options;
    }

    /**
     * Set a new default header to send on every request
     *
     * @param string $name header name
     * @param string $value header value
     * @return string
     */
    public static function curlOpt($name, $value)
    {
        return self::$curlOpts[$name] = $value;
    }

    /**
     * Clear all the default headers
     */
    public static function clearCurlOpts()
    {
        return self::$curlOpts = array();
    }

    /**
     * Set a Mashape key to send on every request as a header
     * Obtain your Mashape key by browsing one of your Mashape applications on https://www.mashape.com
     *
     * Note: Mashape provides 2 keys for each application: a 'Testing' and a 'Production' one.
     *       Be aware of which key you are using and do not share your Production key.
     *
     * @param string $key Mashape key
     * @return string
     */
    public static function setMashapeKey($key)
    {
        return self::defaultHeader('X-Mashape-Key', $key);
    }

    /**
     * Set a new default header to send on every request
     *
     * @param string $name header name
     * @param string $value header value
     * @return string
     */
    public static function defaultHeader($name, $value)
    {
        return self::$defaultHeaders[$name] = $value;
    }

    /**
     * Set a cookie string for enabling cookie handling
     *
     * @param string $cookie
     */
    public static function cookie($cookie)
    {
        self::$cookie = $cookie;
    }

    /**
     * Set a cookie file path for enabling cookie handling
     *
     * $cookieFile must be a correct path with write permission
     *
     * @param string $cookieFile - path to file for saving cookie
     */
    public static function cookieFile($cookieFile)
    {
        self::$cookieFile = $cookieFile;
    }

    /**
     * Set authentication method to use
     *
     * @param string $username authentication username
     * @param string $password authentication password
     * @param integer $method authentication method
     */
    public static function auth($username = '', $password = '', $method = CURLAUTH_BASIC)
    {
        self::$auth['user'] = $username;
        self::$auth['pass'] = $password;
        self::$auth['method'] = $method;
    }

    /**
     * Set proxy to use
     *
     * @param string $address proxy address
     * @param integer $port proxy port
     * @param integer $type (Available options for this are CURLPROXY_HTTP, CURLPROXY_HTTP_1_0 CURLPROXY_SOCKS4, CURLPROXY_SOCKS5, CURLPROXY_SOCKS4A and CURLPROXY_SOCKS5_HOSTNAME)
     * @param bool $tunnel enable/disable tunneling
     */
    public static function proxy($address, $port = 1080, $type = CURLPROXY_HTTP, $tunnel = false)
    {
        self::$proxy['type'] = $type;
        self::$proxy['port'] = $port;
        self::$proxy['tunnel'] = $tunnel;
        self::$proxy['address'] = $address;
    }

    /**
     * Set proxy authentication method to use
     *
     * @param string $username authentication username
     * @param string $password authentication password
     * @param integer $method authentication method
     */
    public static function proxyAuth($username = '', $password = '', $method = CURLAUTH_BASIC)
    {
        self::$proxy['auth']['user'] = $username;
        self::$proxy['auth']['pass'] = $password;
        self::$proxy['auth']['method'] = $method;
    }

    /**
     * Send a GET request to a URL
     *
     * @param string $url URL to send the GET request to
     * @param array $headers additional headers to send
     * @param mixed $parameters parameters to send in the querystring
     * @param string $username Authentication username (deprecated)
     * @param string $password Authentication password (deprecated)
     * @return Response
     */
    public static function get($url, $headers = array(), $parameters = null, $username = null, $password = null)
    {
        return self::send(Method::GET, $url, $parameters, $headers, $username, $password);
    }

    /**
     * Send a cURL request
     * @param \Http\Method|string $method HTTP method to use
     * @param string $url URL to send the request to
     * @param mixed $body request body
     * @param array $headers additional headers to send
     * @param string $username Authentication username (deprecated)
     * @param string $password Authentication password (deprecated)
     * @return Response
     * @throws \Http\Exception if a cURL error occurs
     */
    public static function send($method, $url, $body = null, $headers = [], $username = null, $password = null): Response
    {
        self::$handle = curl_init();

        $curl_base_options = [
            CURLOPT_URL => self::encodeUrl($url),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_HTTPHEADER => self::getFormattedHeaders($headers),
            CURLOPT_HEADER => true,
            CURLOPT_SSL_VERIFYPEER => self::$verifyPeer,
            CURLOPT_SSL_VERIFYHOST => self::$verifyHost === false ? 0 : 2,
            CURLOPT_ENCODING => '',
            CURLOPT_CAINFO => __DIR__ . '/cacert.pem'
        ];

        if ($method === Method::POST) {
            $curl_base_options[CURLOPT_POST] = true;
            $curl_base_options[CURLOPT_POSTFIELDS] = $body;
        } elseif ($method !== Method::GET) {
            $curl_base_options[CURLOPT_CUSTOMREQUEST] = $method;
            $curl_base_options[CURLOPT_POSTFIELDS] = $body;
        } elseif (is_array($body)) {
            $query = http_build_query(self::buildHTTPCurlQuery($body));
            $url .= (strpos($url, '?') !== false ? '&' : '?') . urldecode($query);
        }

        curl_setopt_array(self::$handle, self::mergeCurlOptions($curl_base_options, self::$curlOpts));

        if (self::$socketTimeout !== null) {
            curl_setopt(self::$handle, CURLOPT_TIMEOUT, self::$socketTimeout);
        }

        if (self::$cookie) {
            curl_setopt(self::$handle, CURLOPT_COOKIE, self::$cookie);
        }

        if (self::$cookieFile) {
            curl_setopt(self::$handle, CURLOPT_COOKIEFILE, self::$cookieFile);
            curl_setopt(self::$handle, CURLOPT_COOKIEJAR, self::$cookieFile);
        }

        if (!empty($username)) {
            curl_setopt_array(self::$handle, [
                CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
                CURLOPT_USERPWD => $username . ':' . $password
            ]);
        }

        if (!empty(self::$auth['user'])) {
            curl_setopt_array(self::$handle, [
                CURLOPT_HTTPAUTH => self::$auth['method'],
                CURLOPT_USERPWD => self::$auth['user'] . ':' . self::$auth['pass']
            ]);
        }

        if (self::$proxy['address'] !== false) {
            curl_setopt_array(self::$handle, [
                CURLOPT_PROXYTYPE => self::$proxy['type'],
                CURLOPT_PROXY => self::$proxy['address'],
                CURLOPT_PROXYPORT => self::$proxy['port'],
                CURLOPT_HTTPPROXYTUNNEL => self::$proxy['tunnel'],
                CURLOPT_PROXYAUTH => self::$proxy['auth']['method'],
                CURLOPT_PROXYUSERPWD => self::$proxy['auth']['user'] . ':' . self::$proxy['auth']['pass']
            ]);
        }

        $response = curl_exec(self::$handle);
        $error = curl_error(self::$handle);
        $info = self::getInfo();

        if ($error) {
            throw new Exception($error);
        }

        $header_size = $info['header_size'];
        $header = substr($response, 0, $header_size);
        $body = substr($response, $header_size);
        $httpCode = $info['http_code'];

        return new Response($httpCode, $body, $header, self::$jsonOpts);
    }

    /**
     * Ensure that a URL is encoded and safe to use with cURL
     * @param string $url URL to encode
     * @return string
     */
    private static function encodeUrl($url)
    {
        $url_parsed = parse_url($url);

        $scheme = $url_parsed['scheme'] . '://';
        $host = $url_parsed['host'];
        $port = (isset($url_parsed['port']) ? $url_parsed['port'] : null);
        $path = (isset($url_parsed['path']) ? $url_parsed['path'] : null);
        $query = (isset($url_parsed['query']) ? $url_parsed['query'] : null);

        if ($query !== null) {
            $query = '?' . http_build_query(self::getArrayFromQuerystring($query));
        }

        if ($port) {
            $port = ':' . $port;
        }

        $result = $scheme . $host . $port . $path . $query;
        return $result;
    }

    private static function getArrayFromQuerystring($query)
    {
        $query = preg_replace_callback('/(?:^|(?<=&))[^=[]+/', function ($match) {
            return bin2hex(urldecode($match[0]));
        }, $query);

        parse_str($query, $values);

        return array_combine(array_map('hex2bin', array_keys($values)), $values);
    }

    public static function getFormattedHeaders($headers)
    {
        $formattedHeaders = [];
        $combinedHeaders = self::$defaultHeaders + (array)$headers;
        foreach ($combinedHeaders as $key => $val) {
            $formattedHeaders[] = self::getHeaderString($key, $val);
        }
        if (!isset($combinedHeaders['user-agent'])) {
            $formattedHeaders[] = 'user-agent: Litebase-httpsClient/2.0';
        }
        if (!isset($combinedHeaders['expect'])) {
            $formattedHeaders[] = 'expect:';
        }
        return $formattedHeaders;
    }

    private static function getHeaderString($key, $val)
    {
        $key = strtolower(trim($key));
        return $key . ': ' . $val;
    }

    /**
     * This function is useful for serializing multidimensional arrays, and avoid getting
     * the 'Array to string conversion' notice
     * @param array|object $data array to flatten.
     * @param bool|string $parent parent key or false if no parent
     * @return array
     */
    public static function buildHTTPCurlQuery($data)
    {
        $result = [];
        $stack = [[$data, false]];

        while (!empty($stack)) {
            [$currentData, $parent] = array_pop($stack);

            if (is_object($currentData)) {
                $currentData = (array)$currentData;
            }

            foreach ($currentData as $key => $value) {
                $new_key = $parent ? sprintf('%s[%s]', $parent, $key) : $key;

                if (is_array($value) || is_object($value)) {
                    $stack[] = [$value, $new_key];
                } else {
                    $result[$new_key] = $value;
                }
            }
        }

        return $result;
    }

    public static function getInfo($opt = false)
    {
        return curl_getinfo(self::$handle, $opt);
    }

    /**
     * Send a HEAD request to a URL
     * @param string $url URL to send the HEAD request to
     * @param array $headers additional headers to send
     * @param mixed $parameters parameters to send in the querystring
     * @param string $username Basic Authentication username (deprecated)
     * @param string $password Basic Authentication password (deprecated)
     * @return Response
     */
    public static function head($url, $headers = array(), $parameters = null, $username = null, $password = null)
    {
        return self::send(Method::HEAD, $url, $parameters, $headers, $username, $password);
    }

    /**
     * Send a OPTIONS request to a URL
     * @param string $url URL to send the OPTIONS request to
     * @param array $headers additional headers to send
     * @param mixed $parameters parameters to send in the querystring
     * @param string $username Basic Authentication username
     * @param string $password Basic Authentication password
     * @return Response
     */
    public static function options($url, $headers = array(), $parameters = null, $username = null, $password = null)
    {
        return self::send(Method::OPTIONS, $url, $parameters, $headers, $username, $password);
    }

    /**
     * Send a CONNECT request to a URL
     * @param string $url URL to send the CONNECT request to
     * @param array $headers additional headers to send
     * @param mixed $parameters parameters to send in the querystring
     * @param string $username Basic Authentication username (deprecated)
     * @param string $password Basic Authentication password (deprecated)
     * @return Response
     */
    public static function connect($url, $headers = array(), $parameters = null, $username = null, $password = null)
    {
        return self::send(Method::CONNECT, $url, $parameters, $headers, $username, $password);
    }

    /**
     * Send POST request to a URL
     * @param string $url URL to send the POST request to
     * @param array $headers additional headers to send
     * @param mixed $body POST body data
     * @param string $username Basic Authentication username (deprecated)
     * @param string $password Basic Authentication password (deprecated)
     * @return Response response
     */
    public static function post($url, $headers = array(), $body = null, $username = null, $password = null)
    {
        return self::send(Method::POST, $url, $body, $headers, $username, $password);
    }

    /**
     * Send DELETE request to a URL
     * @param string $url URL to send the DELETE request to
     * @param array $headers additional headers to send
     * @param mixed $body DELETE body data
     * @param string $username Basic Authentication username (deprecated)
     * @param string $password Basic Authentication password (deprecated)
     * @return Response
     */
    public static function delete($url, $headers = array(), $body = null, $username = null, $password = null)
    {
        return self::send(Method::DELETE, $url, $body, $headers, $username, $password);
    }

    /**
     * Send PUT request to a URL
     * @param string $url URL to send the PUT request to
     * @param array $headers additional headers to send
     * @param mixed $body PUT body data
     * @param string $username Basic Authentication username (deprecated)
     * @param string $password Basic Authentication password (deprecated)
     * @return Response
     */
    public static function put($url, $headers = array(), $body = null, $username = null, $password = null)
    {
        return self::send(Method::PUT, $url, $body, $headers, $username, $password);
    }

    /**
     * Send PATCH request to a URL
     * @param string $url URL to send the PATCH request to
     * @param array $headers additional headers to send
     * @param mixed $body PATCH body data
     * @param string $username Basic Authentication username (deprecated)
     * @param string $password Basic Authentication password (deprecated)
     * @return Response
     */
    public static function patch($url, $headers = array(), $body = null, $username = null, $password = null)
    {
        return self::send(Method::PATCH, $url, $body, $headers, $username, $password);
    }

    /**
     * Send TRACE request to a URL
     * @param string $url URL to send the TRACE request to
     * @param array $headers additional headers to send
     * @param mixed $body TRACE body data
     * @param string $username Basic Authentication username (deprecated)
     * @param string $password Basic Authentication password (deprecated)
     * @return Response
     */
    public static function trace($url, $headers = array(), $body = null, $username = null, $password = null)
    {
        return self::send(Method::TRACE, $url, $body, $headers, $username, $password);
    }

    public static function getCurlHandle()
    {
        return self::$handle;
    }
}