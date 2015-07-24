<?php
class Paypal extends CI_Model {
	private $debug = TRUE;
	
	/*	PAYPAL API VALUES	*/
	private $acc = array(
		'classic' => array(),
		'oauth' => array()
	);
	
	function __construct() {
		parent::__construct();
		
		if (defined('PAYPAL_ENDPOINT_CLASSIC') && defined('PAYPAL_USER_CLASSIC') && defined('PAYPAL_PASS_CLASSIC') && defined('PAYPAL_SIGNATURE_CLASSIC'))
			$this->setPayPalAccClassic(array( 'ENDPOINT' => PAYPAL_ENDPOINT_CLASSIC, 'USER' => PAYPAL_USER_CLASSIC, 'PASS' => PAYPAL_PASS_CLASSIC, 'SIGNATURE' => PAYPAL_SIGNATURE_CLASSIC ));
		
		if (defined('PAYPAL_ENDPOINT') && defined('PAYPAL_CLIENT_ID') && defined('PAYPAL_SECRET'))
			$this->setPayPalAccOAuth(array( 'ENDPOINT' => PAYPAL_ENDPOINT, 'CLIENT_ID' => PAYPAL_CLIENT_ID, 'SECRET' => PAYPAL_SECRET ));
		
		$this->setCurrencyCode('usd');
		$this->setRefundSource('default');
		$this->setRefundType('full');
	}
	
	/*	ACC INFO: CLASSIC	*/
	
	/**	getPayPalAccClassic()
	 *	Simply retrieves ENDPOINT, USER, PASS, & SIGNATURE as an Associative array
	 *
	 *	@return ARRAY [ 'ENDPOINT' => value, 'USER' => value, 'PASS' => value, 'SIGNATURE' => value ]
	 */
	public function getPayPalAccClassic() {
		if (!empty($this->acc['classic'])) {
			$acc = $this->acc['classic'];
			if (!empty($acc['ENDPOINT']) && !empty($acc['USER']) && !empty($acc['PASS']) && !empty($acc['SIGNATURE']))
				return $acc;
		}
		return NULL;
	}
	
	/**	setPayPalAccClassic(MIXED)
	 *	Used to set ENDPOINT, USER, PASS, SIGNATURE for your paypal account.
	 *	Accepts 3 types of arguments.
	 *	 - 3 STRINGS in order of ($endpointUrl, $user, $pass, $signature)
	 *	 - ARRAY Whereby Count Must be 4 AND order of values MUST BE [ ENDPOINT, USER, PASS, SIGNATURE ]
	 *	 - Associative ARRAY [ 'ENDPOINT' => value, 'USER' => value, 'PASS' => value, 'SIGNATURE' => value ]
	 *
	 *	 @example $this->paypal->setPayPalAccClassic((array)$paypalData);
	 *	 @example $this->paypal->setPayPalAccClassic((string)PAYPAL_USER, (string)PAYPAL_PASS, (string)PAYPAL_SIGNATURE);
	 *
	 *	 @param MIXED Must be an array having Keys of ENDPOINT, USER, PASS, & SIGNATURE; or 3 strings in order by EndPoint, User, Pass, & Signature
	 *	 @return ARRAY Returns $this->getPayPalAccClassic();
	 */
	public function setPayPalAccClassic() {
		$args = func_get_args();
		$x = array_fill(0, 4, NULL);
		
		foreach($args as $i => &$v) {
			if (is_array($v)) {
				if (array_keys($v) !== range(0, count($v) - 1)) {
					$v = array_change_key_case($v, CASE_UPPER);
					if (array_key_exists('ENDPOINT', $v)) {
						$x[0] = $v['ENDPOINT'];
						unset($args[$i]);
					}
					if (array_key_exists('USER', $v)) {
						$x[1] = $v['USER'];
						unset($args[$i]);
					}
					if (array_key_exists('PASS', $v)) {
						$x[2] = $v['PASS'];
						unset($args[$i]);
					}
					if (array_key_exists('SIGNATURE', $v)) {
						$x[3] = $v['SIGNATURE'];
						unset($args[$i]);
					}
				}
				elseif (count($v) == 4) $x = $v;
			}
		}
		if (!empty($args) && in_array(NULL, $x)) {
			foreach($args as $i => &$v) {
				if (is_string($v)) $x[array_search(NULL, $x)] = $v;
				if (in_array(NULL, $x) === FALSE) break;
			}
		}
		if (!in_array(NULL, $x) && implode($x) !== '') {
			$this->acc['classic'] = array(
				'ENDPOINT' => is_string($x[0]) && strpos($x[0], 'paypal.com') !== TRUE ? $x[0] : NULL,
				'USER' => is_string($x[1]) && !empty($x[1]) ? $x[1] : NULL,
				'PASS' => is_string($x[2]) && !empty($x[2]) ? $x[2] : NULL,
				'SIGNATURE' => is_string($x[3]) && !empty($x[3]) ? $x[3] : NULL,
			);
		}
		if (in_array(NULL, $x)) $this->acc['classic'] = array();
		return $this->getPayPalAccClassic();
	}
	
