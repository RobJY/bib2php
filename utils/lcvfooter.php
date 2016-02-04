<?php
function lcvfooter(){
  if(func_num_args() > 1){
    echo "<TABLE BORDER=0 CELLPADDING=0 CELLSPACING=3 width=\"" . func_get_arg(1) . "\" ALIGN=\"CENTER\">";
  }else{
    echo "<TABLE BORDER=0 CELLPADDING=0 CELLSPACING=3 width=\"100%\" ALIGN=\"CENTER\">";
  }
  echo "<TR><TD COLSPAN=3 BGCOLOR=\"#FF0000\" HEIGHT=2>";
  echo "</TD>";
  echo "</TR>";
  echo "<TR>";
  echo "<TD NOWRAP VALIGN=\"TOP\" ALIGN=\"LEFT\" WIDTH=\"33%\"><FONT SIZE=\"-2\">";
  echo " Revised:&nbsp;";
  //echo date("F d Y.",filemtime($_SERVER['PATH_TRANSLATED'])); 
  echo date("F d Y.",filemtime($_SERVER['SCRIPT_FILENAME']));
  if(func_num_args() > 0 && strlen(func_get_arg(0)) > 0){
    echo "<br> Created:&nbsp;" . func_get_arg(0);
  }
  echo "</FONT></TD>";
  echo "<TD NOWRAP VALIGN=\"TOP\" ALIGN=\"CENTER\" WIDTH=\"33%\"><FONT SIZE=\"-2\">";
  echo "<script language=\"JavaScript\">";
  echo "eaddr2 = 'cns.nyu.edu';";
  echo "eaddr1 = 'acosta';";
  echo "document.write('<a href=\"mailto:' + eaddr1 + \"@\" + eaddr2 + '\">' + eaddr1 + \"@\" + eaddr2 + '</a>');";
  echo "</script>";
  echo "<noscript>acosta AT cns.nyu.edu</noscript>";
  echo "</TD>";
  echo "<TD NOWRAP VALIGN=\"TOP\" ALIGN=\"RIGHT\" WIDTH=\"33%\"><FONT SIZE=\"-2\">";
  $path = $_SERVER['SCRIPT_NAME'];
  $serv = $_SERVER['SERVER_NAME'];
  echo "<a href=\"" . $path . "#top\">top</a>";
  echo "</FONT></TD>";
  echo "</TR>";
  echo "</TABLE>";
}
?>
