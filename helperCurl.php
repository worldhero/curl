<?php
namespace Helper\Crm;
class helperCurl
{
    const SEND_METHOD_GET = 'GET';
    const SEND_METHOD_POST = 'POST';
    const SEND_METHOD_JSON = 'POST/JSON';

	private $userAgent='Keeper CRM Bot';
	private $fileCookies;
	private $referer = null;
	private $timeout = 10;
	private $debug = FALSE;
	private $proxy = FALSE;
	private $proxyAuth = FALSE;
	private $curl;

	public function __construct()
	{
        $this->fileCookies = 'tmpCurl' . DIRECTORY_SEPARATOR;
	}

	public function setProxy ($ip, $port, $login = NULL, $password = NUll) {
		$this->proxy = $ip . ':' . $port;
		if (!empty($login)) {
			$this->proxyAuth = $login;
			if (!empty($password))
				$this->proxyAuth .= ':' . $password;
		}
	}
	public function disableProxy () {
		$this->proxy = false;
		$this->proxyAuth = false;
	}
	public function setTimeout ($second) {
		$second = (int) $second;
		$this->timeout = $second;
	}
	public function setReferer ($url) {
		$this->referer = (string) $url;
	}
	public function setUserAgent ($userAgent) {
		$this->userAgent = (string) $userAgent;
	}
	public function setCookies ($file) {
		if (!is_dir($this->fileCookies)) {
			@mkdir($this->fileCookies, 0755);
		}
		$this->fileCookies .= (string) $file;
	}
	public function enableDebug () {
		$this->debug = TRUE;
	}
	public function disableDebug () {
		$this->debug = false;
	}

	public function curl($url, $data=array(), $post='GET', array $header = array()) {
		if ($this->debug ) {
			$this->eh('URL: ' . $url);
		}
		if (!empty($this->curl)) {
			curl_close($this->curl);
		}
		$this->curl = curl_init();
		curl_setopt( $this->curl, CURLOPT_HEADER, 0);
		curl_setopt( $this->curl, CURLOPT_USERAGENT, $this->userAgent);
		if (!empty($data)) {
			$params=http_build_query($data);
			if ($post==self::SEND_METHOD_GET) {
				$url.='?'.$params;
			}
		}
		if ($post==self::SEND_METHOD_POST) {
			curl_setopt($this->curl, CURLOPT_POST, 1);
			if (empty($data)) $data = array();
			curl_setopt( $this->curl, CURLOPT_POSTFIELDS, http_build_query( $data )  );
		}
		if ($post==self::SEND_METHOD_JSON) {
			curl_setopt($this->curl, CURLOPT_POST, 1);
			if (empty($data)) $data = array();
			$dataString = json_encode($data);
			$header[]='Content-Type: application/json';
			$header[]='Content-Length: ' . strlen($dataString);
			curl_setopt( $this->curl, CURLOPT_POSTFIELDS, $dataString );
		}

		if (!empty($this->timeout)) {
			curl_setopt( $this->curl, CURLOPT_TIMEOUT, $this->timeout);
			curl_setopt( $this->curl, CURLOPT_CONNECTTIMEOUT, $this->timeout);
		}
		if (!empty($this->referer))
			curl_setopt($this->curl,CURLOPT_REFERER, $this->referer);
		if (!empty($this->fileCookies)) {
			curl_setopt($this->curl, CURLOPT_COOKIEJAR, $this->fileCookies);
			curl_setopt($this->curl, CURLOPT_COOKIEFILE, $this->fileCookies);
		}
		if (!empty($this->proxy)) {
			//Указываем к какому прокси подключаемся и передаем логин-пароль
			curl_setopt($this->curl, CURLOPT_PROXY, $this->proxy );
			if (!empty($this->proxyAuth))
				curl_setopt($this->curl, CURLOPT_PROXYUSERPWD, $this->proxyAuth);
		}
		curl_setopt($this->curl, CURLOPT_HTTPHEADER, $header);
		curl_setopt($this->curl, CURLOPT_URL, $url);

		// возвращать результат работы
		curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, 1);
		// не проверять SSL сертификат
		curl_setopt($this->curl, CURLOPT_SSL_VERIFYPEER, 0);
		// не проверять Host SSL сертификата
		curl_setopt($this->curl, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($this->curl, CURLOPT_FOLLOWLOCATION, true);

		if (!$ar = curl_exec($this->curl)) {
			$ar=false;
		}
		if ($this->debug ) 	var_dump($ar);
		if ($this->debug ) {
			$this->eh('CODE RETURN CURL: ' . curl_getinfo($this->curl, CURLINFO_HTTP_CODE));
		}

		if (curl_getinfo($this->curl, CURLINFO_HTTP_CODE) != 200)
			return FALSE;

		return $ar;
	}

	protected function eh ($text) {
		echo $text."<br> \n ";
	}
}