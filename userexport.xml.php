<?php
defined('C5_EXECUTE') || die('Access Denied.');
Loader::model('user_list');
$nh = Loader::helper('navigation');

$dom = new DOMDocument('1.0', 'UTF-8');

$dnode = $dom->createElement('Users');
$docNode = $dom->appendChild($dnode);

foreach ((new UserList())->get(3000) as $user) {
    $ui = UserInfo::getByID($user->getUserID());
    $node = $dom->createElement('User');
    $userNode = $docNode->appendChild($node);

    $node = $dom->createElement('name');
    $cdata = $node->ownerDocument->createCDATASection($user->getUserName());
    $node->appendChild($cdata);
    $userNode->appendChild($node);

    $node = $dom->createElement('email');
    $cdata = $node->ownerDocument->createCDATASection($user->getUserEmail());
    $node->appendChild($cdata);
    $userNode->appendChild($node);

    $node = $dom->createElement('raw_pass');
    $cdata = $node->ownerDocument->createCDATASection($user->getUserPassword());
    $node->appendChild($cdata);
    $userNode->appendChild($node);

    $node = $dom->createElement('Attributes');
    $attrNode = $userNode->appendChild($node);
    foreach (UserAttributeKey::getAttributes($user->getUserID()) as $key => $uak) {
        if (is_string($uak)) {
            $node = $dom->createElement($key);
            $cdata = $node->ownerDocument->createCDATASection($uak);
            $node->appendChild($cdata);
            $attrNode->appendChild($node);
        }
    }
    $node = $dom->createElement('Groups');
    $groupNode = $userNode->appendChild($node);
    foreach ((array) $user->getUserObject()->getUserGroups() as $gID => $group) {
        $node = $dom->createElement("group");
        $cdata = $node->ownerDocument->createCDATASection($group);
        $node->appendChild($cdata);
        $groupAttr = $dom->createAttribute('id');
        $groupAttr->value = $gID;
        $node->appendChild($groupAttr);
        $groupNode->appendChild($node);
    }
}

$xmlOutput = $dom->saveXML();
header('Content-type: application/xml');
echo $xmlOutput;
exit;
