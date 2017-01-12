<?php
$time = time()+60*60*9;
$player_data = array();
$matching_data = array();
$ini_data = file_get_contents('./player.ini');
$_temp = json_decode($ini_data,true);
$player_data = $_temp['player_data'];

//最終更新時刻がpostデータと一致しなかった（自分が更新を掛ける前に他の人に更新された)ので、マージ作業
if(array_key_exists('time', $_REQUEST) &&
   $_REQUEST['time'] != $_temp['time'])
{
	$_temp['is_need_update'] = true;
}
//iniファイルの最終更新時刻が、postデータと一致したら、actionを取る
else
{
	//postデータ処理
	switch($_REQUEST['action'])
	{
		case '新規登録':
			$_temp['action'] = '新規登録';
			$player_data = registrate_player($_REQUEST,$player_data);
			break;
		case 'ロール変更':
			$_temp['action'] = 'ロール変更';
			$player_data = roll_change($_REQUEST,$player_data);
			break;
		case '登録削除':
			$_temp['action'] = '登録削除';
			$player_data = delete_player($_REQUEST,$player_data);
			break;
		case 'マッチング':
			$_temp['action'] = 'マッチング';
			$matching_data = matching($player_data,1);
			$matching_data['matching_time'] = date('Y-m-d H:i:s',$time);
			file_put_contents('./matching.dat', json_encode($matching_data));
			break;
		default:
			$_temp['action'] = '';
			break;
	}	

	$_temp['player_data'] = $player_data;
	$_temp['matching_data'] = $matching_data;
	$_temp['time'] = $time;
	$_temp['is_need_update'] = false;
	$text = json_encode($_temp);
	file_put_contents('./player.ini', $text);
}

require('header.tpl');
require('wlw.tpl');
require('footer.tpl');

echo $_output;

function registrate_player($_REQUEST, $player_data)
{
	//新規登録処理
	if(!array_key_exists('name', $_REQUEST))
	{
		return $player_data;
	}
	$player_name = htmlspecialchars($_REQUEST['name']);
	if($player_name != '')
	{
		//新規登録処理開始
		$new_player_data = array();
		//ファイターです
		if(array_key_exists('F', $_REQUEST) &&
		   $_REQUEST['F'] == 1)
		{
			$new_player_data['F'] = 1;
		}
		else
		{
			$new_player_data['F'] = 0;
		}
		//アタッカーです
		if(array_key_exists('A', $_REQUEST) &&
		   $_REQUEST['A'] == 1)
		{
			$new_player_data['A'] = 1;
		}
		else
		{
			$new_player_data['A'] = 0;
		}
		//サポーターです
		if(array_key_exists('S', $_REQUEST) &&
		   $_REQUEST['S'] == 1)
		{
			$new_player_data['S'] = 1;
		}
		else
		{
			$new_player_data['S'] = 0;
		}
		$player_data[$player_name] = $new_player_data;
	}
	return $player_data;
}

function roll_change($_REQUEST, $player_data)
{
	//ユーザ名がリクエストされていて、それがiniファイルに存在していたら、ロール変更
	if(!array_key_exists('name',$_REQUEST))
	{
		return $player_data;
	}
	$player_name = htmlspecialchars($_REQUEST['name']);
	if(array_key_exists($player_name, $player_data))
	{
		//ファイターにチェックが入っていた
		if(array_key_exists('F', $_REQUEST) &&
		   $_REQUEST['F'] == 1)
		{
			$player_data[$player_name]['F'] = 1;
		}
		else
		{
			$player_data[$player_name]['F'] = 0;
		}
		//アタッカーにチェックが入っていた
		if(array_key_exists('A', $_REQUEST) &&
		   $_REQUEST['A'] == 1)
		{
			$player_data[$player_name]['A'] = 1;
		}
		else
		{
			$player_data[$player_name]['A'] = 0;
		}
		//サポーターにチェックが入っていた
		if(array_key_exists('S', $_REQUEST) &&
		   $_REQUEST['S'] == 1)
		{
			$player_data[$player_name]['S'] = 1;
		}
		else
		{
			$player_data[$player_name]['S'] = 0;
		}
	}
	return $player_data;
}

function delete_player($_REQUEST, $player_data)
{
	//ユーザ名がリクエストされていて、それがiniファイルに存在していたら、登録削除
	if(!array_key_exists('name',$_REQUEST))
	{
		return $player_data;
	}
	$player_name = htmlspecialchars($_REQUEST['name']);
	if(array_key_exists($player_name, $player_data))
	{
		unset($player_data[$player_name]);
	}
	return $player_data;
}

function matching($player_data,$depth)
{
	$player_count = count($player_data);
	$max_team_member_count = ($player_count > 8) ? 4 : floor($player_count / 2);
	if($player_count < 4)
	{
		return array();
	}

	$team_a = array();
	$team_b = array();
	$shuffled_player_data = shuffle_assoc($player_data);
	foreach ($shuffled_player_data as $player_name => $roll)
	{
		$temp_data = $roll;
		$temp_data['name'] = $player_name;
		if(count($team_a) < $player_count/2)
		{
			//このチームにすでに4人いた場合は、[待機組]の文字を名前につける
			if(count($team_a) >= $max_team_member_count)
			{
				$temp_data['name'] = "[待機組] {$temp_data['name']}";
			}
			$team_a[] = $temp_data;
		}
		else
		{
			//このチームにすでに4人いた場合は、[待機組]の文字を名前につける
			if(count($team_b) >= $max_team_member_count)
			{
				$temp_data['name'] = "[待機組] {$temp_data['name']}";
			}
			$team_b[] = $temp_data;
		}
	}

	//無限ループ防止用に、10回ループしたらロールバランス関係なくチーム分けを返す
	if($depth > 100)
	{
		return array('teamA'=>$team_a, 'teamB'=>$team_b);
	}

	//ロールバランスが取れたマッチングができていなかったら、再マッチング
	$roll_balance_array = check_roll_balance($team_a, $team_b);
	if(empty($roll_balance_array))
	{
		return matching($player_data,$depth+1);
	}

	//ロールバランスが取れていたので、ロールバランス内訳とチーム分けを返す
	return array('roll_balance'=>$roll_balance_array['roll_balance'],'teamA'=>$team_a, 'roll_assignment_a'=>$roll_balance_array['roll_assignment_a'], 'teamB'=>$team_b, 'roll_assignment_b'=>$roll_balance_array['roll_assignment_b']);
}

