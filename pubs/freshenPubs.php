#!/usr/bin/php -q
<?php
// freshenPubs.php updates cached files.
// This file assumes that publications.php and bib2php.conf are in the 
//    base directory.
// written by Rob Young

include("utils.php");
include("../utils/bib2php-sty.php");

// Read config file
$ini_vars = parse_ini_file("bib2php.conf",true);
$bibfile = $ini_vars['bib2php_vars']['BIBTEX'];
$auxfile = $ini_vars['bib2php_vars']['AUX'];
$absdir = $ini_vars['bib2php_vars']['ABSTRACT'];
$pdfdir = $ini_vars['bib2php_vars']['PDF'];
$authfile = $ini_vars['bib2php_vars']['AUTHOR'];
$journalfile = $ini_vars['bib2php_vars']['JOURNAL'];
$basedir = $ini_vars['bib2php_vars']['BASE1'];
$basedir2 = $ini_vars['bib2php_vars']['BASE2'];
if(strpos($ini_vars['bib2php_vars']['EXCLUDE'],"super") !== false){
  $rmsuper = "on";
}
if(strpos($ini_vars['bib2php_vars']['EXCLUDE'],"conf") !== false){
  $rmconf = "on";
}
if(strpos($ini_vars['bib2php_vars']['EXCLUDE'],"bcttr") !== false){
  $rmbct = "on";
}
if(strpos($ini_vars['bib2php_vars']['EXCLUDE'],"abs") !== false){
  $rmconfa = "on";
}

// create array of all cache files
$filenames = array();
$filenames[] = "CACHE/publications_topic_noconf_noabs_nobct_nosuper.php";
$filenames[] = "CACHE/publications_topic_noconf_noabs_nobct.php";
$filenames[] = "CACHE/publications_topic_noabs_nobct_nosuper.php";
$filenames[] = "CACHE/publications_topic_noconf_nobct_nosuper.php";
$filenames[] = "CACHE/publications_topic_noconf_noabs_nosuper.php";
$filenames[] = "CACHE/publications_topic_noconf_nosuper.php";
$filenames[] = "CACHE/publications_topic_noabs_nosuper.php";
$filenames[] = "CACHE/publications_topic_nobct_nosuper.php";
$filenames[] = "CACHE/publications_topic_noconf_noabs.php";
$filenames[] = "CACHE/publications_topic_noconf_nobct.php";
$filenames[] = "CACHE/publications_topic_noabs_nobct.php";
$filenames[] = "CACHE/publications_topic_nosuper.php";
$filenames[] = "CACHE/publications_topic_noconf.php";
$filenames[] = "CACHE/publications_topic_noabs.php";
$filenames[] = "CACHE/publications_topic_nobct.php";
$filenames[] = "CACHE/publications_topic.php";
$filenames[] = "CACHE/publications_date_noconf_noabs_nobct_nosuper.php";
$filenames[] = "CACHE/publications_date_noconf_noabs_nobct.php";
$filenames[] = "CACHE/publications_date_noabs_nobct_nosuper.php";
$filenames[] = "CACHE/publications_date_noconf_nobct_nosuper.php";
$filenames[] = "CACHE/publications_date_noconf_noabs_nosuper.php";
$filenames[] = "CACHE/publications_date_noconf_nosuper.php";
$filenames[] = "CACHE/publications_date_noabs_nosuper.php";
$filenames[] = "CACHE/publications_date_nobct_nosuper.php";
$filenames[] = "CACHE/publications_date_noconf_noabs.php";
$filenames[] = "CACHE/publications_date_noconf_nobct.php";
$filenames[] = "CACHE/publications_date_noabs_nobct.php";
$filenames[] = "CACHE/publications_date_nosuper.php";
$filenames[] = "CACHE/publications_date_noconf.php";
$filenames[] = "CACHE/publications_date_noabs.php";
$filenames[] = "CACHE/publications_date_nobct.php";
$filenames[] = "CACHE/publications_date.php";
$filenames[] = "CACHE/publications_author_noconf_noabs_nobct_nosuper.php";
$filenames[] = "CACHE/publications_author_noconf_noabs_nobct.php";
$filenames[] = "CACHE/publications_author_noabs_nobct_nosuper.php";
$filenames[] = "CACHE/publications_author_noconf_nobct_nosuper.php";
$filenames[] = "CACHE/publications_author_noconf_noabs_nosuper.php";
$filenames[] = "CACHE/publications_author_noconf_nosuper.php";
$filenames[] = "CACHE/publications_author_noabs_nosuper.php";
$filenames[] = "CACHE/publications_author_nobct_nosuper.php";
$filenames[] = "CACHE/publications_author_noconf_noabs.php";
$filenames[] = "CACHE/publications_author_noconf_nobct.php";
$filenames[] = "CACHE/publications_author_noabs_nobct.php";
$filenames[] = "CACHE/publications_author_nosuper.php";
$filenames[] = "CACHE/publications_author_noconf.php";
$filenames[] = "CACHE/publications_author_noabs.php";
$filenames[] = "CACHE/publications_author_nobct.php";
$filenames[] = "CACHE/publications_author.php";
$filenames[] = "CACHE/publications_type_noconf_noabs_nobct_nosuper.php";
$filenames[] = "CACHE/publications_type_noconf_noabs_nobct.php";
$filenames[] = "CACHE/publications_type_noabs_nobct_nosuper.php";
$filenames[] = "CACHE/publications_type_noconf_nobct_nosuper.php";
$filenames[] = "CACHE/publications_type_noconf_noabs_nosuper.php";
$filenames[] = "CACHE/publications_type_noconf_nosuper.php";
$filenames[] = "CACHE/publications_type_noabs_nosuper.php";
$filenames[] = "CACHE/publications_type_nobct_nosuper.php";
$filenames[] = "CACHE/publications_type_noconf_noabs.php";
$filenames[] = "CACHE/publications_type_noconf_nobct.php";
$filenames[] = "CACHE/publications_type_noabs_nobct.php";
$filenames[] = "CACHE/publications_type_nosuper.php";
$filenames[] = "CACHE/publications_type_noconf.php";
$filenames[] = "CACHE/publications_type_noabs.php";
$filenames[] = "CACHE/publications_type_nobct.php";
$filenames[] = "CACHE/publications_type.php";

