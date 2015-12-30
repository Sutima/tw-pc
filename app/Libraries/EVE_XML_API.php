<?php

namespace App\Libraries;

use App\Models\eveCharacterModel;
use App\Exceptions\eveAPIException;

class EVE_XML_API
{
    /**
     * EVE XML API server address
     *
     * @var string
     */
    protected $url;

    /**
     * Identifier sent to EVE API
     *
     * @var string
     */
    protected $useragent;

    /**
     * Cache date sent from last API call
     *
     * @var string
     */
    public $cachedUntil;

    /**
     * Last API curl error
     *
     * @var string
     */
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

        //@set_exception_handler(array($this, 'exceptionHandler'));
        /*
        App::error(function(eveAPIException $exception, $code)
        {
            return 'Debug: CustomException<br/>';
        });
        */
	}

    public function exceptionHandler($exception)
    {
        return 'Exception: ' . $exception;
    }

    /**
     * Parses API curl call for cached until date
     *
     * @return void
     */
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

    /**
     * Performs all curl requests sent to EVE API server
     * Checks curl response HTTP status and stores error message in $apiError
     * returning -1. Also calls getCachedUntil() if successful.
     * NOTE: CURLOPT_SSL_VERIFYPEER fails with some OSes
     *
     * @param string $url The url string that points to the specific API end-point
     * @param array $params The key => value pairs of query values to send
     * @return string The curl request response body
     */
    private function call($url, $params)
    {
        $url = $this->url . $url;

        $curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($params));
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
		curl_setopt($curl, CURLOPT_USERAGENT, $this->useragent);

		$result = curl_exec($curl);
        $http_status = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        if ($http_status == 403 || $http_status == 400) {
            throw new eveAPIException('API key not found, check Key and vCode fields');
        } else if ($http_status == 404) {
            throw new eveAPIException('EVE API server cannot be reached');
        } else if ($http_status !== 200) {
            throw new eveAPIException('Unknown EVE API error');
        }

		$this->getCachedUntil($result);
		return $result;
    }

    /**
     * Compares or returns the EVE API key mask
     *
     * @param string $keyID The EVE API key ID
     * @param string $vCode The EVE API key vCode
     * @param number $mask optional If passed compares and returns 0 or 1
     * @return mixed If $mask is passed returns 0 or 1 otherwise returns the mask
     */
    public function checkMask($keyID, $vCode, $mask = false)
    {
        $url = '/account/APIKeyInfo.xml.aspx';
		$params = array('keyId' => $keyID, 'vCode' => $vCode);

		if ($xml = @simplexml_load_string($this->call($url, $params))) {
			$xpath = $xml->xpath('//key[@accessMask]');

            if ($mask) {
                return $xpath[0]->attributes()->accessMask == $mask ? 1 : 0;
            } else {
                return $xpath[0]->attributes()->accessMask;
            }
		}

		return false;
    }

    /**
     * Gathers EVE character info from any API key and returns a keyed array of characters
     *
     * @param string $keyID The EVE API key ID
     * @param string $vCode The EVE API vCode
     * @return array [Character ID] => Array
     */
    public function getCharacters($keyID, $vCode)
    {
		$url = '/account/Characters.xml.aspx';
		$params = array('keyId' => $keyID, 'vCode' => $vCode);
        $results = array();

		if ($xml = @simplexml_load_string($this->call($url, $params))) {
			$xpath = $xml->xpath('//rowset[@name=\'characters\']/row');

			foreach ($xpath as $xmlRow) {
				$result = new eveCharacterModel;

				$result->characterID = $xmlRow['characterID']->__toString();
				$result->characterName = $xmlRow['name']->__toString();
				$result->corporationID = $xmlRow['corporationID']->__toString();
				$result->corporationName = $xmlRow['corporationName']->__toString();
				$result->allianceID = $xmlRow['allianceID']->__toString();
				$result->allianceName = $xmlRow['allianceName']->__toString();
				$result->factionID = $xmlRow['factionID']->__toString();
				$result->factionName = $xmlRow['factionName']->__toString();

				$results[$result->characterID] = $result;
			}

			return count($results) > 0 ? $results : 0;
		}

		return false;
	}
}
