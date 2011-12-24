<?php
require_once 'HTML/BBCodeParser/Filter.php';

class HTML_BBCodeParser_Filter_Flash extends HTML_BBCodeParser_Filter {
  var $_definedTags = array(
        'object' => array(
            'htmlopen'  => 'object',
            'htmlclose' => 'object',
            'allowed'   => 'none^param,embed',
            'attributes'=> array(
                'w'     => 'width=%2$s%1$d%2$s',
                'h'     => 'height=%2$s%1$d%2$s',
            )
	),
        'param' => array(
            'htmlopen'  => 'param',
            'htmlclose' => '',
            'allowed'   => 'none',
            'attributes'=> array(
				 'param' => 'name=%2$s%1$s%2$s',
				'value' => 'value=%2$s%1$s%2$s'
            )
	),	
        'embed' => array(
            'htmlopen'  => 'embed type="application/x-shockwave-flash"',
            'htmlclose' => '',
            'allowed'   => 'none',
            'attributes'=> array(
                'w'     => 'width=%2$s%1$d%2$s',
                'h'     => 'height=%2$s%1$d%2$s',
		'embed' => 'src=%2$s%1$s%2$s'
            )
	)
    );

  function _preparse() {
    $options = PEAR::getStaticProperty('HTML_BBCodeParser','_options');
    $o  = $options['open'];
    $c  = $options['close'];
    $oe = $options['open_esc'];
    $ce = $options['close_esc'];
    $this->_preparsed =
      preg_replace(
		   "!".$oe."flash(\s?.*)".$ce."(.*)".$oe."/flash".$ce."!Ui",
		   $o."object$1".$c.$o."param=movie value=\"$2\"".$c.$o.'/param'.$c.$o."embed=\"\$2\"\$1".$c . $o."/embed".$c.$o."/object".$c,
		   $this->_text);

    $flv_prefix = 'flvplayer.swf?file=';
    $this->_preparsed =
      preg_replace(
    		   "!".$oe."flv(\s?.*)".$ce."(.*)".$oe."/flv".$ce."!Ui",
  		   $o."object$1".$c.$o."param=movie value=\"$flv_prefix$2\"".$c.$o.'/param'.$c.$o.'param=fullscreen value=true'.$c.$o.'/param'.$c.$o."embed=\"$flv_prefix$2\"\$1".$c . $o."/embed".$c.$o."/object".$c,
    		   $this->_preparsed);
  }
}
?>