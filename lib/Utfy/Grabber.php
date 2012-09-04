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
require_once __DIR__ . '/PageData.php';

/**
 * Class for grabbing content by url with curl
 *
 * @author Fedoronchuk Igor
 */
class Grabber 
{

    /**
     * curl handle
     * @var resource 
     */
    protected $_ch = NULL;

    /**
     * array of http responses
     * @var array 
     */
    protected $_response = array();

    /**
     * array of default curl options
     * @var array 
     */
    protected $_defaultOptions = array();

    /**
     * Constructor
     * @param array $defaultOptions <p>
     * array containing curl options
     * </p>
     */
    public function __construct($defaultOptions = array()) 
    {

        $this->_defaultOptions = $defaultOptions + array(
            CURLOPT_HEADER => TRUE,
            CURLOPT_RETURNTRANSFER => TRUE,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_MAXREDIRS => 5);
    }

    /**
     * Add url with custom curl options for grabbing
     * @param string $url
     * @param array $options 
     */
    public function addUrl($url, $options = array()) 
    {

        $options = $this->_defaultOptions + $options;
        if (is_array($url)) {
            foreach ($url as $value) {
                $options[CURLOPT_URL] = $value;
                $this->_options[$value] = $options;
            }
        } else {
            $options[CURLOPT_URL] = $url;
            $this->_options[$url] = $options;
        }
    }

    /**
     * Get responses
     * @return array 
     */
    public function getResponse() 
    {
        return $this->_response;
    }

    /**
     * Use curl to grab single page
     */
    protected function singleExecute() 
    {

        $this->_ch = curl_init();
        $this->curlSetOptArray($this->_ch, reset($this->_options));
        $url = key($this->_options);
        $this->_result[$url] = array();

        $this->_response[$url] = new PageData(curl_exec($this->_ch), curl_getinfo($ch), curl_error($ch));

        curl_close($this->_ch);
    }

    /**
     * Use multicurl to grab pages
     */
    protected function multiExecute() 
    {
        $curl = array();
        $this->_ch = curl_multi_init();
        foreach ($this->_options as $url => $options) {


            $ch = curl_init();
            $this->curlSetOptArray($ch, $options);
            $curl[$url] = $ch;
            curl_multi_add_handle($this->_ch, $ch);
        }

        $running = null;
        do
            curl_multi_exec($this->_ch, $running); while ($running > 0);

        foreach ($curl as $url => $ch) {
            $this->_result[$url] = array();

            $this->_response[$url] = new PageData(curl_multi_getcontent($ch), curl_getinfo($ch), curl_error($ch));

            curl_multi_remove_handle($this->_ch, $ch);
        }

        curl_multi_close($this->_ch);
    }

    /**
     * Execute page grabbing
     * @return array 
     */
    public function execute() 
    {
        if (!count($this->_options)) {
            return array();
        }
        if (count($this->_options) > 1) {
            $this->multiExecute();
        } else {
            $this->singleExecute();
        }
        return $this->_response;
    }

    /**
     * Set curl options to curl handle 
     * @param resource $ch
     * @param array $options
     * @return boolean 
     */
    protected function curlSetOptArray(&$ch, $options) 
    {

        if (!function_exists('curl_setopt_array')) {
            foreach ($options as $option => $value) {
                if (!curl_setopt($ch, $option, $value)) {
                    return false;
                }
            }
            return true;
        }

        return curl_setopt_array($ch, $options);
    }

}

