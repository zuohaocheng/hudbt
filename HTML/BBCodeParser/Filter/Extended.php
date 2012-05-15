<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | PHP Version 4                                                        |
// +----------------------------------------------------------------------+
// | Copyright (c) 1997-2003 The PHP Group                                |
// +----------------------------------------------------------------------+
// | This source file is subject to version 2.02 of the PHP license,      |
// | that is bundled with this package in the file LICENSE, and is        |
// | available at through the world-wide-web at                           |
// | http://www.php.net/license/2_02.txt.                                 |
// | If you did not receive a copy of the PHP license and are unable to   |
// | obtain it through the world-wide-web, please send a note to          |
// | license@php.net so we can mail you a copy immediately.               |
// +----------------------------------------------------------------------+
// | Author: Stijn de Reede <sjr@gmx.co.uk>                               |
// +----------------------------------------------------------------------+
//
// $Id: Extended.php,v 1.3 2007/07/02 16:54:25 cweiske Exp $
//

/**
* @package  HTML_BBCodeParser
* @author   Stijn de Reede  <sjr@gmx.co.uk>
*/


require_once 'HTML/BBCodeParser/Filter.php';




class HTML_BBCodeParser_Filter_Extended extends HTML_BBCodeParser_Filter
{

    /**
    * An array of tags parsed by the engine
    *
    * @access   private
    * @var      array
    */
    var $_definedTags = array(
                                'color' => array( 'htmlopen'  => 'span',
                                                'htmlclose' => 'span',
                                                'allowed'   => 'all',
                                                'attributes'=> array('color' =>'style=%2$scolor:%1$s%2$s')),
                                'size' => array( 'htmlopen'  => 'font',
                                                'htmlclose' => 'font',
                                                'allowed'   => 'all',
                                                'attributes'=> array('size' =>'size=%2$s%1$s%2$s')),
                                'font' => array( 'htmlopen'  => 'span',
                                                'htmlclose' => 'span',
                                                'allowed'   => 'all',
                                                'attributes'=> array('font' =>'style=%2$sfont-family:%1$s%2$s')),
                                'align' => array( 'htmlopen'  => 'div',
                                                'htmlclose' => 'div',
                                                'allowed'   => 'all',
                                                'attributes'=> array('align' =>'style=%2$stext-align:%1$s%2$s')),
                                'quote' => array('htmlclose' => 'blockquote></fieldset',
                                                'allowed'   => 'all',
						 'attributes'=> array('quote' =>'cite=%2$s%1$s%2$s'),
						 'htmlopencallback' => array('HTML_BBCodeParser_Filter_Extended', 'quoteCallback')),
                                'code' => array('htmlopen'  => 'code',
                                                'htmlclose' => 'code',
                                                'allowed'   => 'none^url',
                                                'attributes'=> array()),
				'pre' => array('htmlopen'  => 'pre',
                                                'htmlclose' => 'pre',
                                                'allowed'   => 'none^url',
                                                'attributes'=> array()),
                                'h1' => array('htmlopen'  => 'h1',
                                                'htmlclose' => 'h1',
                                                'allowed'   => 'all',
                                                'attributes'=> array()),
                                'h2' => array('htmlopen'  => 'h2',
                                                'htmlclose' => 'h2',
                                                'allowed'   => 'all',
                                                'attributes'=> array()),
                                'h3' => array('htmlopen'  => 'h3',
                                                'htmlclose' => 'h3',
                                                'allowed'   => 'all',
                                                'attributes'=> array()),
                                'h4' => array('htmlopen'  => 'h4',
                                                'htmlclose' => 'h4',
                                                'allowed'   => 'all',
                                                'attributes'=> array()),
                                'h5' => array('htmlopen'  => 'h5',
                                                'htmlclose' => 'h5',
                                                'allowed'   => 'all',
                                                'attributes'=> array()),
                                'h6' => array('htmlopen'  => 'h6',
                                                'htmlclose' => 'h6',
                                                'allowed'   => 'all',
					      'attributes'=> array()),
				'hr' => array('htmlopen' => 'hr',
					      'htmlclose' => '',
					      'allowed' => 'none',
					      'attributes'=> array()),
    );


    static function quoteCallback($tag) {
      $out = 'fieldset>';
      if ($tag['attributes']['quote']) {
	global $lang_functions;
	$out .= '<legend>' . $lang_functions['text_quote'] . ': ' . $tag['attributes']['quote'] . '</legend>';
      }
      $out .= '<blockquote';
      return $out;
    }

   function _preparse() {
	$options = PEAR::getStaticProperty('HTML_BBCodeParser','_options');
	$o  = $options['open'];
        $c  = $options['close'];
        $oe = $options['open_esc'];
        $ce = $options['close_esc'];

	$this->_preparsed = preg_replace_callback(
					 "!".$oe."pre".$ce."(.*)".$oe."/pre".$ce."!Ui",
					 array($this, 'removeBr'),
					 $this->_text);
	$this->_preparsed = preg_replace(
					 "!".$oe."hr".$ce."!Ui",
					 $o."hr".$c.$o."/hr".$c,
					 $this->_preparsed);
   }

   function removeBr($s) {
     $so = preg_replace('!<br />!', "\n", $s[1]);
     $so = preg_replace('!\[!', '&#91;', $so);
     $options = PEAR::getStaticProperty('HTML_BBCodeParser','_options');
     $o  = $options['open'];
     $c  = $options['close'];
     return $o . 'pre' . $c . $so . $o . '/pre' . $c;
   }

}

?>