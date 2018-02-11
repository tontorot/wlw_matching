<?php
$_output .= get_reaction_html($_temp['action']);
$_output .= get_matching_html();
$_output .= get_matching_result_html($_temp['matching_data']);
$_output .= get_user_list_html($_temp['user_list']);
$_output .= get_new_user_html();
$_output .= get_top_link_html();

function get_reaction_html($action)
{
	switch ($action)
	{
		case '新規登録':
			$action = <<<HTML
				「新規登録」を実行しました。<br>
HTML;
			break;
		case 'ロール変更':
			$action = <<<HTML
				「ロール変更」を実行しました。<br>
HTML;
			break;
		case 'マッチングから除外':
		$action = <<<HTML
			「マッチングから除外」を実行しました。<br>
HTML;
			break;
		case 'マッチングに参加':
		$action = <<<HTML
			「マッチングに参加」を実行しました。<br>
HTML;
			break;
		case '登録削除':
			$action = <<<HTML
				「登録削除」を実行しました。<br>
HTML;
			break;
		case 'マッチング':
			$action = <<<HTML
				「マッチング」を実行しました。<br>
HTML;
			break;
		default:
			$action = <<<HTML
HTML;
			break;
	}
	return $action;
}
function get_user_list_html($user_list)
{
	$roll_select = <<<HTML
		＝＝＝＝＝＝登録済みユーザ＝＝＝＝＝＝
		<table border="0">
HTML;
	foreach($user_list as $user_info)
	{
		$is_f = "";
		$is_a = "";
		$is_s = "";
		if($user_info['is_f'])
		{
			$is_f = "checked";
		}
		if($user_info['is_a'])
		{
			$is_a = "checked";
		}
		if($user_info['is_s'])
		{
			$is_s = "checked";
		}
		if($user_info['is_matching'])
		{
			$bg_color = '';
			$submit_text = "マッチングから除外";
		}
		else
		{
			$bg_color = "#cccccc";
			$submit_text = "マッチングに参加";
		}
		$span_end = "</span>";
		$roll_select .= <<<HTML
			<form action="./wlw2.php" method="get">
			<tr bgcolor="{$bg_color}">
				<td>
					{$user_info['user_name']}
				</td>
				<td>
					<input type="checkbox" name="F" value="1" {$is_f}>F
					<input type="checkbox" name="A" value="1" {$is_a}>A
					<input type="checkbox" name="S" value="1" {$is_s}>S
					<input type="hidden" name="user_id" value={$user_info['user_id']}>
					<input type="submit" name="action" value="ロール変更">
					<input type="submit" name="action" value="登録削除">
					<input type="submit" name="action" value="{$submit_text}">
				</td>
			</tr>
			</form>
HTML;
	}
	$roll_select .= <<<HTML
		</table>
HTML;
	return $roll_select;
}

function get_new_user_html()
{
	$html = <<<HTML
		＝＝＝＝＝＝＝新規登録＝＝＝＝＝＝＝
		<form action="./wlw2.php" method="get">
		名前<input type="text" name="name" size="10" maxlength="20">
		<input type="checkbox" name="F" value="1">F
		<input type="checkbox" name="A" value="1">A
		<input type="checkbox" name="S" value="1">S
		<input type="submit" name="action" value="新規登録">
		</form>
HTML;
	return $html;
}

function get_matching_html()
{
	$html = <<<HTML
		＝＝＝＝＝＝＝マッチング＝＝＝＝＝＝＝
		<form action="./wlw2.php" method="get">
		<input type="submit" name="action" value="マッチング" style="width:100px; height:40px">
		</form>
HTML;
	return $html;
}

function get_top_link_html()
{
	$html = <<<HTML
	<form action="./wlw2.php" method="get">
	<input type="submit" value="TOP">
	</form>
HTML;
	return $html;
}

function get_matching_result_html($matching_data)
{
	if(empty($matching_data))
	{
		return '';
	}
	if(array_key_exists('roll_balance', $matching_data))
	{
		$roll_balance = <<<HTML
		<span style="background-color:#adff2f;">ロールバランス</span> <br>
		[F:{$matching_data['roll_balance']['F']}]
		[A:{$matching_data['roll_balance']['A']}]
		[S:{$matching_data['roll_balance']['S']}]<br>
HTML;
	}
	else
	{
		$roll_balance = <<<HTML
		ロールバランスが取れませんでした。<br>
		各プレイヤーのロールバランスを見直してください。<br>
HTML;
	}
	$roll_balance .= <<<HTML
		<br>
		<span style="background-color:#ee82ee;">マッチング生成時間</span><br>
		{$matching_data['matching_time']}<br>

HTML;
	$teamA_member = '';
	foreach ($matching_data['teamA'] as $key => $member) {
		if(array_key_exists($key,$matching_data['roll_assignment_a']))
		{
			$roll = $matching_data['roll_assignment_a'][$key]; //F or A or S
		}
		else
		{
			$roll = '';
		}
		$teamA_member .= <<<HTML
		{$roll} {$member['name']}<br>
HTML;
	}
	$teamB_member = '';
	foreach ($matching_data['teamB'] as $key => $member) {
		$roll = $matching_data['roll_assignment_b'][$key]; //F or A or S
		$teamB_member .= <<<HTML
		{$roll} {$member['name']}<br>
HTML;
	}
	$matching_result = <<<HTML
		{$roll_balance}
		<br>
		<span style="background-color:#ffdab9;">teamA</span><br>
		{$teamA_member}
		<br>
		<span style="background-color:#afeeee;">teamB</span><br>
		{$teamB_member}
		<br>
HTML;
	return $matching_result;
}
?>
