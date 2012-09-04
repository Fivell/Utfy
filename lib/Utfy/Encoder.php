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

require_once __DIR__ . '/Utils.php';

/**
 * Encoder
 * Class to encode strings to utf-8
 */
class Encoder 
{

    /**
     * Encodes string from given encoding to utf-8
     * @param type $string
     * @param type $currentEncoding
     * @return string
     * @throws EncoderException 
     */
    public function encode($string, $currentEncoding = 'UTF-8') 
    {
        
        if (!in_array(strtolower(trim($currentEncoding)), array_map('strtolower', Utils::getEncodings()), 1)) {
            throw new EncoderException("Unsupported encoding");
        } else {

            return mb_convert_encoding($string, 'UTF-8', $currentEncoding);
        }
    }

}

/**
 * Specific Encoding exception
 * 
 */
class EncoderException extends \Exception {}