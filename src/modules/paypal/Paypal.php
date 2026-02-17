<?php

declare(strict_types=1);

namespace pff\modules;

use pff\Abs\AModule;
use pff\modules\Exception\PaypalException;

/**
 * Module to perform payment with Paypal express checkout system
 * You can find all API Reference at:
 * https://www.x.com/developers/paypal/documentation-tools/api
 *
 * @author Marco Bernini <marco.bernini@neropaco.net>
 */
class Paypal extends AModule
{
    /**
     * This constant value is provided by Paypal to a merchant (seller) account.
     */
    private string $_API_USERNAME;

    /**
     * This constant value is provided by Paypal to a merchant (seller) account.
     */
    private string $_API_PASSWORD;

    /**
     * This constant value is provided by Paypal to a merchant (seller) account.
     */
    private string $_API_SIGNATURE;

    /**
     * This is the server URL which you have to connect for submitting your API request.
     */
    private string $_API_ENDPOINT;

    /**
     * The latest version of API service.
     */
    private string $_version;

    /**
     * Define the PayPal URL. This is the URL that the buyer is first sent to
     * to authorize payment with their paypal account.
     */
    private string $_paypal_url;

    /*
     * Cgi-bin webscreen for command (used for ipn validation)
     */
    private string $_command_url;

    /**
     * @var NetworkHelper
     */
    private mixed $_nh = null;

    public function __construct(string $configFile = 'paypal/paypal.conf.yaml')
    {
        $this->loadConfig($this->readConfig($configFile));
    }


    public function getAPI_USERNAME(): string
    {
        return $this->_API_USERNAME;
    }

    public function setAPI_USERNAME(string $API_USERNAME): void
    {
        $this->_API_USERNAME = $API_USERNAME;
    }

    public function getAPI_PASSWORD(): string
    {
        return $this->_API_PASSWORD;
    }

    public function setAPI_PASSWORD(string $API_PASSWORD): void
    {
        $this->_API_PASSWORD = $API_PASSWORD;
    }

    public function getAPI_SIGNATURE(): string
    {
        return $this->_API_SIGNATURE;
    }

    public function setAPI_SIGNATURE(string $API_SIGNATURE): void
    {
        $this->_API_SIGNATURE = $API_SIGNATURE;
    }

    public function getAPI_ENDPOINT(): string
    {
        return $this->_API_ENDPOINT;
    }

    public function setAPI_ENDPOINT(string $API_ENDPOINT): void
    {
        $this->_API_ENDPOINT = $API_ENDPOINT;
    }

    public function getVersion(): string
    {
        return $this->_version;
    }

    public function setVersion(string $_version): void
    {
        $this->_version = $_version;
    }

    public function getPaypal_url(): string
    {
        return $this->_paypal_url;
    }

    public function setPaypal_url(string $_paypal_url): void
    {
        $this->_paypal_url = $_paypal_url;
    }

    public function getNh(): mixed
    {
        return $this->_nh;
    }

    public function setNh(mixed $_nh): void
    {
        $this->_nh = $_nh;
    }

    public function getCommand_url(): string
    {
        return $this->_command_url;
    }

    public function setCommand_url(string $_command_url): void
    {
        $this->_command_url = $_command_url;
    }


    /**
     * Reads parsed config file and sets needed attributes
     *
     * @param array $config
     * @throws PaypalException
     */
    private function loadConfig(array $config): void
    {
        if (
            isset($config['moduleConf']['API_USERNAME']) &&
            isset($config['moduleConf']['API_PASSWORD']) &&
            isset($config['moduleConf']['API_SIGNATURE']) &&
            isset($config['moduleConf']['API_ENDPOINT']) &&
            isset($config['moduleConf']['version']) &&
            isset($config['moduleConf']['paypal_url']) &&
            isset($config['moduleConf']['command_url'])
        ) {
            $this->setAPI_USERNAME($config['moduleConf']['API_USERNAME']);
            $this->setAPI_PASSWORD($config['moduleConf']['API_PASSWORD']);
            $this->setAPI_SIGNATURE($config['moduleConf']['API_SIGNATURE']);
            $this->setAPI_ENDPOINT($config['moduleConf']['API_ENDPOINT']);
            $this->setVersion($config['moduleConf']['version']);
            $this->setPaypal_url($config['moduleConf']['paypal_url']);
            $this->setCommand_url($config['moduleConf']['command_url']);
        } else {
            throw new PaypalException('Missing values in config file.');
        }
    }

