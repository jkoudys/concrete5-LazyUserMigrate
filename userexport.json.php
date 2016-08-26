<?php
defined('C5_EXECUTE') || die('Access Denied.');
$nh = Loader::helper('navigation');
Loader::model('user_list');

$users = array();
foreach ((new UserList())->get() as $user) {
    $attributes = array();
    foreach (UserAttributeKey::getAttributes($user->getUserID()) as $key => $uak) {
        if (is_string($uak)) {
            $attributes[] = array($key => $uak);
        }
    }
    $groups = array();
    foreach ((array) $user->getUserObject()->getUserGroups() as $gID => $group) {
        $groups[] = array($gID => $group);
    }
    $users['Users'][] = array(
        'name' => $user->getUserName(),
        'email' => $user->getUserEmail(),
        'raw_pass' => $user->getUserPassword(),
        'attributes' => $attributes,
        'groups' => $groups
    );
}
header('Content-type: application/json');
echo json_encode($users);
exit;
