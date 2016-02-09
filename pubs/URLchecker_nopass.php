<?php
// URLchecker_nopass.php checks the links on an associated page
// written by Rob Young

echo "<h1>CNS Openhouse Link Checker</h1><hr>";

$url = array();
if( $_POST['URL'] ){
  $url[] = $_POST['URL'];
}else{
  $url[] = $_SERVER['HTTP_REFERER'];
}

$tmpstr = explode("/",$url[0]);
$baseURL = $tmpstr[2];
    
$script = $_SERVER['SCRIPT_NAME'];
$pathArr = explode('/',$script);
$path = implode('/',array_slice ($pathArr,0,count($pathArr)-1));
$server = $_SERVER['SERVER_NAME'];
$baseURL = "http://" . $server . $path;

$new_urls = array();
$broken = array();
$working = array();
$callPage = array();

for($i=0; $i<2; $i++){
  if($i == 1){
    $url = $new_urls;
  }
  foreach($url as $curr_url){
    $remote = @fopen($curr_url, 'r');
    $html = fread($remote, 1048576);  // only read up to 1MB
    fclose($remote);
    
    $urls = '(http|telnet|gopher|file|wais|ftp)';
    $ltrs = '\w';
    $gunk = '/#~:.?+=&%@!\-, ';
    $punc = '.:?\-';
    $quote = '"';
    $any = "$ltrs$gunk$punc";
    
    preg_match_all("{\b$urls :[$any] +?(?=[$punc]*[^$any]|$)}x", $html, $matches);
    preg_match_all('/a href=".*(.html|.php).*"/i',$html,$matches2);
    $links = array();
    foreach ($matches2[0] as $u){
      if(!strpos($u,"http")){
	$tmparr = explode('"',$u);
	if($tmparr[1]{0} != '/'){
	  $links[] = $baseURL . '/' . $tmparr[1];
	}
      }
    }
    
    foreach ($matches[0] as $u) {
      $tmpstr2 = explode(".",$u);
      if(!in_array($u,$broken) && !in_array($u,$working)){
	$fp = @fopen($u,"r");
	if($fp){
	  $working[] = $u;
	  fclose($fp);
	  if($i == 0){
	    $tmpstr = explode("/",$u);
	    if($tmpstr[2] != "www.w3.org" && $tmpstr[2] != "www.nyu.edu" && 
	       $tmpstr[2] != "www.google.com"){
	      $new_urls[] = $u;
	    }
	  }
	}else{
	  $broken[] = $u;
	  $callPage[] = $curr_url;
	}
      }
    }
      
    foreach ($links as $u) {
      $tmpstr2 = explode(".",$u);
      $foo = $tmpstr[count($tmpstr)-1];
      if(!in_array($u,$broken) && !in_array($u,$working)){
	$fp = @fopen($u,"r");
	if($fp){
	  $working[] = $u;
	  fclose($fp);
	  if($i == 0){
	    $new_urls[] = $u;
	  }
	}else{
	  $broken[] = $u;
	  $callPage[] = $curr_url;
	}
      }
    }
  }
}

if(count($broken) == 0){
  echo "<h2>There were no broken links.</h2>";
}else{
  $tmpurl = '';
  $len = count($broken);
  for($i=0; $i<$len; $i++){
    if(strcmp($tmpurl,$callPage[$i]) != 0){
      $tmpurl = $callPage[$i];
      echo "<p>On <font color=\"red\">$tmpurl</font> the following link(s) are broken:<br>";
    }
    echo "<A HREF=\"$broken[$i]\">$broken[$i]</A><br>";
  }
}



?>
