<?php
// utils.php contains utility functions for bib2php
// written by Rob Young and Eero Simoncelli

class Ref {
  // system vars
  var $abstract;
  var $author2 = array();
  var $authorPrint;
  var $bibtex;
  var $copy;
  var $cover_pic;
  var $cover_url;
  var $dateNum;
  var $desc;
  var $dlTitle;
  var $dlTitlearr;
  var $dlURL;
  var $dlURLarr;
  var $doi;
  var $keywords;
  var $loc;
  var $monthNum;
  var $official_url;
  var $omit;
  var $other;
  var $pdf_url;
  var $ps_url;
  var $related;
  var $related_title;
  var $relatedArr = array();
  var $relatedCtr = 0;
  var $relatedLoc;
  var $relatedLocarr;
  var $status = "";
  var $super;
  var $topic;
  var $type;
  // bibtex vars
  var $address;
  var $author;
  var $booktitle;
  var $chapter;
  var $editor;
  var $howpublished;
  var $institution;
  var $journal;
  var $month = "";
  var $note;
  var $number;
  var $pages;
  var $publisher;
  var $school;
  var $title;
  var $volume;
  var $year;

  function printSelf($basedir,$pdfdir,$mode,$authorURLarray){
    // if mode is abstract make publication URL arrays
    // get journal URL from file
    if(strcmp($mode,"meta") === 0 || strcmp($mode,"abstract") === 0){
      //$ini_vars = parse_ini_file($maindir."bib2php.conf",true);
      // FIX: need to set file name at top of file, but I get
      //      errors when I use a global var. WHY?!
      $ini_vars = parse_ini_file("./bib2php.conf", true);
      $journalfile = $ini_vars['bib2php_vars']['JOURNAL'];
      $journalNames = array();
      $journalURLs = array();
      $confNames = array();
      if(file_exists($journalfile)){
	$fp = fopen($journalfile,"r");
	while(!feof($fp)){
	  $line = trim(fgets($fp));
	  $tmparr = explode("|",$line);
	  if(count($tmparr) == 2){
	    list($journalNames[],$journalURLs[]) = $tmparr;
	    $confNames[] = "foo";
	  }elseif(count($tmparr) == 3){
	    list($confNames[],$journalURLs[],$journalNames[]) = $tmparr;
	  }
	}
	fclose($fp);
      }
    }
    if(strcmp($mode,"meta") === 0){      // special mode for meta tags
      echo "<meta name=\"citation_title\" content=\"" . cleanStr($this->title,1) . "\">";
      foreach($this->author2 as $author){
	echo "<meta name=\"citation_author\" content=\"$author\">";
      }
      if(empty($this->month))
	echo "<meta name=\"publication_date\" content=\"$this->year\">";
      else
	echo "<meta name=\"publication_date\" content=\"$this->year/$this->month/01\">";
      echo "<meta name=\"citation_pdf_url\" content=\"$this->pdf_url\">";
      switch($this->type){
      case "ARTICLE":
	if(!empty($this->journal))
	  echo "<meta name=\"citation_journal_title\" content=\"$this->journal\">";
	if(!empty($this->volume))
	  echo "<meta name=\"citation_volume\" content=\"$this->volume\">";
	if(!empty($this->number))
	  echo "<meta name=\"citation_issue\" content=\"$this->number\">";
	break;
      case "INPROCEEDINGS":
	if(!empty($this->booktitle))
	  echo "<meta name=\"citation_journal_title\" content=\"$this->booktitle\">";
	//if(in_array(stripslashes($tmpRef->booktitle),$journalNames)){
	//  $targ_idx = array_search(stripslashes($tmpRef->booktitle),$journalNames);
	if(in_array(stripslashes($this->booktitle),$journalNames)){
	  $targ_idx = array_search(stripslashes($this->booktitle),
				   $journalNames);
	  echo "<meta name=\"citation_conference_title\" content=\"$this->confNames[$targ_idx]\">";
	}
	if(!empty($this->volume))
	  echo "<meta name=\"citation_volume\" content=\"$this->volume\">";
	if(!empty($this->number))
	  echo "<meta name=\"citation_issue\" content=\"$this->number\">";
	break;
      case "INCOLLECTION":
	if(!empty($this->booktitle))
	  echo "<meta name=\"citation_journal_title\" content=\"$this->booktitle\">";
	break;
      case "CONFABSTRACT":
	if(!empty($this->booktitle))
	  echo "<meta name=\"citation_journal_title\" content=\"$this->booktitle\">";
	//if(in_array(stripslashes($tmpRef->booktitle),$journalNames)){
	//$targ_idx = array_search(stripslashes($tmpRef->booktitle),$journalNames);
	if(in_array(stripslashes($this->booktitle),$journalNames)){
	  $targ_idx = array_search(stripslashes($this->booktitle),$journalNames);
	  echo "<meta name=\"citation_conference_title\" content=\"$this->confNames[$targ_idx]\">";
	}
	if(!empty($this->volume))
	  echo "<meta name=\"citation_volume\" content=\"$this->volume\">";
	if(!empty($this->number))
	  echo "<meta name=\"citation_issue\" content=\"$this->number\">";
	break;
      case "INCOLLECTION":
	if(!empty($this->booktitle))
	  echo "<meta name=\"citation_journal_title\" content=\"$this->booktitle\">";
	break;
      case "TECHREPORT":
	if(!empty($this->institution))
	  echo "<meta name=\"citation_technical_report_institution\" content=\"$this->institution\">";
	if(!empty($this->number))
	  echo "<meta name=\"citation_technical_report_number\" content=\"$this->number\">";
	break;
      case "PHDTHESIS":
	if(!empty($this->school))
	  echo "<meta name=\"citation_dissertation_institution\" content=\"$this->school\">";
	break;
      case "MASTERSTHESIS":
	if(!empty($this->school))
	  echo "<meta name=\"citation_dissertation_institution\" content=\"$this->school\">";
	break;
      case "BACHELORSTHESIS":
	if(!empty($this->school))
	  echo "<meta name=\"citation_dissertation_institution\" content=\"$this->school\">";
	break;
      }
    }else{  // not meta mode
      // now start printing to string
      $outstr = "";
      $this->title = cleanStr(stripslashes($this->title),1);
      if(strcmp($mode,"main") == 0){
	//echo "<P class=\"titledlist\">\n";
	//echo "&nbsp;&nbsp;&nbsp;&nbsp;<b>$this->title</b><br>";
	$outstr .= "<P class=\"titledlist\">";
	$outstr .= "&nbsp;&nbsp;&nbsp;&nbsp;<b>$this->title</b><br>";
      }elseif(strcmp($mode,"abstract") === 0){
	//echo "<h2>$this->title</h2>";
	$outstr .= "<h2>$this->title</h2>";
      }elseif(strcmp($mode,"popup") === 0){
	$outstr .= "<font size=-1><b><nobr>$this->title</nobr></b><br>";
      }elseif(strcmp($mode,"super") === 0){
	$outstr .= "<b><nobr>$this->title</nobr></b><br>";
      }
      
      $autharr = explode(" and ",$this->author);
      if(strcmp($mode,"abstract") === 0){
	$ho = "<h3>"; $hc = "</h3>";
	$io = "<i>"; $ic = "</i>";
	//}elseif(strcmp($mode,"main") === 0 || strcmp($mode,"popup") === 0){
      }else{
	$ho = ""; $hc = "";
	$io = ""; $ic = "";
      }
      //echo $ho;
      $outstr .= $ho;
      for($i=0;$i<count($autharr);$i++){
	$currauth = $io . cleanStr($autharr[$i],1) . $ic;
	if($i == count($autharr)-1){
	  //echo "$currauth ";
	  if(strcmp($mode,"abstract") === 0 && 
	     array_key_exists($autharr[$i],$authorURLarray))
	    $outstr .= "<a href=\"" . $authorURLarray[$autharr[$i]] . "\">" . $currauth ."</a> ";
	  else
	    $outstr .= "$currauth. ";
	}elseif($i == count($autharr)-2){
	  //echo "$currauth and ";
	  if(strcmp($mode,"abstract") === 0 && 
	     array_key_exists($autharr[$i],$authorURLarray))
	    $outstr .= "<a href=\"" . $authorURLarray[$autharr[$i]] . "\">" . $currauth ."</a> and ";
	  else
	    $outstr .= "$currauth and ";
	}else{
	  //echo "$currauth, ";
	  if(strcmp($mode,"abstract") === 0 && 
	     array_key_exists($autharr[$i],$authorURLarray))
	    $outstr .= "<a href=\"" . $authorURLarray[$autharr[$i]] . "\">" . $currauth ."</a>, ";
	  else
	    $outstr .= "$currauth, ";
	}
      }
      //echo $hc;
      $outstr .= $hc;
      if(strcmp($mode,"popup") === 0 || strcmp($mode,"super") === 0){
	$outstr .= "<br>";
      }
      
      switch($this->type){
      case "ARTICLE":
	$tmpt = cleanStr($this->journal,1);
	if(strcmp($mode,"abstract") === 0){
	  //echo "Published in";
	  $outstr .= "Published in";
	  if(in_array($tmpt,$journalNames))
	    $journalURL = $journalURLs[array_search($tmpt,$journalNames)];
	  else
	    $journalURL = "";
	  if(strlen($journalURL) > 0)
	    $outstr .= " <a href=$journalURL><i>$tmpt</i></a>, ";
	  else
	    $outstr .= " <i>$tmpt</i>, ";
	}else{
	  //echo "\n <i>$tmpt</i>, ";
	  $outstr .= " <i>$tmpt</i>, ";
	}
	if(strcmp($mode,"popup") !== 0){
	  if(strlen($this->volume) > 0){
	    //echo "vol.$this->volume";
	    $outstr .= "vol.$this->volume";
	  }
	  if(strlen($this->number) > 0){
	    $this->number = trim($this->number);
	    //echo "($this->number),";
	    $outstr .= "($this->number),";
	  }
	  if(strlen($this->pages) > 0){
	    //echo " pp. $this->pages,";
	    $outstr .= " pp. $this->pages,";
	  }
	}
      //echo " $this->month $this->year.";
	$outstr .= " $this->month $this->year.";
	break;
      case "INPROCEEDINGS":
	$tmpt = stripslashes(cleanStr($this->booktitle,1));
	if(strcmp($mode,"abstract") === 0){
	  //echo "Published in";
	  //$outstr .= "Published in";
	  //if(in_array(stripslashes($tmpRef->booktitle),$journalNames)){
	  //$targ_idx = array_search(stripslashes($tmpRef->booktitle),$journalNames);
	  if(in_array(stripslashes($this->booktitle),$journalNames)){
	    $targ_idx = array_search(stripslashes($this->booktitle),
				     $journalNames);
	    $outstr .= "Presented at:<br><i><a href=$journalURLs[$targ_idx]>$confNames[$targ_idx]</a></i><p>";
	  }
	  $tmpt = cleanStr($this->booktitle,1);
	  $outstr .= "Published in <i>$tmpt</i>, ";
	}else{
	  //echo "\n <i>$tmpt</i>, ";
	  $outstr .= " <i>$tmpt</i>, ";
	}
	if(strcmp($mode,"popup") !== 0){
	  if(strlen($this->volume) > 0){
	    //echo "vol.$this->volume";
	    $outstr .= "vol.$this->volume";
	  }
	  if(strlen($this->number) > 0){
	    //echo "($this->number),";
	    $outstr .= "($this->number),";
	  }
	  if(strlen($this->pages) > 0){
	    //echo " pp. $this->pages,";
	    $outstr .= " pp. $this->pages,";
	  }
	}
	//echo " $this->month $this->year.";
	$outstr .= " $this->month $this->year.";
	break;
      case "CONFABSTRACT":
	$tmpt = stripslashes(cleanStr($this->booktitle,1));
	if(strcmp($mode,"abstract") === 0)
	  //echo "Published in";
	  $outstr .= "Published in";
	//echo "\n <i>$tmpt</i>, ";
	$outstr .= " <i>$tmpt</i>, ";
	if(strcmp($mode,"popup") !== 0){
	  if(strlen($this->volume) > 0){
	    //echo "vol.$this->volume";
	    $outstr .= "vol.$this->volume";
	  }
	  if(strlen($this->number) > 0){
	    //echo "($this->number),";
	    $outstr .= "($this->number),";
	  }
	  if(strlen($this->pages) > 0){
	    //echo " pp. $this->pages,";
	    $outstr .= " pp. $this->pages,";
	  }
	}
	//echo " $this->month $this->year.";
	$outstr .= " $this->month $this->year.";
	break;
      case "INCOLLECTION":
	$tmpt = stripslashes(cleanStr($this->booktitle,1));
	if(strcmp($mode,"abstract") === 0)
	  //echo "Published in";
	  $outstr .= "Published in";
	//echo "\n <i>$tmpt</i>,";
	$outstr .= " <i>$tmpt</i>,";
	if(strcmp($mode,"popup") !== 0){
	  if(strlen($this->pages) > 0){
	    //echo " pages $this->pages.";
	    $outstr .= " pages $this->pages. $this->publisher,";
	  }
	}
	//echo " $this->publisher, $this->month $this->year.";
	$outstr .= " $this->month $this->year.";
	break;
      case "TECHREPORT":
	//echo "$this->institution, Technical Report $this->number, ";
	$outstr .= "$this->institution, Technical Report $this->number, ";
	//echo " $this->month $this->year.";
	$outstr .= " $this->month $this->year.";
	break;
      case "PHDTHESIS":
	//echo "PhD thesis, $this->school,<br>";
	$outstr .= "PhD thesis, $this->school,<br>";
	if(strlen($this->address) > 0){
	  //echo "$this->address, ";
	  $outstr .= "$this->address, ";
	}
	//echo "$this->month $this->year.";
	$outstr .= "$this->month $this->year.";
	break;
      case "MASTERSTHESIS":
	//echo "MS thesis, $this->school,<br>";
	$outstr .= "MS thesis, $this->school,<br>";
	if(strlen($this->address) > 0){
	  //echo "$this->address, ";
	  $outstr .= "$this->address, ";
	}
	//echo "$this->month $this->year.";
	$outstr .= "$this->month $this->year.";
	break;
      case "TALK":
	$tmpt = stripslashes(cleanStr($this->booktitle,1));
	//echo "\n <i>$tmpt</i>, ";
	$outstr .= " <i>$tmpt</i>, ";
	//echo " $this->month $this->year.";
	$outstr .= " $this->month $this->year.";
	break;
      case "POSTER":
	$tmpt = stripslashes(cleanStr($this->booktitle,1));
	//echo "\n <i>$tmpt</i>, ";
	$outstr .= " <i>$tmpt</i>, ";
	//echo " $this->month $this->year.";
	$outstr .= " $this->month $this->year.";
	break;
      default:
	if(strpos($this->desc,"|") !== false){
	  $descarr = explode('|',$this->desc);
	  //echo "$descarr[1]<br>";
	  $outstr .= "$descarr[1]<br>";
	}else{
	  //echo "$this->type<br>";
	  $outstr .= "$this->type<br>";
	}
	//echo "$this->month $this->year.";
	$outstr .= "$this->month $this->year.";
	break;
      }
      
      //echo " $this->status<br>";
      $outstr .= " $this->status<br>";
      
      if(strcmp($mode,"abstract") === 0){
	if(strlen($this->copy) > 0)
	  $outstr .= "&copy; <i>$this->copy</i><br>";
      }
      
      if(strlen($this->other) > 0){
	if(strcmp($mode,"abstract") === 0)
	  $outstr .= "<P>";
	$outstr .= stripslashes("$this->other<br>");
      }
      
      if(strcmp($mode,"main") === 0){
	
	// if $this->pdf is empty check default directory
	if(strlen($this->pdf_url) == 0){
	  $tmpurl = $pdfdir . strtolower($this->loc) . ".pdf";
	  clearstatcache();
	  if(file_exists($tmpurl) !== false){
	    //$tmpfp = @fopen($tmpurl, "r");
	    $this->pdf_url = $tmpurl;
	    //fclose($tmpfp);
	  }
	}
	
	if(strcmp($mode,"popup") !== 0){
	  $tmpstr = sprintf('%smakeAbs.php?loc=',$basedir);
	  //echo "<a href=\"$tmpstr";
	  $outstr .= "<a href=\"$tmpstr";
	  //echo "$this->loc";
	  $outstr .= "$this->loc";
	  //echo "\">Abstract</a>";
	  $outstr .= "\">Abstract</a>";
	  if(strlen($this->pdf_url) > 0){
	    //echo "&nbsp;|&nbsp;<a href=\"";
	    $outstr .= "&nbsp;|&nbsp;<a href=\"";
	    //echo "$this->pdf_url";
	    $outstr .= "$this->pdf_url";
	    //echo "\">PDF</a>";
	    $outstr .= "\">PDF</a>";
	  }
	  if(strlen($this->ps_url) > 0){
	    //echo "&nbsp;|&nbsp;<a href=\"";
	    $outstr .= "&nbsp;|&nbsp;<a href=\"";
	    //echo "$this->ps_url";
	    $outstr .= "$this->ps_url";
	    //echo "\">PS</a>";
	    $outstr .= "\">PS</a>";
	  }
	  //echo "<p>\n";
	  $outstr .= "<p>\n";
	}
      }
      
      if(strcmp($mode,"popup") === 0){
	$outstr .= "</font>";
	$outstr = str_replace("'","\'",$outstr);
	return $outstr;
      }elseif(strcmp($mode,"super") === 0){
	return $outstr;
      }else{
	echo $outstr;
      }
    }
  }
}

