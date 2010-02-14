<?php
/**
 * Curl
 *
 * [Short description here]
 *
 * @copyright (c)2005-2010, WDT Media Corp (http://wdtmedia.net)
 * @author jadb
 */
class Curl {
	/**
	 * Curl resource handle
	 *
	 * @var resource
	 * @access public
	 */
	public $ch = null;
	/**
	 * Current `CURLOPT_COOKIE`
	 *
	 * @var string
	 * @access public
	 */
	public $cookie = null;
	/**
	 * Current `CURLOPT_COOKIEFILE`
	 *
	 * @var string
	 * @access public
	 */
	public $cookieFile = null;
	/**
	 * Current `CURLOPT_COOKIEJAR`
	 *
	 * @var string
	 * @access public
	 */
	public $cookieJar = null;
	/**
	 * Allow cookies or not
	 *
	 * @var bool
	 * @access public
	 */
	public $cookies = false;
	/**
	 * Current `CURLOPT_ENCODING`
	 *
	 * @var string
	 * @access public
	 */
	public $encoding = null;
	/**
	 * Supported "Accept-Encoding: " header types
	 *
	 * @var array
	 * @access public
	 */
	public $encodingTypes = array('identity', 'deflate', 'gzip');
	/**
	 * Current `CURLOPT_FOLLOWLOCATION`
	 *
	 * @var bool
	 * @access public
	 */
	public $followLocation = null;
	/**
	 * Current `CURLOPT_HTTPHEADER`
	 *
	 * @var array
	 * @access public
	 */
	public $httpHeader = null;
	/**
	 * Current `CURLOPT_HTTP_VERSION`
	 *
	 * @var string
	 * @access public
	 */
	public $httpVersion = null;
	/**
	 * Supported HTTP versions
	 *
	 * @var array
	 * @access public
	 */
	public $httpVersions = array(
		'' => CURL_HTTP_VERSION_NONE,
		'1.0' => CURL_HTTP_VERSION_1_0,
		'1.1' => CURL_HTTP_VERSION_1_1,
	);
	/**
	 * Last transfer info
	 *
	 * @var array
	 * @access public
	 */
	public $info = array();
	/**
	 * Current `CURLOPT_MAXREDIRS`
	 *
	 * @var int
	 * @access public
	 */
	public $maxRedirects = null;
	/**
	 * Current `CURLOPT_POSTFIELDS`
	 *
	 * @var string|array
	 * @access public
	 */
	public $postFields = null;
	/**
	 * Current `CURLOPT_REFERER`
	 *
	 * @var string
	 * @access public
	 */
	public $referer = null;
	/**
	 * undocumented variable
	 *
	 * @var string
	 * @access public
	 */
	public $request = null;
	/**
	 * Current request type
	 *
	 * @var string
	 * @access public
	 */
	public $requestType = null;
	/**
	 * Supported HTTP request types
	 *
	 * @var string
	 * @access public
	 */
	public $requestTypes = array('CONNECT', 'DELETE', 'GET', 'POST', 'PUT');
	/**
	 * undocumented variable
	 *
	 * @var string
	 * @access public
	 */
	public $response = null;
	/**
	 * undocumented variable
	 *
	 * @var bool
	 * @access public
	 */
	public $returnHeader = null;
	/**
	 * Current `CURLOPT_SSL_VERIFYHOST`
	 *
	 * @var bool
	 * @access public
	 */
	public $sslVerifyHost = null;
	/**
	 * Current `CURLOPT_SSL_VERIFYPEER`
	 *
	 * @var bool
	 * @access public
	 */
	public $sslVerifyPeer = null;
	/**
	 * Current `CURLOPT_CONNECTTIMEOUT`
	 *
	 * @var int
	 * @access public
	 */
	public $timeout = 60;
	/**
	 * Current URL
	 *
	 * @var string
	 * @access public
	 */
	public $url = null;
	/**
	 * Current `CURLOPT_USERAGENT`
	 *
	 * @var string
	 * @access public
	 */
	public $userAgent = null;
	/**
	 * Constructor
	 *
	 * @param undefined $url
	 * @param undefined $options
	 * @access public
	 */
	public function __construct($url = null, $options = array()) {
		if (!extension_loaded('curl')) {
			if (!dl('curl')) {
				trigger_error('Curl: PHP was not built with --with-curl', E_USER_ERROR);
				return false;
			}
		}

		$this->url = $url;

		$this->connect();

		if (!is_null($this->url)) {
			$this->execute();
		}
	}
	/**
	 * Close connection and free-up resource
	 *
	 * @return void
	 * @access public
	 */
	public function close() {
		curl_close($this->ch);
	}
	/**
	 * Start the curl resource
	 *
	 * @return void
	 * @access public
	 * @todo add CURLOPT_FRESH_CONNECT
	 */
	public function connect() {
		if (!is_resource($this->ch)) {
			$this->ch = is_null($this->url) ? curl_init() : curl_init($this->url);
			if (!is_resource($this->ch)) {
				$this->ch = null;
				return false;
			}
		}
		return $this->ch;
	}
	/**
	 * Last error for current session
	 *
	 * @return string
	 * @access public
	 */
	public function error() {
		$this->lastError = curl_error($this->ch);
		if (empty($this->lastError)) {
			if ((int)$this->info('http_code') >= 400) {
				$this->lastError = $this->info['http_code'];
			}
		}
		return $this->lastError;
	}
	/**
	 * Execute curl
	 *
	 * @return void
	 * @access public
	 */
	public function execute($url = null, $options = array(), $type = 'GET', $ssl = false) {
		$this->info = array();
		$this->lastError = null;
		$this->url = $url;

		foreach ($options as $key => $val) {
			$method = 'set' . str_replace(" ", "", ucwords(str_replace("_", " ", $key)));
			$this->{$method}($val);
		}

		if ($this->requestType != $type) {
			$this->setRequestType($type);
		}

		curl_setopt($this->ch, CURLOPT_URL, $this->url);
		// response as string instead of outputting (which is curl's default)
		curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);

