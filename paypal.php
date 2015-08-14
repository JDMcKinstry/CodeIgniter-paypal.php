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
		
		//	These should be set in application/config/constants.php
		if (defined('PAYPAL_ENDPOINT_CLASSIC') && defined('PAYPAL_USER_CLASSIC') && defined('PAYPAL_PASS_CLASSIC') && defined('PAYPAL_SIGNATURE_CLASSIC'))
			$this->setPayPalAccClassic(array( 'ENDPOINT' => PAYPAL_ENDPOINT_CLASSIC, 'USER' => PAYPAL_USER_CLASSIC, 'PASS' => PAYPAL_PASS_CLASSIC, 'SIGNATURE' => PAYPAL_SIGNATURE_CLASSIC ));
		
		//	These should be set in application/config/constants.php
		if (defined('PAYPAL_ENDPOINT') && defined('PAYPAL_CLIENT_ID') && defined('PAYPAL_SECRET'))
			$this->setPayPalAccOAuth(array( 'ENDPOINT' => PAYPAL_ENDPOINT, 'CLIENT_ID' => PAYPAL_CLIENT_ID, 'SECRET' => PAYPAL_SECRET ));
		
		//	Default property settings
		//$this->setCurrencyCode('usd');
		//$this->setRefundSource('default');
		//$this->setRefundType('full');
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
			if (!empty($acc['ENDPOINT']) && !empty($acc['USER']) && !empty($acc['PASS']) && !empty($acc['SIGNATURE'])) return $acc;
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
			if (!empty($acc['ENDPOINT']) && !empty($acc['CLIENT_ID']) && !empty($acc['SECRET'])) return $acc;
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
		$index = array_search(strtolower(trim($value)), array_map('strtolower', $array));
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
		if (is_numeric($value) || is_string($value)) {
			$c = $this->getCurrencyCode();
			$v = floatval($value);
			$amt = !empty($c) && !in_array($c, $this->currencyCodeNoDecimal) ? number_format($v, 2) : number_format($v);
			if ((float)$amt > 0) return $this->setProperty('amt', $amt);
			show_error("AMT value must be greater than 0.");
		}
		show_error("AMT value must be numeric.");
	}
	public function amt($value) {
		$this->setAmt($value);
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
		if (is_string($type) && strlen($type) == 3) {
			$cc = $this->setArrayProperty($this->currencyCode, $this->currencyCodeTypes, $type);
			if (!empty($cc)) return $this->setProperty('currencycode', $cc);
			show_error("Did not recognize [$type] as a valid Currency Code.");
		}
		show_error("Currency Code must be a 3 letter STRING.");
	}
	public function currencyCode($type) {
		$this->setCurrencyCode($type);
		return $this;
	}
	
	/**	$intent
	 *
	 */
	private $intent;
	protected $intentTypes = array( 'authorize', 'order', 'sale' );
	public function getIntent() {
		return $this->getProperty('intent');
	}
	public function setIntent($value) {
		if (is_string($type)) {
			$type = $this->setArrayProperty($this->intent, $this->intentTypes, $type);
			if (!empty($type)) return $type;
			show_error("Did not recognize [$value] as a valid Intent Type.<br /><b>Valid Intent Types</b> are ( <i>" . implode(", ", $this->intentTypes) . "</i> )");
		}
		show_error("Intent Type must be a STRING");
	}
	public function intent($value) {
		$this->setAmt($value);
		return $this;
	}
	
	private $itemAmt;
	public function getItemAmt() {
		return $this->getProperty('itemAmt');
	}
	public function setItemAmt($value) {
		if (is_numeric($value) || is_string($value)) {
			$c = $this->getCurrencyCode();
			$v = floatval($value);
			$amt = !empty($c) && !in_array($c, $this->currencyCodeNoDecimal) ? number_format($v, 2) : number_format($v);
			if ((float)$amt > 0) return $this->setProperty('itemAmt', $amt);
			show_error("ITEMAMT value must be equal to or greater than 0.");
		}
		show_error("ITEMAMT value must be numeric.");
	}
	public function itemAmt($value) {
		$this->setItemAmt($value);
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
	protected $methodTypesExpress = array( 'AddressVerify', 'BAUpdate', 'BillOutstandingAmount', 'Callback', 'CreateBillingAgreement', 'CreateRecurringPaymentsProfile', 'DoAuthorization', 'DoCapture', 'DoExpressCheckoutPayment', 'DoReauthorization', 'DoReferenceTransaction', 'DoVoid', 'GetBalance', 'GetBillingAgreementCustomerDetails', 'GetExpressCheckoutDetails', 'GetPalDetails', 'GetRecurringPaymentsProfileDetails', 'GetTransactionDetails', 'ManageRecurringPaymentsProfileStatus', 'RefundTransaction', 'SetCustomerBillingAgreement', 'SetExpressCheckout', 'TransactionSearch', 'UpdateAuthorization', 'UpdateRecurringPaymentsProfile' );
	protected $methodTypesPro = array( 'BillOutstandingAmount', 'CreateRecurringPaymentsProfile', 'DoCapture', 'DoDirectPayment', 'DoNonReferencedCredit', 'DoReauthorization', 'DoReferenceTransaction', 'DoVoid', 'GetBalance', 'GetRecurringPaymentsProfileDetails', 'GetTransactionDetails', 'ManagePendingTransactionStatus', 'ManageRecurringPaymentsProfileStatus', 'RefundTransaction', 'TransactionSearch', 'UpdateAuthorization', 'UpdateRecurringPaymentsProfile' );
	public function getMethod() {
		return $this->getProperty('method');
	}
	public function setMethod($type) {
		if (is_string($type)) {
			$method = $this->setArrayProperty($this->method, $this->methodTypesExpress, $type);
			if (empty($method)) $method = $this->setArrayProperty($this->method, $this->methodTypesPro, $type);
			if (!empty($method)) return $method;
			show_error("Did not recognize [$type] as a valid API Method.<br /><b>Valid Express Methods</b> are ( <i>" . implode(", ", $this->methodTypesExpress) . "</i> )<br /><b>Valid Pro Methods</b> are ( <i>" . implode(", ", $this->methodTypesPro) . "</i> )");
		}
		show_error("API Method must be a STRING of a Valid PayPal API Method.<br /><b>Valid Express Methods</b> are ( <i>" . implode(", ", $this->methodTypesExpress) . "</i> )<br /><b>Valid Pro Methods</b> are ( <i>" . implode(", ", $this->methodTypesPro) . "</i> )");
	}
	public function method($type) {
		$this->setMethod($type);
		return $this;
	}
	
	/**	$msgSubID	v92.0
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
		if (is_string($value)) return $this->setProperty('msgsubid', $value);
		show_error("<i>(Optional)</i>MSGSUBID must be a STRING value.");
	}
	public function msgSubID($value) {
		$this->setMsgSubID($value);
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
		if (is_string($value)) return $this->setProperty('note', $value);
		show_error("<i>(Optional)</i>NOTE must be a STRING value.");
	}
	public function note($value) {
		$this->setNote($value);
		return $this;
	}
	
	/**	$paymentAction
	 *
	 */
	private $paymentAction;
	protected $paymentActionTypes = array( 'Authorization', 'Order', 'Sale' );
	public function getPaymentAction() {
		return $this->getProperty('paymentAction');
	}
	public function setPaymentAction($value) {
		if (is_string($value)) {
			$type = $this->setArrayProperty($this->paymentAction, $this->paymentActionTypes, $value);
			if (!empty($type)) return $type;
			show_error("Did not recognize [$value] as a valid Payment Action.<br /><b>Valid Payment Actions</b> are ( <i>" . implode(", ", $this->paymentActionTypes) . "</i> )");
		}
		show_error("Payment Method must be a STRING");
	}
	public function paymentAction($value) {
		$this->setPaymentAction($value);
		return $this;
	}
	
	/**	$paymentMethod
	 *
	 */
	private $paymentMethod;
	protected $paymentMethodTypes = array( 'credit_card ', 'paypal' );
	public function getPaymentMethod() {
		return $this->getProperty('paymentMethod');
	}
	public function setPaymentMethod($value) {
		if (is_string($type)) {
			$type = $this->setArrayProperty($this->paymentMethod, $this->paymentMethodTypes, $type);
			if (!empty($type)) return $type;
			show_error("Did not recognize [$value] as a valid Payment Method.<br /><b>Valid Intent Methods</b> are ( <i>" . implode(", ", $this->methodTypesExpress) . "</i> )");
		}
		show_error("Payment Method must be a STRING");
	}
	public function paymentMethod($value) {
		$this->setAmt($value);
		return $this;
	}
	
	private $referenceID;
	public function getReferenceID() {	
		return $this->getProperty('referenceid');
	}
	public function setReferenceID($value) {
		if (is_numeric($value)) $value = (string)$value;
		if (is_string($value)) return $this->setProperty('referenceid', $value);
		show_error("TAXAMT value must be STRING or NUMERIC.");
	}
	public function referenceID($value) {
		$this->setReferenceID($value);
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
		if (is_bool($value)) return $this->setProperty('refundadvice', $value);
		show_error("<i>(Optional)</i>REFUNDADVICE must be a BOOLEAN value.");
	}
	public function refundAdvice($value) {
		$this->setRefundAdvice($value);
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
		if (is_string($type)) {
			$rs = $this->setArrayProperty($this->refundSource, $this->refundSourceTypes, $type);
			if (!empty($rs)) return $rs;
			show_error("[$type] was not recognized as a valid REFUNDSOURCE.<br><b>Valid Refund Sources Types</b> are ( <i>" . implode(", ", $this->refundSourceTypes) . "</i> )");
		}
		show_error("<i>(Optional)</i>REFUNDSOURCE must be a STRING value.");
	}
	public function refundSource($type) {
		$this->setRefundSource($type);
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
		if (is_string($type)) {
			$rs = $this->setArrayProperty($this->refundType, $this->refundTypeTypes, $type);
			if (!empty($rs)) return $rs;
			show_error("[$type] was not recognized as a valid REFUNDTYPE.<br><b>Valid Refund Types</b> are ( <i>" . implode(", ", $this->refundTypeTypes) . "</i> )");
		}
		show_error("REFUNDTYPE must be a STRING value.");
	}
	public function refundType($type) {
		$this->setRefundType($type);
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
		if (is_string($value) && preg_match('/[0-9]{4}-[0-9]{2}-[0-9]{2}(T|\s){1}[0-9]{2}:[0-9]{2}:[0-9]{2}(Z|\s){1}/', $value)) return $this->setProperty('retryuntil', $value);
		show_error('RETRYUNTIL must be a valid STRING value matching the following date format: gmdate("Y-m-d\TH:i:s\Z")');
	}
	public function retryUntil($value) {
		$this->setRetryUntil($value);
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
		if (is_numeric($value) || is_string($value)) {
			$c = $this->getCurrencyCode();
			$v = floatval($value);
			$amt = !empty($c) && !in_array($c, $this->currencyCodeNoDecimal) ? number_format($v, 2) : number_format($v);
			if ((float)$amt >= 0) return $this->setProperty('shippingamt', $amt);
			show_error("SHIPPINGAMT value must be 0 or greater.");
		}
		show_error("SHIPPINGAMT value must be numeric.");
	}
	public function shippingAmt($value) {
		$this->setShippingAmt($value);
		return $this;
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
		if (is_numeric($value) || is_string($value)) {
			$c = $this->getCurrencyCode();
			$v = floatval($value);
			$amt = !empty($c) && !in_array($c, $this->currencyCodeNoDecimal) ? number_format($v, 2) : number_format($v);
			if ((float)$amt >= 0) return $this->setProperty('taxamt', $amt);
			show_error("TAXAMT value must be 0 or greater.");
		}
		show_error("TAXAMT value must be NUMERIC.");
	}
	public function taxAmt($value) {
		$this->setTaxAmt($value);
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
		if (is_numeric($value)) $value = (string)$value;
		if (is_string($value)) return $this->setProperty('transactionid', $value);
		show_error("TAXAMT value must be STRING or NUMERIC.");
	}
	public function transactionID($value) {
		$this->setTransactionID($value);
		return $this;
	}
	
	/**	$version
	 *
	 *	@method (STRING) getScopeVersion() Returns String value of currently set version. Default 1.
	 *	@method (STRING) setScopeVersion() Sets value of version. Default 1.
	 */
	private $scopeVersion = NULL;	//	will default to 1
	public function getScopeVersion() {
		return $this->getProperty('scopeVersion');
	}
	public function setScopeVersion($value=1) {
		if (is_numeric($value)) return $this->setProperty('scopeVersion', !empty($value) ? $value : 1);
		show_error("PayPal Scope Version value must be NUMERIC. Default is 1");
	}
	public function scopeVersion($value) {
		$this->setScopeVersion($value);
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
	
	/*	QUICK CURL CALLS	*/
	
	public function referenceTransaction() {
		$this->method('doReferenceTransaction')->paymentAction('sale');
		if (empty($this->currencyCode)) $this->setCurrencyCode('usd');
		return $this->callClassic();
	}
	
	public function refundTransaction() {
		$this->method('refundtransaction')->refundType('partial');
		return $this->callClassic();
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
	
	public function callClassic() {
		$acc = $this->getPayPalAccClassic();
		
		if (!empty($acc)) {
			$ep = $acc['ENDPOINT'];
			$user = $acc['USER'];
			$pass = $acc['PASS'];
			$sig = $acc['SIGNATURE'];
			
			$curl = curl_init($ep);
			
			curl_setopt($curl, CURLOPT_VERBOSE, TRUE);
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
			curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
			curl_setopt($curl, CURLOPT_POST, TRUE);
			
			$post = array(
				'VERSION' => 64,
				'PWD' => $pass,
				'USER' => $user,
				'SIGNATURE' => $sig
			);
			
			$ref = new ReflectionClass('paypal');
			$props = $ref->getProperties(ReflectionProperty::IS_PRIVATE);
			$opts = array();
			foreach($props as $prop) {
				$name = strtoupper($prop->name);
				$value = $this->getProperty($name);
				if (!preg_match('/^acc|debug$/i', $name) && !empty($value)) $opts[$name] = $value;
			}
			
			$opts = array_merge($post, $opts);
			curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($opts));
			
			$response = curl_exec($curl);
			
			if (empty($response)) {
				$errNo = curl_errno($curl);
				$errMsg = curl_error($curl);
				$curlInfo = curl_getinfo($curl);
				curl_close($curl);
				$arrErr = array(
					'ERROR',
					'SEARCH' => "cURL Error($errNo) $errMsg",
					'ERROR_NO' => $errNo,
					'ERROR_MSG' => $errMsg,
					'URL' => $url,
					'USERPWD' => $userPwd,
					'POSTFIELDS' => $postData,
					'CURL_INFO' => $curlInfo,
				);
				return $arrErr;
			}
			else {
				$this->resetClassic();
				$results = $this->deformatNVP($response);
				$info = curl_getinfo($curl);
				curl_close($curl);
				
				if($info['http_code'] != 200 && $info['http_code'] != 201 ) {
					$dieResponse = "Received error: ".$info['http_code']."\n<br />\n";
					$dieResponse .= "Raw response:".$response."\n<br />\n";
					die($dieResponse);
				}
				elseif(!empty($results['ACK'])) {
					if ($results['ACK'] == 'Success') return $results;
					else return $results + $opts;
				}
			}
		}
		return NULL;
	}
	private function resetClassic() {
		$ref = new ReflectionClass('paypal');
		$props = $ref->getProperties(ReflectionProperty::IS_PRIVATE);
		foreach($props as $prop) {
			$name = strtoupper($prop->name);
			$value = $this->getProperty($name);
			if (!preg_match('/^acc|debug$/i', $name) && !empty($value)) $this->setProperty($name, NULL);
		}
	}
	
	//	TODO: Finish building to use OAuth Rest API
	public function getOAuth($method, $postData=NULL, $asObject=FALSE, $asArray=FALSE) {
		$acc = $this->getPayPalAccOAuth();
		if (!empty($acc)) {
			$ep = $acc['ENDPOINT'];
			$clientID = $acc['CLIENT_ID'];
			$secret = $acc['SECRET'];
			
			$ver = $this->getScopeVersion();
			
			$url = "$ep/v$ver/oauth2/$method";
			$userPwd = "$clientID:$secret";
			
			$curl = curl_init($url);
			
			if ($method == 'token') {
				$httpHeader = array(
					'Accept: application/json',
					'Accept-Language: en_US'
				);
				$postData = http_build_query(array( 'grant_type' => 'client_credentials' ));
				curl_setopt($curl, CURLOPT_USERPWD, $userPwd);
			}
			else {
				$token = $this->getOAuth('token');
				$httpHeader = array(
					"Authorization: Bearer $token",
					'Accept: application/json',
					'Content-Type: application/json'
				);
			}
			
			curl_setopt($curl, CURLOPT_HEADER, FALSE);
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
			curl_setopt($curl, CURLOPT_POST, TRUE);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
			curl_setopt($curl, CURLOPT_POSTFIELDS, $postData);
			curl_setopt($curl, CURLOPT_HTTPHEADER, $httpHeader);
			
			$response = curl_exec($curl);
			
			if (empty($response)) {
				$errNo = curl_errno($curl);
				$errMsg = curl_error($curl);
				$curlInfo = curl_getinfo($curl);
				curl_close($curl);
				$arrErr = array(
					'ERROR',
					'SEARCH' => "cURL Error($errNo) $errMsg",
					'ERROR_NO' => $errNo,
					'ERROR_MSG' => $errMsg,
					'URL' => $url,
					'USERPWD' => $userPwd,
					'POSTFIELDS' => $postData,
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
		}
	}
}
