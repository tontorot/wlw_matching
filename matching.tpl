<?php
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

$time = time();
$_output .= <<<HTML
	<form action="./wlw.php" method="get">
	<input type="submit" value="TOP" style="width:60px; height:40px">
	</form>
	<form action="./matching.php" method="get">
	<input type="hidden" name="matching_code" value={$time}>
	<input type="submit" value="最新のマッチングを確認する" style="width:200px; height:40px">
	</form>
	＝＝＝＝＝＝＝マッチング＝＝＝＝＝＝＝<br>
	{$matching_result}
HTML;
?>