function LcF2FsL($instr){
  // convert string name from last comma first to first space last
  $tmparr = explode(", ",$instr);
  return $tmparr[1] . " " . $tmparr[0];
}

function loadStruct($line,$fp,&$EOEflag,&$tmpRef,$map,$targLoc,&$supersedeLoc){
  if(strlen($line) > 0 && strpos($line,'=') !== false && $line{0} !== 'x' && 
     $line{0} !== '*' && $line{0} !== '%'){

    // get tag
    $linearr = explode("=",$line);
    $tag = trim($linearr[0]);
    $tag = str_replace("-","_",$tag);
    $tag = strtoupper($tag);

    switch($tag){
    case "AUTHOR":
      // need to be able to handle both author name orderings in a single
      //   line and document.
      $tmpRef->author = read_entry($line,$fp,$EOEflag,$tmpRef->bibtex);
      // separate authors into author array
      $tmpRef->author2=explode(' and ',$tmpRef->author);
      // reorder authors name so last name is first.
      $Nauth = sizeof($tmpRef->author2);
      for($i=0;$i<$Nauth;$i++){
	$tmpauthor = trim($tmpRef->author2[$i]);
	$tmpauthor = cleanStr($tmpauthor,0);
	// check if name is last name first, if so you are done
	//   else need to put last name first.
	if(strpos($tmpauthor,',') === false){
	  $pos1 = strpos($tmpauthor,'{');
	  $pos2 = strpos($tmpauthor,'}');
	  if($pos1 && $pos2){  // bracketed last name
	    $tmpRef->author2[$i] = substr($tmpauthor,$pos1+1,$pos2-$pos1-1);
	    $tmpRef->author2[$i] = $tmpRef->author2[$i] . ", ";
	    $firstNames = substr($tmpauthor,0,$pos1);
	    $FNarray = explode(" ",$firstNames);
	    for($j=0;$j<count($FNarray);$j++){
	      $tmpRef->author2[$i] = $tmpRef->author2[$i] . " " . $FNarray[$j];
	    }
	  }else{
	    $tmparr = explode(' ',$tmpauthor);
	    $tmparrsz = sizeof($tmparr);
	    $tmpRef->author2[$i] = $tmparr[$tmparrsz-1] . ", ";
	    for($j=0;$j<$tmparrsz-1;$j++){
	      if(strcmp($tmparr[$j]," ") != 0){ 
		$tmpRef->author2[$i] = $tmpRef->author2[$i] . " " . $tmparr[$j];
	      }
	    }
	  }
	}else{
	  $tmpRef->author2[$i] = $tmpauthor;
	}
      }
      break;
    case "MONTH":
      $tmpRef->month = read_entry($line,$fp,$EOEflag,$tmpRef->bibtex);
      if(strpos($tmpRef->month,"Jan") !== false){
	$tmpRef->month = "Jan";
	$tmpRef->monthNum = 1;
      }elseif(strpos($tmpRef->month,"Feb") !== false){
	$tmpRef->month = "Feb";
	$tmpRef->monthNum = 2;
      }elseif(strpos($tmpRef->month,"Mar") !== false){
	$tmpRef->month = "Mar";
	$tmpRef->monthNum = 3;
      }elseif(strpos($tmpRef->month,"Apr") !== false){
	$tmpRef->month = "Apr";
	$tmpRef->monthNum = 4;
      }elseif(strpos($tmpRef->month,"May") !== false){
	$tmpRef->month = "May";
	$tmpRef->monthNum = 5;
      }elseif(strpos($tmpRef->month,"Jun") !== false){
	$tmpRef->month = "Jun";
	$tmpRef->monthNum = 6;
      }elseif(strpos($tmpRef->month,"Jul") !== false){
	$tmpRef->month = "Jul";
	$tmpRef->monthNum = 7;
      }elseif(strpos($tmpRef->month,"Aug") !== false){
	$tmpRef->month = "Aug";
	$tmpRef->monthNum = 8;
      }elseif(strpos($tmpRef->month,"Sep") !== false){
	$tmpRef->month = "Sep";
	$tmpRef->monthNum = 9;
      }elseif(strpos($tmpRef->month,"Oct") !== false){
	$tmpRef->month = "Oct";
	$tmpRef->monthNum = 10;
      }elseif(strpos($tmpRef->month,"Nov") !== false){
	$tmpRef->month = "Nov";
	$tmpRef->monthNum = 11;
      }elseif(strpos($tmpRef->month,"Dec") !== false){
	$tmpRef->month = "Dec";
	$tmpRef->monthNum = 12;
      }else{
	$tmpRef->month = "";
	$tmpRef->monthNum = 13;
      }
      break;
    case "TOPIC":
      $tmpRef->topic = read_entry($line,$fp,$EOEflag,$tmpRef->bibtex);
      if(empty($tmpRef->topic))
	$tmpRef->topic = "no topic";
      break;
    case "TYPE":
      // type in aux file overrides bibtex file type
      if(!empty($map)){
	$tmpRef->type = read_entry($line,$fp,$EOEflag,$tmpRef->bibtex);
	if(array_key_exists($tmpRef->type,$map)){
	  $tmparr = explode('|',$map[$tmpRef->type]);
	  $tmpRef->desc = $tmparr[1];
	}else{
	  $tmpRef->desc = $tmpRef->type;
	}
      }
      break;
    case "SUPERSEDES":
      $tmpRef->super = read_entry($line,$fp,$EOEflag,$tmpRef->bibtex);
      if(!empty($targLoc) && strcmp($tmpRef->loc,$targLoc) !== 0){ 
	// is targLoc in $tmpRef->super (pipe sep. string)
	//if(strlen($targLoc) > 0 && strpos($tmpRef->super,$targLoc) !== false){
	$tmpSuper = explode("|",$tmpRef->super);
	if(strlen($targLoc) > 0 && in_array($targLoc,$tmpSuper)){
	  $supersedeLoc[] = $tmpRef->loc;
	}	
      }     
      break;
    case "RELATED":
      $tmpRef->relatedArr[$tmpRef->relatedCtr] = read_entry($line,$fp,$EOEflag,$tmpRef->bibtex);
      $tmpRef->relatedCtr++;
      break;
    case "DL_TITLE":
      $tmpRef->dlTitle = read_entry($line,$fp,$EOEflag,$tmpRef->bibtex);
      $tmpRef->dlTitlearr = explode("|",$tmpRef->dlTitle);
      break;
    case "DL_URL":
      $tmpRef->dlURL = read_entry($line,$fp,$EOEflag,$tmpRef->bibtex);
      $tmpRef->dlURLarr = explode("|",$tmpRef->dlURL);
      break;
    default:
      $taglow = strtolower($tag);
      //echo "<P>$line<P>";
      $evalstr = '$tmpRef->' . $taglow . ' = addslashes(read_entry($line,$fp,$EOEflag,$tmpRef->bibtex));';
      eval($evalstr);
    }
  }
}