function shuffle_assoc($list) {
    if (!is_array($list)) return $list;
    $keys = array_keys($list);
    shuffle($keys);
    $random = array();
    foreach ($keys as $key) {
        $random[$key] = $list[$key];
    }
    return $random;
}

function check_roll_balance($team_a, $team_b)
{
	$roll_balance_a = array('F'=>0, 'A'=>0, 'S'=>0);
	$roll_balance_b = array('F'=>0, 'A'=>0, 'S'=>0);

	//ロール配分パターン配列
	$min_team_member_count = min(count($team_a),count($team_b));
	for($f=0;$f<=$min_team_member_count;$f++)
	{
		for($a=0;$a<=$min_team_member_count;$a++)
		{
			for($s=0;$s<=$min_team_member_count;$s++)
			{
				if($f+$a+$s == $min_team_member_count)
				{
					$roll_balance_array[] = array('F'=>$f,'A'=>$a,'S'=>$s);
				}
			}
		}
	}
	//シャッフルして上から取り出して、ロールバランスチェック
	$roll_balance_array = shuffle_assoc($roll_balance_array);
	foreach ($roll_balance_array as $roll_balance)
	{
		$roll_assignment_a = get_roll_assignment($team_a,$roll_balance,$min_team_member_count);
		$roll_assignment_b = get_roll_assignment($team_b,$roll_balance,$min_team_member_count);
		if(!empty($roll_assignment_a) &&
		   !empty($roll_assignment_b))
		{
			return array('roll_balance'=>$roll_balance,
						 'roll_assignment_a'=>$roll_assignment_a,
						 'roll_assignment_b'=>$roll_assignment_b);
		}
	}
	return array(); //ロールバランスマッチ失敗なら空配列を返す
}

function get_roll_assignment($team,$roll_balance,$max_team_member_count)
{
	foreach ($team[0] as $roll0 => $is_playable0)
	{
		foreach ($team[1] as $roll1 => $is_playable1)
		{
			foreach ($team[2] as $roll2 => $is_playable2)
			{
				foreach ($team[3] as $roll3 => $is_playable3)
				{
					$temp_team_roll = array('F'=>0,'A'=>0,'S'=>0);
					$temp_team_roll[$roll0] += $is_playable0;
					$temp_team_roll[$roll1] += $is_playable1;
					$temp_team_roll[$roll2] += $is_playable2;
					$temp_team_roll[$roll3] += $is_playable3;
					if($temp_team_roll['F'] == $roll_balance['F'] &&
					   $temp_team_roll['A'] == $roll_balance['A'] &&
					   $temp_team_roll['S'] == $roll_balance['S'])
					{
						$returnArray = array();
						$returnArray[] = $roll0;
						$returnArray[] = $roll1;
						$returnArray[] = $roll2;
						$returnArray[] = $roll3;
						return $returnArray;
					}
				}
				//1チーム3人のときは、$team[3]のforeachに入らないので、ここでチェック
				if($max_team_member_count == 3)
				{
					$temp_team_roll = array('F'=>0,'A'=>0,'S'=>0);
					$temp_team_roll[$roll0] += $is_playable0;
					$temp_team_roll[$roll1] += $is_playable1;
					$temp_team_roll[$roll2] += $is_playable2;
					if($temp_team_roll['F'] == $roll_balance['F'] &&
					   $temp_team_roll['A'] == $roll_balance['A'] &&
					   $temp_team_roll['S'] == $roll_balance['S'])
					{
						$returnArray = array();
						$returnArray[] = $roll0;
						$returnArray[] = $roll1;
						$returnArray[] = $roll2;
						return $returnArray;
					}
				}
			}
			//1チーム2人のときは、$team[2]のforeachに入らないので、ここでチェック
			if($max_team_member_count == 2)
			{
				$temp_team_roll = array('F'=>0,'A'=>0,'S'=>0);
				$temp_team_roll[$roll0] += $is_playable0;
				$temp_team_roll[$roll1] += $is_playable1;
				if($temp_team_roll['F'] == $roll_balance['F'] &&
				   $temp_team_roll['A'] == $roll_balance['A'] &&
				   $temp_team_roll['S'] == $roll_balance['S'])
				{
					$returnArray = array();
					$returnArray[] = $roll0;
					$returnArray[] = $roll1;
					return $returnArray;
				}
			}
		}
		//1チーム1人のときは、$team[1]のforeachに入らないので、ここでチェック
		if($max_team_member_count == 1)
		{
			$temp_team_roll = array('F'=>0,'A'=>0,'S'=>0);
			$temp_team_roll[$roll0] += $is_playable0;
			if($temp_team_roll['F'] == $roll_balance['F'] &&
			   $temp_team_roll['A'] == $roll_balance['A'] &&
			   $temp_team_roll['S'] == $roll_balance['S'])
			{
				$returnArray = array();
				$returnArray[] = $roll0;
				return $returnArray;
			}
		}
	}
	return array();
}

?>