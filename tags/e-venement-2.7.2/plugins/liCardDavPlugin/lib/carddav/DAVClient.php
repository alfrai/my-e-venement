<?php

//namespace Sabre\DAV;

/**
 * SabreDAV DAV client
 *
 * This client wraps around Curl to provide a convenient API to a WebDAV
 * server.
 *
 * NOTE: This class is experimental, it's api will likely change in the future.
 *
 * @copyright Copyright (C) 2007-2013 fruux GmbH (https://fruux.com/).
 * @author Evert Pot (http://evertpot.com/)
 * @license http://code.google.com/p/sabredav/wiki/License Modified BSD License
 */
class DAVClient extends Sabre\DAV\Client {

    /**
     * Performs an actual HTTP request, and returns the result.
     *
     * If the specified url is relative, it will be expanded based on the base
     * url.
     *
     * The returned array contains 3 keys:
     *   * body - the response body
     *   * httpCode - a HTTP code (200, 404, etc)
     *   * headers - a list of response http headers. The header names have
     *     been lowercased.
     *
     * @param string $method
     * @param string $url
     * @param string $body
     * @param array $headers
     * @return array
     */
    public function request($method, $url = '', $body = null, $headers = array()) {

        $url = $this->getAbsoluteUrl($url);

        $curlSettings = array(
            CURLOPT_RETURNTRANSFER => true,
            // Return headers as part of the response
            CURLOPT_HEADER => true,
            CURLOPT_POSTFIELDS => $body,
            // Automatically follow redirects
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 5,
        );

        if($this->verifyPeer !== null) {
            $curlSettings[CURLOPT_SSL_VERIFYPEER] = $this->verifyPeer;
        }

        if($this->trustedCertificates) {
            $curlSettings[CURLOPT_CAINFO] = $this->trustedCertificates;
        }

        switch ($method) {
            case 'HEAD' :

                // do not read body with HEAD requests (this is necessary because cURL does not ignore the body with HEAD
                // requests when the Content-Length header is given - which in turn is perfectly valid according to HTTP
                // specs...) cURL does unfortunately return an error in this case ("transfer closed transfer closed with
                // ... bytes remaining to read") this can be circumvented by explicitly telling cURL to ignore the
                // response body
                $curlSettings[CURLOPT_NOBODY] = true;
                $curlSettings[CURLOPT_CUSTOMREQUEST] = 'HEAD';
                break;

            default:
                $curlSettings[CURLOPT_CUSTOMREQUEST] = $method;
                break;

        }

        // Adding HTTP headers
        $nHeaders = array();
        foreach($headers as $key=>$value) {

            $nHeaders[] = $key . ': ' . $value;

        }
        $curlSettings[CURLOPT_HTTPHEADER] = $nHeaders;

        if ($this->proxy) {
            $curlSettings[CURLOPT_PROXY] = $this->proxy;
        }

        if ($this->userName && $this->authType) {
            $curlType = 0;
            if ($this->authType & self::AUTH_BASIC) {
                $curlType |= CURLAUTH_BASIC;
            }
            if ($this->authType & self::AUTH_DIGEST) {
                $curlType |= CURLAUTH_DIGEST;
            }
            $curlSettings[CURLOPT_HTTPAUTH] = $curlType;
            $curlSettings[CURLOPT_USERPWD] = $this->userName . ':' . $this->password;
        }

        list(
            $response,
            $curlInfo,
            $curlErrNo,
            $curlError
        ) = $this->curlRequest($url, $curlSettings);

        $headerBlob = substr($response, 0, $curlInfo['header_size']);
        $response = substr($response, $curlInfo['header_size']);

        // In the case of 100 Continue, or redirects we'll have multiple lists
        // of headers for each separate HTTP response. We can easily split this
        // because they are separated by \r\n\r\n
        $headerBlob = explode("\r\n\r\n", trim($headerBlob, "\r\n"));

        // We only care about the last set of headers
        $headerBlob = $headerBlob[count($headerBlob)-1];

        // Splitting headers
        $headerBlob = explode("\r\n", $headerBlob);

        $headers = array();
        foreach($headerBlob as $header) {
            $parts = explode(':', $header, 2);
            if (count($parts)==2) {
                $headers[strtolower(trim($parts[0]))] = trim($parts[1]);
            }
        }

        $response = array(
            'body' => $response,
            'statusCode' => $curlInfo['http_code'],
            'headers' => $headers
        );

        if ($curlErrNo) {
            throw new Exception('[CURL] Error while making request: ' . $curlError . ' (error code: ' . $curlErrNo . ')');
        }

        if ($response['statusCode']>=400) {
            switch ($response['statusCode']) {
                case 400 :
                    throw new liCardDavResponseException(sprintf('400 Bad request (based on: %s)', $method.' '.$url));
                case 401 :
                    throw new liCardDavResponseException(sprintf('401 Not authenticated (based on: %s)', $method.' '.$url));
                case 402 :
                    throw new liCardDavResponseException(sprintf('402 Payment required (based on: %s)', $method.' '.$url));
                case 403 :
                    throw new liCardDavResponseException(sprintf('403 Forbidden (based on: %s)', $method.' '.$url));
                case 404:
                    throw new liCardDavResponse404Exception(sprintf('404 Resource not found. (based on: %s)', $method.' '.$url));
                case 405 :
                    throw new liCardDavResponseException(sprintf('405 Method not allowed (based on: %s)', $method.' '.$url));
                case 409 :
                    $extra = '';
                    if ( isset($nHeaders['If-Match']) || isset($nHeaders['If-Not-Match']) )
                      $extra = ' '.print_r($nHeaders,true);
                    throw new liCardDavResponseException(sprintf('409 Conflict (based on "%s"%s)', $method.' '.$url, $extra));
                case 412 :
                    throw new liCardDavResponseException(sprintf('412 Precondition failed (based on: %s)', $method.' '.$url));
                case 416 :
                    throw new liCardDavResponseException(sprintf('416 Requested Range Not Satisfiable (based on: %s)', $method.' '.$url));
                case 500 :
                    throw new liCardDavResponseException(sprintf('500 Internal server error (based on: %s)', $method.' '.$url));
                case 501 :
                    throw new liCardDavResponseException(sprintf('501 Not Implemented (based on: %s)', $method.' '.$url));
                case 507 :
                    throw new liCardDavResponseException(sprintf('507 Insufficient storage (based on: %s)', $method.' '.$url));
                default:
                    throw new liCardDavResponseException(sprintf('HTTP error response. (errorcode ' . $response['statusCode'] . ') (based on: %s)', $method.' '.$url));
            }
        }

        return $response;

    }

}
