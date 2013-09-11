<?php
if (function_exists('stdhead')) {
  stdhead($title_for_layout);
}
echo $content_for_layout;

if (function_exists('stdfoot')) {
  stdfoot();
}
?>