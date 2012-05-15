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
		       'span' => ['htmlopen' => 'span',
				  'htmlclose' => 'span',
				  'allowed' => 'all',
				  'attributes' => ['style' =>'style=%2$s%1$s%2$s']],
		       ];

}