    /**
     * Initialize the npv string with required pairs for the specified method used.
     * If type of goods is phisycal the created string is generic for any method used
     * and will have to be add specific name-value pairs.
     *
     * @param string $method API service name (es. "SetExpressCheckout")
     * @param int $digitalGoods 1 if for digital goods 0 otherwise
     * @param array $session is the session array where you stored the nvpArray
     * returned by "GetExpressCheckoutDetails".
     * @return string|false
     */
    public function nvpStringInit(string $method, int $digitalGoods, ?array $session = null): string|false
    {
        if ($method != "") {
            $str = "";
            if ($digitalGoods) {
                switch ($method) {
                    case "SetExpressCheckout":
                        $str = "METHOD=" . urlencode($method) . "&USER=" . urlencode($this->getAPI_USERNAME()) . "&PWD=" . urlencode($this->getAPI_PASSWORD()) .
                            "&SIGNATURE=" . urlencode($this->getAPI_SIGNATURE()) .
                            "&VERSION=" . urlencode($this->getVersion()) . "&NOSHIPPING=1&LOCALECODE=IT
                            &PAYMENTREQUEST_0_PAYMENTACTION=Sale&REQCONFIRMSHIPPING=0
                            &L_PAYMENTREQUEST_0_ITEMCATEGORY0=Digital&PAYMENTREQUEST_0_CURRENCYCODE=EUR";
                        break;
                    case "GetExpressCheckoutDetails":
                        if (null === $session) {
                            return false;
                        }
                        $str = "USER=" . urlencode($this->getAPI_USERNAME()) . "&PWD=" . urlencode($this->getAPI_PASSWORD()) .
                            "&SIGNATURE=" . urlencode($this->getAPI_SIGNATURE()) . "&METHOD=" . urlencode($method) .
                            "&VERSION=" . urlencode($this->getVersion()) . "&TOKEN=" . urlencode((string) $session['TOKEN']);
                        break;
                    case "CreateRecurringPaymentsProfile":
                        if (null === $session) {
                            return false;
                        }
                        $str = "USER=" . urlencode($this->getAPI_USERNAME()) . "&PWD=" . urlencode($this->getAPI_PASSWORD()) .
                            "&SIGNATURE=" . urlencode($this->getAPI_SIGNATURE()) . "&METHOD=" . urlencode($method) .
                            "&VERSION=" . urlencode($this->getVersion()) . "&TOKEN=" . urlencode((string) $session['TOKEN']);
                        break;
                    case "DoExpressCheckoutPayment":
                        if (null === $session) {
                            return false;
                        }
                        $str = "USER=" . urlencode($this->getAPI_USERNAME()) . "&PWD=" . urlencode($this->getAPI_PASSWORD()) .
                            "&SIGNATURE=" . urlencode($this->getAPI_SIGNATURE()) . "&METHOD=" . urlencode($method) .
                            "&VERSION=" . urlencode($this->getVersion()) . "&TOKEN=" . urlencode((string) $session['TOKEN']) .
                            "&PAYMENTREQUEST_0_PAYMENTACTION=Sale&PAYERID=" . urlencode((string) $session['PAYERID']) .
                            "&PAYMENTREQUEST_0_AMT=" . urlencode((string) $session['PAYMENTREQUEST_0_AMT']) .
                            "&PAYMENTREQUEST_0_CURRENCYCODE=" . urlencode((string) $session['PAYMENTREQUEST_0_CURRENCYCODE']) .
                            "&PAYMENTREQUEST_0_ITEMAMT=" . urlencode((string) $session['PAYMENTREQUEST_0_ITEMAMT']) .
                            "&PAYMENTREQUEST_0_TAXAMT=" . urlencode((string) $session['PAYMENTREQUEST_0_TAXAMT']) .
                            "&L_PAYMENTREQUEST_0_NAME0=" . urlencode((string) $session['L_PAYMENTREQUEST_0_NAME0']) .
                            "&L_PAYMENTREQUEST_0_DESC0=" . urlencode((string) $session['L_PAYMENTREQUEST_0_DESC0']) .
                            "&L_PAYMENTREQUEST_0_AMT0=" . urlencode((string) $session['L_PAYMENTREQUEST_0_AMT0']) .
                            "&L_PAYMENTREQUEST_0_TAXAMT0=" . urlencode((string) $session['L_PAYMENTREQUEST_0_TAXAMT0']) .
                            "&L_PAYMENTREQUEST_0_QTY0=" . urlencode((string) $session['L_PAYMENTREQUEST_0_QTY0']) .
                            "&L_PAYMENTREQUEST_0_ITEMCATEGORY0=" . urlencode((string) $session['L_PAYMENTREQUEST_0_ITEMCATEGORY0']);
                        break;
                    default:
                        return false;
                }
            } else {
                $str = "USER=" . urlencode($this->getAPI_USERNAME()) . "&PWD=" . urlencode($this->getAPI_PASSWORD()) .
                    "&SIGNATURE=" . urlencode($this->getAPI_SIGNATURE()) . "&METHOD=" . urlencode($method) .
                    "&VERSION=" . urlencode($this->getVersion());
            }
            return $str;
        }
        return false;
    }

