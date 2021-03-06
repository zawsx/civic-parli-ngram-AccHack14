<?php
// Usage: hansard_to_db.php <filename.xml> 
// 	  parses a single file 

$majorheading = array();
$minorheading = array();
$speech = array();
$division = array();

$majorheading['text'] = "";
$minorheading['text'] = "";
$speech['text'] = "";

$last_major_heading = "";
$last_minor_heading = "";

// placeholder
$section_name = "NOT_IN_DOC";
$output="";
function startElemHandler($parser, $name, $attribs) {
	global $section_name, $majorheading, $minorheading, $speech, $division, $debatedate, $last_major_heading, $last_minor_heading, $filename;
  if (strcasecmp($name, "major-heading") == 0) {
    $majorheading['type'] = "MAJOR-HEADING";
    $majorheading['id'] = $attribs["id"];
    $majorheading['nospeaker'] = isset($attribs["nospeaker"]) ? $attribs["nospeaker"] : "";
    $majorheading['colnum'] = isset($attribs["colnum"]) ? $attribs["colnum"] : "";
    $majorheading['time'] = isset($attribs["time"]) ? $attribs["time"] : "";
    $majorheading['url'] = isset($attribs["url"]) ? $attribs["url"] : "";
    $majorheading['date'] = $debatedate;
    $majorheading['text'] = "";
    $section_name = "MAJOR-HEADING"; 

  }
  if (strcasecmp($name, "minor-heading") == 0) {
    $minorheading['type'] = "MINOR-HEADING";
    $minorheading['id'] = $attribs["id"];
    $minorheading['nospeaker'] = isset($attribs["nospeaker"]) ? $attribs["nospeaker"] : "";
    $minorheading['colnum'] = isset($attribs["colnum"]) ? $attribs["colnum"] : "";
    $minorheading['time'] = isset($attribs["time"]) ? $attribs["time"] : "";
    $minorheading['url'] = isset($attribs["url"]) ? $attribs["url"] : "";
    $minorheading['date'] = $debatedate;
    $minorheading['text'] = "";
    $section_name = "MINOR-HEADING";
  }
  if (strcasecmp($name, "speech") == 0) {
    $speech['filename'] = $filename;
    $speech['type'] = "SPEECH";
    $speech['id'] = $attribs['id'];
    $speech['speakerid'] = isset($attribs['speakerid']) ? $attribs['speakerid'] : '';
    $speech['nospeaker'] = isset($attribs["nospeaker"]) ? $attribs["nospeaker"] : '';
    $speech['speakername'] = isset($attribs['speakername']) ? $attribs['speakername'] : '';
    $speech['time'] = isset($attribs["time"]) ? $attribs["time"] : "";
    $speech['colnum'] = isset($attribs['colnum']) ? $attribs['colnum'] : "";
    $speech['majorheading'] = $last_major_heading;
    $speech['minorheading'] = $last_minor_heading;
    $speech['url'] = isset($attribs['url']) ? $attribs['url'] : '';
    $speech['date'] = $debatedate;
    $speech['text'] = "";
    $section_name = "SPEECH";
  }
  if (strcasecmp($name, "division") == 0) {
    $division['type'] = "DIVISION";
    $section_name = "DIVISION";
  }
}

function endElemHandler($parser, $name) {
	global $section_name, $pdo, $majorheading, $minorheading, $speech, $division, $debatedate, $last_major_heading, $last_minor_heading, $filename;
  if (strcasecmp($name, "major-heading") == 0) {
    	echo '<div class="alert majorheading alert-warning"><h4>'.ltrim(rtrim($majorheading['text'])).'</h4><h6>'.$majorheading['date'].', '.$majorheading['time'].'</h6></div>';
	global $last_major_heading;
	$last_major_heading = $majorheading['text'];
	$majorheading=array();
	$majorheading['text'] = "";
  }
  if (strcasecmp($name, "minor-heading") == 0) {
    	echo '<div class="alert alert-info"><h4>'.ltrim(rtrim($minorheading['text'])).'</h4></div>';
	global $last_minor_heading;
	$last_minor_heading = $minorheading['text'];
	$minorheading=array();
	$minorheading['text'] = "";
 }
  if (strcasecmp($name, "speech") == 0) {
	echo '<div class="row">';
	echo '<div class="col-lg-12">';
	echo '<span class="label label-success">'.$speech['speakername'].'</span>';
	echo '</div>';
	echo '<div class="col-lg-12">';
	echo '<div class="row">';
	echo '<div class="col-lg-1"><img src="http://placehold.it/60x60" class="img-circle special-img"></img></div>';
	echo '<div class="col-lg-10">';
	echo '<blockquote>'.$speech['text'].'</blockquote>';
	echo '</div>';
	echo '</div>';
	echo '</div>';
	echo '</div>';
	$speech=array();
	$speech['text'] = "";
  }
  if (strcasecmp($name, "division") == 0) {
	echo '<div class="division"></div>';
	$division=array();
  }
 }


function sax_cdata($parser, $data) {
	global $section_name;
	global $speech;
	global $majorheading, $minorheading, $speech, $division, $debatedate;
	$text=htmlspecialchars($data);

  if ($section_name=="MAJOR-HEADING") {
	$majorheading['text']=$majorheading['text'].$text;
  } else if ($section_name=="MINOR-HEADING") {
	$minorheading['text']=$minorheading['text'].$text;
  } else if ($section_name=="SPEECH") {
	$speech['text']=$speech['text'].$text;
  } else if ($section_name=="DIVISION") {

  }

}

$parser = xml_parser_create();
xml_set_element_handler($parser, 'startElemHandler', 'endElemHandler');
xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
xml_set_character_data_handler($parser, 'sax_cdata');

//$filename=$argv[1];
$filename=$_GET['filename'];
if ($filename=="") {
	echo "You need to provide a filename\n";
	die();
}


$debatedate = substr($filename,7,10);

$strXML = implode("",file("debates/$filename"));
xml_parse($parser, $strXML);
xml_parser_free($parser)

?>