function printLinks($names,$urls){
  $namesKeys = array_keys($names);
  $urlsKeys = array_keys($urls);

  echo "<div id=links>";
  for($i=0;$i<count($names);$i++){
    $tmpval1 = $names[$namesKeys[$i]];
    $tmpval2 = $urls[$urlsKeys[$i]];
    echo "<a href=\"#$tmpval2\">$tmpval1</a>";
    if($i != count($names)-1){
      echo " | ";
    }
  }
  echo "</div>";
  echo "<p>";
}

function printDivider($name,$url,$top){
  echo "<div id=divider><div id=divider-left>";
  echo "<a class=divider name=\"$url\">$name</a></div>";
  echo "<div id=divider-right>";
  if(!$top){
    echo "<a class=divider href=\"#top\">top</a>";
  }
  echo "</div></div>";
}

function fixNames($nameArr){
  $retArr = array();
  $keys = array();
  $firstI = array();
  $last = array();
  
  $keys = array_keys($nameArr);
  foreach($nameArr as $name){
    $nameArr = explode(", ",$name);
    $last[] = $nameArr[0];
    $tmpstr = trim($nameArr[1]);
    $firstI[] = $tmpstr[0];
  }
  $Ulast = array_unique($last);
  foreach($Ulast as $ul){
    $subKeys = array_keys($last,$ul);
    $subFI = array();
    foreach($subKeys as $sk){
      $subFI[] = $firstI[$sk];
    }
    if(count(array_unique($subFI)) == 1){
      foreach($subKeys as $sk){
	$retArr[$sk] = $last[$sk];
      }
    }else{
      foreach($subKeys as $sk){
	$retArr[$sk] = $last[$sk] . ", " . $firstI[$sk];
      }
    }
  }

  return $retArr;
}

