<?php
require_once('db.inc.php');

/** Get the masks available for this character.
Return value is a list of mask data */
function getMasks($characterID, $corporationID, $isAdmin, $activeMask) {
	global $mysql;

	$masks = array();

	// Public mask
	$masks[] = array('mask' => '0.0', 'label' => 'Public', 'owner' => false, 'ownerType' => 'global', 'admin' => false, 'type' => 'default', 'joined' => 1, 'joinedBy' => 'global', 'img' => 'images/9_64_2.png');
	// Character mask
	$masks[] = array('mask' => $_SESSION['characterID'].'.1', 'label' => 'Private', 'owner' => false, 'ownerType' => 'personal', 'admin' => true, 'type' => 'default', 'joined' => 1, 'joinedBy' => 'personal', 'img' => '//image.eveonline.com/Character/'.$_SESSION['characterID'].'_64.jpg');
	// Corporation mask
	$masks[] = array('mask' => $_SESSION['corporationID'].'.2', 'label' => 'Corp', 'owner' => false, 'ownerType' => 'corporate', 'admin' => checkAdmin($_SESSION['corporationID'].'.2'), 'type' => 'default', 'joined' => 1, 'joinedBy' => 'corporate', 'img' => '//image.eveonline.com/Corporation/'.$_SESSION['corporationID'].'_64.png');
	// Alliance mask
	if($_SESSION['allianceID'] ?? false) {
		$masks[] = array('mask' => $_SESSION['allianceID'].'.3', 'label' => 'Alliance', 'owner' => false, 'ownerType' => 'alliance', 'admin' => false, 'type' => 'default', 'joined' => 1, 'joinedBy' => 'alliance', 'img' => '//image.eveonline.com/Alliance/'.$_SESSION['allianceID'].'_64.png');
	}
	
	// Custom masks
	$query = 'SELECT DISTINCT masks.maskID, max(name) as name, max(ownerID) as ownerID, max(ownerType) as ownerType, max(eveID) as eveID, max(eveType) as eveType, max(joined) as joined FROM masks LEFT JOIN `groups` ON `groups`.maskID = masks.maskID WHERE (ownerID = :characterID AND ownerType = 1373) OR (ownerID = :corporationID AND ownerType = 2) OR (eveID = :characterID AND eveType = 1373) OR (eveID = :corporationID AND eveType = 2) GROUP BY masks.maskID ORDER BY joined desc';
	$stmt = $mysql->prepare($query);
	$stmt->bindValue(':characterID', $_SESSION['characterID']);
	$stmt->bindValue(':corporationID', $_SESSION['corporationID']);
	$stmt->execute();

	while ($row = $stmt->fetchObject()) {
		$owned = $_SESSION['admin'] && $row->ownerID == $_SESSION['corporationID'] || $row->ownerID == $_SESSION['characterID'] ? true : false;
		$type = $owned ? 'owned' : 'invited';
		$ownerType = $row->ownerType == 1373 ? 'personal' : 'corporate';
		$joinedBy = $owned ? $ownerType : ($row->eveType == 1373 ? 'personal' : 'corporate');
		$masks[] = array(
			'mask' => $row->maskID,
			'label' => $row->name,
			'joined' => $row->joined,
			'optional' => ($_SESSION['admin'] && $row->eveID == $_SESSION['corporationID']) || $row->eveID == $_SESSION['characterID'] ? true : false,
			'owner' => $owned,
			'ownerType' => $ownerType,
			'admin' => checkOwner($row->maskID) || checkAdmin($row->maskID) ? true : false,
			'type' => $type,
			'joinedBy' => $joinedBy,
			'img' => $row->ownerType == 2?'https://image.eveonline.com/Corporation/'.$row->ownerID.'_64.png':'https://image.eveonline.com/Character/'.$row->ownerID.'_64.jpg',
		);
	}

	foreach ($masks AS &$mask) {
		$mask['active'] = $activeMask == $mask['mask'] ;
	}

	return $masks;
}
