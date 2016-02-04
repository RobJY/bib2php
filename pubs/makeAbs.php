<?php

$maindir = "./";

$targLoc = $_GET['loc'];
if(strlen($targLoc) == 0){
  header('Location:http://www.cns.nyu.edu/lcv/publications.php');
}

include("utils.php");

// Read config file
$ini_vars = parse_ini_file($maindir."bib2php.conf",true);
$bibfile = $ini_vars['bib2php_vars']['BIBTEX'];
$auxfile = $ini_vars['bib2php_vars']['AUX'];
$absdir = $ini_vars['bib2php_vars']['ABSTRACT'];
$pdfdir = $ini_vars['bib2php_vars']['PDF'];
$authfile = $ini_vars['bib2php_vars']['AUTHOR'];
$journalfile = $ini_vars['bib2php_vars']['JOURNAL'];
$basedir = $ini_vars['bib2php_vars']['BASE1'];
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

$allRefs=array();
$allLoc=array();
$refctr = 0;

$Nrefs = bibtex2array($allRefs,$allLoc,$bibfile,$ini_vars,$targLoc);

$supersedeLoc = array();

readAux($allRefs,$allLoc,$auxfile,$ini_vars,$targLoc,$supersedeLoc);
// if $targLoc not in $allLoc set flag for html error message
//$tmpRef = $allRefs[array_search($targLoc,$allLoc)];
$targIdx = array_search(str_replace(" ","+",$targLoc),$allLoc);
if($targIdx === false){
  echo "<html><head><title>Error</title>";
  echo "<link rel=\"stylesheet\" href=\"../utils/lcv.css\" type=\"text/css\">\n";
  echo "<link rel=\"stylesheet\" href=\"../utils/pubs.css\" type=\"text/css\">\n";
  echo "</head>";
  echo "<body bgcolor=\"#FFFFFF\" text=\"#000000\">";
  echo "<h2>Error retrieving LCV publication abstract: unknown key '$targLoc'.";
  echo "</h2>Please check the link and try again.<p>";
  echo "A full list of LCV publications is available at <a href='";
  echo "http://www.cns.nyu.edu/~lcv/publications.php'>";
  echo "http://www.cns.nyu.edu/~lcv/publications.php</a><p>";  
  exit(1);
}else{
  $tmpRef = $allRefs[$targIdx];
}

// now go through all $supersedeLoc[] and find corresponding $superIdx values
//   in allRefs[]
$superIdx = array();
for($i = 0; $i < count($supersedeLoc); $i++){
  for($j = 0; $j < $Nrefs; $j++){
    if(strcmp($allRefs[$j]->loc,$supersedeLoc[$i]) == 0){
      $superIdx[$i] = $j;
    }
  }
}

// read in abstract file if it exists
$abfilename = $absdir.$targLoc."-abstract.txt";
if(file_exists($abfilename)){
  $fp = fopen($abfilename,"r");
  $abstract = "";
  if($fp){
    while(!feof($fp)){
      $line = trim(fgets($fp));
      $abstract .= " ".$line;
    }
    fclose($fp);
  }
}elseif(strlen($tmpRef->abstract) > 0){
  $abstract = $tmpRef->abstract;
}else{
  $abstract = "Abstract currently unavailable.";
}

if(file_exists($authfile)){
  $authorURLarray = parse_ini_file($authfile);
}
/////////////////////////////////////////////////
//
//         HTML starts here
// 
/////////////////////////////////////////////////
$tmpstr = cleanStr($tmpRef->title,1);
echo "<html><head><title>Abstract: $tmpstr</title>";
if(strcmp($tmpRef->keywords,"") != 0){
  echo '<META name="keywords" content="';
  echo "$tmpRef->keywords";
  echo '">';
}
// print google scholar mea tags
$tmpRef->printSelf($basedir,$pdfdir,"meta",$authorURLarray);

echo "<link rel=\"stylesheet\" href=\"../utils/lcv.css\" type=\"text/css\">\n";
echo "<link rel=\"stylesheet\" href=\"../utils/pubs.css\" type=\"text/css\">\n";
echo "</head>";
echo "<body bgcolor=\"#FFFFFF\" text=\"#000000\">";
echo "<script type='text/javascript' src='../utils/wz_tooltip.js'></script>";
if(strlen($tmpRef->cover_url) > 0){
  echo "<table border=0 width=100%><tr><td width=70%>";
}else{
  echo "<table border=0 width=100%><tr><td width=100%>";
}