    /**
     * Create the nvp (name value pairs) string for the API call
     *
     * @param string $name the name of the pair name - value, must be uppercase
     * @param string $value the value associated to name provided
     * @param string $str the nvp string generated so far (initially is empty)
     * @return string|false
     */
    public function nvpStringCreator(string $name = "", string $value = "", string $str = ""): string|false
    {
        if ($str != "") {
            if ($name != "" && $value != "") {
                $valtoadd = "&" . $name . "=" . urlencode($value);
                $str = $str . $valtoadd;
                return $str;
            } else {
                return false;
            }
        }
        //is first value in the string
        else {
            if ($name != "" && $value != "") {
                $valtoadd = $name . "=" . urlencode($value);
                $str = $valtoadd;
                return $str;
            } else {
                return false;
            }
        }
    }

    /**
     * Perform an API call to service specified in nvp string (METHOD field) and
     * return an associative array of the response
     *
     * @param string $nvpstr
     * @return array $nvpArray
     * @throws PaypalException
     */
    public function callAPIService(string $nvpstr): array
    {
        if ($nvpstr == "") {
            throw new PaypalException('Paypal api call failed with nvpstr: ' . $nvpstr);
        }
        $url = $this->getAPI_ENDPOINT();
        $response = $this->_nh->doPost($url, $nvpstr, 443);
        if (!empty($response)) {
            [$info, $header, $body] = $response;
            if ($info['http_code'] == 200 && !empty($body)) {
                $nvpArray = $this->deformatNVP($body);
                if ($nvpArray['ACK'] == "Success") {
                    return $nvpArray;
                } else {
                    throw new PaypalException('PayPal API call fail, result array: ' . print_r($nvpArray, true));
                }
            } else {
                throw new PaypalException('Error in remote server response: ' . print_r($info, true));
            }
        } else {
            throw new PaypalException('Remote server response is empty ' . print_r($response, true));
        }
    }

    public function IPNListener(string $raw_post_data): bool
    {
        // STEP 1: Read POST data
        // reading posted data from directly from $_POST causes serialization
        // issues with array data in POST
        // reading raw POST data from input stream instead.
        $raw_post_array = explode('&', $raw_post_data);
        $myPost = [];
        foreach ($raw_post_array as $keyval) {
            $keyval = explode('=', $keyval);
            if (count($keyval) == 2) {
                $myPost[$keyval[0]] = urldecode($keyval[1]);
            }
        }
        // read the post from PayPal system and add 'cmd'
        $req = 'cmd=_notify-validate';
        foreach ($myPost as $key => $value) {
            $value = urlencode($value);
            $req .= "&$key=$value";
        }
        // STEP 2: Post IPN data back to paypal to validate
        $response = $this->_nh->doPost($this->getCommand_url(), $req, 443);
        // STEP 3: Inspect IPN validation result and act accordingly
        if (!empty($response)) {
            [$info, $header, $body] = $response;
            if ($info['http_code'] == 200 && !empty($body)) {
                if ($body == "VERIFIED") {
                    return true;
                } elseif ($body == "INVALID") {
                    throw new PaypalException('IPN validation failed, paypal response ' . print_r($body, true));
                } else {
                    throw new PaypalException('IPNs validation failed, paypal response ' . print_r($body, true));
                }
            } else {
                throw new PaypalException('Error in IPN paypal response ' . print_r($info, true));
            }
        } else {
            throw new PaypalException('empty response from paypal');
        }
    }


    /**
     * This function will take NVPString and convert it to an Associative Array and it will decode the response.
     * It is usefull to search for a particular key and displaying arrays.
     *
     * @param string $nvpstr is NVPString.
     * @return array Associative Array.
     */
    public function deformatNVP(string $nvpstr): array
    {
        $intial = 0;
        $nvpArray = [];

        while (strlen($nvpstr)) {
            //postion of Key
            $keypos = strpos($nvpstr, '=');
            //position of value
            $valuepos = strpos($nvpstr, '&') ?: strlen($nvpstr);
            //getting the Key and Value values and storing in a Associative Array
            $keyval = substr($nvpstr, $intial, $keypos);
            $valval = substr($nvpstr, $keypos + 1, $valuepos - $keypos - 1);
            //decoding the respose
            $nvpArray[urldecode($keyval)] = urldecode($valval);
            $nvpstr = substr($nvpstr, $valuepos + 1, strlen($nvpstr));
        }
        return $nvpArray;
    }
}
