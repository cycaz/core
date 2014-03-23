<?php

class TMC_Helper_Security {

	protected $_xssHash			= '';

	protected $_csrfHash			= '';

	protected $_csrfExpire			= 7200;
        
        protected $_secureCookie                = FALSE;
        
        protected $_cookiePath                  = '';
        
        protected $_cookieDomain                = '';

	protected $_csrfTokenName		= '_csrf_token';

	protected $_csrfCookieName	= 'tmc_csrf_token';
        
        protected $_charset                     ='utf-8';
        
	protected $_blockString = array(
		'document.cookie'	=> '[removed]',
		'document.write'	=> '[removed]',
		'.parentNode'		=> '[removed]',
		'.innerHTML'		=> '[removed]',
		'window.location'	=> '[removed]',
		'-moz-binding'		=> '[removed]',
		'<!--'				=> '&lt;!--',
		'-->'				=> '--&gt;',
		'<![CDATA['			=> '&lt;![CDATA[',
		'<comment>'			=> '&lt;comment&gt;'
	);

	protected $_blockRegex = array(
		'javascript\s*:',
		'expression\s*(\(|&\#40;)', // CSS and IE
		'vbscript\s*:', // IE, surprise!
		'Redirect\s+302',
		"([\"'])?data\s*:[^\\1]*?base64[^\\1]*?,[^\\1]*?\\1?"
	);

	public function __construct()
        {
        }

	public function verifyCSRF()
	{
		if (strtoupper($_SERVER['REQUEST_METHOD']) !== 'POST')
		{
			return $this->setCsrfCookie();
		}
		if ( ! isset($_POST[$this->_csrfTokenName], $_COOKIE[$this->_csrfCookieName]))
		{
			return $this->getCsrfError();
		}
		if ($_POST[$this->_csrfTokenName] != $_COOKIE[$this->_csrfCookieName])
		{
			return $this->getCsrfError();
		}

		unset($_POST[$this->_csrfTokenName]);

		unset($_COOKIE[$this->_csrfCookieName]);
		$this->setCsrfHash();
		$this->setCsrfCookie();

		return $this;
	}


	public function setCsrfCookie()
	{
		$expire = time() + $this->_csrfExpire;
		$secure_cookie = ($this->_secureCookie === TRUE) ? 1 : 0;

		if ($secure_cookie && (empty($_SERVER['HTTPS']) OR strtolower($_SERVER['HTTPS']) === 'off'))
		{
			return FALSE;
		}

		setcookie($this->_csrfCookieName, $this->_csrfHash, $expire, $this->_cookiePath, $this->_cookieDomain, $secure_cookie);

		return $this;
	}

	public function getCsrfError()
	{
		return ('The action you have requested is not allowed.');
	}

	public function getCsrfHash()
	{
		return $this->_csrfHash;
	}

	public function getCsrfTokenName()
	{
		return $this->_csrfTokenName;
	}

