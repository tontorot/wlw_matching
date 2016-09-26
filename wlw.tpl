<?php
//他の人と編集が競合した
if($_temp['is_need_update'])
{
	$is_need_update = <<<HTML
		他の人とアクションが競合しました。<br>
		もう一度設定を確認してから、アクションをしてください。<br>
HTML;
}

switch ($_temp['action'])
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
//マッチングボタンとマッチング結果
$matching = <<<HTML
	<form action="./wlw.php" method="get">
	<input type="hidden" name="time" value={$_temp['time']}>
	<input type="submit" name="action" value="マッチング" style="width:100px; height:40px">
	</form>
	<form action="./matching.php" method="get">
	<input type="hidden" name="matching_code" value={$_temp['time']}>
	<input type="submit" value="マッチング結果を見る" style="width:160px; height:40px">
	</form>
HTML;

//マッチング結果表示
if(!empty($_temp['matching_data']))
{
	if(array_key_exists('roll_balance', $_temp['matching_data']))
	{
		$roll_balance = <<<HTML
		<span style="background-color:#adff2f;">ロールバランス</span> <br>
		[F:{$_temp['matching_data']['roll_balance']['F']}]
		[A:{$_temp['matching_data']['roll_balance']['A']}]
		[S:{$_temp['matching_data']['roll_balance']['S']}]<br>
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
		{$_temp['matching_data']['matching_time']}<br>

HTML;
	$teamA_member = '';
	foreach ($_temp['matching_data']['teamA'] as $key => $member) {
		$roll = $_temp['matching_data']['roll_assignment_a'][$key]; //F or A or S
		$teamA_member .= <<<HTML
		{$roll} {$member['name']}<br>
HTML;
	}
	$teamB_member = '';
	foreach ($_temp['matching_data']['teamB'] as $key => $member) {
		$roll = $_temp['matching_data']['roll_assignment_b'][$key]; //F or A or S
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
HTML;
}

//登録済みユーザのロール選択・削除
$roll_select = <<<HTML
	<table border="0">

HTML;
foreach($_temp['player_data'] as $name => $roll_info)
{
	$roll_select .= <<<HTML
		<form action="./wlw.php" method="get">
		<input type="hidden" name="name" value={$name}>
		<tr>
			<td>
				{$name}
			</td>
			<td>
HTML;
	foreach ($roll_info as $roll => $flag)
	{
		$checked = '';
		if($flag)
		{
			$checked = ' checked';
		}
		$roll_select .= <<<HTML
			<input type="checkbox" name="{$roll}" value="1" {$checked}>{$roll}
HTML;
	}
	$roll_select .= <<<HTML
		<input type="hidden" name="time" value={$_temp['time']}>
		<input type="submit" name="action" value="ロール変更">
		<input type="submit" name="action" value="登録削除">
		</form>
		</td>
		</tr>
HTML;
}
$roll_select .= <<<HTML
	</table>
HTML;
//新規ユーザ登録
$registration = <<<HTML
	<form action="./wlw.php" method="get">
	名前<input type="text" name="name" size="10" maxlength="20">
	<input type="checkbox" name="F" value="1">F
	<input type="checkbox" name="A" value="1">A
	<input type="checkbox" name="S" value="1">S
	<input type="hidden" name="time" value={$_temp['time']}>
	<input type="submit" name="action" value="新規登録">
	</form>
HTML;

$_output .= <<<HTML
	{$action}
	{$is_need_update}
	＝＝＝＝＝＝＝マッチング＝＝＝＝＝＝＝
	{$matching}
	{$matching_result}
	<br>
	＝＝＝＝＝＝登録済みユーザ＝＝＝＝＝＝
	{$roll_select}
	<br>
	＝＝＝＝＝＝＝新規登録＝＝＝＝＝＝＝
	{$registration}
	<br>
	<div>
	※すでに登録済みの名前で新規登録しようとすると、<br>
	  登録済みの情報を上書きします。
	</div>
	<form action="./wlw.php" method="get">
	<input type="submit" value="TOP">
	</form>
HTML;
?>