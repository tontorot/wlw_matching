<?php
date_default_timezone_set('UTC');
try
{
	$user = 'root';
	$pass = '';
	// ローカルにmysqlインストールしたデフォルト設定ではこっちのコマンドでPDOを使用する
	// $dbh = new PDO('mysql:host=localhost;unix_socket=/tmp/mysql.sock', $user, $pass);
	// AWSのデフォルトはこっち
	$dbh = new PDO('mysql:host=localhost;', $user, $pass);
}
catch(PDOException $e)
{
    print "エラー!: " . $e->getMessage() . "<br/>";
}

function get_user_list()
{
	global $dbh;
	$sql = "SELECT * FROM wlw.user_info";
	$stmt = $dbh->prepare($sql);
	$stmt->execute(array());
	$result_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
	return $result_list;
}

function update_user_info($set_params,$where_params)
{
		global $dbh;
		if(empty($set_params))
		{
			return;
		}
		$set_text = convert_params_into_text($set_params, FALSE);
		$where_text = convert_params_into_text($where_params, TRUE);
		$sql = "UPDATE wlw.user_info SET $set_text WHERE $where_text";
		$stmt = $dbh->prepare($sql);
		$stmt->execute(array());
}

function delete_user_info($where_params)
{
		global $dbh;
		$where_text = convert_params_into_text($where_params, TRUE);
		$sql = "DELETE FROM wlw.user_info WHERE $where_text";
		$stmt = $dbh->prepare($sql);
		$stmt->execute(array());
}

function insert_user_info($set_params)
{
		global $dbh;
		$insert_column_text = '';
		$insert_value_text = '';
		$is_first_key = TRUE;
		foreach ($set_params as $key => $value)
		{
			if(!$is_first_key)
			{
				$insert_column_text .= ',';
				$insert_value_text .= ',';
			}
			$insert_column_text .= $key;
			$insert_value_text .= $value;
			$is_first_key = FALSE;
		}
		$sql = "INSERT INTO wlw.user_info ($insert_column_text) VALUES ($insert_value_text)";
		$stmt = $dbh->prepare($sql);
		$stmt->execute(array());
}
/**
 * (a=>1,b=>2)といった配列を、'a = 1 AND b = 2 'といったテキストに変換
 */
function convert_params_into_text($params, $is_and)
{
	$return_text = '';
	if($is_and)
	{
		$separater = 'AND ';
	}
	else
	{
		$separater = ', ';
	}
	$is_first_key = TRUE;
	foreach ($params as $key => $value)
	{
		if(!$is_first_key)
		{
			$return_text .= $separater;
		}
		$return_text .= "$key = $value ";
		$is_first_key = FALSE;
	}
	return $return_text;
}




// function get_viewer_id()
// {
// 	if(!empty($_COOKIE['viewer_id']) && is_viewer_id_exists($_COOKIE['viewer_id']))
// 	{
// 		return $_COOKIE['viewer_id'];
// 	}
//
// 	$viewer_id = generate_viewer_id();
// 	setcookie('viewer_id',$viewer_id);
// 	$_COOKIE['viewer_id'] = $viewer_id;
// 	return $viewer_id;
// }
//
// function generate_viewer_id()
// {
// 	global $dbh;
// 	$max_viewer_id = 999999999;
// 	$min_viewer_id = 100000000;
//
// 	do
// 	{
// 		$viewer_id = rand($min_viewer_id, $max_viewer_id);
// 		$sql = "SELECT * FROM wedding.user_info where viewer_id = ?";
// 		$stmt = $dbh->prepare($sql);
// 		$stmt->execute(array($viewer_id));
// 		$row = $stmt->fetch(PDO::FETCH_ASSOC);
// 	}while($row);
//
// 	$sql = "INSERT INTO wedding.user_info (viewer_id, name, clear_time) VALUES (?, 'test', ?)";
// 	$stmt = $dbh->prepare($sql);
// 	$stmt->execute(array($viewer_id,PHP_INT_MAX));
//
// 	return $viewer_id;
// }
//
// function get_by_viewer_id($viewer_id)
// {
// 	global $dbh;
// 	$sql = "SELECT * FROM wedding.user_info where viewer_id = ?";
// 	$stmt = $dbh->prepare($sql);
// 	$stmt->execute(array($viewer_id));
// 	return $stmt->fetch(PDO::FETCH_ASSOC);
// }
//
// function is_viewer_id_exists($viewer_id)
// {
// 	if(get_by_viewer_id($viewer_id))
// 	{
// 		print("return true<br>");
// 		return TRUE;
// 	}
// 		print("return false<br>");
// 	return FALSE;
// }
//
// function update_tutorial_clear($viewer_id)
// {
// 	global $dbh;
// 	$sql = "UPDATE wedding.user_info set is_tutorial_clear = 1 where viewer_id = ?";
// 	$stmt = $dbh->prepare($sql);
// 	$stmt->execute(array($viewer_id));
// 	return $stmt->rowCount();
// }
//
// function insert_finish_time($viewer_id)
// {
// 	global $dbh;
// 	$sql = "INSERT INTO wedding.game_finish_time (viewer_id, finish_time) VALUES (?, ?)";
// 	$stmt = $dbh->prepare($sql);
// 	$stmt->execute(array($viewer_id,date('Y-m-d H:i:s')));
// 	$id = $dbh->lastInsertId();
// 	return get_finish_number($viewer_id, $id);
// }
//
// function get_finish_number($viewer_id, $id=0)
// {
// 	require('config.php');
// 	global $dbh;
// 	if($id == 0)
// 	{
// 		//insertに失敗したということは、すでにレコードが存在しているので、改めてselectしてidを投げる
// 		$result = select_finish_time($viewer_id);
// 		$id = $result['id'];
// 	}
// 	// insertidが取得できているので、式の開始時刻からのレコードの件数と合わせて、何番目か取得できそう
//
// 	$sql = "SELECT count(*) as count FROM wedding.game_finish_time WHERE finish_time > ? AND id <= ?";
// 	$stmt = $dbh->prepare($sql);
// 	$stmt->execute(array($event_start_time,$id));
// 	$result = $stmt->fetch(PDO::FETCH_ASSOC);
// 	return $result['count'];
// }
//
// function select_finish_time($viewer_id)
// {
// 	global $dbh;
// 	$sql = "SELECT id FROM wedding.game_finish_time where viewer_id = ?";
// 	$stmt = $dbh->prepare($sql);
// 	$stmt->execute(array($viewer_id));
// 	$result = $stmt->fetch(PDO::FETCH_ASSOC);
// 	return $result;
// }
?>