	public function cleanXSS($str, $isImage = FALSE)
	{
		if (is_array($str))
		{
			while (list($key) = each($str))
			{
				$str[$key] = $this->cleanXSS($str[$key]);
			}

			return $str;
		}

		$str = $this->cleanString($str);

		$str = $this->validateEntities($str);

		$str = rawurldecode($str);

		$str = preg_replace_callback("/[a-z]+=([\'\"]).*?\\1/si", array($this, 'convertAttribute'), $str);

		$str = preg_replace_callback("/<\w+.*?(?=>|<|$)/si", array($this, 'decodeEntity'), $str);

		$str = $this->cleanString($str);


		if (strpos($str, "\t") !== FALSE)
		{
			$str = str_replace("\t", ' ', $str);
		}

		$converted_string = $str;
		$str = $this->cleanString($str);

		if ($isImage === TRUE)
		{
			$str = preg_replace('/<\?(php)/i', "&lt;?\\1", $str);
		}
		else
		{
			$str = str_replace(array('<?', '?'.'>'),  array('&lt;?', '?&gt;'), $str);
		}

		$words = array(
			'javascript', 'expression', 'vbscript', 'script', 'base64',
			'applet', 'alert', 'document', 'write', 'cookie', 'window'
		);

		foreach ($words as $word)
		{
			$temp = '';

			for ($i = 0, $wordlen = strlen($word); $i < $wordlen; $i++)
			{
				$temp .= substr($word, $i, 1)."\s*";
			}
			$str = preg_replace_callback('#('.substr($temp, 0, -3).')(\W)#is', array($this, 'compactExplodedWords'), $str);
		}

		do
		{
			$original = $str;

			if (preg_match("/<a/i", $str))
			{
				$str = preg_replace_callback("#<a\s+([^>]*?)(>|$)#si", array($this, 'removeJsLink'), $str);
			}

			if (preg_match("/<img/i", $str))
			{
				$str = preg_replace_callback("#<img\s+([^>]*?)(\s?/?>|$)#si", array($this, 'removeJsImg'), $str);
			}

			if (preg_match("/script/i", $str) OR preg_match("/xss/i", $str))
			{
				$str = preg_replace("#<(/*)(script|xss)(.*?)\>#si", '[removed]', $str);
			}
		}
		while($original != $str);

		unset($original);

		$str = $this->removeEvilAttributes($str, $is_image);
		$naughty = 'alert|applet|audio|basefont|base|behavior|bgsound|blink|body|embed|expression|form|frameset|frame|head|html|ilayer|iframe|input|isindex|layer|link|meta|object|plaintext|style|script|textarea|title|video|xml|xss';
		$str = preg_replace_callback('#<(/*\s*)('.$naughty.')([^><]*)([><]*)#is', array($this, 'sanitizeNaughtyHtml'), $str);

		$str = preg_replace('#(alert|cmd|passthru|eval|exec|expression|system|fopen|fsockopen|file|file_get_contents|readfile|unlink)(\s*)\((.*?)\)#si', "\\1\\2&#40;\\3&#41;", $str);


		$str = $this->_cleanString($str);

		if ($isImage === TRUE)
		{
			return ($str == $convertedString) ? TRUE: FALSE;
		}

		#log_message('debug', "XSS Filtering completed");
		return $str;
	}

	public function getXSSHash()
	{
		if ($this->_xssHash == '')
		{
			mt_srand();
			$this->_xssHash = md5(time() + mt_rand(0, 1999999999));
		}

		return $this->_xssHash;
	}


	public function entityDecode($str, $charset='UTF-8')
	{
		if (stristr($str, '&') === FALSE)
		{
			return $str;
		}

		$str = html_entity_decode($str, ENT_COMPAT, $charset);
		$str = preg_replace('~&#x(0*[0-9a-f]{2,5})~ei', 'chr(hexdec("\\1"))', $str);
		return preg_replace('~&#([0-9]{2,4})~e', 'chr(\\1)', $str);
	}

	public function sanitizeFilename($str, $relative_path = FALSE)
	{
		$bad = array(
			"../",
			"<!--",
			"-->",
			"<",
			">",
			"'",
			'"',
			'&',
			'$',
			'#',
			'{',
			'}',
			'[',
			']',
			'=',
			';',
			'?',
			"%20",
			"%22",
			"%3c",		// <
			"%253c",	// <
			"%3e",		// >
			"%0e",		// >
			"%28",		// (
			"%29",		// )
			"%2528",	// (
			"%26",		// &
			"%24",		// $
			"%3f",		// ?
			"%3b",		// ;
			"%3d"		// =
		);

		if ( ! $relative_path)
		{
			$bad[] = './';
			$bad[] = '/';
		}

		$str = $this->cleanString($str, FALSE);
		return stripslashes(str_replace($bad, '', $str));
	}

	protected function compactExplodedWords($matches)
	{
		return preg_replace('/\s+/s', '', $matches[1]).$matches[2];
	}

	protected function removeEvilAttributes($str, $isImage)
	{
		$evilAttributes = array('on\w*', 'style', 'xmlns', 'formaction');

		if ($isImage === TRUE)
		{
			unset($evilAttributes[array_search('xmlns', $evilAttributes)]);
		}

		do {
			$count = 0;
			$attribs = array();
			preg_match_all('/('.implode('|', $evilAttributes).')\s*=\s*([^\s>]*)/is', $str, $matches, PREG_SET_ORDER);
			foreach ($matches as $attr)
			{
				$attribs[] = preg_quote($attr[0], '/');
			}
			preg_match_all("/(".implode('|', $evilAttributes).")\s*=\s*(\042|\047)([^\\2]*?)(\\2)/is",  $str, $matches, PREG_SET_ORDER);
			foreach ($matches as $attr)
			{
				$attribs[] = preg_quote($attr[0], '/');
			}
			if (count($attribs) > 0)
			{
				$str = preg_replace("/<(\/?[^><]+?)([^A-Za-z<>\-])(.*?)(".implode('|', $attribs).")(.*?)([\s><])([><]*)/i", '<$1 $3$5$6$7', $str, -1, $count);
			}

		} while ($count);

		return $str;
	}

