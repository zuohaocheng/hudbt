<?php
require_once 'HTML/BBCodeParser/Filter.php';

class HTML_BBCodeParser_Filter_Template extends HTML_BBCodeParser_Filter {
  function _preparse() {
    $this->_preparsed = $this->parseTemplate($this->_text);
  }

  function parseTemplate($text, $parsed = []) {
    $options = PEAR::getStaticProperty('HTML_BBCodeParser','_options');
    $o  = $options['open'];
    $c  = $options['close'];
    $oe = $options['open_esc'];
    $ce = $options['close_esc'];

    return preg_replace_callback(
			      "!".$oe. 'template' ."=([0-9]+)( [^$ce]*)?".$ce."!Ui",
			      function($keys) use ($parsed) {
    $key = 0 + $keys[1];
    if (isset($parsed[$key])) {
      return '{{警告: 循环包含模版' . $keys[1] . '}} ';
    }
    $parsed[$key] = true;
    $query = 'SELECT body FROM posts WHERE id=' . $key . ' LIMIT 1';
    $r = sql_query($query) or sqlerr(__FILE__, __LINE__);
    $a = mysql_fetch_row($r);
    if ($a) {
      $body = $a[0];
      $sargs = $keys[2] . '  ';
      $args = [];
      $k_buf = '';
      $v_buf = '';
      $status = 0;
      $len = strlen($sargs);

      try {
	for ($i = 0; $i < $len; ++$i) {
	  $ch = $sargs[$i];
	  switch ($status) {
	  case 0: // Waiting for key
	    if ($k_buf != '') {
	      $args[$k_buf] = $v_buf;
	      $k_buf = '';
	      $v_buf = '';
	    }
	    
	    if ($ch == '_' || ctype_alpha($ch)) {
	      $k_buf = $ch;
	      $status = 10;
	    }
	    elseif ($ch != ' ') {
	      throw new Exception("Invalid char in key");
	    }
	    break;
	  case 10: // Reading key
	    if ($ch == '=') {
	      $status = 20;
	    }
	    elseif ($ch == '_' || ctype_alnum($ch)) {
	      $k_buf .= $ch;
	    }
	    elseif ($ch == ' ') {
	      $status = 15;
	    }
	    else {
	      throw new Exception("Invalid char in key");
	    }
	    break;
	  case 15: // Waiting for key-value dlm
	    if ($ch == '=') {
	      $status = 20;
	    }
	    elseif ($ch != ' ') {
	      throw new Exception("Invalid char in key");
	    }
	    break;
	  case 20: //Waiting for value
	    if ($ch == '"') {
	      $status = 31;
	    }
	    elseif ($ch == "'") {
	      $status = 32;
	    }
	    elseif ($ch != ' ') {
	      $status = 33;
	      $v_buf = $ch;
	    }
	    break;
	  case 31: //Reading value in ""
	    if ($ch == '"') {
	      $status = 0;
	    }
	    elseif ($ch =="\\") {
	      $status = 36;
	    }
	    else {
	      $v_buf .= $ch;
	    }
	    break;
	  case 36: //Escaping in ""
	    $v_buf .= $ch;
	    $status = 31;
	    break;
	  case 32: //Reading value in ''
	    if ($ch == "'") {
	      $status = 0;
	    }
	    else {
	      $v_buf .= $ch;
	    }
	    break;
	  case 33: //Reading value without ""
	    if ($ch != ' ') {
	      $v_buf .= $ch;
	    }
	    else {
	      $status = 0;
	    }
	    break;
	  default:
	    throw new Exception("Invalid status");
	    break;
	  }
	}
      } catch (Exception $e) {
#	var_dump($e);
      }
      $body = preg_replace_callback('!\{\{\{([_a-z][_a-z0-9]*)\}\}\}!Ui', function($k) use ($args) {
	  if (isset($args[$k[1]])) {
	    return $args[$k[1]];
	  }
	  else {
	    return $k[0];
	  }
	}, $body);

      $body = htmlspecialchars($body, ENT_HTML401 | ENT_NOQUOTES);
      $body = str_replace("\r", "", $body);
      $body = str_replace("\n", " <br />", $body);
      $body = $this->parseTemplate($body, $parsed);

      return $body;
    }
    else {
      return '{{无此帖子}}';
    }
  },
			      $text);
  }


}
