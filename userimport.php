<?php
defined('C5_EXECUTE') or die("Access Denied.");

Loader::model('user_list');
$nh = Loader::helper('navigation');

function createUser($uName, $uEmail, $uPassword = null, $uAttributes) {
  if(null !== UserInfo::getByEmail($uEmail)) {
    echo "User email $uEmail exists.<br />";
  }
  else if(null !== UserInfo::getByUserName($uName)) {
    echo "User name $uName exists.<br />";
  } 
  else {
    $uName = $uName ?: $uEmail; // Default to email as username if none set
    echo "Creating $uName<br />";
    $ui = UserInfo::add(
      ['uName' => $uName,
      'uEmail' => $uEmail,
      'uPassword' => $uPassword,
      'uIsValidated' => LUM_VALIDATE], [UserInfo::ADD_OPTIONS_NOHASH]);
    foreach($uAttributes as $attHandle=>$attValue) {
      // If set to check, validate that the attribute in the 'from' site is in the 'to' site
      if(!LUM_CHECK_ATTRIBUTES || UserAttributeKey::getByHandle($attHandle) ) {
        $ui->setAttribute($attHandle, $attValue);
      } else {
        echo "Omitting attribute $attHandle.<br />";
      }
    }
  }
}

define('LUM_VALIDATE', isset($_REQUEST['validate']) ? $validate : 1);
define('LUM_CHECK_ATTRIBUTES', isset($_REQUEST['checkAttributes']));
if(isset($_REQUEST['xml'])) {
  $doc = new DOMDocument();
  $doc->loadXML(file_get_contents($_REQUEST['xml']));
  foreach($doc->getElementsByTagName('User') as $user) {
    $uAttributes = array();
    foreach($user->getElementsByTagName('Attributes')->item(0)->childNodes as $ua) {
      $uAttributes[$ua->tagName] = $ua->textContent;
    }
    createUser( $user->getElementsByTagName('name')->item(0)->textContent,
      $user->getElementsByTagName('email')->item(0)->textContent,
      $user->getElementsByTagName('raw_pass')->item(0)->textContent,
      $uAttributes );
  }
}
else if(isset($_REQUEST['json'])) {
}

?>