$tmpRef->printSelf($basedir,$pdfdir,"abstract",$authorURLarray);

//if(strcmp($tmpRef->copy,"") != 0){
//  echo "&copy; <i>$tmpRef->copy</i><br>";
//}

//if(strcmp($tmpRef->other,"") != 0){
//  echo "<br>$tmpRef->other<p>";
//}

if(strcmp($tmpRef->doi,"") != 0){
  echo "<p>DOI: <a href=\"http://dx.doi.org/$tmpRef->doi\">$tmpRef->doi</a><br>";
}

// If this doc has been superseded, give user a link to most current paper.
// If this is a list of refs, find the most recent.
if(count($supersedeLoc) == 1){
  $loc = $supersedeLoc[0];
  $superTitle = cleanStr($allRefs[$superIdx[0]]->title,1);
  $superAuthor = $allRefs[$superIdx[0]]->author;
  $authArr = explode(" and ",$allRefs[$superIdx[0]]->author);
}elseif(count($supersedeLoc) > 1){
  $superyears = array();
  for($i=0;$i<count($supersedeLoc)-1;$i++){
    $superyears[] = $allRefs[$superIdx[$i]]->year;
  }
  $maxvalue = max($superyears);
  $key = array_search($maxvalue,$superyears);
  $loc = $allRefs[$superIdx[$key]]->loc;
  $superTitle = $allRefs[$superIdx[$key]]->title;
  $superAuthor = $allRefs[$superIdx[$key]]->author;
  $authArr = explode(" and ",$allRefs[$superIdx[$key]]->author);
}
if(!empty($supersedeLoc)){
  $authStr = "";
  for($i=0;$i<count($authArr);$i++){
    $authArr[$i] = cleanStr($authArr[$i],1);
    $testauth = explode(" ",$authArr[$i]);
    if(count($testauth) == 2){
      $testLname = $testauth[1];
    }elseif(count($testauth) == 3){
      $testLname = $testauth[2];
    }
    if($i == count($authArr)-1){
      $authStr = $authStr."$authArr[$i]";
    }elseif($i == count($authArr)-2 && count($authArr) > 2){
      $authStr = $authStr."$authArr[$i], </i>and<i> ";
    }elseif($i == count($authArr)-2){
      $authStr = $authStr."$authArr[$i] </i>and<i> ";
    }else{
      $authStr = $authStr."$authArr[$i], ";
    }
  }
  //$tmpstr = sprintf("<p>This paper has been superseded by:<br><a href=\"%smakeAbs.php?loc=$loc\"><b>$superTitle</b><br>by <i>$authStr</i></a>.<p>",$basedir);
  $tmpidx  = array_search($loc,$allLoc);
  $tmpstr = sprintf("<p>This paper has been superseded by:<br><a href=\"%smakeAbs.php?loc=$loc\">" . $allRefs[$tmpidx]->printSelf(NULL,NULL,"super",NULL) . "</a><p>",$basedir);

  echo $tmpstr;
}

$tmpRef->bibtex .= "}";
echo "\n";
?>

<script type="text/javascript">
  function show_hide(){
  if(document.getElementById('text1').style.display == 'none'){
    document.getElementById('text1').style.display='inline';
    document.getElementById('text2').style.display='none';
    document.getElementById('text3').style.display='inline';
  }else if(document.getElementById('text1').style.display == 'inline'){
    document.getElementById('text1').style.display='none';
    document.getElementById('text2').style.display='inline';
    document.getElementById('text3').style.display='none';
  }}
document.write("<div id=\"text2\" style=\"display:inline;\">");
document.write("BibTeX: <a href=\"javascript:show_hide();\">[show]</a>");
document.write("</div>");
document.write("<div id=\"text3\" style=\"display:none;\">");
document.write("BibTeX: <a href=\"javascript:show_hide();\">[hide]</a><br>");
document.write("</div>");
document.write("<div id=\"text1\" style=\"display:none;\"><font size=-1>");

