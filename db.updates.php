<?php

function apply_updates($mysql) {
	if($_SESSION['applied_db_updates']) return;
	
	// Make the updates table itself if needed (can remove in future version)
	$query = 'CREATE TABLE IF NOT EXISTS `update_history` (
			`diff_name` VARCHAR(100) PRIMARY KEY,
			`applied_at` TIMESTAMP NOT NULL
		)';
	$stmt = $mysql->prepare($query);
	$stmt->execute();	
	
	$updates = array(
		'esi_token_length' => 'ALTER TABLE `esi` CHANGE `accessToken` `accessToken` TEXT NOT NULL'
	);
	
	$query = 'SELECT DISTINCT `diff_name` FROM `update_history`';
	$stmt = $mysql->prepare($query);
	$stmt->execute();
	$applied = $stmt->fetchAll(PDO::FETCH_COLUMN, 'diff_name');
	
	foreach($updates as $name => $diff) {
		if(!in_array($name, $applied)) {
			$stmt = $mysql->prepare($diff);
			if(!$stmt->execute()) {
				// Generally, PDO exceptions should be thrown anyway. But even if it isn't configured like that, don't ignore this
				die('Diff invalid ' . $diff . ' - ' . print_r($stmt->errorInfo(), true));
			}
			$stmt = $mysql->prepare('INSERT INTO update_history VALUES (:name, NOW())');
			$stmt->bindValue(':name', $name);
			$stmt->execute();
		}
	}
	
	$_SESSION['applied_db_updates'] = true;
}

apply_updates($mysql);