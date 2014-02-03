<?php
defined('C5_EXECUTE') or die("Access Denied.");
$nh = Loader::helper('navigation');
Loader::model('user_list');


$users = array();
foreach((new UserList())->get(3000) as $user) {
  $attributes = array();
  foreach(UserAttributeKey::getAttributes($user->getUserID()) as $key=>$uak) {
    if(is_string($uak)) {
      $attributes[] = array($key => $uak);
    } else if(is_object($uak)) {
      // Put special cases for custom attribute objects here
      switch(get_class($uak)) {
      default:
        break;
      }
    }
  }
  $users['Users'][] = array(
    'name' => $user->getUserName(),
    'email' => $user->getUserEmail(),
    'raw_pass' => $user->getUserPassword(),
    'attributes' => $attributes);
}
header('Content-type: application/json');
echo json_encode($users);
exit;
?>
