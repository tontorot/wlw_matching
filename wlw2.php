<?php
require("common/db.php");

$_temp['action'] = '';
if(isset($_REQUEST['action']))
{
  switch($_REQUEST['action'])
  {
    case '新規登録':
      $_temp['action'] = '新規登録';
      registrate_player($_REQUEST);
      break;
    case 'ロール変更':
      $_temp['action'] = 'ロール変更';
      roll_change($_REQUEST);
      break;
    case 'マッチングから除外':
      $_temp['action'] = 'マッチングから除外';
      matching_change($_REQUEST,0);
      break;
    case 'マッチングに参加':
      $_temp['action'] = 'マッチングに参加';
      matching_change($_REQUEST,1);
      break;
    case '登録削除':
      $_temp['action'] = '登録削除';
      delete_player($_REQUEST);
      break;
    case 'マッチング':
      $_temp['action'] = 'マッチング';
      $user_list = get_matching_user_list();
      $matching_data = matching($user_list,1);
			$matching_data['matching_time'] = date('Y-m-d H:i:s',time());
    	$_temp['matching_data'] = $matching_data;
      break;
    default:
      break;
  }
}

$user_list = get_user_list_order_by_is_matching();
$_temp['user_list'] = $user_list;


$_output = '';
require('header.tpl');
require('wlw2.tpl');
require('footer.tpl');
echo $_output;


function get_user_list_order_by_is_matching()
{
  $user_list = get_user_list();

  $match_list = array();
  $non_match_list = array();
  foreach ($user_list as $user_info)
  {
    if($user_info['is_matching'])
    {
      $match_list[] = $user_info;
    }
    else
    {
      $non_match_list[] = $user_info;
    }
  }
  return array_merge($match_list, $non_match_list);
}

function roll_change($request)
{
  $user_id = $request['user_id'];
  $is_f = isset($request['F']) ? $request['F'] : 0;
  $is_a = isset($request['A']) ? $request['A'] : 0;
  $is_s = isset($request['S']) ? $request['S'] : 0;
  if(is_null($user_id)
   || is_null($is_f)
   || is_null($is_a)
   || is_null($is_s))
  {
      return;
  }
  $set_params = array('is_f'=>$is_f,'is_a'=>$is_a,'is_s'=>$is_s);
  $where_params = array('user_id'=>$user_id);
  update_user_info($set_params, $where_params);
}

function matching_change($request,$is_matching)
{
  if(is_null($request['user_id']))
  {
    return;
  }
  $set_params = array('is_matching'=>$is_matching);
  $where_params = array('user_id'=>$request['user_id']);
  update_user_info($set_params, $where_params);
}

function delete_player($request)
{
  if(is_null($request['user_id']))
  {
    return;
  }
  $where_params = array('user_id'=>$request['user_id']);
  delete_user_info($where_params);
}

function registrate_player($request)
{
  if(empty($request['name']))
  {
    return;
  }
  $set_params = array('user_name'=>"'".$request['name']."'");
  if(isset($request['F']))
  {
    $set_params['is_f'] = $request['F'];
  }
  if(isset($request['A']))
  {
    $set_params['is_a'] = $request['A'];
  }
  if(isset($request['S']))
  {
    $set_params['is_s'] = $request['S'];
  }
  insert_user_info($set_params);
}

function get_matching_user_list()
{
  $all_user_list = get_user_list();
  $user_list = array();
  foreach ($all_user_list as $user_info)
  {
    if($user_info['is_matching'])
    {
      $temp_user_info = array();
      $temp_user_info['F'] = $user_info['is_f'];
      $temp_user_info['A'] = $user_info['is_a'];
      $temp_user_info['S'] = $user_info['is_s'];
      $user_list[$user_info['user_name']] = $temp_user_info;
    }
  }
  return $user_list;
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
	$roll_balance_array = check_roll_balance($team_a, $team_b, $max_team_member_count);
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

function check_roll_balance($team_a, $team_b, $max_team_member_count)
{
	$roll_balance_a = array('F'=>0, 'A'=>0, 'S'=>0);
	$roll_balance_b = array('F'=>0, 'A'=>0, 'S'=>0);

	//マッチングの最大人数になるまで、ロールを配分
	for($f=0;$f<=$max_team_member_count;$f++)
	{
		for($a=0;$a<=$max_team_member_count;$a++)
		{
			for($s=0;$s<=$max_team_member_count;$s++)
			{
				if($f+$a+$s == $max_team_member_count)
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
		$roll_assignment_a = get_roll_assignment($team_a,$roll_balance,$max_team_member_count);
		$roll_assignment_b = get_roll_assignment($team_b,$roll_balance,$max_team_member_count);
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
			if($max_team_member_count == 1)
				break;
			foreach ($team[2] as $roll2 => $is_playable2)
			{
				if($max_team_member_count == 2)
					break;
				foreach ($team[3] as $roll3 => $is_playable3)
				{
					if($max_team_member_count == 3)
						break;
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