function read_entry_old($linein,$fp) {
  $eqloc = strpos($linein,"=");
  $tmpstr=substr($linein,$eqloc+1,strlen($linein));
  // if line ends with comma or quotes then read in one or more
  //   lines and remove comma and/or quotes.
  // else just return value after equal sign
  if(strpos($linein,',') !== false || strpos($linein,'"') !== false){
    while($linein{strlen($linein)-1} != "," && 
	  $linein{strlen($linein)-1} != '"'){
      $linein=trim(fgets($fp));
      $tmpstr=$tmpstr." ".$linein;
    }
    // remove quotes and comma
    if(strpos($tmpstr,'"') !== false){
      $first = strpos($tmpstr,'"')+1;
      $tmplen = strrpos($tmpstr,'"') - $first;
      $tmpstr=substr($tmpstr,$first,$tmplen);
    }elseif(strpos($tmpstr,',') !== false){
      $tmpstr = substr($tmpstr,0,strpos($tmpstr,','));
    }
  }

  return trim($tmpstr);
}

// handles lines bounded by either quotes or curly braces and terminated by a 
//   comma unless it's the last line.
// allows any number of spaces between closing bound char and a comma.
function read_entry($linein,$fp,&$flag,&$bibtex) {
  $eqloc = strpos($linein,"=");
  $linein=trim(substr($linein,$eqloc+1,strlen($linein)));
  if(strpos($linein,'{') === 0){
    $closechar = "},";
    $openflag = 1;
    // remove all spaces between closing char and comma
    $linein = preg_replace('/}\s+,/','\},',$linein);
  }else if(strpos($linein,'"') === 0){
    $closechar = '",';
    $openflag = 1;
    // remove all spaces between closing char and comma
    $linein = preg_replace('/"\s+,/','",',$linein);
  }else{
    $openflag = 0;
  }

  $closechar2 = ",";
  while(!feof($fp) && $flag != 1 &&
	(($openflag == 0 && strpos($linein,$closechar2) !== strlen($linein)-1) || ($openflag == 1 && strpos($linein,$closechar) !== strlen($linein)-2))){
    $tmpstr=trim(fgets($fp));
    if(strpos($tmpstr,'}') === 0){
      $flag = 1;
    }else{
      $linein=trim($linein." ".$tmpstr);
      $bibtex .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;".$tmpstr."<br>";
    }
  }
  $tmpstr = $linein;

  // remove comma at end
  if(strrpos($tmpstr,',') === strlen($tmpstr)-1){
    $tmpstr = substr($tmpstr,0,strlen($tmpstr)-1);
  }
  // remove bounding quotes and braces
  if(strrpos($tmpstr,'"') === strlen($tmpstr)-1 && 
     strpos($tmpstr,'"') !== strlen($tmpstr)-1){
    $tmpstr = substr($tmpstr,strpos($tmpstr,'"')+1,
  		     strrpos($tmpstr,'"')-strpos($tmpstr,'"')-1);
  }else if(strrpos($tmpstr,'}') === strlen($tmpstr)-1 && 
     strpos($tmpstr,'{') !== strlen($tmpstr)-1){
    $tmpstr = substr($tmpstr,strpos($tmpstr,'{')+1,
  		     strrpos($tmpstr,'}')-strpos($tmpstr,'{')-1);
  }
  
  return $tmpstr;
}

