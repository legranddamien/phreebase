<?php

namespace Phreebase;

use Phreebase\Exception\APIResponseException;
use Phreebase\Exception\HttpRequestException;
use Phreebase\Exception\HttpResponseException;
use Phreebase\Exception\InvalidArgumentsException;

/**
 * Class Phreebase
 *
 * @category Main
 * @package  Phreebase
 * @author   Bruno M V Souza <241103@gmail.com>
 * @link     Phreebase
 * @licence  https://github.com/brunomvsouza/phreebase/blob/master/LICENSE MIT Licence
 */
class Phreebase {

    // According to https://developers.google.com/freebase/v1/getting-started#libraries
    // ALL requests must be made through https
    const API_BASE_ENDPOINT = 'https://www.googleapis.com/freebase';
    const API_VERSION = 'v1';
    const API_SANDBOX_VERSION = 'v1sandbox';

    const SEARCH_API_NAME = 'search';
    const TOPIC_API_NAME = 'topic';
    const MQLREAD_API_NAME = 'mqlread';
    const MQLWRITE_API_NAME = 'mqlwrite';

    private $authenticationToken;
    private $useSandbox = false;

    /**
     * Class constructor
     *
     * @param string $authenticationToken Can be the Google API Key.
     *   This client does not support OAuth 2.0 token
     * @param bool $useSandbox See more at:
     *   https://developers.google.com/freebase/v1/getting-started#environments-and-versions
     */
    public function __construct($authenticationToken, $useSandbox = false) {
        $this->authenticationToken = $authenticationToken;
        $this->useSandbox = $useSandbox;
    }

