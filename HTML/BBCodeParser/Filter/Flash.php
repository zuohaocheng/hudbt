<?php
require_once 'HTML/BBCodeParser/Filter.php';

class HTML_BBCodeParser_Filter_Flash extends HTML_BBCodeParser_Filter {
  var $_definedTags = array(
        'embed' => array(
            'htmlopen'  => 'embed type="application/x-shockwave-flash"',
            'htmlclose' => '',
            'allowed'   => 'none',
            'attributes'=> array(
                'w'     => 'width=%2$s%1$d%2$s',
                'h'     => 'height=%2$s%1$d%2$s',
		'embed' => 'src=%2$s%1$s%2$s'
            )
	),
        'youku' => array(
            'htmlopen'  => 'iframe frameborder="0" allowfullscreen',
            'htmlclose' => 'iframe',
            'allowed'   => 'none',
            'attributes'=> array(
                'w'     => 'width=%2$s%1$d%2$s',
                'h'     => 'height=%2$s%1$d%2$s',
		'youku' => 'src=%2$shttp://player.youku.com/embed/%1$s%2$s'
            )
	),
        'tudou' => array(
            'htmlopen'  => 'iframe frameborder="0" allowfullscreen',
            'htmlclose' => 'iframe',
            'allowed'   => 'none',
            'attributes'=> array(
                'w'     => 'width=%2$s%1$d%2$s',
                'h'     => 'height=%2$s%1$d%2$s',
		'tudou' => 'src=%2$shttp://www.tudou.com/programs/view/html5embed.action?code=%1$s%2$s'
            )
	),
    );

  function _preparse() {
    $pear = new PEAR();
    $options = $pear->getStaticProperty('HTML_BBCodeParser','_options');

    $o  = $options['open'];
    $c  = $options['close'];
    $oe = $options['open_esc'];
    $ce = $options['close_esc'];

    $this->_preparsed = $this->_text;
    
    // Use Youku universal player here
    $this->_preparsed =
      preg_replace(
		   "!".$oe."flash(\s?[^]]*)".$ce."https?://player.youku.com[\.a-z0-9/]*sid/([a-z0-9]+)(?:/[^\[]*)?".$oe."/flash".$ce."!Ui",
		   $o."youku=\"$2\"$1".$c.$o."/youku".$c,
		   $this->_preparsed);

    // Tudou
    $this->_preparsed =
      preg_replace(
		   "!".$oe."flash(\s?[^]]*)".$ce."https?://www.tudou.com/v/([-_a-z0-9]+)(?:[^-_a-z0-9][^\[]*)?".$oe."/flash".$ce."!Ui",
		   $o."tudou=\"$2\"$1".$c.$o."/tudou".$c,
		   $this->_preparsed);


    //Process Youku direct link; technically it's incorrect.
    $this->_preparsed =
      preg_replace(
		   "!".$oe."flash(\s?[^]]*)".$ce."https?://v.youku.com[\._a-z0-9/]*/id_([a-z0-9]+)(?:\.html)".$oe."/flash".$ce."!Ui",
		   $o."youku=\"$2\"$1".$c.$o."/youku".$c,
		   $this->_preparsed);

    //Tudou
    $this->_preparsed =
      preg_replace(
		   "!".$oe."flash(\s?[^]]*)".$ce."https?://www.tudou.com/programs/view/([-_a-z0-9]+)(?:[^-_a-z0-9][^\[]*)?".$oe."/flash".$ce."!Ui",
		   $o."tudou=\"$2\"$1".$c.$o."/tudou".$c,
		   $this->_preparsed);
    
    $flv_prefix = 'flvplayer.swf?file=';
    $this->_preparsed =
      preg_replace(
    		   "!".$oe."flv(\s?.*)".$ce."(.*)".$oe."/flv".$ce."!Ui",
  		   $o."flash$1".$c.$flv_prefix.'$2'.$o.'/flash'.$c,
    		   $this->_preparsed);
    
    $this->_preparsed =
      preg_replace(
		   "!".$oe."flash(\s?.*)".$ce."(.*)".$oe."/flash".$ce."!Ui",
		   $o."embed=\"$2\"$1".$c.$o."/embed".$c,
		   $this->_preparsed);
  }
}
?>