	/**	getPayPalAccOAuth()
	 *	Simply retrieves ENDPOINT, CLIENT_ID, & SECRET as an Associative array
	 *
	 *	@return ARRAY [ 'ENDPOINT' => value, 'CLIENT_ID' => value, 'SECRET' => value ]
	 */
	public function getPayPalAccOAuth() {
		if (!empty($this->acc['oauth'])) {
			$acc = $this->acc['oauth'];
			if (!empty($acc['ENDPOINT']) && !empty($acc['CLIENT_ID']) && !empty($acc['SECRET']))
				return $acc;
		}
		return NULL;
	}
	
	/**	setPayPalAccOAuth(MIXED)
	 *	Used to set ENDPOINT, CLIENT_ID, SECRET for your paypal account.
	 *	Accepts 3 types of arguments.
	 *	 - 3 STRINGS in order of ($endpointUrl, $clientID, $secret)
	 *	 - ARRAY Whereby Count Must be 3 AND order of values MUST BE [ ENDPOINT, CLIENT_ID, SECRET ]
	 *	 - Associative ARRAY [ 'ENDPOINT' => value, 'CLIENT_ID' => value, 'SECRET' => value ]
	 *
	 *	 @example $this->paypal->setPayPalAccOAuth((array)$paypalData);
	 *	 @example $this->paypal->setPayPalAccOAuth((string)CLIENT_ID, (string)SECRET);
	 *
	 *	 @param MIXED Must be an array having Keys of ENDPOINT, CLIENT_ID, & SECRET; or 3 strings in order by EndPoint, ClientID, & Secret
	 *	 @return ARRAY Returns $this->getPayPalAccOAuth();
	 */
	public function setPayPalAccOAuth() {
		$args = func_get_args();
		$x = array_fill(0, 3, NULL);
		
		foreach($args as $i => &$v) {
			if (is_array($v)) {
				if (array_keys($v) !== range(0, count($v) - 1)) {
					$v = array_change_key_case($v, CASE_UPPER);
					if (array_key_exists('ENDPOINT', $v)) {
						$x[0] = $v['ENDPOINT'];
						unset($args[$i]);
					}
					if (array_key_exists('CLIENT_ID', $v)) {
						$x[1] = $v['CLIENT_ID'];
						unset($args[$i]);
					}
					if (array_key_exists('SECRET', $v)) {
						$x[2] = $v['SECRET'];
						unset($args[$i]);
					}
				}
				elseif (count($v) == 3) $x = $v;
			}
		}
		if (!empty($args) && in_array(NULL, $x)) {
			foreach($args as $i => &$v) {
				if (is_string($v)) $x[array_search(NULL, $x)] = $v;
				if (in_array(NULL, $x) === FALSE) break;
			}
		}
		
		if (!in_array(NULL, $x) && implode($x) !== '') {
			$this->acc['oauth'] = array(
				'ENDPOINT' => is_string($x[0]) && strpos($x[0], 'paypal.com') !== TRUE ? $x[0] : NULL,
				'CLIENT_ID' => is_string($x[1]) && !empty($x[1]) ? $x[1] : NULL,
				'SECRET' => is_string($x[2]) && !empty($x[2]) ? $x[2] : NULL
			);
		}
		if (in_array(NULL, $x)) $this->acc['oauth'] = array();
		return $this->getPayPalAccOAuth();
	}
	
	/*	ACC INFO: OAuth	*/
	
	/*-----------------------------------------------*/
	
	/*	Get/Set Base Properties Methods	*/
	/**	getProperty($name)
	 *	
	 *	Simple method for getting properties by case insensitive name, if they exist.
	 *
	 *	@example $this->getProperty('currencycode');
	 *
	 *	@param (STRING) $name The name of the property expected to be found in current class.
	 *	@return (MIXED) Returns given property value if found, else NULL.
	 */
	public function getProperty($name) {
		$prop = $this->getPropertyName($name);
		if (!empty($prop)) return $this->{$prop};
		return NULL;
	}
	