function cleanStr($instr,$cbflag){
  $chars2remove = array("{", "}");

  $instr = str_replace('\"{a}',"&auml;",$instr);
  $instr = str_replace('\"{e}',"&euml;",$instr);
  $instr = str_replace('\"{i}',"&iuml;",$instr);
  $instr = str_replace('\"{o}',"&ouml;",$instr);
  $instr = str_replace('\"{u}',"&uuml;",$instr);
  $instr = str_replace('\"{y}',"&yuml;",$instr);
  $instr = str_replace("\'{a}","&aacute;",$instr);
  $instr = str_replace("\'{e}","&eacute;",$instr);
  $instr = str_replace("\'{i}","&iacute;",$instr);
  $instr = str_replace("\'{o}","&oacute;",$instr);
  $instr = str_replace("\'{u}","&uacute;",$instr);
  $instr = str_replace("\'{y}","&yacute;",$instr);
  $instr = str_replace("\`{a}","&agrave;",$instr);
  $instr = str_replace("\`{e}","&egrave;",$instr);
  $instr = str_replace("\`{i}","&igrave;",$instr);
  $instr = str_replace("\`{o}","&ograve;",$instr);
  $instr = str_replace("\`{u}","&ugrave;",$instr);

  $instr = str_replace('\"a',"&auml;",$instr);
  $instr = str_replace('\"e',"&euml;",$instr);
  $instr = str_replace('\"i',"&iuml;",$instr);
  $instr = str_replace('\"o',"&ouml;",$instr);
  $instr = str_replace('\"u',"&uuml;",$instr);
  $instr = str_replace('\"y',"&yuml;",$instr);
  $instr = str_replace("\'a","&aacute;",$instr);
  $instr = str_replace("\'e","&eacute;",$instr);
  $instr = str_replace("\'i","&iacute;",$instr);
  $instr = str_replace("\'o","&oacute;",$instr);
  $instr = str_replace("\'u","&uacute;",$instr);
  $instr = str_replace("\'y","&yacute;",$instr);
  $instr = str_replace("\`a","&agrave;",$instr);
  $instr = str_replace("\`e","&egrave;",$instr);
  $instr = str_replace("\`i","&igrave;",$instr);
  $instr = str_replace("\`o","&ograve;",$instr);
  $instr = str_replace("\`u","&ugrave;",$instr);
  
  $instr = stripslashes($instr);

  if($cbflag == 1){
    $instr = str_replace($chars2remove,"",$instr);
  }

  return $instr;
}

function bibtex2array(&$allRefs,&$allLoc,$bibfile,$ini_vars,$targLoc){
  $fp = fopen($bibfile,"r"," ,") or die("bibtex2array:Error opening file $bibfile");
  $line = trim(fgets($fp));
  while(!feof($fp)){
    if(strpos($line,"@") === 0 && strpos($line,"@COMMENT") === false){
      $tmpRef=& new Ref();
      $sublen = strpos($line,"{")-1;
      $tmpRef->type = strtoupper(substr($line,1,$sublen));  // to upper for imported bibtex
      // get type descriptor
      $last = strpos($line,"{")-1;
      if(array_key_exists($tmpRef->type,$ini_vars['type_mappings']))
	$tmpRef->desc = $ini_vars['type_mappings'][$tmpRef->type];
      else
	$tmpRef->desc = $tmpRef->type;
      
      $locStart = strpos($line,"{");
      $locEnd = strpos($line,",");
      $tmpRef->loc = substr($line,$locStart+1,$locEnd-($locStart+1));

      $tmpRef->bibtex .= $line."<br>";
      
      $line=trim(fgets($fp));
      $EOEflag = 0;
      // while not at the end of the article
      while(strpos($line,"}") !== 0 && $EOEflag == 0){
	// don't display x'ed, *'ed items or empty lines in bibtex
	if(strlen($line) > 0 && $line{0} !== 'x' && $line{0} !== '*')
	  $tmpRef->bibtex .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;".$line."<br>";
	loadStruct($line,$fp,$EOEflag,$tmpRef,NULL,NULL,$supersedeLoc);
	$line=trim(fgets($fp));
      }
      // set $tmpRef->month if no MONTH tag in bibtex file
      if(strlen($tmpRef->month) == 0){
	$tmpRef->monthNum = 13;
      }
      // add all references now
      // we will remove references after reading aux file
      $allRefs[]=&$tmpRef;
      $allLoc[] = $tmpRef->loc;
    }
    $line=trim(fgets($fp));
  }
  $Nrefs = count($allRefs);
  fclose($fp);
  
  return $Nrefs;
}

