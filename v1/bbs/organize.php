<?php
function rtlog($str) {
	echo $str."<br />";
	flush();
}

function getDisplayString($fullpath) {

	$line = "";

	//파일명
	$paths = explode("/", $fullpath);
	$line .= $paths[count($paths)-1];

	//EXIF 시간
	$exif = exif_read_data($fullpath, "EXIF", true);
	$line .= " / ".getExifDatetime($exif);

	//파일 생성 시간
	$time = filemtime($fullpath);
	$line .= " / ".date("Y-m-d H:i:s", $time);

	return $line;
}



function getExifDatetime($exif) {
	if($exif) {
		if($exif["EXIF"]) {
			if($exif["EXIF"]["DateTimeOriginal"]) {
				if(strlen(trim($exif["EXIF"]["DateTimeOriginal"])) >= 19) {
					return $exif["EXIF"]["DateTimeOriginal"];
				}
			}
		}
	}
	return "--------------------";
}

include_once('./_common.php');
include_once(G5_PATH."/head.sub.php");

if($member['mb_level'] < $board['bo_write_level']) {
	alert('권한이 없습니다.', G5_URL);
	return;
}

$bRun = true;

$bo_table = $_GET["bo_table"];
if(!$bo_table) $bo_table = $_POST["bo_table"];
$bo_table = str_replace(" ", "", $bo_table);

if(!$bo_table) {
	rtlog("No table id");
	exit;
}

$tablename = "g5_write_".$bo_table;


echo "<!--";
var_dump($_FILES);
echo "-->";

$task = $_POST["task"];

if($task == "reorder") {
	//순서 재정렬
	$ordering = $_POST["ordering"];

	rtlog("정렬순서 : ".$ordering);

	$keyArr = explode(";", $ordering);

	$ord = 1;
	foreach($keyArr as $id) {
		if(strlen($id) > 0) {
			$ordStr = sprintf("%04d", $ord);
			$sql = "UPDATE {$tablename} SET wr_1='{$ordStr}' WHERE wr_id={$id}";
			mysql_query($sql);

			rtlog("순서 재배정 : ".$id." ==> ".$ordStr." : ".(mysql_affected_rows() > 0?"UPDATED":"NOT CHANGED"));
			$ord++;
		}
	}
}else if($task == "cleanup") {
	//첨부정리
}
//폼 표시

if (!$board['bo_table']) {
   alert('존재하지 않는 게시판입니다.', G5_URL);
   return;
}

$categorylist = explode("|", $board["bo_category_list"]);
$category = $_GET["category"];
if(!$category) $category = $_POST["category"];

//sql injection 방지
if(!strstr($board["bo_category_list"], $category)) {
	$category = "";
}

?>

<script type="text/javascript">
function getIndexString(str, idx) {
	var arr = (""+str).split("/");

	return arr[idx].trim();
}

function sortIndex(idx) {
	var frm = document.forms["reorder"];
	var list = frm.sel_order.options;

	var arr = selectToArray(list);
	var fn = sortFilename;
	if(idx == 1) fn = sortFilename;
	else if(idx == 2) fn = sortExifTime;
	else if(idx == 3) fn = sortFileTime;

	arr.sort(fn);

	arrayToSelect(arr, list);
}

function sortFilename(a, b) { return (a.fn > b.fn) ? 1 : -1; }
function sortExifTime(a, b) { return (a.exif > b.exif) ? 1 : -1; }
function sortFileTime(a, b) { return (a.mtime > b.mtime) ? 1 : -1; }



function selectToArray(opts) {
	var arr = [];

	for(var i=0;i<opts.length;i++) {
		var s = opts[i].innerHTML.split("/");
		var itm = [];

		itm.fn = s[0].trim();
		itm.exif = s[1].trim();
		itm.mtime = s[2].trim();
		itm.id = opts[i].value;

		arr.push(itm);
	}

	return arr;
}

function arrayToSelect(arr, opt) {
	opt.length = 0;

	for(var i=0;i<arr.length;i++) {
		var op = document.createElement("option");
		op.value = arr[i].id;
		op.innerHTML = arr[i].fn + " / " + arr[i].exif + " / " + arr[i].mtime;

		opt[i] = op;
	}
}



function submitCheck(frm) {
	var list = frm.sel_order.options;
	var ordStr = "";

	for(var i=0;i<list.length;i++) {
		ordStr += (list[i].value + ";");
	}

	frm.ordering.value = ordStr;

	return true;
}

function categoryView() {
	var f = document.forms["reorder"];

	if(f.wr_selcategory.value != "") {
		location.href = "?bo_table=<?php echo $bo_table?>&category=" + f.wr_selcategory.value;
	}
}

</script>

<form name="reorder" method="post" onsubmit="return submitCheck(this)" action="organize.php">
<input type="hidden" name="bo_table" value="<?php echo $board["bo_table"]?>" />
<input type="hidden" name="ordering" />
<input type="hidden" name="task" value="reorder" />
<input type="hidden" name="category" value="<?php echo $category?>" />
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
						<option value="">카테고리 선택</option>

<?php
	foreach($categorylist as $cate) {
?>
						<option value="<?php echo $cate?>" <?php echo ($cate==$category?" selected " : "")?>><?php echo $cate?></option>
<?php
	}
?>
					</select>
				</td>
			</tr>
			<tr>
				<th colspan="2"><label>순서 조정</label></th>
			</tr>
			<tr>
				<td colspan="2">
					<select multiple name="sel_order" style="width:100%;height:300px;">
<?php
	$sql = "
SELECT
    A.wr_id,
    B.bf_file

FROM {$tablename} A
    LEFT OUTER JOIN g5_board_file B
    ON B.wr_id = A.wr_id

WHERE
    A.wr_is_comment=0
    AND B.bo_table='{$bo_table}'";
	if($category) {
		$sql .= "
	AND A.ca_name='{$category}'";
	}
	$sql .= "
ORDER BY
	A.ca_name,
    A.wr_1";
	
	$res = mysql_query($sql);

	while($data = mysql_fetch_array($res)) {
		$fullpath = G5_DATA_PATH."/file/".$bo_table."/".$data["bf_file"];
?>
						<option value="<?php echo $data["wr_id"]?>"><?php echo getDisplayString($fullpath)?></option>
<?php
	}
?>
					</select>
				</td>
			</tr>
			<tr>
				<td colspan="2">
					<a href="#" onclick="sortIndex(1);" class="btn_cancel">파일명</a>
					<a href="#" onclick="sortIndex(2);" class="btn_cancel">EXIF시간</a>
					<a href="#" onclick="sortIndex(3);" class="btn_cancel">파일시간</a>
					<a href="#" onclick="currSwap(-1);" class="btn_cancel">위로</a>
					<a href="#" onclick="currSwap(1);" class="btn_cancel">아래로</a>
				</td>
			</tr>
		</tbody>
	</table>
</div>
<div class="btn_confirm">
	<input type="submit" value="설정" id="btn_submit" class="btn_submit">
	<a href="javascript:window.close();" class="btn_cancel">취소</a>
</div>
</form>


<div class="btn_confirm">
	<a href="javascript:window.close();" class="btn_cancel">닫기</a>
</div>

<?php
include_once(G5_PATH.'/tail.sub.php');
?>
