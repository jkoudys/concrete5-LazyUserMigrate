<?php
defined('C5_EXECUTE') or die("Access Denied.");

Loader::model('user_list');
$nh = Loader::helper('navigation');

function createUser($uName, $uEmail, $uPassword = null, $uAttributes, $uGroups) {
  if(null !== UserInfo::getByEmail($uEmail)) {
    echo "User email $uEmail exists.<br />";
  }
  else if(null !== UserInfo::getByUserName($uName)) {
    echo "User name $uName exists.<br />";
  } 
  else {
    if(null !== ($uName = $uName ?: $uEmail)) { // Default to email as username if none set
      echo "Creating $uName<br />";
      $ui = UserInfo::add(
        ['uName' => $uName,
        'uEmail' => $uEmail,
        'uPassword' => $uPassword,
        'uIsValidated' => LUM_VALIDATE], [UserInfo::ADD_OPTIONS_NOHASH]);
      foreach((array) $uAttributes as $attHandle=>$attValue) {
        // If set to check, validate that the attribute in the 'from' site is in the 'to' site
        if(!LUM_CHECK_ATTRIBUTES || UserAttributeKey::getByHandle($attHandle) ) {
          try {
            $ui->setAttribute($attHandle, $attValue);
          } catch(Exception $e) {
            echo "Error setting attribute $attHandle for $uName. Check that attribute exists in target and is of the correct type.<br />";
          }
        } else {
          echo "Omitting attribute $attHandle.<br />";
        }
      }
      foreach((array) $uGroups as $gid=>$groupName) {
        if(null !== ($group = Group::getByName($groupName))) {
          try {
            $ui->getUserObject()->enterGroup($group);
          } catch(Exception $e) {
            echo "Error adding $uName to $groupName.<br />";
          }
        }
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
    foreach((array) $user->getElementsByTagName('Attributes')->item(0)->childNodes as $ua) {
      $uAttributes[$ua->tagName] = $ua->textContent;
    }
    $uGroup = array();
    foreach((array) $user->getElementsByTagName('Groups')->item(0)->childNodes as $group) {
      $uGroup[$group->getAttribute('id')] = $group->textContent;
    }

    createUser( $user->getElementsByTagName('name')->item(0)->textContent,
      $user->getElementsByTagName('email')->item(0)->textContent,
      $user->getElementsByTagName('raw_pass')->item(0)->textContent,
      $uAttributes,
      $uGroup );
  }
}
else if(isset($_REQUEST['json'])) {
  $json = json_decode(file_get_contents($_REQUEST['json']));
  foreach($json->{'Users'} as $user) {
    createUser($user->{'name'}, $user->email, $user->raw_pass, $user->{'attributes'}, $user->groups);
  }
}

?>