function readAux(&$allRefs,$allLoc,$auxfile,$ini_vars,$targLoc,&$supersedeLoc){
  $fp = fopen($auxfile,"r"," ,");
  if($fp){
    while(!feof($fp)){
      $line = trim(fgets($fp));
      if(strpos($line,"@") === 0){
	$locStart = strpos($line,"{")+1;
	$locEnd = strpos($line,",");
	$loc = substr($line,$locStart,$locEnd-$locStart);
	// while not at end of record ...
	$EOEflag = 0;
	while(strpos($line,"}") !== 0){
	  if(strpos($line,'"')){
	    $start = strpos($line,'"')+1;
	    $end = strrpos($line,'"');
	  }else{
	    $start = 0;
	    $end = 0;
	  }
	  $idx  = array_search($loc,$allLoc);
	  loadStruct($line,$fp,$EOEflag,$allRefs[$idx],$ini_vars['type_mappings'],$targLoc,$supersedeLoc);
	  $line = trim(fgets($fp));
	}
      }
    }
    fclose($fp);
  }
}

function getLocArray($inarr){
  $outarr = array();
  
  for($i=0;$i<count($inarr);$i++){
    $tmparr = explode("|",$inarr[$i]);
    //for($j=2;$j<count($tmparr);$j++){
    //  $outarr[] = $tmparr[$j++];  // loc values in every other array location
    //}
    if(count($tmparr) == 1)
      $outarr[] = $tmparr[0];
    else
      $outarr[] = join("|",$tmparr);
    //$outarr[] = $tmparr[0] . "|" . $tmparr[1];
  }

  return($outarr);
}

