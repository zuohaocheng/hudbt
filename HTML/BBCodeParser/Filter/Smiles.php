<?php
require_once 'HTML/BBCodeParser/Filter.php';

class HTML_BBCodeParser_Filter_Smiles extends HTML_BBCodeParser_Filter {
  function _preparse() {
    $options = PEAR::getStaticProperty('HTML_BBCodeParser','_options');
    $o  = $options['open'];
    $c  = $options['close'];
    $oe = $options['open_esc'];
    $ce = $options['close_esc'];
    $this->_preparsed =
      preg_replace(
		   "!".$oe."em([0-9]+)".$ce."!Ui",
		   '<img src="pic/smilies/\1.gif" alt="[em\1]" />',
		   $this->_text);
  }
}
?>