	/**	getPropertyName($name)
	 *	
	 *	Simple method for getting correct property name use any case insensitive variation of the name.
	 *		Must still be spelled properly.
	 *
	 *	@example $this->getPropertyName('currencycode');
	 *
	 *	@param (STRING) $name The name of the property expected to be found in current class.
	 *	@return (MIXED) Returns proper property name if found, else NULL.
	 */
	private function getPropertyName($name) {
		$propNames = array_keys(get_object_vars($this));
		foreach ($propNames as $n) if (strcasecmp($name, $n) == 0) return $n;
		return NULL;
	}
	
	/**	setArrayProperty(&$prop, $array, $value)
	 *	
	 *	Method of setting properties from an array for getting proper values.
	 *		Will check the array provided for the value.
	 *		If it and the property name exist, will set property to that value;
	 *		
	 *	@example $this->setArrayProperty($currencyCode, $currencyCodeTypes, 'usd');
	 *
	 *	@param (STRING) &$prop The property name to find and set.
	 *	@param (ARRAY) $array The array to search through for proper value name.
	 *	@param (MIXED) $value The value to look for in the array and set to the property.
	 *	@return (MIXED) Return the property value, if set, else NULL.
	 */
	private function setArrayProperty(&$prop, $array, $value) {
		$index = array_search(strtolower($value), array_map('strtolower', $array));
		return $prop = $index !== FALSE ? $array[$index] : NULL;
	}
	
	/**	setProperty($name, $value)
	 *	
	 *	Method for setting property, if name is found, to given value.
	 *
	 *	@example $this->setProperty('note', $value);
	 *
	 *	@param (STRING) $name The name of the property expected to be found in current class.
	 *	@param (MIXED) $value The value to set the property too.
	 *	@return (MIXED) Return the property value, if set, else NULL.
	 */
	public function setProperty($name, $value) {
		$prop = $this->getPropertyName($name);
		if (!empty($prop)) return $this->{$prop} = $value;
		return NULL;
	}
	/*---------------------------------*/
	
	/*	Base Properties	*/
	/**	$amt
	 *
	 *	(Optional) Refund amount. The amount is required if refundType is 'Partial'.
	 *		Note - If RefundType is Full, do not set the amount.
	 *		Character length and limitations: Value is typically a positive number which
	 *		cannot exceed 10,000.00 USD or the per transaction limit for the currency.
	 *		It includes no currency symbol. Most currencies require two decimal places;
	 *		the decimal separator must be a period (.), and the optional thousands separator
	 *		must be a comma (,). Some currencies do not allow decimals.
	 *		See the currency codes page for details.
	 *	
	 *		@see https://developer.paypal.com/docs/classic/api/currency_codes/
	 *
	 *	@method (STRING) getAmt() Returns String value of currently set amount. Default NULL.
	 *	@method (STRING) setAmt() Sets value of amount to String Number Format adhering to current currancy code. Default NULL.
	 */
	private $amt;
	public function getAmt() {
		return $this->getProperty('amt');
	}
	public function setAmt($value) {
		$c = $this->getCurrencyCode();
		$v = (float)$value;
		$this->setProperty('amt', !empty($c) && !in_array($c, $this->currencyCodeNoDecimal) ? number_format($v, 2) : number_format($v));
		return $this;
	}
	
	/**	$currencyCode
	 *
	 *	(Conditional) This field is required for partial refunds and is also required for refunds greater than 100%.
	 *		An ISO 4217 3-letter currency code, for example, USD for US Dollars.
	 *	
	 *	@see https://developer.paypal.com/docs/classic/api/currency_codes/
	 *	
	 *	@property (ARRAY) $currencyCodeNoDecimal Array of PayPal Currency Codes that do NOT accept Decimal notation.
	 *	@property (ARRAY) $currencyCodeTypes Array of PayPal Currency Codes.
	 *	
	 *	@method (STRING) getCurrencyCode() Returns String value of currently set currencyCode. Default 'USD'.
	 *	@method (STRING) setCurrencyCode() Sets value of amount to String Number Format adhering to current currancy code. Default 'USD'.
	 */
	private $currencyCode;
	protected $currencyCodeNoDecimal = array( 'HUF', 'JPY', 'TWD' );
	protected $currencyCodeTypes = array( 'AUD', 'BRL', 'CAD', 'CZK', 'DKK', 'EUR', 'HKD', 'HUF', 'ILS', 'JPY', 'MYR', 'MXN', 'NOK', 'NZD', 'PHP', 'PLN', 'GBP', 'RUB', 'SGD', 'SEK', 'CHF', 'TWD', 'THB', 'TRY', 'USD' );
	public function getCurrencyCode() {
		$cc = $this->getProperty('currencyCode');
		return !empty($cc) ? $cc : 'USD';
	}
	public function setCurrencyCode($type) {
		$cc = $this->setArrayProperty($this->currencyCode, $this->currencyCodeTypes, $type);
		if (!empty($cc)) $this->setProperty('currencycode', 'USD');
		return $this;
	}
	