<?php
echo "document.write('" . addslashes($tmpRef->bibtex) . "');";
?>

document.write("</font></div>");
</script><noscript></noscript>

<?php

if(strcmp($tmpRef->pdf_url,"") != 0){
  echo "<br>Download:";
  echo "<li><a href=$tmpRef->pdf_url>Reprint (pdf)</a>";
}else{
  $tmpurl = $pdfdir . strtolower($tmpRef->loc) . ".pdf";
  if(file_exists($tmpurl)){
    echo "<li><a href=$tmpurl>Reprint (pdf)</a>";
  }
}
if(strlen($tmpRef->official_url) > 0){
  echo "<li><a href=$tmpRef->official_url>Official (pdf)</a>";
}
if(strcmp($tmpRef->ps_url,"") != 0){
  echo "<li><a href=$tmpRef->ps_url>Reprint (ps)</a>";
}
if(count($tmpRef->dlTitle) > 0){
  for($i=0;$i<count($tmpRef->dlTitlearr);$i++){
    printf("<li><a href=%s>%s</a>",$tmpRef->dlURLarr[$i],$tmpRef->dlTitlearr[$i]);
  }
 }

if(count($tmpRef->cover_url) > 0){
  echo "</td><td width=30% align=center><a href=$tmpRef->cover_url><img src=$tmpRef->cover_pic></a></td></table>";
}else{
  echo "</td><td></td></table>";
}
echo "<hr size=1 noshade>";
echo "$abstract";
echo "<hr size=1 noshade>";

//echo "Related:";
// list superseded documents first if there are any
if(strcmp($tmpRef->super,"") != 0){
  echo "<li>Superseded Publications: ";
  $superArray = explode("|",$tmpRef->super);
  for($i=0;$i<count($superArray);$i++){
    $tmpidx = array_search($superArray[$i],$allLoc);
    $authArr = explode(" and ",$allRefs[$tmpidx]->author);
    $authStr = "";
    for($j=0;$j<count($authArr);$j++){
      $authArr[$j] = cleanStr($authArr[$j],1);
      $testauth = explode(" ",$authArr[$j]);
      if(count($testauth) == 2){
	$testLname = $testauth[1];
      }elseif(count($testauth) == 3){
	$testLname = $testauth[2];
      }
      if($j == count($authArr)-1){
	$authStr = $authStr."$authArr[$j]";
      }elseif($j == count($authArr)-2 && count($authArr) > 2){
	$authStr = $authStr."$authArr[$j], </i>and<i> ";
      }elseif($j == count($authArr)-2){
	$authStr = $authStr."$authArr[$j] </i>and<i> ";
      }else{
	$authStr = $authStr."$authArr[$j], ";
      }
    }
    $allRefs[$tmpidx]->title = str_replace("{","",$allRefs[$tmpidx]->title);
    $allRefs[$tmpidx]->title = str_replace("}","",$allRefs[$tmpidx]->title);
    $allRefs[$tmpidx]->title = str_replace("'","\'",$allRefs[$tmpidx]->title);
    //$popups[] = sprintf("<font size=-1><b><nobr>%s</nobr></b><br>by <i>%s</i></font>",$allRefs[$tmpidx]->title,$authStr);
    $popups[] = $allRefs[$tmpidx]->printSelf(NULL,NULL,"popup",NULL);
  }
  for($i=0;$i<count($superArray);$i++){
    $tmpstr = sprintf("<a class=pop2 onmouseover=\"Tip('%s')\" onmouseout=\"UnTip()\" href=%smakeAbs.php?loc=%s>%s</a>",$popups[$i],$basedir,$superArray[$i],$superArray[$i]);
    if($i == count($superArray)-1){
      echo $tmpstr;
    }else{
      echo $tmpstr . ", ";
    }
  }
 }

