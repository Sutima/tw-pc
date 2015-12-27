<?php

namespace App\Libraries;

class EVE_XML_API
{
    /**
     * EVE XML API server address
     *
     * @var string
     */
    protected $url;

    protected $useragent;

    public $cachedUntil;

    public $apiError;

    /**
	 * Create a new controller instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
        $this->url = env('EVE_API', 'https://api.eveonline.com');
        $this->useragent = env('EVE_API_USERAGENT', '');
	}

    private function getCachedUntil($curl)
    {
		if ($xmlFile = @simplexml_load_string($curl)) {
			$xpath = $xmlFile->xpath('//cachedUntil');
			$cachedUntil = $xpath[0]->__toString();

			$cachedUntil = date('h:i:s', strtotime($cachedUntil));

			$this->cachedUntil = $cachedUntil;
		} else {
			$this->cachedUntil = null;
		}
    }

    private function call($url, $params)
    {
        //set_exception_handler($this->apiError);
        $url = $this->url . $url;
        //return $url . http_build_query($params);

        $curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($params));
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
		curl_setopt($curl, CURLOPT_USERAGENT, $this->useragent);

		$result = curl_exec($curl);
        $http_status = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        if ($http_status == 403) {
            //throw new Exception('API key not found');
            //return call_user_func($this->apiError, 'API key not found');
            $this->apiError = 'API key not found, check Key and vCode fields';
            return -1;
        } else if ($http_status == 404) {
            $this->apiError = 'EVE API server cannot be reached';
            return -1;
        } else if ($http_status !== 200) {
            $this->apiError = 'Unknown EVE API error';
            return -1;
        }

		$this->getCachedUntil($result);
		return $result;
    }

    public function checkMask($keyID, $vCode, $mask = null)
    {
        $url = '/account/APIKeyInfo.xml.aspx';
		$params = array('keyId' => $keyID, 'vCode' => $vCode);

        //$xpath = "//key[@accessMask]";
        //return $this->call($url, $params);
        $result = $this->call($url, $params);
		if ($xmlFile = @simplexml_load_string($result)) {
			$xpath = $xmlFile->xpath('//key[@accessMask]');

            if ($mask) {
                return $xpath[0]->attributes()->accessMask == $mask ? 1 : 0;
            } else {
                return $xpath[0]->attributes()->accessMask;
            }
		}

		return 0;
    }
}
