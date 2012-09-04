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


/**
 * Utils
 * Class with needed library utils
 * 
 */
class Utils {
   
    /**
     * supported encodings 
     * @var array 
     */
    private static $_encodings = NULL;
    
    /**
     * supported content types
     * @var array 
     */
    private static $_contentTypes = array('application/rss+xml', 'application/xml', 'text/xml', 'text/html');
            
    /**
     * Get list of supported content types
     * @return array 
     */
    public static function getContentTypes()
    {
        
        return self::$_contentTypes;
        
    }
    
    /**
     * Get list of supported encodings
     * @return array 
     */
    public static function getEncodings()
    {
        if(!self::$_encodings){
            self::$_encodings = array_diff(mb_list_encodings(), 
                    array('pass', 'auto', 'wchar', 'byte2be', 'byte2le', 'byte4be',
                          'byte4le', 'BASE64', 'UUENCODE', 'HTML-ENTITIES', 
                          'Quoted-Printable', '7bit', '8bit'));
        }
        return self::$_encodings;
    }
    
}

