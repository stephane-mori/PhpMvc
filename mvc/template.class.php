<?php

class Template {

  private $_page;    // Template file HTML code 
  private $_infoTpl = array(); // data array

  /* 
   * Check if .tpl file exists
   * */

  public function __construct ($file) {
    // check the file
    if (empty($file) or !file_exists($file) or !is_readable($file))
      die('Template error : file '.$file.' not found.');
    
    // open and read it
    else {
      $handle = fopen($file, 'rb');
      $this->_page = fread($handle, filesize($file));
      fclose($handle);
    }
  }

  /* 
   * Set value in $infoTpl
   * */

  public function setSimpleVar($varArray = array()) {
    // only if not empty array
    if (empty($varArray)) 
      exit;

    // load data
    foreach($varArray as $var => $data) 
      $this->_infoTpl['.'][][$var] = $data;
  }

  /* 
   * Set value in $infoTpl
   * */

  public function setLoopVar($type, $varArray = array()) {
    // only if not empty array
    if (empty($varArray))
      exit;

    $lastID = (isset($this->_infoTpl[$type]) && count($this->_infoTpl[$type]) != 0) ? (count($this->_infoTpl[$type])) : 0;
    foreach($varArray as $constant => $data) 
      $this->_infoTpl[$type][$lastID][$constant] = $data;
  }

  /*
   * Get HTML Code
   * */

  public function displayHTMLCode () {
    $this->setConstantReplace();
    echo $this->_page;
  }

  /* 
   * set data
   * */

  private function setConstantReplace () {
    foreach($this->_infoTpl as $type => $info) {

      if ($type == '.') { // SimpleVar
	for ($i = 0; $i < count($info); $i++) {
	  // replace {CONST} by data & update .tpl HTML code in _page
	  foreach($info[$i] as $constant => $data) {
	    $data = (file_exists($data)) ? $this->includeFile($data) : $data;
	    $this->_page = preg_replace('`{'.$constant.'}`', $data, $this->_page);
	  }

	}
      }

      else { // LoopVar
	$infoSize = count($info);
	$block = '';

	for ($i = 0; $i < $infoSize; $i++) {
	  // parse HTML
	  $page = htmlentities($this->_page);
	  $infoArray = explode("\n", $page);
	  // remove blank before and after code
	  for ($k = 0; $k < count($infoArray); $k++)
	    $infoArray[$k] = trim($infoArray[$k]);
	  
	  // catch tags
	  $startTag = '<!-- BEGIN '.$type.' -->';
	  $startTag = htmlentities($startTag);
	  $endTag = '<!-- END '.$type.' -->';
	  $endTag = htmlentities($endTag);

	  // catch tag Key in infoArray
	  $startTag = (array_search($startTag, $infoArray)) + 1;
	  $endTag = (array_search($endTag, $infoArray)) - 1;
	  // number of lines between tags
	  $lengthTag = ($endTag - $startTag) + 1;

	  // catch block between Tag
	  $blockTag = array_slice($infoArray, $startTag, $lengthTag);
	  $blockTag = implode("\n", $blockTag); // convert in string
	  
	  // replace constant by data
	  foreach($info[$i] as $constant => $data) {
	    $data = (file_exists($data)) ? $this->includeFile($data) : $data;
	    $blockTag = preg_replace('`{'.$type.'.'.$constant.'}`', $data, $blockTag);
	  }

	  // add data in block
	  $block = ($block == '') ? $blockTag : $block."\n".$blockTag;
	}

	$block = explode ("\n", $block); // convert in array
	$firstPart = array_slice($infoArray, 0, $startTag - 1);
	$secondPart = array_slice($infoArray, $startTag + $lengthTag +1);

	// insert data in HTML code
	$page = array_merge($firstPart, $block, $secondPart);
	
	// decode HTML entities
	for ($i = 0; $i < count($page); $i++)
	  $page[$i] = html_entity_decode($page[$i]);

	$this->_page = implode("\n", $page);
      }
    }
  }
}

?>