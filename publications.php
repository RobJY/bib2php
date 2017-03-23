<?php
// This is the main page for the bib2php system.  Please see pubs/README for 
//   more details.
// written by Rob Young and Eero Simoncelli
// This file assumes that publications.php and bib2php.conf are in the 
//    base directory.

include("pubs/utils.php");

$maindir = "pubs/";

// Read config file
$ini_vars = parse_ini_file($maindir."bib2php.conf",true);
$caching = $ini_vars['bib2php_vars']['CACHING'];
$bibfile = $ini_vars['bib2php_vars']['BIBTEX'];
$auxfile = $ini_vars['bib2php_vars']['AUX'];
$absdir = $ini_vars['bib2php_vars']['ABSTRACT'];
$pdfdir = $ini_vars['bib2php_vars']['PDF'];
$authfile = $ini_vars['bib2php_vars']['AUTHOR'];
$journalfile = $ini_vars['bib2php_vars']['JOURNAL'];
$basedir = $ini_vars['bib2php_vars']['BASE1'];
//$basedir2 = $ini_vars['bib2php_vars']['BASE2'];
$types = array_keys($ini_vars['type_mappings']);
$exgroup = $ini_vars['bib2php_vars']['EXGROUP'];
$cacheLoc = $ini_vars['bib2php_vars']['CACHELOC'];

// get posted variables
//$smethod = $_GET['smethod'];
// if smethod not set, set defaults
//if(strlen($smethod) == 0){
$author = $_GET['author'];  // author variable for anyauthor mode
if(!isset($_GET['smethod'])){
  $smethod = "date";
  // get list of types
  $excludeArr = explode(",",$ini_vars['bib2php_vars']['EXCLUDE']);
}else{
  $smethod = $_GET['smethod'];
  // pull exgroup types out of types
  $tmparr = explode("|",$exgroup);
  $exgroup = explode(",",$tmparr[1]);
  $subtypes = array_values(array_diff($types,$exgroup));
  $excludeArr = array();
  for($i=0; $i<count($subtypes); $i++){
    $currentType = $subtypes[$i];
    //if(!strcmp($_GET[$currentType],"on"))
    if(isset($_GET[$currentType]) && !strcmp($_GET[$currentType],"on"))
      $excludeArr[] = $currentType;
  }
  //if(strcmp($_GET['EXGROUP'],"on")===0){
  if(isset($_GET['EXGROUP']) && strcmp($_GET['EXGROUP'],"on")===0){
    for($i=0;$i<count($exgroup);$i++){
      $excludeArr[] = $exgroup[$i];
    }
  }
}

$types = array_diff(array_keys($ini_vars['type_mappings']),$excludeArr);

// make filename
$filename = "publications_" . $smethod;
foreach($types as $value){
  $filename = $filename . "_" . $value;
}
$filename = $filename . ".php";
//$fullfilename = $basedir2 . "CACHE/" . $filename;
$fullfilename = $cacheLoc . $filename;

if(strcmp($caching,"off") === 0){
  // make and show page
  makePage($filename, "show", $author);
}elseif(file_exists($fullfilename) && 
	filemtime("pubs/" . $bibfile) < filemtime($fullfilename) && 
	filemtime("pubs/" . $auxfile) < filemtime($fullfilename) && 
	filemtime("pubs/bib2php.conf") < filemtime($fullfilename) && 
	filemtime("pubs/utils.php") < filemtime($fullfilename) && 
	filemtime("utils/lcv.css") < filemtime($fullfilename) && 
	filemtime("utils/lcvheader_dynamic.html") < filemtime($fullfilename) && 
	filemtime("utils/lcvfooter.php") < filemtime($fullfilename)){
  // nothing has changed, so no need to make new files.
  include("$fullfilename");
}else{
  // make page creates page and displays.
  makePage($filename, "cache", $author);
}

?>

</body>
</html>