	protected function sanitizeNaughtyHtml($matches)
	{
		$str = '&lt;'.$matches[1].$matches[2].$matches[3];
		$str .= str_replace(array('>', '<'), array('&gt;', '&lt;'),
							$matches[4]);
		return $str;
	}

	protected function removeJsLink($match)
	{
		return str_replace(
			$match[1],
			preg_replace(
				'#href=.*?(alert\(|alert&\#40;|javascript\:|livescript\:|mocha\:|charset\=|window\.|document\.|\.cookie|<script|<xss|data\s*:)#si',
				'',
				$this->filterAttributes(str_replace(array('<', '>'), '', $match[1]))
			),
			$match[0]
		);
	}

	protected function removeJsImg($match)
	{
		return str_replace(
			$match[1],
			preg_replace(
				'#src=.*?(alert\(|alert&\#40;|javascript\:|livescript\:|mocha\:|charset\=|window\.|document\.|\.cookie|<script|<xss|base64\s*,)#si',
				'',
				$this->filterAttributes(str_replace(array('<', '>'), '', $match[1]))
			),
			$match[0]
		);
	}

	protected function convertAttribute($match)
	{
		return str_replace(array('>', '<', '\\'), array('&gt;', '&lt;', '\\\\'), $match[0]);
	}

	protected function filterAttributes($str)
	{
		$out = '';

		if (preg_match_all('#\s*[a-z\-]+\s*=\s*(\042|\047)([^\\1]*?)\\1#is', $str, $matches))
		{
			foreach ($matches[0] as $match)
			{
				$out .= preg_replace("#/\*.*?\*/#s", '', $match);
			}
		}

		return $out;
	}

	protected function decodeEntity($match)
	{
		return $this->entityDecode($match[0], strtoupper($this->_charset));
	}

	protected function validateEntities($str)
	{
		$str = preg_replace('|\&([a-z\_0-9\-]+)\=([a-z\_0-9\-]+)|i', $this->getXSSHash()."\\1=\\2", $str);
		$str = preg_replace('#(&\#?[0-9a-z]{2,})([\x00-\x20])*;?#i', "\\1;\\2", $str);
		$str = preg_replace('#(&\#x?)([0-9A-F]+);?#i',"\\1\\2;",$str);
		$str = str_replace($this->getXSSHash(), '&', $str);
		return $str;
	}

	protected function cleanString($str)
	{
		$str = str_replace(array_keys($this->_blockString), $this->_blockString, $str);

		foreach ($this->_blockRegex as $regex)
		{
			$str = preg_replace('#'.$regex.'#is', '[removed]', $str);
		}

		return $str;
	}

	protected function setCsrfHash()
	{
		if ($this->_csrfHash == '')
		{
                    if (isset($_COOKIE[$this->_csrfCookieName]) && preg_match('#^[0-9a-f]{32}$#iS', $_COOKIE[$this->_csrfCookieName]) === 1)
                    {
                            return $this->_csrfHash = $_COOKIE[$this->_csrfCookieName];
                    }

		    return $this->_csrfHash = md5(uniqid(rand(), TRUE));
		}

		return $this->_csrfHash;
	}
        
	protected function removeHiddenChar($str, $urlEncoded = TRUE)
	{
		$hiddenChars = array();		
		if ($urlEncoded)
		{
			$hiddenChars[] = '/%0[0-8bcef]/';
			$hiddenChars[] = '/%1[0-9a-f]/';
		}		
		$hiddenChars[] = '/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]+/S';

		do
		{
			$str = preg_replace($hiddenChars, '', $str, -1, $count);
		}
		while ($count);

		return $str;
	}        

}