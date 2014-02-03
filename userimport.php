<?php
defined('C5_EXECUTE') or die("Access Denied.");

Loader::model('user_list');
$nh = Loader::helper('navigation');

$doc = new DOMDocument();
$doc->loadXML(file_get_contents($_REQUEST['xml']));
foreach($doc->getElementsByTagName('User') as $user) {
  $soauth = null;
  if(UserInfo::getByEmail($user_email = $user->getElementsByTagName('email')->item(0)->textContent)) {
    echo "User {$user_email} exists.<br />";
  } else {
    echo "{$user_email}<br />";
    $ui = UserInfo::add(
      ['uName' => $user->getElementsByTagName('name')->item(0)->textContent,
      'uEmail' => $user_email,
      'uPassword' => $user->getElementsByTagName('raw_pass')->item(0)->textContent,
      'uIsValidated' => 1], [UserInfo::ADD_OPTIONS_NOHASH]);
    foreach($user->getElementsByTagName('Attributes')->item(0)->childNodes as $ua) {
      $ui->setAttribute($ua->tagName, $ua->textContent);
    }
  }
}

?>
