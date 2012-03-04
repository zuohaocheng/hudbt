<html>
<body>
<?php
$url="htTp://www.baidu.com/mp3?uid=1";
preg_match('@^([a-z][a-z0-9.+-]*):@i', $url, $reg);
echo $reg[0];
echo "</br>";
echo $reg[1];
echo "</br>";
$url=substr($url,strlen($reg[0]));
echo $url;
echo "</br>";
preg_match('@^//([^/#?]+)@', $url, $reg); 
echo $reg[1];
echo "</br>";
$url = substr($url, strlen($reg[0]));
echo $url;
echo "</br>";
$i = strcspn($url, '?#');
echo substr($url, 0, $i);
echo "</br>";
$url = substr($url, $i);
echo $url;
echo "</br>";
?>
</body>
</html>