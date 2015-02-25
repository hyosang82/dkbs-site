<?php
function rtlog($str) {
	echo $str."<br />";
	flush();
}

include_once('./_common.php');
include_once(G5_PATH."/head.sub.php");

if($member['mb_level'] < $board['bo_write_level']) {
	alert('업로드 권한이 없습니다.', G5_URL);
	return;
}

$bRun = true;

echo "<!--";
var_dump($_FILES);
echo "-->";

if(is_uploaded_file($_FILES["wr_zipfile"]["tmp_name"])) {
	//파일 업로드 처리
	$bOk = false;
	$successCount = 0;

	$bo_table = $_POST["bo_table"];

	//카테고리 처리
	$category = $_POST["wr_selcategory"];
	if($category == "__NEW__") {
		$category = $_POST["wr_newcategory"];

		//카테고리 추가
		$que = "SELECT bo_category_list FROM g5_board WHERE bo_table='${bo_table}'";
		$data = mysql_fetch_row(mysql_query($que));

		$excate = $data[0];
		if(strlen($excate) == 0) {
			$excate = $category;
		}else {
			$excate = $excate."|".$category;
		}

		$que = "UPDATE g5_board SET bo_category_list='${excate}' WHERE bo_table='${bo_table}'";
		if($bRun) mysql_query($que);

		rtlog("카테고리 추가 : ".$category);
	}

	$write_table = "g5_write_".$bo_table;


	//마지막 정렬 값 구함
	$sql = "SELECT CONV(IFNULL(MAX(wr_1), 0), 10, 10)+1 FROM ${write_table}";
	$data = mysql_fetch_row(mysql_query($sql));
	$ord_num = $data[0];

	/////////////////////////
	////// 글쓰기 준비 //////
	/////////////////////////
	// 디렉토리가 없다면 생성합니다. (퍼미션도 변경하구요.)
	@mkdir(G5_DATA_PATH.'/file/'.$bo_table, G5_DIR_PERMISSION);
	@chmod(G5_DATA_PATH.'/file/'.$bo_table, G5_DIR_PERMISSION);
	$upload = array();



	rtlog("업로드 파일 : ".$_FILES["wr_zipfile"]["name"]);
	rtlog("파일 형식 : ".$_FILES["wr_zipfile"]["type"]);

	$zipfile = zip_open($_FILES["wr_zipfile"]["tmp_name"]);
	if(is_resource($zipfile)) {
		$tmpdir = tempnam(sys_get_temp_dir(), 'UPLOAD');

		$i = 0;
		while( ($entry = zip_read($zipfile)) !== FALSE) {
			rtlog("파일명 : ".zip_entry_name($entry).", 크기 : ".zip_entry_filesize($entry));

			if(zip_entry_filesize($entry) > 0) {
				if(zip_entry_open($zipfile, $entry)) {
					$tempfile = $tmpdir.zip_entry_name($entry);
					
					$fp = fopen($tempfile, "wb");

					if(is_resource($fp)) {
						while( ($buf = zip_entry_read($entry)) ) {
							fwrite($fp, $buf);
						}
						fclose($fp);

						$info = getimagesize($tempfile, $exif);

						if(isset($info)) {
							rtlog("width : ".$info[0].", height : ".$info[1].", mimetype : ".$info["mime"].", 정렬번호 : ".$ord_num);

							//파일명 생성
							$dstfilename = zip_entry_name($entry);
							$dstfilename = str_replace('/', '_', $dstfilename);

							$dstfullpath = G5_DATA_PATH."/file/".$bo_table."/".$dstfilename;
							if(file_exists($dstfullpath)) {
								$dstfilename = time().$dstfilename;
								$dstfullpath = G5_DATA_PATH."/file/".$bo_table."/".$dstfilename;
							}

							//해당 위치로 파일 복사
							if($bRun) {
								copy($tempfile, $dstfullpath);
								chmod($dstfullpath, G5_FILE_PERMISSION);
							}

							//DB인서트
							$mb_id = $member['mb_id'];
							$wr_name = $member['mb_name'];
							$wr_password = $member['mb_password'];
							$wr_email = $member['mb_email'];
							$wr_homepage = $member['mb_homepage'];
							$wr_num = get_next_num($write_table);
							$wr_id = 0;

							$sql = "INSERT INTO ${write_table} SET wr_num='${wr_num}', wr_comment=0, ca_name='${category}', wr_option='0,0,0', wr_subject='', wr_content='', wr_hit=0, wr_good=0, wr_nogood=0,
							wr_1=RIGHT(CONCAT('0000', {$ord_num}), 4),
									mb_id='{$member['mb_id']}', wr_password='${wr_password}', wr_name='${wr_name}', wr_email='${wr_email}', wr_datetime='".G5_TIME_YMDHIS."', wr_last='".G5_TIME_YMDHIS."',
									wr_ip='{$_SERVER['REMOTE_ADDR']}'";
							if($bRun) mysql_query($sql);
							else rtlog($sql);

							if($bRun) {
								$wr_id = mysql_insert_id();


								// 부모 아이디에 UPDATE
								sql_query(" update $write_table set wr_parent = '$wr_id' where wr_id = '$wr_id' ");

								// 새글 INSERT
								sql_query(" insert into {$g5['board_new_table']} ( bo_table, wr_id, wr_parent, bn_datetime, mb_id ) values ( '{$bo_table}', '{$wr_id}', '{$wr_id}', '".G5_TIME_YMDHIS."', '{$member['mb_id']}' ) ");

								// 게시글 1 증가
								sql_query("update {$g5['board_table']} set bo_count_write = bo_count_write + 1 where bo_table = '{$bo_table}'");

								//포인트 추가
								insert_point($member['mb_id'], $board['bo_write_point'], "{$board['bo_subject']} {$wr_id} 사진업로드", $bo_table, $wr_id, '쓰기');
							}else {
							}


							//파일정보 DB에 저장
							$sql = " insert into {$g5['board_file_table']}
										set bo_table = '{$bo_table}',
											 wr_id = '{$wr_id}',
											 bf_no = '0',
											 bf_source = '',
											 bf_file = '{$dstfilename}',
											 bf_content = '',
											 bf_download = 0,
											 bf_filesize = '".zip_entry_filesize($entry)."',
											 bf_width = '{$info[0]}',
											 bf_height = '{$info[1]}',
											 bf_type = '{$info[2]}',
											 bf_datetime = '".G5_TIME_YMDHIS."' ";
							if($bRun) {
								sql_query($sql);
							}else {
								rtlog($sql);
							}


							// 파일의 개수를 게시물에 업데이트 한다.
							$row = sql_fetch(" select count(*) as cnt from {$g5['board_file_table']} where bo_table = '{$bo_table}' and wr_id = '{$wr_id}' ");
							sql_query(" update {$write_table} set wr_file = '{$row['cnt']}' where wr_id = '{$wr_id}' ");

							$bOk = true;
							$successCount++;
							$ord_num++;
						}else {
							rtlog("maybe not image file... pass...");
						}

						unlink($tempfile);
					}else {
						rtlog("Failed to create temp file : ".$tempfile);
					}

					zip_entry_close($entry);
				}else {
					rtlog("zip_entry_open() fail");
				}
			}else {
				rtlog("Size is zero. passing...");
			}
		}

		zip_close($zipfile);
	}else {
		rtlog("zip_open() returns error : ".$zipfile);
	}

	rtlog("업로드 완료 : ".$successCount."건");

}else {
	//폼 표시

	if (!$board['bo_table']) {
	   alert('존재하지 않는 게시판입니다.', G5_URL);
	   return;
	}

	$categorylist = explode("|", $board["bo_category_list"]);
?>

<script type="text/javascript">
function submitCheck(frm) {
	return true;
}

function categoryView() {
	var f = document.forms["zipupload"];

	if(f.wr_selcategory.value == "__NEW__") {
		f.wr_newcategory.disabled = false;
	}else {
		f.wr_newcategory.disabled = true;
	}
}

</script>

<div class="btn_confirm">
업로드된 파일이 없습니다
</div>

<form name="zipupload" method="post" enctype="multipart/form-data" onsubmit="submitCheck(this)" action="zipupload.php">
<input type="hidden" name="bo_table" value="<?php echo $board["bo_table"]?>" />
<input type="hidden" name="wr_category" />
 <div class="tbl_frm01 tbl_wrap">
    <table>
        <tbody>
			<tr>
				<th scope="row"><label>게시판</label></th>
				<td><?php echo $board["bo_subject"]?></td>
			</tr>
			<tr>
				<th scope="row"><label for="wr_selcategory">카테고리</label></th>
				<td>
					<select name="wr_selcategory" class="frm_input" onchange="categoryView();">
<?php
	foreach($categorylist as $cate) {
?>
						<option value="<?php echo $cate?>"><?php echo $cate?></option>
<?php
	}
?>
						<option value="__NEW__">카테고리추가</option>
					</select>


					<input type="text" name="wr_newcategory" class="frm_input" size="40" maxlength="50">
				</td>
			</tr>
			<tr>
				<th scope="row"><label>압축파일</label></th>
				<td>
					<input type="file" name="wr_zipfile" class="frm_input" />
					JPG파일을 ZIP으로 묶어 업로드 해주세요
				</td>
			</tr>
		</tbody>
	</table>
</div>
<div class="btn_confirm">
	<input type="submit" value="업로드" id="btn_submit" class="btn_submit">
	<a href="javascript:window.close();" class="btn_cancel">취소</a>
</div>
</form>




<?php
}
?>

<div class="btn_confirm">
	<a href="javascript:window.close();" class="btn_cancel">닫기</a>
</div>

<?php
include_once(G5_PATH.'/tail.sub.php');
?>