// cycle through all files freshening if needed
foreach($filenames as $filename){
  $alltypes = array();
  if(file_exists($filename) && 
     filemtime($bibfile) <= filectime($filename) && 
     filemtime($auxfile) <= filectime($filename) && 
     filemtime("bib2php.conf") <= filectime($filename) && 
     filemtime("utils.php") <= filectime($filename) && 
     filemtime("../utils/lcv.css") <= filectime($filename) && 
     filemtime("freshenPubs.php") <= filectime($filename) &&
     filemtime("../utils/lcvheader_dynamic.html") <= filectime($filename) && 
     filemtime("../utils/lcvfooter.php") <= filectime($filename)){
    // nothing has changed, so no need to make new files.
  }else{
    // set variables based on filename not server calls
    if(strpos($filename,"date")){
      $smethod = "date";
    }elseif(strpos($filename,"author")){
      $smethod = "author";
    }elseif(strpos($filename,"type")){
      $smethod = "type";
    }elseif(strpos($filename,"topic")){
      $smethod = "topic";
    }else{
      $smethod = "date";
      echo "Error finding smethod from $filename! Setting to date.\n";
    }
    if(strpos($filename,"noconf")){
      $rmconf = "on";
    }else{
      $rmconf = "";
    }
    if(strpos($filename,"noabs")){
      $rmconfa = "on";
    }else{
      $rmconfa = "";
    }
    if(strpos($filename,"nobct")){
      $rmbct = "on";
    }else{
      $rmbct = "";
    }
    if(strpos($filename,"nosuper")){
      $rmsuper = "on";
    }else{
      $rmsuper = "";
    }
    
    $allRefs=array();
    $allLoc=array();
    
    //$Nrefs = bibtex2array(&$allRefs,&$allLoc,$bibfile,$ini_vars,$targLoc);
    //readAux(&$allRefs,$allLoc,$auxfile,$ini_vars,$targLoc,&$supersedeLoc);
    $targLoc = "";
    $Nrefs = bibtex2array($allRefs,$allLoc,$bibfile,$ini_vars,$targLoc);
    readAux($allRefs,$allLoc,$auxfile,$ini_vars,$targLoc,$supersedeLoc);

    // if $rmsuper is on create an array of all superseded docs
    $superseded = "";
    if(strcmp($rmsuper,"on") == 0){
      for($i=0;$i<$Nrefs;$i++){
	if(strlen($superseded) == 0){
	  $superseded = $allRefs[$i]->super;
	}else{
	  $superseded = $superseded . "|" . $allRefs[$i]->super;
	}
      }
    }
    
    // remove refs based on aux file and checkboxes
    $newRefs=array();
    $newLoc=array();
    for($i=0;$i<$Nrefs;$i++){
      $pullFlag = 0;
      if(strcmp($allRefs[$i]->omit,"true") == 0){
	$pullFlag = 1;
      }
      if($pullFlag == 0 && strcmp($rmsuper,"on") == 0){
	$tmparr = explode("|",$superseded);
	if(in_array($allRefs[$i]->loc,$tmparr)){
	  $pullFlag = 1;
	}
      }
      if($pullFlag == 0 && strcmp($rmconf,"on") == 0 && 
	 strcmp($allRefs[$i]->type,"INPROCEEDINGS") == 0){
	$pullFlag = 1;
      }
      if($pullFlag == 0 && strcmp($rmconfa,"on") == 0 && 
	 strcmp($allRefs[$i]->type,"CONFABSTRACT") == 0){
	$pullFlag = 1;
      }
      if($pullFlag == 0 && strcmp($rmbct,"on") == 0 && 
	 (strcmp($allRefs[$i]->type,"ARTICLE") != 0 && 
	  strcmp($allRefs[$i]->type,"INPROCEEDINGS") != 0 && 
	  strcmp($allRefs[$i]->type,"CONFABSTRACT") != 0)){
	$pullFlag = 1;
      }
      if($pullFlag == 0){
	$alltypes[] = $allRefs[$i]->type;
	$newRefs[] = $allRefs[$i];
	$newLoc[] = $allLoc[$i];
      }
    }
    $allRefs = array();
    $allRefs = $newRefs;
    $allLoc = array();
    $allLoc = $newLoc;
    $Nrefs = count($allRefs); 
    
    // add references with multiple topics if sorting method is by topic
    if(strcmp($smethod,"topic") == 0){
      for($i=0;$i<$Nrefs;$i++){
	if(strpos($allRefs[$i]->topic,"|") != 0){
	  $topics = explode("|",$allRefs[$i]->topic);
	  $vals2sort[] = $topics[0];
	  $allRefs[$i]->topic = $topics[0];
	  for($j=1;$j<count($topics);$j++){ //we already have one, so start i=1
	    $vals2sort[] = $topics[$j];
	    $tmpRef =& new Ref();
	    $tmpRef = $allRefs[$i];
	    $tmpRef->topic = $topics[$j];
	    $allRefs[] = $tmpRef;
	  }
	}else{
	  $vals2sort[] = $allRefs[$i]->topic;
	}
      }
    }
    $Nrefs = count($allRefs);
    
    // sort object array by date. Most recent given the smallest index value.
    // calculate dateNum for all objects first.
    for($i=0;$i<$Nrefs;$i++){
      $allRefs[$i]->dateNum = $allRefs[$i]->year+($allRefs[$i]->monthNum*0.01);
    }
    
    // now make new array and sort objects by date
    $newRefs = array();
    for($i=0;$i<$Nrefs;$i++){
      if($i == 0){
	$newRefs[$i]=$allRefs[$i];
      }else{
	if($allRefs[$i]->dateNum <= $newRefs[$i-1]->dateNum){
	  $newRefs[$i]=$allRefs[$i];
	}else{
	  $ctr=1;
	  //while($allRefs[$i]->dateNum >= $newRefs[$i-$ctr]->dateNum &&
	  //$i-$ctr >= 0){   // this was >= but caused "Notice:..."
	  while($i-$ctr >= 0 && $allRefs[$i]->dateNum >= $newRefs[$i-$ctr]->dateNum){	    $ctr++;
	  }
	  $ctr--;  // one too many in loop
	           // do I need this now that I fixed above 
	  // now put allRefs[$i] at position $i-$ctr and move others down
	  for($j=$i-1;$j>=$i-$ctr;$j--){
	    $newRefs[$j+1] = $newRefs[$j];
	  }
	  $newRefs[$i-$ctr] = $allRefs[$i];
	}
      }
    }
    
    $fp1 = fopen($filename,"w");
    
    fwrite($fp1,"<html><head>\n");
    fwrite($fp1,"<TITLE>Online Publications: LCV</TITLE>\n");
    fwrite($fp1,"   <META HTTP-EQUIV=\"Content-Type\" CONTENT=\"text/html; charset=iso-8859-1\">\n");
    fwrite($fp1,"   <META NAME=\"GENERATOR\" CONTENT=\"Gnu Emacs v19.34\">\n");
    fwrite($fp1,"<link rel=\"stylesheet\" href=\"utils/lcv.css\" type=\"text/css\">\n");
    fwrite($fp1,"   </HEAD>\n");
    fwrite($fp1,"   <BODY>\n");
    fwrite($fp1,"<a name=\"top\"> </a>\n");
    $tmpstr = "../utils/lcvheader_dynamic.html";
    fwrite($fp1,file_get_contents($tmpstr));
    fwrite($fp1,"<?php include(\"" . $basedir2 . "../utils/bib2php-sty.php\"); ?>");
    fwrite($fp1,"<FONT FACE=\"Helvetica,Arial,sans-serif\" SIZE=+2 COLOR=\"#FF0000\"><b>Selected Online Publications</b></FONT>\n");
    fwrite($fp1,"<form name=\"sortm\" action=\"publications.php\" method=\"get\"><br>");
    //$tmpstr = $_SERVER['SCRIPT_NAME'];
    //fwrite($fp1,"<form name=\"sortm\" action=\"$tmpstr\" method=\"get\"><br>");
    fwrite($fp1,"Sort by:&nbsp;&nbsp; \n");
    if(empty($smethod)){
      fwrite($fp1,"<input type=\"radio\" name=\"smethod\" value=\"date\" onClick=\"sortm.submit();\" checked>Year\n");
      fwrite($fp1,"&nbsp;&nbsp;&nbsp;\n");
      fwrite($fp1,"<input type=\"radio\" name=\"smethod\" value=\"type\" onClick=\"sortm.submit();\">Type\n");
      fwrite($fp1,"&nbsp;&nbsp;&nbsp;\n");
      fwrite($fp1,"<input type=\"radio\" name=\"smethod\" value=\"author\" onClick=\"sortm.submit();\">First Author\n");
    }else{
      switch ($smethod) {
      case "date":    
	fwrite($fp1,'<input type="radio" name="smethod" value="date" onClick="sortm.submit();" checked>Year'."\n");
	fwrite($fp1,'&nbsp;&nbsp;&nbsp;'."\n");
	fwrite($fp1,'<input type="radio" name="smethod" value="type" onClick="sortm.submit();">Type'."\n");
	fwrite($fp1,'&nbsp;&nbsp;&nbsp;'."\n");
	fwrite($fp1,'<input type="radio" name="smethod" value="author" onClick="sortm.submit();">First Author'."\n");
	break;
      case "author":
	fwrite($fp1,'<input type="radio" name="smethod" value="date" onClick="sortm.submit();">Year'."\n");
	fwrite($fp1,'&nbsp;&nbsp;&nbsp;'."\n");
	fwrite($fp1,'<input type="radio" name="smethod" value="type" onClick="sortm.submit();">Type'."\n");
	fwrite($fp1,'&nbsp;&nbsp;&nbsp;'."\n");
	fwrite($fp1,'<input type="radio" name="smethod" value="author" onClick="sortm.submit();" checked>First Author'."\n");
	break;
      case "type":
	fwrite($fp1,'<input type="radio" name="smethod" value="date" onClick="sortm.submit();">Year'."\n");
	fwrite($fp1,'&nbsp;&nbsp;&nbsp;'."\n");
	fwrite($fp1,'<input type="radio" name="smethod" value="type" onClick="sortm.submit();" checked>Type'."\n");
	fwrite($fp1,'&nbsp;&nbsp;&nbsp;'."\n");
	fwrite($fp1,'<input type="radio" name="smethod" value="author" onClick="sortm.submit();">First Author'."\n");
	break;
      case "topic":
	fwrite($fp1,'<input type="radio" name="smethod" value="date" onClick="sortm.submit();">Year'."\n");
	fwrite($fp1,'&nbsp;&nbsp;&nbsp;'."\n");
	fwrite($fp1,'<input type="radio" name="smethod" value="type" onClick="sortm.submit();">Type'."\n");
	fwrite($fp1,'&nbsp;&nbsp;&nbsp;'."\n");
	fwrite($fp1,'<input type="radio" name="smethod" value="author" onClick="sortm.submit();">First Author'."\n");
	break;
      }
    }
    fwrite($fp1,"<br>");
    fwrite($fp1,"Exclude:&nbsp;&nbsp;&nbsp;\n");
    if(strcmp($rmsuper,"on") == 0){
      fwrite($fp1,'<input type="checkbox" name="rmsuper" onClick="sortm.submit();" checked>'."\n");
    }else{
      fwrite($fp1,'<input type="checkbox" name="rmsuper" onClick="sortm.submit();">'."\n");
    }
    fwrite($fp1,"Superseded Papers&nbsp;&nbsp;&nbsp;\n");
    if(strcmp($rmconf,"on") == 0){
      fwrite($fp1,'<input type="checkbox" name="rmconf" onClick="sortm.submit();" checked>'."\n");
    }else{
      fwrite($fp1,'<input type="checkbox" name="rmconf" onClick="sortm.submit();">'."\n");
    }
    fwrite($fp1,"Conference Papers&nbsp;&nbsp;&nbsp;\n");
    if(strcmp($rmbct,"on") == 0){
      fwrite($fp1,'<input type="checkbox" name="rmbct" onClick="sortm.submit();" checked>'."\n");
    }else{
      fwrite($fp1,'<input type="checkbox" name="rmbct" onClick="sortm.submit();">'."\n");
    }
    fwrite($fp1,"Book Chapters, Theses, Tech Reports & Other&nbsp;&nbsp;&nbsp;\n");
    if(strcmp($rmconfa,"on") == 0){
      fwrite($fp1,'<input type="checkbox" name="rmconfa" onClick="sortm.submit();" checked>'."\n");
    }else{
      fwrite($fp1,'<input type="checkbox" name="rmconfa" onClick="sortm.submit();">'."\n");
    }
    fwrite($fp1,"Conference Abstracts&nbsp;&nbsp;&nbsp;\n");
    fwrite($fp1,'</form><p>'."\n");
    
    // compute sorted index based on method and print
    $vals2sort = array();
    switch ($smethod) {
    case "date":
      $allyears = array();
      for($i=0;$i<$Nrefs;$i++){
	$allyears[] = trim($newRefs[$i]->year);
      }
      $uallyears = array_unique($allyears);
      //printLinks($fp1,$uallyears,$uallyears);
      $uallyearsKeys = array_keys($uallyears);
      fwrite($fp1,"<font size=-1>");
      for($i=0;$i<count($uallyears);$i++){
      $tmpval = $allyears[$uallyearsKeys[$i]];
      fwrite($fp1,'<a href="#');
      fwrite($fp1,"$tmpval");
      fwrite($fp1,'"');
      fwrite($fp1,">$tmpval</a>");
      if($i != count($uallyears)-1){
        fwrite($fp1," | ");
      }
      }
      fwrite($fp1,"</font>");
      fwrite($fp1,"<p>");
      $tmpyear = 0;
      for($i=0;$i<$Nrefs;$i++){
	if(trim($newRefs[$i]->year) != trim($tmpyear)){
	  $tmpyear = $newRefs[$i]->year;
	  fwrite($fp1,"<table bgcolor=$barcolor width=100%><tr><td width=\"90%\">");
	  fwrite($fp1,"<font size=+1 color=$bartextcolor><b>");
	  fwrite($fp1,"<a name=$tmpyear>&nbsp;&nbsp;$tmpyear</a>");
	  fwrite($fp1,"</b></font></td><td width=\"10%\" align=\"right\">");
	  if($i != 0){
	    fwrite($fp1,"<a href=\"#top\"><font size=-1 color=$bartextcolor>top</font></a>&nbsp;&nbsp;");
	  }
	  fwrite($fp1,"</td></tr></table>");
	}
	$newRefs[$i]->printSelf($fp1,$basedir,$pdfdir);
      }
      break;
    case "author":
      for($i=0;$i<$Nrefs;$i++){
	//$tmparr = explode(',',$newRefs[$i]->author2[0]);
	//$vals2sort[] = $tmparr[0];
	$vals2sort[] = $newRefs[$i]->author2[0];
      }
      $vals2sort = fixNames($vals2sort);
      for($i=0;$i<count($vals2sort);$i++){
	$newRefs[$i]->authorPrint = $vals2sort[$i];
      }
      $vals2sort2 = $vals2sort;
      sort($vals2sort);
      asort($vals2sort2);
      $raw_keys = array_keys($vals2sort2);
      $unique_auth = array_unique($vals2sort);
      //printLinks($fp1,$unique_auth,$unique_auth);
      $keys = array_keys($unique_auth);
      fwrite($fp1,"<font size=-1>");
      for($i=0;$i<count($unique_auth);$i++){
	$tmpval = trim($vals2sort[$keys[$i]]);
	fwrite($fp1,"<a href=\"#$tmpval\">$tmpval</a>");
	if($i != count($unique_auth)-1){
	  fwrite($fp1," | ");
	}     
      }
      fwrite($fp1,"</font><p>");
      $start = 0;
      for($i=0;$i<count($keys);$i++){
	if($i == 0){
	  $start = 0;
	}else{
	  $start = $keys[$i];
	}
	if($i == count($keys)-1){
	  $end = count($vals2sort);
	}else{
	  $end = $keys[$i+1];
	}
	$tmparr = array_slice($raw_keys,$start,$end-$start);
	$datearr = array();
	for($j=0;$j<count($tmparr);$j++){
	  $datearr[] = $newRefs[$tmparr[$j]]->dateNum;
	}
	arsort($datearr);
	$date_keys = array_keys($datearr);
	// write headers
	for($j=0;$j<count($tmparr);$j++){
	  if($j == 0){
	    $tmpauth = $newRefs[$tmparr[$date_keys[$j]]]->authorPrint;
	    fwrite($fp1,"<table bgcolor=$barcolor width=100%><tr><td width=\"90%\">");
	    fwrite($fp1,"<font size=+1 color=$bartextcolor><b>");
	    fwrite($fp1,"<a name=\"$tmpauth\">&nbsp;&nbsp;$tmpauth</a>");
	    fwrite($fp1,'</b></font></td><td width="10%" align="right">');
	    if($i != 0){
	      fwrite($fp1,'<a href="#top"<font size=-1 color=$bartextcolor>top</font></a>&nbsp;&nbsp;');
	    }
	    fwrite($fp1,"</td></tr></table>");
	  }
	  $newRefs[$tmparr[$date_keys[$j]]]->printSelf($fp1,$basedir,$pdfdir);
	}
      }
      break;
    case "type":
      $DOctr = 1;
      fwrite($fp1,'<font size=-1>');
      $docOrder = array();
      if(strcmp($rmconf,"on") == 0 && strcmp($rmconfa,"on") == 0 && 
	 strcmp($rmbct,"on") == 0){
	// don't display any links
      }else{
	// use order of mapping vars to set order of links and headings
	$DOctr = 1;
	$basetypes = array_keys($ini_vars['type_mappings']);
	$alltypes = array_unique($alltypes);
	foreach($basetypes as $curtype){
	  if(in_array($curtype,$alltypes)){
	    $tmparr = array($curtype=>$DOctr);
	    $DOctr = $DOctr + 1;
	    $docOrder = array_merge($docOrder,$tmparr);
	  }
	}
	foreach($alltypes as $curtype){
	  if(!in_array($curtype,$basetypes)){
	    $tmparr = array($curtype=>$DOctr);
	    $DOctr = $DOctr + 1;
	    $docOrder = array_merge($docOrder,$tmparr);
	  }
	}

	//$printbar = 0;
	$doKeys = array_keys($docOrder);
	//foreach($docOrder as $key => $value){
	for($i=0;$i<count($doKeys);$i++){
	  if(array_key_exists($doKeys[$i],$ini_vars['type_mappings'])){
	    $tmpurl = $doKeys[$i];
	    $fooarr = explode("|",$ini_vars['type_mappings'][$doKeys[$i]]);
	    $tmpname = $fooarr[0];
	  }else{
	    $tmpurl = $doKeys[$i];
	    $tmpname = $doKeys[$i];
	  }
	  fwrite($fp1,"<a href=\"#$tmpurl\">$tmpname</a>");
	  if($i != count($doKeys)-1){
	    fwrite($fp1," | ");
	  }
	}
      }
      fwrite($fp1,'</font><p>');

      $vals2sort = array();
      $vals2sort2 = array();
      if(count($docOrder) > 0){
	for($i=0;$i<$Nrefs;$i++){
	  $vals2sort[] = $docOrder[$newRefs[$i]->type];
	}
      }else{
	for($i=0;$i<$Nrefs;$i++){
	  $vals2sort[] = 0;
	}
      }
      $vals2sort2 = $vals2sort;
      sort($vals2sort);
      asort($vals2sort2);
      $raw_keys = array_keys($vals2sort2);
      $unique_auth = array_unique($vals2sort);
      $keys = array_keys($unique_auth);
      //$start = 0;
      for($i=1;$i<=count($keys);$i++){
	if($i == 1){
	  $start = 0;
	}else{
	  $start = $keys[$i-1];
	}
	if($i == count($keys)){
	  $end = count($vals2sort);
	}else{
	  $end = $keys[$i];
	}
	$tmparr = array_slice($raw_keys,$start,$end-$start);
	$datearr = array();
	for($j=0;$j<count($tmparr);$j++){
	  $datearr[] = $newRefs[$tmparr[$j]]->dateNum;
	}
	arsort($datearr);
	$date_keys = array_keys($datearr);
	for($j=0;$j<count($tmparr);$j++){
	  if($j == 0){
	    $tmpval = $newRefs[$tmparr[$date_keys[$j]]]->type;
	    fwrite($fp1,"<table bgcolor=$barcolor width=100%><tr><td width=\"90%\">\n");
	    fwrite($fp1,"<font size=+1 color=$bartextcolor><b>");
	    fwrite($fp1,"<a name=$tmpval>\n");
	    if(array_key_exists($tmpval,$ini_vars['type_mappings'])){
	      $tagarr = explode('|',$ini_vars['type_mappings'][$tmpval]);
	      $tag = $tagarr[0];
	      fwrite($fp1,"&nbsp;&nbsp;$tag</a></b></font></td>\n");
	    }else{
	      fwrite($fp1,"&nbsp;&nbsp;$tmpval</a></b></font></td>\n");
	    }
	    fwrite($fp1,'<td width="10%" align="right">'."\n");
	    if($i == 1){
	    }else{
	      fwrite($fp1,"<a href=\"#top\"><font size=-1 color=$bartextcolor>top</font></a>&nbsp;&nbsp;\n");
	    }
	    fwrite($fp1,"</td></tr></table>\n");
	  } 
	  $newRefs[$tmparr[$date_keys[$j]]]->printSelf($fp1,$basedir,$pdfdir);
	}
	//$start = $keys[$i+1];
      }
      break;
    case "topic":
      for($i=0;$i<$Nrefs;$i++){
	$vals2sort[] = $newRefs[$i]->topic;
      }
      $vals2sort2 = $vals2sort;
      sort($vals2sort);
      asort($vals2sort2);
      $raw_keys = array_keys($vals2sort2);
      $unique_auth = array_unique($vals2sort);
      $keys = array_keys($unique_auth);
      $topic = "";
      $start = 1;
      fwrite($fp1,"<table width=\"100%\" border=0 cellpadding=\"0%\"><tr>\n");
      for($i=0;$i<count($unique_auth);$i++){
	$tmpval = trim($vals2sort[$keys[$i]],",");
	$foo = explode(" - ",$tmpval);
	if(count($foo) < 2){
	  $foo[] = "";
	} 
	if(strcmp($topic,$foo[0]) != 0){
	  $topic = $foo[0];
	  if($i == 0){
	    if(strcmp($foo[0],"no topic") != 0){
	      fwrite($fp1,"<td valign=top><table cellpadding=\"0%\" cellspacing=\"0%\"border=0 width=\"100%\"><tr><th colspan=2 align=left><a href=\"#$foo[0]\">$foo[0]</a></th></tr>");
	    }
	  }else{
	    if(strcmp($foo[0],"no topic") != 0){
	      fwrite($fp1,"</td></table><td valign=top><table cellpadding=\"0%\" cellspacing=\"0%\"border=0 width=\"100%\"><tr><th colspan=2 align=left><a href=\"#$foo[0]\">$foo[0]</a></th></tr>");
	    }
	  }
	  if(strcmp($foo[0],"no topic") != 0){
	    fwrite($fp1,'<tr><td width=\"20\"></td><td><a href="#');
	    fwrite($fp1,"$tmpval");
	    fwrite($fp1,'"');
	    fwrite($fp1,"><font size=-2><b>$foo[1]</b></font></a></td></tr>\n");
	  }
	}else{
	  if(strcmp($foo[0],"no topic") != 0){
	    fwrite($fp1,'<tr><td width=\"20\"></td><td><a href="#');
	    fwrite($fp1,"$tmpval");
	    fwrite($fp1,'"');
	    fwrite($fp1,"><font size=-2><b>$foo[1]<b></font></a></td></tr>\n");
	  }
	}
      }
      fwrite($fp1,"</table></tr></table><p>\n");
      $start = 0;
      $topic = '';
      //for($i=1;$i<=count($keys);$i++){
      for($i=1;$i<count($keys);$i++){
	if($i == count($keys)){
	  $end = count($vals2sort);
	}else{
	  $end = $keys[$i];
	}
	$tmparr = array_slice($raw_keys,$start,$end-$start);
	$datearr = array();
	for($j=0;$j<count($tmparr);$j++){
	  $datearr[] = $newRefs[$tmparr[$j]]->dateNum;
	}
	arsort($datearr);
	$date_keys = array_keys($datearr);
	for($j=0;$j<count($tmparr);$j++){
	  if($j == 0){
	    $tmpauth = $newRefs[$tmparr[$date_keys[$j]]]->topic;
	    $topics = explode(" - ",$tmpauth);
	    if(count($topics) < 2){
	      $topics[] = "";
	    }
	    $foo = strcmp($topic, $topics[0]);
	    if(strcmp($topic,$topics[0]) != 0){
	      $topic = trim($topics[0]);
	      fwrite($fp1,"<table width=100% bgcolor=$barcolor><tr><td align=center>\n");
	      fwrite($fp1,"<b><font size=+2 color=$bartextcolor><a name=\"");
	      fwrite($fp1,"$topics[0]");
	      fwrite($fp1,'">');
	      fwrite($fp1,"&nbsp;&nbsp;$topics[0]</a>");
	      fwrite($fp1,'</h1></font></b></td><td>');
	      fwrite($fp1,"</td></tr></table>\n");
	      fwrite($fp1,"<table width=\"100%\"><tr><td></td></tr></table>\n");
	    } 
	    fwrite($fp1,"<table border=0 bgcolor=$barcolor width=100%><tr><td width=\"90%\">\n");
	    if($i == 1){
	      switch ($topics[1]){
	      case "Perceptual Image Metrics":
		fwrite($fp1,"<b><font size=+1 color=$bartextcolor>\n");
		fwrite($fp1,"&nbsp;&nbsp;$topics[1]\n");
		fwrite($fp1,"&nbsp;&nbsp;<a link=$barcolor vlink=\"#AA0000\" href=http://www.cns.nyu.edu/~zwang/files/research/ssim name=\"\n");
		fwrite($fp1,"$tmpauth");
		fwrite($fp1,'>"');
		fwrite($fp1,"</b>[ Topic Page ]<b></a>\n");
		break;
	      case "Texture Analysis/Representation/Synthesis":
		fwrite($fp1,"<b><font size=+1 color=$bartextcolor>");
		fwrite($fp1,"&nbsp;&nbsp;$topics[1]</a>\n");
		fwrite($fp1,"&nbsp;&nbsp;<a link=$barcolor vlink=\"#AA0000\" href=http://www.cns.nyu.edu/~lcv/texture name=\"");
		fwrite($fp1,"$tmpauth");
		fwrite($fp1,'">');
		fwrite($fp1,"</b>[ Topic Page ]<b></a>\n");
		break;
	      case "Compression":
		fwrite($fp1,"<b><font size=+1 color=$bartextcolor>");
		fwrite($fp1,"&nbsp;&nbsp;$topics[1]</a>\n");
		fwrite($fp1,"&nbsp;&nbsp;<a link=$barcolor vlink=\"#AA0000\" href=http://www.cns.nyu.edu/~eero/EPWIC name=\"");
		fwrite($fp1,"$tmpauth");
		fwrite($fp1,'">');
		fwrite($fp1,"</b>[ Topic Page ]<b></a>\n");
		break;
	      case "Modeling Physiology":
		fwrite($fp1,"<b><font size=+1 color=$bartextcolor>");
		fwrite($fp1,"&nbsp;&nbsp;$topics[1]</a>\n");
		fwrite($fp1,"&nbsp;&nbsp;<a link=$barcolor vlink=\"#AA0000\" href=http://www.cns.nyu.edu/~eero/MT-model.html name=\"");
		fwrite($fp1,"$tmpauth");
		fwrite($fp1,'">');
		fwrite($fp1,"</b>[ Topic Page ]<b></a>\n");
		break;
	      case "Multi-Scale, Oriented Representations (Steerable Pyramids)":
		fwrite($fp1,"<b><font size=+1 color=$bartextcolor>");
		fwrite($fp1,"&nbsp;&nbsp;$topics[1]</a>\n");
		fwrite($fp1,"&nbsp;&nbsp;<a link=$barcolor vlink=\"#AA0000\" href=http://www.cns.nyu.edu/~eero/STEERPYR name=\"");
		fwrite($fp1,"$tmpauth");
		fwrite($fp1,'">');
		fwrite($fp1,"</b>[ Topic Page ]<b></a>\n");
		break;
	      default:
		fwrite($fp1,"<b><font size=+1 color=$bartextcolor><a name=\"");
		fwrite($fp1,"$tmpauth");
		fwrite($fp1,'">');
		fwrite($fp1,"&nbsp;&nbsp;$topics[1]</a>\n");
		break;
	      }
	      fwrite($fp1,'</font></b></td><td width="10%" align="right">');
	      fwrite($fp1,"</td></tr></table>\n");
	    }else{
	      switch ($topics[1]){
	      case "Perceptual Image Metrics":
		fwrite($fp1,"<b><font size=+1 color=$bartextcolor>");
		fwrite($fp1,"&nbsp;&nbsp;$topics[1]\n");
		fwrite($fp1,"&nbsp;&nbsp;<a link=$barcolor vlink=\"#AA0000\" href=http://www.cns.nyu.edu/~zwang/files/research/ssim name=\"");
		fwrite($fp1,"$tmpauth");
		fwrite($fp1,'">');
		fwrite($fp1,"</b>[ Topic Page ]<b></a>\n");
		break;
	      case "Texture Analysis/Representation/Synthesis":
		fwrite($fp1,"<b><font size=+1 color=$bartextcolor>");
		fwrite($fp1,"&nbsp;&nbsp;$topics[1]</a>\n");
		fwrite($fp1,"&nbsp;&nbsp;<a link=$barcolor vlink=\"#AA0000\" href=http://www.cns.nyu.edu/~lcv/texture name=\"");
		fwrite($fp1,"$tmpauth");
		fwrite($fp1,'">');
		fwrite($fp1,"</b>[ Topic Page ]<b></a>\n");
		break;
	      case "Compression":
		fwrite($fp1,"<b><font size=+1 color=$bartextcolor>");
		fwrite($fp1,"&nbsp;&nbsp;$topics[1]</a>\n");
		fwrite($fp1,"&nbsp;&nbsp;<a link=$barcolor vlink=\"#AA0000\" href=http://www.cns.nyu.edu/~eero/EPWIC name=\"");
		fwrite($fp1,"$tmpauth");
		fwrite($fp1,'">');
		fwrite($fp1,"</b>[ Topic Page ]<b></a>\n");
		break;
	      case "Modeling Physiology":
		fwrite($fp1,"<b><font size=+1 color=$bartextcolor>");
		fwrite($fp1,"&nbsp;&nbsp;$topics[1]</a>\n");
		fwrite($fp1,"&nbsp;&nbsp;<a link=$barcolor vlink=\"#AA0000\" href=http://www.cns.nyu.edu/~eero/MT-model.html name=\"");
		fwrite($fp1,"$tmpauth");
		fwrite($fp1,'">');
		fwrite($fp1,"</b>[ Topic Page ]<b></a>\n");
		break;
	      case "Multi-Scale, Oriented Representations (Steerable Pyramids)":
		fwrite($fp1,"<b><font size=+1 color=$bartextcolor>");
		fwrite($fp1,"&nbsp;&nbsp;$topics[1]</a>\n");
		fwrite($fp1,"&nbsp;&nbsp;<a link=$barcolor vlink=\"#AA0000\" href=http://www.cns.nyu.edu/~eero/STEERPYR name=\"");
		fwrite($fp1,"$tmpauth");
		fwrite($fp1,'">');
		fwrite($fp1,"</b>[ Topic Page ]<b></a>\n");
		break;
	      default:
		fwrite($fp1,"<b><font size=+1 color=$bartextcolor><a name=\"");
		fwrite($fp1,"$tmpauth");
		fwrite($fp1,'">');
		fwrite($fp1,"&nbsp;&nbsp;$topics[1]</a>\n");
		break;
	      }
	      fwrite($fp1,'</font></b></td><td width="10%" align="right"><a href="#top">'."\n");
	      fwrite($fp1,"<font size=-1 color=$bartextcolor>top</font></a>&nbsp;&nbsp;</td></tr></table>");
	    }
	  }
	  $newRefs[$tmparr[$date_keys[$j]]]->printSelf($fp1,$basedir,$pdfdir);
	}
	$start = $keys[$i];
      }
      break;
    default:
      // same as "date" at top.  Need to unify the two!
      $allyears = array();
      for($i=0;$i<$Nrefs;$i++){
	$allyears[] = trim($newRefs[$i]->year);
      }
      $uallyears = array_unique($allyears);
      //printLinks($fp1,$uallyears,$uallyears);
      $uallyearsKeys = array_keys($uallyears);
      fwrite($fp1,"<font size=-1>");
      for($i=0;$i<count($uallyears);$i++){
      $tmpval = $allyears[$uallyearsKeys[$i]];
      fwrite($fp1,'<a href="#');
      fwrite($fp1,"$tmpval");
      fwrite($fp1,'"');
      fwrite($fp1,">$tmpval</a>");
      if($i != count($uallyears)-1){
        fwrite($fp1," | ");
      }
      }
      fwrite($fp1,"</font>");
      fwrite($fp1,"<p>");
      $tmpyear = 0;
      for($i=0;$i<$Nrefs;$i++){
	if(trim($newRefs[$i]->year) != trim($tmpyear)){
	  $tmpyear = $newRefs[$i]->year;
	  fwrite($fp1,"<table bgcolor=$barcolor width=100%><tr><td width=\"90%\">");
	  fwrite($fp1,"<font size=+1 color=$bartextcolor><b>");
	  fwrite($fp1,"<a name=$tmpyear>&nbsp;&nbsp;$tmpyear</a>");
	  fwrite($fp1,"</b></font></td><td width=\"10%\" align=\"right\">");
	  if($i != 0){
	    fwrite($fp1,"<a href=\"#top\"><font size=-1 color=$bartextcolor>top</font></a>&nbsp;&nbsp;");
	  }
	  fwrite($fp1,"</td></tr></table>");
	}
	$newRefs[$i]->printSelf($fp1,$basedir,$pdfdir);
      }
      break;
    }
    fclose($fp1);
  }
}

?>

