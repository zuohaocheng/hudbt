<?php

require_once 'HTML/BBCodeParser/Filter.php';

class HTML_BBCodeParser_Filter_Span extends HTML_BBCodeParser_Filter {

    /**
    * An array of tags parsed by the engine
    *
    * @access   private
    * @var      array
    */
  var $_definedTags = [
		       'span' => ['htmlopencallback' => ['HTML_BBCodeParser_Filter_Span', 'spanCallback'],
				  'htmlclose' => 'span',
				  'allowed' => 'all',
				  'attributes' => ['style' =>'']],
		       ];

  static function spanCallback($tag) {
    $out = 'span';
    if (!stristr($tag['attributes']['style'], 'fixed')) {
      $out .= ' style="' . $tag['attributes']['style'] . '"';
    }
    return $out;
  }

}