function makePage($filename,$mode){
  // makePage.php is run in one of two modes: show or cache.  In cache 
  //   it checks to see if any supporting files have been updated since the 
  //   existing cached page was created and makes a new cached page if so.
  //   If it's run in show mode it creates a page and displays it.
  
  // This file assumes that bib2php.conf is in the base directory.

  //include("pubs/bib2php-sty.php");

  // Read config file
  $ini_vars = parse_ini_file("pubs/bib2php.conf",true);
  $cacheLoc = $ini_vars['bib2php_vars']['CACHELOC'];
  $bibfile = $ini_vars['bib2php_vars']['BIBTEX'];
  $auxfile = $ini_vars['bib2php_vars']['AUX'];
  $pdfdir = $ini_vars['bib2php_vars']['PDF'];
  $basedir = $ini_vars['bib2php_vars']['BASE1'];
  $exgroup = $ini_vars['bib2php_vars']['EXGROUP'];
  $lcvheader = $ini_vars['bib2php_vars']['LCVHEADER'];
  $types = array_keys($ini_vars['type_mappings']);
  $unexcludable = explode(",",$ini_vars['bib2php_vars']['UNEXCLUDABLE']);

  // generate $excludeArr from filename
  $tmpArr = explode(".",$filename);
  $tmpArr = explode("_",$tmpArr[0]);
  unset($tmpArr[0]);
  unset($tmpArr[1]);
  $excludeArr = array_diff($types,$tmpArr);

  // set variables based on filename
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

  $targLoc = "";
  $allRefs = array();
  $allLoc = array();
  $Nrefs = bibtex2array($allRefs,$allLoc,$bibfile,$ini_vars,$targLoc);
  readAux($allRefs,$allLoc,$auxfile,$ini_vars,$targLoc,$supersedeLoc);
  
  // if superseded docs excluded, create an array of all superseded docs
  $superseded = "";
  if(in_array("SUPER",$excludeArr)){
    for($i=0;$i<$Nrefs;$i++){
      if(strlen($superseded) == 0){
	$superseded = $allRefs[$i]->super;
      }else{
	$superseded = $superseded . "|" . $allRefs[$i]->super;
      }
    }
  }
  
  // add references with multiple types
  for($i=0;$i<$Nrefs;$i++){
    if(strpos($allRefs[$i]->type,",") !== false){  // contains multiple types
      $typeArray = explode(",",$allRefs[$i]->type);
      for($j=0;$j<count($typeArray);$j++){
	if($j == 0){
	  $allRefs[$i]->type = $typeArray[$j];
	}else{
	  $tmpRef =& new Ref();
	  $tmpRef = $allRefs[$i];
	  $tmpRef->type = $typeArray[$j];
	  $allRefs[] = $tmpRef;
	}
      }
    }
  }
  $Nrefs = count($allRefs); 
  
  // remove refs based on aux file and checkboxes
  $alltypes = array();
  $newRefs = array();
  $newLoc = array();
  for($i=0;$i<$Nrefs;$i++){
    $pullFlag = 0;
    if(strcmp($allRefs[$i]->omit,"true") == 0){
      $pullFlag = 1;
    }
    if($pullFlag == 0 && in_array("SUPER",$excludeArr)){
      $tmparr = explode("|",$superseded);
      if(in_array($allRefs[$i]->loc,$tmparr)){
	$pullFlag = 1;
      }
    }
    if($pullFlag == 0 && in_array($allRefs[$i]->type,$excludeArr)){
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
	while($i-$ctr >= 0 && $allRefs[$i]->dateNum >= $newRefs[$i-$ctr]->dateNum){
	  $ctr++;
	}
	$ctr--;  // one too many in loop
	// now put allRefs[$i] at position $i-$ctr and move others down
	for($j=$i-1;$j>=$i-$ctr;$j--){
	  $newRefs[$j+1] = $newRefs[$j];
	}
	$newRefs[$i-$ctr] = $allRefs[$i];
      }
    }
  }
  
  ///////////////////////////////////////////
  //
  //    HTML Starts here.
  //
  ///////////////////////////////////////////
  header('Expires: Mon, 14 Oct 2002 05:00:00 GMT');
  header('Last-Modified: ' . gmdate("D, d M Y H:i:s") . ' GMT');
  header('Cache-Control: no-store, no-cache, must-revalidate');
  header('Cache-Control: post-check=0, pre-check=0', false);

  ob_start();           // put output in buffer
  
  echo("<html><head>\n");
  echo("<TITLE>Online Publications: LCV</TITLE>\n");
  echo("   <META HTTP-EQUIV=\"Content-Type\" CONTENT=\"text/html; charset=iso-8859-1\">\n");
  echo("   <META NAME=\"GENERATOR\" CONTENT=\"Gnu Emacs v19.34\">\n");
  echo("<link rel=\"stylesheet\" href=\"utils/lcv.css\" type=\"text/css\">\n");
  echo("<link rel=\"stylesheet\" href=\"utils/pubs.css\" type=\"text/css\">\n");
  echo("   </HEAD>\n");
  echo("   <BODY>\n");
  echo("<a name=\"top\"> </a>\n");
  if(strcmp($lcvheader,"on") === 0){
    $tmpstr = "utils/lcvheader_dynamic.html";
    echo(file_get_contents($tmpstr));
    echo("<div id=\"pagetitle\">Selected Online Publications</div>\n");
  }
  echo("<form name=\"sortm\" action=\"publications.php\" method=\"get\"><br>");
  echo("<table>");
  echo("<tr><td colspan=2>Sort by:&nbsp;&nbsp; \n");

  $Dcheck="";    $Tcheck="";    $Acheck="";    $TOPcheck="";   
  if(empty($smethod) || strcmp($smethod,"date") === 0)
    $Dcheck="checked";
  elseif(strcmp($smethod,"author") === 0)
    $Acheck="checked";
  elseif(strcmp($smethod,"type") === 0)
    $Tcheck="checked";
  elseif(strcmp($smethod,"topic") === 0)
    $TOPcheck="checked";
  else{
    $Dcheck="checked";
    echo "Error: invalid sort method: $smethod";
  }

  echo("<input type=\"radio\" name=\"smethod\" value=\"date\" onClick=\"sortm.submit();\" $Dcheck>Year\n");
  echo("&nbsp;&nbsp;&nbsp;\n");
  echo("<input type=\"radio\" name=\"smethod\" value=\"type\" onClick=\"sortm.submit();\" $Tcheck>Type\n");
  echo("&nbsp;&nbsp;&nbsp;\n");
  echo("<input type=\"radio\" name=\"smethod\" value=\"author\" onClick=\"sortm.submit();\" $Acheck>First Author\n");

  echo("</td></tr>");
  echo("<tr><td valign=top>Exclude:&nbsp;&nbsp;</td><td>");

  $types = array_keys($ini_vars['type_mappings']);
  // pull exgroup types out of $types array.
  $tmparr = explode("|",$exgroup);
  $exgroupTitle = $tmparr[0];
  $exgroup = explode(",",$tmparr[1]);  

  $subtypes = array_values(array_diff($types,$exgroup));
  for($i=0; $i<count($subtypes); $i++){
    if(!in_array($subtypes[$i],$unexcludable)){
      $name=$subtypes[$i];
      $checked="";
      if(in_array($name,$excludeArr))
	$checked="checked";
      $tmparr = explode("|",$ini_vars['type_mappings'][$name]);
      $title = $tmparr[0];
      echo("<nobr><input type=\"checkbox\" name=\"$name\" onClick=\"sortm.submit();\" $checked>\n");
      echo $title . "</nobr>&nbsp;&nbsp;&nbsp;&nbsp;\n";
    }
  }
  // now make exgroup checkbox
  //   decide if checked
  if(count($exgroup) > 1){
    $cond = 1;
    foreach($exgroup as $value){
      if(!in_array($value,$excludeArr))
	$cond = 0;
    }
    
    $checked = "";
    if($cond)
      $checked="checked";
    echo("<nobr><input type=\"checkbox\" name=\"EXGROUP\" onClick=\"sortm.submit();\" $checked>\n");
    echo $exgroupTitle . "</nobr>&nbsp;&nbsp;&nbsp;&nbsp;\n";
  }
  
  echo("</td></tr></table>");
  echo('</form><p>'."\n");
  
  // compute sorted index list based on method and print
  $vals2sort = array();
  switch ($smethod) {
  case "date": default:
    $allyears = array();
    for($i=0;$i<$Nrefs;$i++){
      $allyears[] = trim($newRefs[$i]->year);
    }
    $uallyears = array_unique($allyears);
    printLinks($uallyears,$uallyears);
    $tmpyear = 0;
    for($i=0;$i<$Nrefs;$i++){
      if(trim($newRefs[$i]->year) != trim($tmpyear)){
	$tmpyear = $newRefs[$i]->year;
	printDivider($tmpyear,$tmpyear,$i==0);
      }
      $newRefs[$i]->printSelf($basedir,$pdfdir,"main",NULL);
    }
    break;
  case "author":
    for($i=0;$i<$Nrefs;$i++){
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
    printLinks($unique_auth,$unique_auth);
    $keys = array_keys($unique_auth);
    $start = 0;
    for($i=0;$i<count($keys);$i++){
      if($i == 0)
	$start = 0;
      else
	$start = $keys[$i];
      if($i == count($keys)-1)
	$end = count($vals2sort);
      else
	$end = $keys[$i+1];
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
	  printDivider($tmpauth,$tmpauth,$i==0);
	}
	$newRefs[$tmparr[$date_keys[$j]]]->printSelf($basedir,$pdfdir,"main",NULL);
      }
    }
    break;
  case "type":
    $DOctr = 1;
    $docOrder = array();
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
    
    $doKeys = array_keys($docOrder);
    $ln = array();
    $lu = array();
    for($i=0;$i<count($doKeys);$i++){
      if(array_key_exists($doKeys[$i],$ini_vars['type_mappings'])){
	$lu[] = $doKeys[$i];
	$fooarr = explode("|",$ini_vars['type_mappings'][$doKeys[$i]]);
	$ln[] = $fooarr[0];
      }else{
	$lu[] = $doKeys[$i];
	$ln[] = $doKeys[$i];
      }
    }
    printLinks($ln,$lu);
    
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
    for($i=1;$i<=count($keys);$i++){
      if($i == 1)
	$start = 0;
      else
	$start = $keys[$i-1];
      if($i == count($keys))
	$end = count($vals2sort);
      else
	$end = $keys[$i];
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
	  $name = $tmpval;
	  if(array_key_exists($tmpval,$ini_vars['type_mappings'])){
	    $tagarr = explode('|',$ini_vars['type_mappings'][$tmpval]);
	    $name = $tagarr[0];
	  }
	  printDivider($name,$tmpval,$i==1);
	} 
	$newRefs[$tmparr[$date_keys[$j]]]->printSelf($basedir,$pdfdir,"main",NULL);
      }
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
    echo("<table width=\"100%\" border=0 cellpadding=\"0%\"><tr>\n");
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
	    echo("<td valign=top><table cellpadding=\"0%\" cellspacing=\"0%\"border=0 width=\"100%\"><tr><th colspan=2 align=left><a href=\"#$foo[0]\">$foo[0]</a></th></tr>");
	  }
	}else{
	  if(strcmp($foo[0],"no topic") != 0){
	    echo("</td></table><td valign=top><table cellpadding=\"0%\" cellspacing=\"0%\"border=0 width=\"100%\"><tr><th colspan=2 align=left><a href=\"#$foo[0]\">$foo[0]</a></th></tr>");
	  }
	}
	if(strcmp($foo[0],"no topic") != 0){
	  echo('<tr><td width=\"20\"></td><td><a href="#');
	  echo("$tmpval");
	  echo('"');
	  echo("><font size=-2><b>$foo[1]</b></font></a></td></tr>\n");
	}
      }else{
	if(strcmp($foo[0],"no topic") != 0){
	  echo('<tr><td width=\"20\"></td><td><a href="#');
	  echo("$tmpval");
	  echo('"');
	  echo("><font size=-2><b>$foo[1]<b></font></a></td></tr>\n");
	}
      }
    }
    echo("</table></tr></table><p>\n");
    $start = 0;
    $topic = '';
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
	    echo("<table width=100% bgcolor=$barcolor><tr><td align=center>\n");
	    echo("<b><font size=+2 color=$bartextcolor><a name=\"");
	    echo("$topics[0]");
	    echo('">');
	    echo("&nbsp;&nbsp;$topics[0]</a>");
	    echo('</h1></font></b></td><td>');
	    echo("</td></tr></table>\n");
	    echo("<table width=\"100%\"><tr><td></td></tr></table>\n");
	  } 
	  echo("<table border=0 bgcolor=$barcolor width=100%><tr><td width=\"90%\">\n");
	  if($i == 1){
	    switch ($topics[1]){
	    case "Perceptual Image Metrics":
	      echo("<b><font size=+1 color=$bartextcolor>\n");
	      echo("&nbsp;&nbsp;$topics[1]\n");
	      echo("&nbsp;&nbsp;<a link=$barcolor vlink=\"#AA0000\" href=http://www.cns.nyu.edu/~zwang/files/research/ssim name=\"\n");
	      echo("$tmpauth");
	      echo('>"');
	      echo("</b>[ Topic Page ]<b></a>\n");
	      break;
	    case "Texture Analysis/Representation/Synthesis":
	      echo("<b><font size=+1 color=$bartextcolor>");
	      echo("&nbsp;&nbsp;$topics[1]</a>\n");
	      echo("&nbsp;&nbsp;<a link=$barcolor vlink=\"#AA0000\" href=http://www.cns.nyu.edu/~lcv/texture name=\"");
	      echo("$tmpauth");
	      echo('">');
	      echo("</b>[ Topic Page ]<b></a>\n");
	      break;
	    case "Compression":
	      echo("<b><font size=+1 color=$bartextcolor>");
	      echo("&nbsp;&nbsp;$topics[1]</a>\n");
	      echo("&nbsp;&nbsp;<a link=$barcolor vlink=\"#AA0000\" href=http://www.cns.nyu.edu/~eero/EPWIC name=\"");
	      echo("$tmpauth");
	      echo('">');
	      echo("</b>[ Topic Page ]<b></a>\n");
	      break;
	    case "Modeling Physiology":
	      echo("<b><font size=+1 color=$bartextcolor>");
	      echo("&nbsp;&nbsp;$topics[1]</a>\n");
	      echo("&nbsp;&nbsp;<a link=$barcolor vlink=\"#AA0000\" href=http://www.cns.nyu.edu/~eero/MT-model.html name=\"");
	      echo("$tmpauth");
	      echo('">');
	      echo("</b>[ Topic Page ]<b></a>\n");
	      break;
	    case "Multi-Scale, Oriented Representations (Steerable Pyramids)":
	      echo("<b><font size=+1 color=$bartextcolor>");
	      echo("&nbsp;&nbsp;$topics[1]</a>\n");
	      echo("&nbsp;&nbsp;<a link=$barcolor vlink=\"#AA0000\" href=http://www.cns.nyu.edu/~eero/STEERPYR name=\"");
	      echo("$tmpauth");
	      echo('">');
	      echo("</b>[ Topic Page ]<b></a>\n");
	      break;
	    default:
	      echo("<b><font size=+1 color=$bartextcolor><a name=\"");
	      echo("$tmpauth");
	      echo('">');
	      echo("&nbsp;&nbsp;$topics[1]</a>\n");
	      break;
	    }
	    echo('</font></b></td><td width="10%" align="right">');
	    echo("</td></tr></table>\n");
	  }else{
	    switch ($topics[1]){
	    case "Perceptual Image Metrics":
	      echo("<b><font size=+1 color=$bartextcolor>");
	      echo("&nbsp;&nbsp;$topics[1]\n");
	      echo("&nbsp;&nbsp;<a link=$barcolor vlink=\"#AA0000\" href=http://www.cns.nyu.edu/~zwang/files/research/ssim name=\"");
	      echo("$tmpauth");
	      echo('">');
	      echo("</b>[ Topic Page ]<b></a>\n");
	      break;
	    case "Texture Analysis/Representation/Synthesis":
	      echo("<b><font size=+1 color=$bartextcolor>");
	      echo("&nbsp;&nbsp;$topics[1]</a>\n");
	      echo("&nbsp;&nbsp;<a link=$barcolor vlink=\"#AA0000\" href=http://www.cns.nyu.edu/~lcv/texture name=\"");
	      echo("$tmpauth");
	      echo('">');
	      echo("</b>[ Topic Page ]<b></a>\n");
	      break;
	    case "Compression":
	      echo("<b><font size=+1 color=$bartextcolor>");
	      echo("&nbsp;&nbsp;$topics[1]</a>\n");
	      echo("&nbsp;&nbsp;<a link=$barcolor vlink=\"#AA0000\" href=http://www.cns.nyu.edu/~eero/EPWIC name=\"");
	      echo("$tmpauth");
	      echo('">');
	      echo("</b>[ Topic Page ]<b></a>\n");
	      break;
	    case "Modeling Physiology":
	      echo("<b><font size=+1 color=$bartextcolor>");
	      echo("&nbsp;&nbsp;$topics[1]</a>\n");
	      echo("&nbsp;&nbsp;<a link=$barcolor vlink=\"#AA0000\" href=http://www.cns.nyu.edu/~eero/MT-model.html name=\"");
	      echo("$tmpauth");
	      echo('">');
	      echo("</b>[ Topic Page ]<b></a>\n");
	      break;
	    case "Multi-Scale, Oriented Representations (Steerable Pyramids)":
	      echo("<b><font size=+1 color=$bartextcolor>");
	      echo("&nbsp;&nbsp;$topics[1]</a>\n");
	      echo("&nbsp;&nbsp;<a link=$barcolor vlink=\"#AA0000\" href=http://www.cns.nyu.edu/~eero/STEERPYR name=\"");
	      echo("$tmpauth");
	      echo('">');
	      echo("</b>[ Topic Page ]<b></a>\n");
	      break;
	    default:
	      echo("<b><font size=+1 color=$bartextcolor><a name=\"");
	      echo("$tmpauth");
	      echo('">');
	      echo("&nbsp;&nbsp;$topics[1]</a>\n");
	      break;
	    }
	    echo('</font></b></td><td width="10%" align="right"><a href="#top">'."\n");
	    echo("<font size=-1 color=$bartextcolor>top</font></a>&nbsp;&nbsp;</td></tr></table>");
	  }
	}
	$newRefs[$tmparr[$date_keys[$j]]]->printSelf($basedir,$pdfdir,"main",NULL);
      }
      $start = $keys[$i];
    }
    break;
  }

  if(strcmp($mode,"cache") === 0){
    $content = ob_get_contents();
    ob_end_flush();  // send to display before writing to file
                     //   This doesn't seem to make it any faster. Why?
    $tmpstr = $cacheLoc . $filename;
    $fp = fopen($tmpstr,"w");
    fwrite($fp,$content);
    fclose($fp);
  }
}

?>
