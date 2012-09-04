<?php

/**
 * Copyright (C) 2011 Igor Fedoronchuk 
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @category DIDWW
 * @author Fedoronchuk Igor <fedoronchuk@gmail.com>
 * @copyright Copyright (C) 2011 by Igor Fedoronchuk 
 * @license MIT
 */

namespace Utfy;

require_once __DIR__ . '/Encoder.php';
require_once __DIR__ . '/Utils.php';

class PageData 
{
    /**
     * regexp for encoding matching from XML
     */

    const ENCODING_XML_REGEXP = '#^<\?xml\s+version=(?:"[^"]*"|\'[^\']*\')\s+encoding=("[^"]*"|\'[^\']*\')#si';

    /**
     * regexp for encoding matching from HTML
     */
    const ENCODING_HTML_REGEXP = '#<[\s]*?meta[^>]+charset=["\'\s]?([\w\d-]+)["\'\s]?[^>]*\\/?>#si';

    /**
     * regexp for encoding and type matching from headers 
     */
    const CONTENT_TYPE_REGEXP = '/^Content-Type:\s+([^;]+)(?:;\s*charset=(.*))?/ium';

    /**
     * curl error string
     * @var string 
     */
    protected $_curlError;

    /**
     * curl info array
     * @var array 
     */
    protected $_curlInfo;

    /**
     * string containing raw response from curl
     * @var string 
     */
    protected $_content;

    /**
     * array of HTTP headers
     * @var array 
     */
    protected $_headers = NULL;

    /**
     *
     * raw HTTP headers
     * @var string 
     */
    protected $_rawHeaders = NULL;

    /**
     * body of page
     * @var string 
     */
    protected $_body = NULL;

    /**
     * encoding of page
     * @var type 
     */
    protected $_encoding = NULL;

    /**
     *
     * encoded utf-8 body
     * @var type 
     */
    protected $_encodedBody = NULL;

    /**
     * Constructor
     * @param type $content
     * @param type $curlInfo
     * @param type $curlError 
     */
    public function __construct($content, $curlInfo, $curlError) 
    {
        $this->_content = $content;
        $this->_curlInfo = $curlInfo;
        $this->_curlError = $curlError;
        if (!$this->_curlError) {
            $this->_splitContent();
        }
    }

    /**
     * Get error
     * @return string 
     */
    public function getError() 
    {
        return $this->_curlError;
    }

    /**
     * Has error ?
     * @return bool 
     */
    public function hasError() 
    {
        return (!empty($this->_curlError));
    }

    /**
     * Get curl info item by key, if key is NULL return array of items 
     * @param string $key
     * @return string 
     */
    public function getCurlInfo($key = NULL) 
    {
        if (!$key) {
            return $this->_curlInfo;
        } elseif (!isset($this->_curlInfo[$key])) {
            return NULL;
        }
        return $this->_curlInfo[$key];
    }

    /**
     * Get raw content
     * @return string 
     */
    public function getContent() 
    {
        return $this->_content;
    }

    /**
     * get content HTTP headers
     * @return array 
     */
    public function getHeaders() 
    {

        return $this->_headers;
    }

    /**
     * Get content HTTP headers
     * @return string 
     */
    public function getRawHeaders() 
    {

        return $this->_rawHeaders;
    }

    /**
     * Get content body
     * @return string 
     */
    public function getBody() 
    {

        return $this->_body;
    }

    /**
     * Get content encoding
     * @throws PageException 
     * @return type 
     */
    public function getEncoding() 
    {
        if (is_null($this->_encoding)) {
            $this->_encoding = $this->_detectEncoding();
        }

        return $this->_encoding;
    }

    /**
     * Get UTF-8 encoded body
     * @throws PageException , EncoderException
     *  
     */
    public function getEncodedBody() 
    {
        if (is_null($this->_encodedBody)) {
            $encoding = $this->getEncoding();
            if (strtolower($encoding) != 'utf-8') {
                $encoder = new Encoder();
                $this->_encodedBody = $encoder->encode($this->getBody(), $this->getEncoding());
            } else {
                return $this->_body;
            }
        }
        return $this->_encodedBody;
    }

    /**
     * Detect encoding of content
     * @return string
     * @throws PageException 
     */
    protected function _detectEncoding() 
    {

        if (!$this->_rawHeaders) {
            throw new PageException("Empty HTTP headers");
        }
        if (!preg_match(self::CONTENT_TYPE_REGEXP, $this->_rawHeaders, $match)) {
            throw new PageException("Empty Content-Type");
        }
        if (!in_array(strtolower($this->_headers['Content-Type']), array_map('strtolower', Utils::getContentTypes()), 1)) {
            if (!in_array(strtolower($match[1]), array_map('strtolower', Utils::getContentTypes()), 1)) {
                throw new PageException("Unsupported Content Typee");
            }
        }
        $encoding = NULL;
        if (isset($match[2])) {
            $encoding = trim(trim($match[2], '"\''));
        }

        if (!$encoding) {
            foreach (array(self::ENCODING_HTML_REGEXP, self::ENCODING_XML_REGEXP) as $regexp) {
                if (preg_match($regexp, $this->_body, $match)) {
                    $encoding = trim(trim($match[1], '"\''));
                    break;
                }
            }
        }

        if (!$encoding) {
            $encoding = mb_detect_encoding($this->_body, "auto");
        }
        return $encoding;
    }

    /**
     *
     * Split content on body and HTTP headers 
     */
    protected function _splitContent() 
    {

        if (!is_null($this->_rawHeaders) || !is_null($this->_body)) {
            return;
        }

        //split response to headers and body
        $offset = strpos($this->_content, "\r\n\r\n");
        $this->_rawHeaders = substr($this->_content, 0, $offset);
        $this->_headers = $this->_httpParseHeaders();
        $this->_body = substr($this->_content, $offset + 4);
    }

    /**
     * 
     * Parse HTTP headers
     * @param string $header <p>
     * string containing HTTP headers
     * </p>
     * @return array an array on success & return false for failure;.
     */
    protected function _httpParseHeaders() 
    {
        if (!function_exists('http_parse_headers')) {
            /**
             * replacement for http_parse_headers from
             * http://stackoverflow.com/questions/6368574/how-to-get-the-functionality-of-http-parse-headers-without-pecl
             */
            $retVal = array();
            $fields = explode("\r\n", preg_replace('/\x0D\x0A[\x09\x20]+/', ' ', $this->_rawHeaders));
            foreach ($fields as $field) {
                if (preg_match('/([^:]+): (.+)/m', $field, $match)) {
                    $match[1] = preg_replace('/(?<=^|[\x09\x20\x2D])./e', 'strtoupper("\0")', strtolower(trim($match[1])));
                    if (isset($retVal[$match[1]])) {
                        $retVal[$match[1]] = array($retVal[$match[1]], $match[2]);
                    } else {
                        $retVal[$match[1]] = trim($match[2]);
                    }
                }
            }
            return $retVal;
        }
        return http_parse_headers($this->_headers);
    }

}

/**
 * Specific PageData Exception
 */
class PageException extends \Exception {}