	/**	$method	(Required!!!)
	 *
	 *	@property (ARRAY) $methodTypes Array of PayPal Methods Available.
	 *	
	 *	@method (STRING) getMethod() Returns String value of currently set method. Default NULL.
	 *	@method (STRING) setMethod() Sets the PayPal method to be called. Default NULL.
	 */
	private $method;
	protected $methodTypes = array( 'DoDirectPayment', 'RefundTransaction' );
	public function getMethod() {
		return $this->getProperty('method');
	}
	public function setMethod($type) {
		$this->setArrayProperty($this->method, $this->methodTypes, $type);
		return $this;
	}
	
	/**	$msgSubID
	 *
	 *	(Optional) A message ID used for idempotence to uniquely identify a message.
	 *		This ID can later be used to request the latest results for a previous request
	 *		without generating a new request. Examples of this include requests due to timeouts
	 *		or errors during the original request.
	 *
	 *	@method (STRING) getMsgSubID() Returns String value of currently set msgSubID. Default NULL.
	 *	@method (STRING) setMsgSubID() Sets value of msgSubID. Default NULL.
	 */
	private $msgSubID;
	public function getMsgSubID() {
		return $this->getProperty('msgsubid');
	}
	public function setMsgSubID($value) {
		$this->setProperty('msgsubid', $value);
		return $this;
	}
	
	/**	$note
	 *
	 *	(Optional) Custom memo about the refund.
	 *		Character length and limitations: 255 single-byte alphanumeric characters
	 *
	 *	@method (STRING) getNote() Returns String value of currently set note. Default NULL.
	 *	@method (STRING) setNote() Sets value of note. Default NULL.
	 */
	private $note;
	public function getNote() {
		return $this->getProperty('note');
	}
	public function setNote($value) {
		$this->setProperty('note', $value);
		return $this;
	}
	
	/**	$refundAdvice	v85.0
	 *
	 *	(Optional) Flag to indicate that the buyer was already given store credit for a given transaction.
	 *		It is one of the following values:
	 *			- true The buyer was already given store credit for a given transaction.
	 *			- false The buyer was not given store credit for a given transaction.
	 *	
	 *	@method (BOOLEAN) getRefundAdvice() Returns String value of currently set refundAdvice. Default 'false'.
	 *	@method (BOOLEAN) setRefundAdvice() Sets value of refundAdvice. Default 'false'.
	 */
	private $refundAdvice;
	public function getRefundAdvice() {
		$ra = $this->getProperty('refundadvice');
		return $ra == 'true' ? $ra : 'false';
	}
	public function setRefundAdvice($value) {
		if (is_string($value)) $value = strtolower($value) == 'true' ? 'true' : 'false';
		elseif (is_bool($value)) $value = !empty($value) ? 'true' : 'false';
		else $value = 'false';
		$this->setProperty('refundadvice', $value);
		return $this;
	}
	
	/**	$refundSource	v82.0
	 *
	 *	(Optional)Type of PayPal funding source (balance or eCheck) that can be used for auto refund.
	 *		It is one of the following values:
	 *			any – The merchant does not have a preference. Use any available funding source.
	 *			default – Use the merchant's preferred funding source, as configured in the merchant's profile.
	 *			instant – Use the merchant's balance as the funding source.
	 *			eCheck – The merchant prefers using the eCheck funding source. If the merchant's PayPal balance can cover the refund amount, use the PayPal balance.
	 *	
	 *	@property (ARRAY) $refundSourceTypes Array of PayPal Refund Sources Available.
	 *	
	 *	@method (STRING) getMethod() Returns String value of currently set refundSource. Default 'default'.
	 *	@method (STRING) setMethod() Sets the PayPal Refund method type. Default 'default'.
	 */
	private $refundSource;
	protected $refundSourceTypes = array( 'any', 'default', 'instant', 'eCheck' );
	public function getRefundSource() {
		$rs = $this->getProperty('refundsource');
		return !empty($rs) ? $rs : 'default';
	}
	public function setRefundSource($type) {
		$rs = $this->setArrayProperty($this->refundSource, $this->refundSourceTypes, $type);
		if (empty($rs)) $rs = $this->setArrayProperty($this->refundSource, $this->refundSourceTypes, 'default');
		return $this;
	}
	
