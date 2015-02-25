<?php

exit();

function logd($str) {
	echo $str."<br />";
}
	error_reporting(E_ALL);
	$RUN = false;

	$db = mysql_connect("localhost", "dkbs", "qkdthd");
	mysql_select_db("dkbs", $db);
	$fileRoot = $_SERVER["DOCUMENT_ROOT"]."/v1/data/file";

	//게시판 목록 조회
	$que = "SELECT * FROM g5_board ORDER BY bo_table";
	$res = mysql_query($que);

	logd("START ::: 한글체크");

	while($data = mysql_fetch_array($res)) {
		logd("Processing {$data['bo_table']}");

		//게시물 wr_num, wr_parent 조정
		$que2 = "SELECT * FROM g5_write_{$data['bo_table']} WHERE wr_2 <> ''";
		$res2 = mysql_query($que2);

		//mkdir
		$dataDir = "{$fileRoot}/{$data['bo_table']}";
		logd("make directory...".$dataDir);
		exec("mkdir {$dataDir}");
		exec("chmod 755 {$dataDir}");
		exec("echo '' > {$dataDir}/index.php");

		while($data2 = mysql_fetch_array($res2)) {
			$num = $data2['wr_id'] * -1;

			//파일 다운로드
			$filename = sprintf("%s%04d.jpg", $data['bo_table'], $data2['wr_id']);
			$fullpath = "{$fileRoot}/{$data['bo_table']}/$filename";
			$exec = "wget -x -O {$fullpath} http://hy054nz.cafe24.com/photo/{$data2['wr_2']}";

			logd("File downloading {$filename}");
			flush();

			//if($RUN) exec($exec);
			//Check file
			if(file_exists($fullpath)) {
				logd("File exists :: {$filename}");
			}else {
				logd("FILE NOT EXISTS ::: {$filename}");
			}


			//파일첨부 처리
			$imgData = getimagesize($fullpath);
			$imgSize = filesize($fullpath);
			$que3 = "INSERT INTO g5_board_file(bo_table, wr_id, bf_file, bf_filesize, bf_width, bf_height, bf_datetime, bf_type) VALUES('{$data['bo_table']}', {$data2['wr_id']}, '{$filename}', {$imgSize}, {$imgData[0]}, {$imgData[1]}, now(), 2)";
			if($RUN) mysql_query($que3);


			//코멘트 처리
			$cmt = $data2["wr_content"];//iconv("EUC-KR", "UTF-8", $data2["wr_content"]);
			$cmtArr = explode("\n", $cmt);
			$cmtCnt = 0;
			foreach($cmtArr as $row) {
				$citem = explode(":::", $row);
				if(sizeof($citem) == 4) {
					$cmtCnt++;
					$wtime = date("Y-m-d H:i:s", $citem[3]);
					$que4 = "INSERT INTO g5_write_{$data['bo_table']}(wr_num, wr_parent, wr_is_comment, wr_comment, ca_name, wr_content, wr_name, wr_email, wr_datetime, wr_ip) VALUES({$data2['wr_id']}, {$data2['wr_id']}, 1, {$cmtCnt}, '{$data2['ca_name']}', '{$citem[2]}', '{$citem[0]}', '{$citem[1]}', '{$wtime}', '127.0.0.1')";
					if($RUN) mysql_query($que4);
					echo $que4;
				}

			}

			//메인 글 데이터 처리
			$que3 = "UPDATE g5_write_{$data['bo_table']} SET wr_num={$num}, wr_parent={$data2['wr_id']}, wr_file=1, wr_comment={$cmtCnt}, wr_content='' WHERE wr_id={$data2['wr_id']}";
			if($RUN) mysql_query($que3);
		}
	}

	//글 카운트 재조정, 댓글에 회원정보 연동
	$que = "SELECT * FROM g5_board";
	$res = mysql_query($que);
	while($data = mysql_fetch_array($res)) {
		$que2 = "UPDATE g5_board SET bo_count_write=(SELECT count(*) FROM g5_write_{$data['bo_table']}) WHERE bo_table='{$data['bo_table']}'";
		if($RUN) mysql_query($que2);

		$que2 = "SELECT * FROM g5_write_{$data['bo_table']} WHERE wr_is_comment=1 AND mb_id=''";
		$res2 = mysql_query($que2);
		while($data2 = mysql_fetch_array($res2)) {
			$que3 = "SELECT * FROM g5_member WHERE mb_email='{$data2['wr_email']}'";
			$res3 = mysql_query($que3);
			if($data3 = mysql_fetch_array($res3)) {
				$que4 = "UPDATE g5_write_{$data['bo_table']} SET mb_id='{$data3['mb_id']}', wr_password='{$data3['mb_password']}' WHERE wr_id={$data2['wr_id']}";
				if($RUN) mysql_query($que4);
			}
		}
	}

?>
