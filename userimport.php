<?php
defined('C5_EXECUTE') || die('Access Denied.');
define('LUM_VALIDATE', isset($_REQUEST['validate']) ? $validate : true);
define('LUM_CHECK_ATTRIBUTES', isset($_REQUEST['checkAttributes']));

Loader::model('user_list');

function createUser($uName, $uEmail, $uPassword = null, $uAttributes = null, $uGroups = null)
{
    if (null !== UserInfo::getByEmail($uEmail) || null !== UserInfo::getByUserName($uName)) {
        return false;
    }
    if (null !== ($uName = $uName ?: $uEmail)) { // Default to email as username if none set
        $l_stats['added']++;
        $ui = UserInfo::add(
            array( 'uName' => $uName,
            'uEmail' => $uEmail,
            'uPassword' => $uPassword,
            'uIsValidated' => LUM_VALIDATE),
            [UserInfo::ADD_OPTIONS_NOHASH]
        );
        foreach ((array) $uAttributes as $attHandle => $attValue) {
            // If set to check, validate that the attribute in the 'from' site is in the 'to' site
            if (!LUM_CHECK_ATTRIBUTES || UserAttributeKey::getByHandle($attHandle)) {
                try {
                    $ui->setAttribute($attHandle, $attValue);
                } catch (Exception $e) {
                    $l->write("Error setting attribute $attHandle for $uName. Check that attribute exists in target and is of the correct type.");
                }
            } else {
                $l_stats['attribute_omitted'][$attHandle] = 1;
            }
        }
        foreach ((array) $uGroups as $gid => $groupName) {
            if (null !== ($group = Group::getByName($groupName))) {
                try {
                    $ui->getUserObject()->enterGroup($group);
                } catch (Exception $e) {
                    $l->write("Error adding $uName to $groupName.");
                }
            }
        }
    }
    return true;
}

$l_stats = array('added' => 0, 'skipped' => 0, 'attribute_omitted' => array());
if (isset($_REQUEST['xml'])) {
    $doc = new DOMDocument();
    $doc->loadXML(file_get_contents($_REQUEST['xml']));
    foreach ($doc->getElementsByTagName('User') as $user) {
        $uAttributes = array();
        foreach ((array) $user->getElementsByTagName('Attributes')->item(0)->childNodes as $ua) {
            $uAttributes[$ua->tagName] = $ua->textContent;
        }
        $uGroup = array();
        foreach ((array) $user->getElementsByTagName('Groups')->item(0)->childNodes as $group) {
            $uGroup[$group->getAttribute('id')] = $group->textContent;
        }

        if (createUser(
            $user->getElementsByTagName('name')->item(0)->textContent,
            $user->getElementsByTagName('email')->item(0)->textContent,
            $user->getElementsByTagName('raw_pass')->item(0)->textContent,
            $uAttributes,
            $uGroup
        ) ) {
            $l_stats['added']++;
        } else {
            $l_stats['skipped']++;
        }
    }
} elseif (isset($_REQUEST['json'])) {
    $json = json_decode(file_get_contents($_REQUEST['json']));
    foreach ($json->{'Users'} as $user) {
        if (createUser($user->{'name'}, $user->email, $user->raw_pass, $user->{'attributes'}, $user->groups)) {
            $l_stats['added']++;
        } else {
            $l_stat['skipped']++;
        }
    }
}
$l = new Log('LazyUserMigrate', true);
if (LUM_CHECK_ATTRIBUTES && sizeof($l_stats['attribute_omitted'])) {
    $l->write("Attributes omitted: " . implode(', ', array_keys($l_stats['attribute_omitted'])));
}
$l->write("Import: new users created: {$l_stats['added']}; users already in database: {$l_stats['skipped']}");
$l->close();