		if (is_null($this->returnHeader)) {
			$this->setReturnHeader(true);
		}

		$this->setSslVerify($ssl);

		$this->response = curl_exec($this->ch);
		$this->error();
		$this->reset($type);
	}
	/**
	 * Execute a GET request
	 *
	 * @return void
	 * @access public
	 */
	public function get($url = null, $options = array()) {
		$this->execute($url, $options);
	}
	/**
	 * undocumented function
	 *
	 * @param undefined $opt
	 * @return void
	 * @access public
	 */
	public function info($opt) {
		if (empty($this->info)) {
			$this->info = curl_getinfo($this->ch);
			if (false === $this->info) {
				trigger_error('', E_USER_ERROR);
				return false;
			}
		}
		if (!array_key_exists($opt, $this->info)) {
			trigger_error('', E_USER_ERROR);
			return false;
		}
		return $this->info[$opt];
	}
	/**
	 * Execute a POST request
	 *
	 * @return void
	 * @access public
	 */
	public function post($url = null, $data = null, $options = array()) {
		if ($this->setPostFields($data)) {
			$this->execute($url, $options, 'POST');
		}
	}
	/**
	 * undocumented function
	 *
	 * @param undefined $type
	 * @return void
	 * @access public
	 */
	public function reset($type) {
		if ('GET' != $type) {
			// reset to default 'GET' type of requests
			curl_setopt($this->ch, CURLOPT_HTTPGET, true);
		}
		// force use of new connection instead of cached one
		curl_setopt($this->ch, CURLOPT_FRESH_CONNECT, true);
	}
	/**
	 * Set `CURLOPT_CONNECTTIMEOUT`
	 *
	 * @param int $secs
	 * @return bool
	 * @access public
	 */
	public function setConnectTimeout($secs) {
		if ($this->timeout != $secs) {
		 	if (curl_setopt($this->ch, CURLOPT_CONNECTTIMEOUT, $secs)) {
				$this->timeout = $secs;
				return true;
			}
			return false;
		}
		return true;
	}
	/**
	 * Set `CURLOPT_COOKIE`, the HTTP request "Cookie: " header
	 *
	 * @param string $cookie
	 * @return bool
	 * @access public
	 */
	public function setCookie($cookie) {
		if ($this->cookie != $cookie) {
			if (curl_setopt($this->ch, CURLOPT_COOKIE, $cookie)) {
				$this->cookie = $cookie;
				return true;
			}
			return false;
		}
		return true;
	}
	/**
	 * Set `CURLOPT_COOKIEFILE`, the name of the file containing the cookie data
	 *
	 * @param string $file
	 * @return bool
	 * @access public
	 */
	public function setCookieFile($file) {
		if ($this->cookieFile != $file) {
			if (curl_setopt($this->ch, CURLOPT_COOKIEFILE, $file)) {
				$this->cookieFile = $file;
				return true;
			}
			return false;
		}
		return true;
	}
	/**
	 * Set `CURLOPT_COOKIEJAR`, the name of the file to save internal cookies
	 * to when the connection closes
	 *
	 * @param string $file
	 * @return bool
	 * @access public
	 */
	public function setCookieJar($file) {
		if ($this->cookieJar != $file) {
			if (curl_setopt($this->ch, CURLOPT_COOKIEJAR, $file)) {
				$this->cookieJar = $file;
				return true;
			}
			return false;
		}
		return true;
	}
	/**
	 * Set `CURLOPT_CUSTOMREQUEST`, just to keep w/ the curl real option names
	 * as function name
	 *
	 * @param string $type
	 * @return bool
	 * @access public
	 */
	public function setCustomRequest($type = 'GET') {
		return $this->setRequestType($type);
	}
	/**
	 * Set `CURLOPT_ENCODING`, the HTTP request "Accept-Encoding: " header to enable
	 * decoding of the response
	 *
	 * @param string $encoding if empty, all supported types (Curl::encodingTypes) are set
	 * @return bool
	 * @access public
	 */
	public function setEncoding($encoding = "gzip") {
		if (!empty($encoding) && !in_array($encoding, $this->encodingTypes)) {
			trigger_error('', E_USER_ERROR);
			return false;
		}

		if ($this->encoding != $encoding) {
			if (curl_setopt($this->ch, CURLOPT_ENCODING, $encoding)) {
				$this->encoding = $encoding;
				return true;
			}
			return false;
		}
		return true;
	}
	/**
	 * Set `CURLOPT_FOLLOWLOCATION`, to follow any "Location: " header
	 * that the server sends as part of the HTTP header
	 *
	 * Note: this is recursive, PHP will follow as many "Location: "
	 * headers that it is sent, unless CURLOPT_MAXREDIRS is set
	 *
	 * @param bool $bool
	 * @return bool
	 * @access public
	 */
	public function setFollowLocation($bool) {
		if ($this->followLocation != $bool) {
			if (curl_setopt($this->ch, CURLOPT_FOLLOWLOCATION, $bool)) {
				$this->followLocation = $bool;
				return true;
			}
			return false;
		}
		return true;
	}
	/**
	 * Set `CURLOPT_HTTPHEADER`, the HTTP request header
	 *
	 * @param array $headers
	 * @return bool
	 * @access public
	 */
	public function setHttpHeader($headers) {
		if ($this->httpHeader != $headers) {
			if (curl_setopt($this->ch, CURLOPT_HTTPHEADER, $headers)) {
				$this->httpHeader = $headers;
				return true;
			}
			return false;
		}
		return true;
	}
	/**
	 * Set `CURL_HTTP_VERSION`
	 *
	 * @param string $version empty, 1.0 or 1.1
	 * @return bool
	 * @access public
	 */
	public function setHttpVersion($version = '') {
		if (!array_key_exists($version, $this->httpVersions)) {
			trigger_error('Curl: invalid HTTP version', E_USER_ERROR);
			return false;
		}
		if ($this->httpVersion != $version) {
			if (curl_setopt($this->ch, CURLOPT_HTTP_VERSION, $this->httpVersions[$version])) {
				$this->httpVersion = $version;
				return true;
			}
			return false;
		}
		return true;
	}
	/**
	 * Set `CURLOPT_MAXREDIRS`, the maximum amount of HTTP redirections to follow
	 *
	 * Note: this will automatically set `CURLOPT_FOLLOWLOCATION` to true
	 *
	 * @param int $max
	 * @return bool
	 * @access public
	 */
	public function setMaxRedirects($max) {
		if ($this->maxRedirects != $max) {
			if ($this->setFollowLocation(true)) {
				if (curl_setopt($this->ch, CURLOPT_MAXREDIRS, $max)) {
					$this->maxRedirects = $max;
					return true;
				}
			}
			return false;
		}
		return true;
	}
	/**
	 * Set `CURLOPT_POSTFIELDS`, the data to post in an HTTP POST request
	 *
	 * To post a file, prepend a filename w/ `@` and use the full path
	 *
	 * @param string|array $data urlencoded string like 'para1=val1&para2=val2&...'
	 *                     or as an array with the field name as key and field
	 *                     data as value (in which case, "Content-Type: " header
	 *                     will be set to multipart/form-data)
	 * @param bool $multipart if FALSE, transforms a $data array into a urlencoded
	 *             string to avoid the "Content-Type: " header being changed
	 * @return bool
	 * @access public
	 */
	public function setPostFields($data, $multipart = true) {
		if ($this->postFields != $data) {
			if (false === $multipart && is_array($data)) {
				$_data = array();
				foreach ($data as $key => $val) {
					$_data[] = $key . '=' . urlencode($val);
				}
				$data = implode('&', $_data);
			}
			if (curl_setopt($this->ch, CURLOPT_POSTFIELDS, $data)) {
				$this->postFields = $data;
				return true;
			}
			return false;
		}
		return true;
	}
	/**
	 * Set `CURLOPT_REFERER`, the HTTP request "Referer: " header
	 *
	 * @param string $referer
	 * @return bool
	 * @access public
	 */
	public function setReferrer($referer) {
		if ($this->referer != $referer) {
			if (curl_setopt($this->ch, CURLOPT_REFERER, $referer)) {
				$this->referer = $referer;
				return true;
			}
			return false;
		}
		return true;
	}
	/**
	 * Set `CURLOPT_CUSTOMREQUEST`, the HTTP request type
	 *
	 * @param string $type supported type (Curl::requestTypes)
	 * @return bool
	 * @access public
	 */
	public function setRequestType($type = 'GET') {
		if ($this->requestType != $type) {
			if (!in_array($type, $this->requestTypes)) {
				$this->lastError = sprintf('un-supported HTTP request type (%s)', $type);
				trigger_error('Curl: ' . $this->lastError, E_USER_ERROR);
				return false;
			}
			if (curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, $type)) {
				$this->requestType = $type;
				return true;
			}
			return false;
		}
		return true;
	}
	/**
	 * Set `CURLOPT_HEADER`, to include or not the HTTP headers in the response
	 *
	 * @param bool $bool
	 * @return bool
	 * @access public
	 */
	public function setReturnHeader($bool) {
		if ($this->returnHeader != $bool) {
			if (curl_setopt($this->ch, CURLOPT_HEADER, $bool)) {
				$this->returnHeader = $bool;
				return true;
			}
			return false;
		}
		return true;
	}
	/**
	 * Set `CURLOPT_USERAGENT`, the HTTP request "User-Agent: " header
	 *
	 * @param string $agent
	 * @return bool
	 * @access public
	 */
	public function setUserAgent($agent) {
		if ($this->userAgent != $agent) {
		 	if (curl_setopt($this->ch, CURLOPT_USERAGENT, $agent)) {
				$this->userAgent = $agent;
				return true;
			}
			return false;
		}
		return true;
	}
	/**
	 * undocumented function
	 *
	 * @param bool $bool
	 * @return bool
	 * @access public
	 */
	public function setSslVerify($bool) {
		if ($this->setSslVerifyHost($bool)) {
			return $this->setSslVerifyPeer($bool);
		}
		return false;
	}
	/**
	 * Set `CURLOPT_SSL_VERIFYHOST`
	 *
	 * @param bool $bool
	 * @return bool
	 * @access public
	 */
	public function setSslVerifyHost($bool) {
		if ($this->sslVerifyHost != $bool) {
			if (curl_setopt($this->ch, CURLOPT_SSL_VERIFYHOST, $bool)) {
				$this->sslVerifyHost = $bool;
				return true;
			}
			return false;
		}
		return true;
	}
	/**
	 * Set `CURLOPT_SSL_VERIFYPEER`
	 *
	 * @param bool $bool
	 * @return bool
	 * @access public
	 */
	public function setSslVerifyPeer($bool) {
		if ($this->sslVerifyPeer != $bool) {
			if (curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, $bool)) {
				$this->sslVerifyPeer = $bool;
				return true;
			}
			return false;
		}
		return true;
	}
}
?>