	/**	$refundType
	 *
	 *	Type of refund you are making. It is one of the following values:
	 *		Full – Full refund (default).
	 *		Partial – Partial refund.
	 *		ExternalDispute – External dispute. (Value available since version 82.0)
	 *		Other – Other type of refund. (Value available since version 82.0)
	 *
	 *	@method (STRING) getRefundType() Returns String value of currently set retryUntil. Default Full.
	 *	@method (STRING) setRefundType() Sets value of retryUntil. Default Full.
	 */
	private $refundType;
	protected $refundTypeTypes = array( 'Full', 'Partial', 'ExternalDispute', 'Other' );
	public function getRefundType() {
		$rs = $this->getProperty('refundtype');
		return !empty($rs) ? $rs : 'Full';
	}
	public function setRefundType($type) {
		$rs = $this->setArrayProperty($this->refundType, $this->refundTypeTypes, $type);
		if (empty($rs)) $rs = $this->setArrayProperty($this->refundType, $this->refundTypeTypes, 'Full');
		return $this;
	}
	
	/**	$retryUntil	v82.0
	 *
	 *	(Optional) Maximum time until you must retry the refund.
	 *		Note - This field does not apply to point-of-sale transactions.
	 *
	 *	@method (STRING) getRetryUntil() Returns String value of currently set retryUntil. Default NULL.
	 *	@method (STRING) setRetryUntil() Sets value of retryUntil. Default NULL.
	 */
	private $retryUntil;
	public function getRetryUntil() {
		return $this->getProperty('retryuntil');
	}
	public function setRetryUntil($value) {
		$this->setProperty('retryuntil', $value);
		return $this;
	}
	
	/**	$shippingAmt
	 *
	 *	(Optional) The amount of shipping paid.
	 *
	 *	@method (STRING) getShippingAmt() Returns String value of currently set shippingAmt. Default NULL.
	 *	@method (STRING) setShippingAmt() Sets value of shippingAmt. Default NULL.
	 */
	private $shippingAmt;
	public function getShippingAmt() {
		return $this->getProperty('shippingamt');
	}
	public function setShippingAmt($value) {
		$c = $this->getCurrencyCode();
		$v = (float)$value;
		return $this->setProperty('shippingamt', !empty($c) && !in_array($c, $this->currencyCodeNoDecimal) ? number_format($v, 2) : number_format($v));
	}
	
	/**	$taxAmt
	 *
	 *	(Optional) The amount of tax paid.
	 *
	 *	@method (STRING) getTaxAmt() Returns String value of currently set taxAmt. Default NULL.
	 *	@method (STRING) setTaxAmt() Sets value of taxAmt. Default NULL.
	 */
	private $taxAmt;
	public function getTaxAmt() {
		return $this->getProperty('taxamt');
	}
	public function setTaxAmt($value) {
		$c = $this->getCurrencyCode();
		$v = (float)$value;
		$this->setProperty('taxamt', !empty($c) && !in_array($c, $this->currencyCodeNoDecimal) ? number_format($v, 2) : number_format($v));
		return $this;
	}
	
	/**	$transactionID
	 *
	 *	(Conditional) Either the transaction ID or the payer ID must be specified.
	 *		The transaction ID is the unique identifier of the transaction to be refunded.
	 *		Note Either the transaction ID or the payer ID must be specified. The payer ID
	 *		is required for non-referenced refunds, a refund against a previously provided
	 *		invoice ID (without the PayPal transaction ID). However, the non-referenced refund
	 *		feature must be enabled by PayPal. Contact PayPal for details. Character length and 
	 *		limitations: 17 characters except for transactions of the type Order have a character length of 19.
	 *
	 *	@method (STRING) getTransactionID() Returns String value of currently set transactionID. Default NULL.
	 *	@method (STRING) setTransactionID() Sets value of transactionID. Default NULL.
	 */
	private $transactionID;
	public function getTransactionID() {
		return $this->getProperty('transactionid');
	}
	public function setTransactionID($value) {
		$this->setProperty('transactionid', $value);
		return $this;
	}
	