    /**
     * Search endpoint: Find entities by keyword search and other constraints
     *
     * @param array $parameters Search parameters. See all possible values see:
     *   - https://developers.google.com/freebase/v1/search
     *   - https://developers.google.com/freebase/v1/search-cookbook
     *
     * @return string
     * @throws Exception\InvalidArgumentsException
     */
    public function search(array $parameters) {

        if (!isset($parameters['query']) && !isset($parameters['filter'])) {
            throw new InvalidArgumentsException('You must have one of eigher query or filter parameters;
                See more at https://developers.google.com/freebase/v1/search');
        }

        if (isset($parameters['encode']) && !in_array($parameters['encode'], ['html', 'off'])) {
            throw new InvalidArgumentsException('The acceptable values for encode parameter are:
                - "html": Encode certain characters in the response (such as tags and ampersands) using HTML encoding.
                - "off": No encoding of the response. You should not print the results directly on a web page without HTML-escaping the content first. (default)
                See more at https://developers.google.com/freebase/v1/search');
        }

        if (isset($parameters['stemmed'])) {
            if (isset($parameters['prefixed']) && $parameters['prefixed'] === true) {
                throw new InvalidArgumentsException('Parameter stemmed can\'t be used with parameter prefixed.
                    See more at https://developers.google.com/freebase/v1/search');
            }
        }

        $parameters = Phreebase::sanitizeParameters($parameters);

        $url = sprintf('%s/%s/%s?key=%s&%s', self::API_BASE_ENDPOINT,
            (!$this->useSandbox) ? self::API_VERSION : self::API_SANDBOX_VERSION,
            self::SEARCH_API_NAME, $this->authenticationToken, http_build_query($parameters)
        );

        return $this->doRequest($url);
    }

    /**
     * Topic endpoint: Get a summary of all the information for an entity
     *
     * @param string $topic_id Freebase topic ID of the item that you want data about
     * @param array $parameters Search parameters. See all possible values see:
     *   - https://developers.google.com/freebase/v1/topic
     *
     * @throws Exception\InvalidArgumentsException
     * @return string
     */
    public function topic($topic_id, array $parameters = []) {

        if (isset($parameters['id'])) {
            throw new InvalidArgumentsException('id should be the first argument of the function');
        }

        $parameters = Phreebase::sanitizeParameters($parameters);

        $url = sprintf('%s/%s/%s%s?key=%s&%s', self::API_BASE_ENDPOINT,
            (!$this->useSandbox) ? self::API_VERSION : self::API_SANDBOX_VERSION,
            self::TOPIC_API_NAME, $topic_id, $this->authenticationToken, http_build_query($parameters)
        );

        return $this->doRequest($url);
    }

    /**
     * MQL Read endpoint: Retrieve detailed structured data about entities or collections of entities
     *
     * More about MQL here: http://mql.freebaseapps.com/
     *
     * @param array $query An array envelope containing a single MQL query
     * @param array $parameters Search parameters. See all possible values see:
     *   - https://developers.google.com/freebase/v1/mqlread
     *
     * @throws Exception\InvalidArgumentsException
     * @return string
     */
    public function mqlRead(array $query, array $parameters = []) {

        if (isset($parameters['query'])) {
            throw new InvalidArgumentsException('query should be the first argument of the function');
        }

        if (isset($parameters['uniqueness_failure']) && !in_array($parameters['uniqueness_failure'], ['hard', 'soft'])) {
            throw new InvalidArgumentsException('The acceptable values for uniqueness_failure parameter are:
                - "hard": Be strict - throw an error. (default)
                - "soft": Just return the first encountered object.
                See more at https://developers.google.com/freebase/v1/mqlread');
        }

        $parameters['query'] = json_encode($query);
        $parameters = Phreebase::sanitizeParameters($parameters);

        $url = sprintf('%s/%s/%s?key=%s&%s', self::API_BASE_ENDPOINT,
            (!$this->useSandbox) ? self::API_VERSION : self::API_SANDBOX_VERSION,
            self::MQLREAD_API_NAME, $this->authenticationToken,
            http_build_query($parameters)
        );

        return $this->doRequest($url);
    }

    /**
     * MQL Write endpoint: Write detailed structured data about entities or collections of entities
     *
     * This request requires authorization with at least one of the following scopes
     *   (read more about authentication and authorization).
     *   https://developers.google.com/freebase/v1/mql-overview#mqlwrite-overview
     *
     * More about MQL here: http://mql.freebaseapps.com/
     *
     * @param array $query An array representing a MQL query with write directives
     * @param array $parameters Search parameters. See all possible values see:
     *   - https://developers.google.com/freebase/v1/mqlwrite
     *
     * @throws Exception\InvalidArgumentsException
     * @return string
     */
    public function mqlWrite(array $query, array $parameters = []) {

        if (isset($parameters['query'])) {
            throw new InvalidArgumentsException('query should be the first argument of the function');
        }

        $parameters['query'] = json_encode($query);
        $parameters = Phreebase::sanitizeParameters($parameters);

        $url = sprintf('%s/%s/%s?key=%s&%s', self::API_BASE_ENDPOINT,
            (!$this->useSandbox) ? self::API_VERSION : self::API_SANDBOX_VERSION,
            self::MQLWRITE_API_NAME, $this->authenticationToken,
            http_build_query($parameters)
        );

        return $this->doRequest($url);
    }

    /**
     * Makes the HTTPS request to fetch/write data through Freebase API
     *
     * @param string $url
     *
     * @throws Exception\HttpRequestException
     * @throws Exception\InvalidArgumentsException
     * @throws Exception\HttpResponseException
     * @return array API Response
     */
    private function doRequest($url) {

        if (strpos($url, 'https://') !== 0) {
            throw new InvalidArgumentsException('For security reasons you can do just external requests');
        }

        echo $url;

        $data = @file_get_contents($url);
        if (!$data) {
            $error = error_get_last();
            throw new HttpRequestException($error['message']);
        }

        $data = @json_decode($data, true);
        if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
            switch (json_last_error()) {
                case JSON_ERROR_DEPTH:
                    $errorMessage = 'Maximum stack depth exceeded';
                    break;
                case JSON_ERROR_STATE_MISMATCH:
                    $errorMessage = 'Underflow or the modes mismatch';
                    break;
                case JSON_ERROR_CTRL_CHAR:
                    $errorMessage = 'Unexpected control character found';
                    break;
                case JSON_ERROR_SYNTAX:
                    $errorMessage = 'Syntax error, malformed JSON';
                    break;
                case JSON_ERROR_UTF8:
                    $errorMessage = 'Malformed UTF-8 characters, possibly incorrectly encoded';
                    break;
                default:
                    $errorMessage = 'Unknown error';
                    break;
            }
            throw new HttpResponseException('JSON Error: ' . $errorMessage);
        }

        return $data;
    }

    /**
     * Sanitizes requests parameters based on Freebase API Docs
     *
     * @param array $parameters
     * @return array
     */
    public static function sanitizeParameters(array $parameters) {

        if (isset($parameters['cursor'])) {
            $parameters['cursor'] = (int) $parameters['cursor'];
        }

        if (isset($parameters['exact'])) {
            $parameters['exact'] = ($parameters['exact']) ? 'true' : false;
        }

        if (isset($parameters['indent'])) {
            $parameters['indent'] = ($parameters['indent']) ? 'true' : false;
        }

        if (isset($parameters['limit'])) {
            $parameters['limit'] = ($parameters['limit']) ? 'true' : false;
        }

        if (isset($parameters['prefixed'])) {
            $parameters['prefixed'] = ($parameters['prefixed']) ? 'true' : false;
        }

        if (isset($parameters['stemmed'])) {
            $parameters['stemmed'] = ($parameters['stemmed']) ? 'true' : false;
        }

        if (isset($parameters['raw'])) {
            $parameters['raw'] = ($parameters['raw']) ? 'true' : false;
        }

        if (isset($parameters['html_escape'])) {
            $parameters['html_escape'] = ($parameters['html_escape']) ? 'true' : false;
        }

        return $parameters;
    }
}