// now list related docs from aux file
// start by making pop-ups
$popups1 = array();  // just loc
$popups2 = array();  // header + loc(s)
$locArr = getLocArray($tmpRef->relatedArr);
for($i=0;$i<count($locArr);$i++){
  $tmpArr = explode("|",$locArr[$i]);
  if(count($tmpArr) == 1){
    $tmpLoc = $tmpArr[0];
    $tmpidx = array_search($tmpLoc,$allLoc);
    $popups1[] = $allRefs[$tmpidx]->printSelf(NULL,NULL,"popup",NULL);
  }else{
    $tmpLoc = $tmpArr[1];
    $tmpidx = array_search($tmpLoc,$allLoc);
    for($j=2;$j<=count($tmpArr);$j++){
      $popups2[] = $allRefs[$tmpidx]->printSelf(NULL,NULL,"popup",NULL);
      $tmpidx = array_search($tmpArr[$j],$allLoc);
    }
  }
 }
// Now print the related section
if(count($tmpRef->relatedArr) > 0){
  $tmpArrShort = array();
  //$locCtr = 0;
  for($i=0;$i<$tmpRef->relatedCtr;$i++){
    $tmpArr = explode("|",$tmpRef->relatedArr[$i]);
    if(count($tmpArr) == 1){
      $tmpArrShort[] = $tmpRef->relatedArr[$i];
    }else{
      //$tmpArrLong = explode("|",$tmpRef->relatedArr[$i]);
      echo "<li>$tmpArr[0]: ";
      $tmpstr = "";
      for($j=1;$j<count($tmpArr);$j++){
	if($j != 1)
	  $tmpstr .= ", ";
	//$tmpstr .= sprintf("<a class=pop2 onmouseover=\"Tip('%s')\" onmouseout=\"UnTip()\" href=\"%smakeAbs.php?loc=%s\">%s</a>",$popups2[$locCtr],$basedir,$tmpArr[$j],$tmpArr[$j]);
	//$tmpstr .= sprintf("<a class=pop2 onmouseover=\"Tip('%s')\" onmouseout=\"UnTip()\" href=\"%smakeAbs.php?loc=%s\">%s</a>",$popups2[$j-1],$basedir,$tmpArr[$j],$tmpArr[$j]);
	$tmpstr .= sprintf("<a class=pop2 onmouseover=\"Tip('%s')\" onmouseout=\"UnTip()\" href=\"%smakeAbs.php?loc=%s\">%s</a>",$popups2[$i],$basedir,$tmpArr[$j],$tmpArr[$j]);
	//echo $tmpstr;
	//$locCtr++;
      }
      echo $tmpstr;
    }
  }
  if(count($tmpArrShort) > 0){
    echo "<li>Related Publications: ";
    for($i=0;$i<count($tmpArrShort);$i++){
      if($i != 0)
	echo ", ";
      //$tmpstr = sprintf("<a class=pop2 onmouseover=\"Tip('%s')\" onmouseout=\"UnTip()\" href=%smakeAbs.php?loc=%s>%s</a>",$popups1[$locCtr],$basedir,$tmpArrShort[$i],$tmpArrShort[$i]);
      $tmpstr = sprintf("<a class=pop2 onmouseover=\"Tip('%s')\" onmouseout=\"UnTip()\" href=%smakeAbs.php?loc=%s>%s</a>",$popups1[$i],$basedir,$tmpArrShort[$i],$tmpArrShort[$i]);
      //$locCtr++;
      echo $tmpstr;
    }
  }
}

//if(count($tmpRef->relatedArr) > 0){
//  //$relatedAreas = explode("||",$tmpRef->related);
//  $locCtr = 0;
//  for($i=0;$i<$tmpRef->relatedCtr;$i++){
//    //$tmparr = explode("|",$tmpRef->newRelated[$i]);
//    $tmparr = explode("|",$tmpRef->relatedArr[$i]);
//    echo "<li>$tmparr[0]: ";
//    for($j=1;$j<count($tmparr);$j++){
//      if($j > 1){
//	echo ", ";
//      }
//      $tmpstr = sprintf("<a class=pop2 onmouseover=\"Tip('%s')\" onmouseout=\"UnTip()\" href=%smakeAbs.php?loc=%s>%s</a>",$popups[$locCtr],$basedir,$tmparr[$j+1],$tmparr[$j]);
//      $j++;
//      echo $tmpstr;
//      $locCtr++;
//    }
//  }
// }

echo "<li><a href=http://www.cns.nyu.edu/~lcv/publications.php>";
echo "Listing of all publications</a>";

// add coins tag
//printCoins($tmpRef);

echo "</body>";
?>
</html>