	/**	$version
	 *
	 *	@method (STRING) getPayPalVer() Returns String value of currently set version. Default 1.
	 *	@method (STRING) setPayPalVer() Sets value of version. Default 1.
	 */
	private $version = 1;
	public function getPayPalVer() {
		return $this->getProperty('version');
	}
	public function setPayPalVer($value=1) {
		$this->setProperty('version', !empty($value) ? $value : 1);
		return $this;
	}
	/*-----------------------------------------------*/
	
	/*	BASIC METHODS	*/
	
	/**	getInstance($new)
	 *
	 *	Method to return current instance of this class.
	 *		However, if TRUE is passed through, then a NEW instance
	 *		of this class will be returned;
	 *
	 *	@example $this->paypal->getInstance();
	 *
	 *	@param (BOOLEAN) $new Default is FALSE, if set to TRUE, will return entirely new instance of this class.
	 *	@return (OBJECT) Returns Instance this class, unless TRUE is passed, in which case, returns NEW Instance of this Class.
	 */
	public function getInstance($new=FALSE) {
		return empty($new) ? $this : new Paypal();
    }
	
	/*-----------------------------------------------*/
	
	/*	PayPal CURL Calls	*/
	private function deformatNVP($nvpstr) {
		$intial = 0; $nvpArray = array();
		while(strlen($nvpstr)) {
			//postion of Key
			$keypos = strpos($nvpstr, '=');
			//position of value
			$valuepos = strpos($nvpstr, '&') ? strpos($nvpstr, '&'): strlen($nvpstr);
			
			/*getting the Key and Value values and storing in a Associative Array*/
			$keyval = substr($nvpstr, $intial, $keypos);
			$valval = substr($nvpstr, $keypos+1, $valuepos - $keypos - 1);
			//decoding the respose
			$nvpArray[urldecode($keyval)] = urldecode( $valval);
			$nvpstr = substr($nvpstr, $valuepos+1, strlen($nvpstr));
		 }
		return $nvpArray;
	}
	/**
	 *
	 */
	public function getAccessToken($asObject=FALSE, $asArray=FALSE) {
		$acc = $this->getPayPalAccOAuth();
		
		if (!empty($acc)) {
			$ep = $acc['ENDPOINT'];
			$clientID = $acc['CLIENT_ID'];
			$secret = $acc['SECRET'];
			
			$ver = $this->getPayPalVer();
			
			$url = "$ep/v$ver/oauth2/token";
			$userPwd = "$clientID:$secret";
			$postData = array( 'grant_type' => 'client_credentials' );
			
			$curl = curl_init();
			
			curl_setopt($curl, CURLOPT_URL, $url);
			curl_setopt($curl, CURLOPT_HEADER, FALSE);
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
			curl_setopt($curl, CURLOPT_POST, TRUE);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
			curl_setopt($curl, CURLOPT_USERPWD, $userPwd);
			curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($postData));
			
			$response = curl_exec($curl);
			
			if (empty($response)) {
				$errNo = curl_errno($curl);
				$errMsg = curl_error($curl);
				$curlInfo = curl_getinfo($curl);
				$arrErr = array(
					'ERROR',
					'SEARCH' => "cURL Error($errNo) $errMsg",
					'ERROR_NO' => $errNo,
					'ERROR_MSG' => $errMsg,
					'URL' => $url,
					'USERPWD' => $userPwd,
					'POSTFIELDS' => http_build_query($postData),
					'CURL_INFO' => $curlInfo,
				);
				if ($this->debug) preDump($arrErr);
				die($arrErr['SEARCH']);
			}
			else {
				$info = curl_getinfo($curl);
				curl_close($curl);
				
				if($info['http_code'] != 200 && $info['http_code'] != 201 ) {
					$dieResponse = "Received error: ".$info['http_code']."\n<br />\n";
					$dieResponse .= "Raw response:".$response."\n<br />\n";
					die($dieResponse);
				}
				else {
					$jsonResponse = json_decode($response);
					$jsonResponse->total_time = ($info['total_time']*1000)."ms";
					$araResponse = array();
					if (!empty($asObject)) return $asObject;
					if (!empty($asArray)) {
						foreach ($jsonResponse as $k => $v) $araResponse[$k] = $v;
						return $araResponse;
					}
					return $jsonResponse->access_token;
				}
			}
			curl_close($curl);
			
		}
		
		return NULL;
	}
	
	
}
