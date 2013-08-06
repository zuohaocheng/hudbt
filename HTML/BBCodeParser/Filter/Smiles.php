<?php
require_once 'HTML/BBCodeParser/Filter.php';

class HTML_BBCodeParser_Filter_Smiles extends HTML_BBCodeParser_Filter {
  function _preparse() {
    global $BASEURL;
    $pear = new PEAR();
    $options = $pear->getStaticProperty('HTML_BBCodeParser','_options');

    $oe = $options['open_esc'];
    $ce = $options['close_esc'];
    $this->_preparsed =
      preg_replace(
		   "!".$oe."em([0-9]+)".$ce."!Ui",
		   '<img src="//' . $BASEURL . '/pic/smilies/\1.gif" alt="[em\1]" />',
		   $this->_text);
  }
}
?>