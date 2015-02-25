<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

function getCommentList($write_table, $wr_id) {
	include_once(G5_CAPTCHA_PATH.'/captcha.lib.php');

	$captcha_html = "";
	if ($is_guest && $board['bo_comment_level'] < 2) {
		$captcha_html = captcha_html('_comment');
	}

	$list = array();

	$is_comment_write = false;
	if ($member['mb_level'] >= $board['bo_comment_level'])
		$is_comment_write = true;

	// 코멘트 출력
	//$sql = " select * from {$write_table} where wr_parent = '{$wr_id}' and wr_is_comment = 1 order by wr_comment desc, wr_comment_reply ";
	$sql = " select * from $write_table where wr_parent = '$wr_id' and wr_is_comment = 1 order by wr_comment, wr_comment_reply ";
	$result = sql_query($sql);
	for ($i=0; $row=sql_fetch_array($result); $i++)
	{
		$comment_list[$i] = $row;

		//$comment_list[$i]['name'] = get_sideview($row['mb_id'], cut_str($row['wr_name'], 20, ''), $row['wr_email'], $row['wr_homepage']);

		$tmp_name = get_text(cut_str($row['wr_name'], $config['cf_cut_name'])); // 설정된 자리수 만큼만 이름 출력
		if ($board['bo_use_sideview'])
			$comment_list[$i]['name'] = get_sideview($row['mb_id'], $tmp_name, $row['wr_email'], $row['wr_homepage']);
		else
			$comment_list[$i]['name'] = '<span class="'.($row['mb_id']?'member':'guest').'">'.$tmp_name.'</span>';



		// 공백없이 연속 입력한 문자 자르기 (way 보드 참고. way.co.kr)
		//$comment_list[$i]['content'] = eregi_replace("[^ \n<>]{130}", "\\0\n", $row['wr_content']);

		$comment_list[$i]['content'] = $comment_list[$i]['content1']= '비밀글 입니다.';
		if (!strstr($row['wr_option'], 'secret') ||
			$is_admin ||
			($write['mb_id']==$member['mb_id'] && $member['mb_id']) ||
			($row['mb_id']==$member['mb_id'] && $member['mb_id'])) {
			$comment_list[$i]['content1'] = $row['wr_content'];
			$comment_list[$i]['content'] = conv_content($row['wr_content'], 0, 'wr_content');
			$comment_list[$i]['content'] = search_font($stx, $comment_list[$i]['content']);
		}

		$comment_list[$i]['datetime'] = substr($row['wr_datetime'],2,14);

		// 관리자가 아니라면 중간 IP 주소를 감춘후 보여줍니다.
		$comment_list[$i]['ip'] = $row['wr_ip'];
		if (!$is_admin)
			$comment_list[$i]['ip'] = preg_replace("/([0-9]+).([0-9]+).([0-9]+).([0-9]+)/", G5_IP_DISPLAY, $row['wr_ip']);

		$comment_list[$i]['is_reply'] = false;
		$comment_list[$i]['is_edit'] = false;
		$comment_list[$i]['is_del']  = false;
		if ($is_comment_write || $is_admin)
		{
			if ($member['mb_id'])
			{
				if ($row['mb_id'] == $member['mb_id'] || $is_admin)
				{
					$comment_list[$i]['del_link']  = './delete_comment.php?bo_table='.$bo_table.'&amp;comment_id='.$row['wr_id'].'&amp;token='.$token.'&amp;page='.$page.$qstr;
					$comment_list[$i]['is_edit']   = true;
					$comment_list[$i]['is_del']    = true;
				}
			}
			else
			{
				if (!$row['mb_id']) {
					$comment_list[$i]['del_link'] = './password.php?w=x&amp;bo_table='.$bo_table.'&amp;comment_id='.$row['wr_id'].'&amp;page='.$page.$qstr;
					$comment_list[$i]['is_del']   = true;
				}
			}

			if (strlen($row['wr_comment_reply']) < 5)
				$comment_list[$i]['is_reply'] = true;
		}

		// 05.05.22
		// 답변있는 코멘트는 수정, 삭제 불가
		if ($i > 0 && !$is_admin)
		{
			if ($row['wr_comment_reply'])
			{
				$tmp_comment_reply = substr($row['wr_comment_reply'], 0, strlen($row['wr_comment_reply']) - 1);
				if ($tmp_comment_reply == $comment_list[$i-1]['wr_comment_reply'])
				{
					$comment_list[$i-1]['is_edit'] = false;
					$comment_list[$i-1]['is_del'] = false;
				}
			}
		}
	}

	return $comment_list;
}



?>