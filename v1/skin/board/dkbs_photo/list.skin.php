<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가
include_once(G5_LIB_PATH.'/thumbnail.lib.php');
include_once($board_skin_path.'/fn_comment.php');

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.$board_skin_url.'/style.css">', 0);
?>

<h2 id="container_title"><?php echo $board['bo_subject'] ?><span class="sound_only"> 목록</span></h2>

<!-- 게시판 목록 시작 { -->
<div id="bo_gall" style="width:<?php echo $width; ?>">
	<!-- 게시판 카테고리 시작 { -->
	<?php if ($is_category) { ?>
	<nav id="bo_cate">
		<h2><?php echo $board['bo_subject'] ?> 카테고리</h2>
		<ul id="bo_cate_ul">
			<?php echo $category_option ?>
		</ul>
	</nav>
	<?php } ?>
	<!-- } 게시판 카테고리 끝 -->

    <!-- 게시판 페이지 정보 및 버튼 시작 { -->
    <div class="bo_fx">
        <div id="bo_list_total">
            <span>Total <?php echo number_format($total_count) ?>건</span>
            <?php echo $page ?> 페이지
        </div>

        <?php if ($rss_href || $write_href) { ?>
        <ul class="btn_bo_user">
            <?php if ($rss_href) { ?><li><a href="<?php echo $rss_href ?>" class="btn_b01">RSS</a></li><?php } ?>
            <?php if ($admin_href) { ?><li><a href="<?php echo $admin_href ?>" class="btn_admin">관리자</a></li><?php } ?>
            <?php if ($write_href) { ?><li><a href="<?php echo $write_href ?>" class="btn_b02">글쓰기</a></li><?php } ?>
        </ul>
        <?php } ?>
    </div>
    <!-- } 게시판 페이지 정보 및 버튼 끝 -->

	<div style="height:25px;"></div>

    <form name="fboardlist"  id="fboardlist" action="./board_list_update.php" onsubmit="return fboardlist_submit(this);" method="post">
    <input type="hidden" name="bo_table" value="<?php echo $bo_table ?>">
    <input type="hidden" name="sfl" value="<?php echo $sfl ?>">
    <input type="hidden" name="stx" value="<?php echo $stx ?>">
    <input type="hidden" name="spt" value="<?php echo $spt ?>">
    <input type="hidden" name="page" value="<?php echo $page ?>">
    <input type="hidden" name="sw" value="">

    <?php if ($is_checkbox) { ?>
    <div id="gall_allchk">
        <label for="chkall" class="sound_only">현재 페이지 게시물 전체</label>
        <input type="checkbox" id="chkall" onclick="if (this.checked) all_checked(true); else all_checked(false);">
    </div>
    <?php } ?>


<?
	$gal_width = $board['bo_image_width'];

	//설정된 갯수 무시하고 한장씩 보여줌
	for($i=0;$i<count($list);$i++) {
		for($j=0;$j<$list[$i]["file"]["count"];$j++) {
			$data = $list[$i];
			$file = $data["file"][$j];
			$filepath = $file["path"]."/".$file["file"];

			$width = $file["image_width"];
			$popup = false;
			if($width > $gal_width) {
				$width = $gal_width;
				$popup = true;
			}
?>
	<div class="alert alert-info" role="alert" style="background-color:#FFFFFF;">
		<div>
<?
			if($is_checkbox) {
?>
            <label for="chk_wr_id_<?php echo $i ?>" class="sound_only"><?php echo $list[$i]['subject'] ?></label>
            <input type="checkbox" name="chk_wr_id[]" value="<?php echo $list[$i]['wr_id'] ?>" id="chk_wr_id_<?php echo $i ?>">
<?
			}
?>

		</div>

		<div style="display:block;text-align:right;">
			<small><i>Uploaded by <?=$data["wr_name"]?> on <?=$data["datetime"]?></i></small>
		</div>
		<div style="text-align:center;">
			<a href="./view_image.php?bo_table=<?=$bo_table?>&amp;fn=<?=$file["file"]?>" class="view_image"><img src="<?=$filepath?>" class="center-block" width="<?=$width?>" /></a>
		</div>
		<p><?=$data["content"]?></p>

		<div style="height:50px;"></div>

		<!-- 코멘트 영역-->
		<div class="row">
<?php
			$comment_list = getCommentList($write_table, $list[$i]["wr_id"]);
?>
			<table width="100%" cellspacing="0" cellpadding="3">
<?php
			foreach($comment_list as $comment) {
?>
				<tr>
					<td><b><?=$comment["wr_name"]?></b> (<?=$comment["wr_datetime"]?> / <?=$comment["wr_ip"]?>)</td>
				</tr>
				<tr>
					<td><?=$comment["wr_content"]?></td>
				</tr>

<?php
			}
?>
				<tr>
					<td align="right"><a class="btn_b01" href="<?=$data["href"]?>">댓글 달기/수정/삭제</a></td>
				</tr>
			</table>

		</div>

		<!-- 공백 -->
		<div style="height:1px;background-color:#cccccc;margin-bottom:50px;"></div>
	</div>


<?
		}
	}
?>

    <?php if ($list_href || $is_checkbox || $write_href) { ?>
    <div class="bo_fx">
        <?php if ($is_checkbox) { ?>
        <ul class="btn_bo_adm">
            <li><input type="submit" name="btn_submit" value="선택삭제" onclick="document.pressed=this.value"></li>
            <li><input type="submit" name="btn_submit" value="선택복사" onclick="document.pressed=this.value"></li>
            <li><input type="submit" name="btn_submit" value="선택이동" onclick="document.pressed=this.value"></li>
        </ul>
        <?php } ?>

        <ul class="btn_bo_user">
		    <?php if ($list_href || $write_href) { ?>
				<?php if ($list_href) { ?><li><a href="<?php echo $list_href ?>" class="btn_b01">목록</a></li><?php } ?>
	            <?php if ($write_href) { ?>
					<li><a href="<?php echo $write_href ?>" class="btn_b02">글쓰기</a></li>
					<li><a href="#" onclick="window.open('./zipupload.php?bo_table=<?php echo $bo_table?>', 'zipupload', 'width=600, height=600');" class="btn_b02">압축 업로드</a></li>
				<?php } ?>
				<?php if ($member["mb_id"] == "hyosang82") {?>
					<li><a href="#" onclick="window.open('./organize.php?bo_table=<?php echo $bo_table?>', 'organize', 'width=600, height=600');" class="btn_b02">순서/데이터정리</a></li>
				<?php } ?>
			<?php } ?>
        </ul>
    </div>
    <?php } ?>
    </form>
</div>

<?php if($is_checkbox) { ?>
<noscript>
<p>자바스크립트를 사용하지 않는 경우<br>별도의 확인 절차 없이 바로 선택삭제 처리하므로 주의하시기 바랍니다.</p>
</noscript>
<?php } ?>

<!-- 페이지 -->
<?php echo $write_pages;  ?>

<!-- 게시물 검색 시작 { -->
<fieldset id="bo_sch">
    <form name="fsearch" method="get" class="form-inline" role="form">
    <input type="hidden" name="bo_table" value="<?php echo $bo_table ?>">
    <input type="hidden" name="sca" value="<?php echo $sca ?>">
    <input type="hidden" name="sop" value="and">
    <label for="sfl" class="sound_only">검색대상</label>
    <select name="sfl" id="sfl" class="form-control">
        <option value="wr_subject"<?php echo get_selected($sfl, 'wr_subject', true); ?>>제목</option>
        <option value="wr_content"<?php echo get_selected($sfl, 'wr_content'); ?>>내용</option>
        <option value="wr_subject||wr_content"<?php echo get_selected($sfl, 'wr_subject||wr_content'); ?>>제목+내용</option>
        <option value="mb_id,1"<?php echo get_selected($sfl, 'mb_id,1'); ?>>회원아이디</option>
        <option value="mb_id,0"<?php echo get_selected($sfl, 'mb_id,0'); ?>>회원아이디(코)</option>
        <option value="wr_name,1"<?php echo get_selected($sfl, 'wr_name,1'); ?>>글쓴이</option>
        <option value="wr_name,0"<?php echo get_selected($sfl, 'wr_name,0'); ?>>글쓴이(코)</option>
    </select>
    <label for="stx" class="sound_only">검색어<strong class="sound_only"> 필수</strong></label>
    <input type="text" name="stx" value="<?php echo stripslashes($stx) ?>" required id="stx" class="form-control required" size="15" maxlength="15">
	<button type="submit" class="btn btn-default">검색</button>
    </form>
</fieldset>
<!-- } 게시물 검색 끝 -->

<script type="text/javascript">
    $("a.view_image").click(function() {
        window.open(this.href, "large_image", "location=yes,links=no,toolbar=no,top=10,left=10,width=10,height=10,resizable=yes,scrollbars=no,status=no");
        return false;
    });
</script>

<?php if ($is_checkbox) { ?>
<script>
function all_checked(sw) {
    var f = document.fboardlist;

    for (var i=0; i<f.length; i++) {
        if (f.elements[i].name == "chk_wr_id[]")
            f.elements[i].checked = sw;
    }
}

function fboardlist_submit(f) {
    var chk_count = 0;

    for (var i=0; i<f.length; i++) {
        if (f.elements[i].name == "chk_wr_id[]" && f.elements[i].checked)
            chk_count++;
    }

    if (!chk_count) {
        alert(document.pressed + "할 게시물을 하나 이상 선택하세요.");
        return false;
    }

    if(document.pressed == "선택복사") {
        select_copy("copy");
        return;
    }

    if(document.pressed == "선택이동") {
        select_copy("move");
        return;
    }

    if(document.pressed == "선택삭제") {
        if (!confirm("선택한 게시물을 정말 삭제하시겠습니까?\n\n한번 삭제한 자료는 복구할 수 없습니다\n\n답변글이 있는 게시글을 선택하신 경우\n답변글도 선택하셔야 게시글이 삭제됩니다."))
            return false;

        f.removeAttribute("target");
        f.action = "./board_list_update.php";
    }

    return true;
}

// 선택한 게시물 복사 및 이동
function select_copy(sw) {
    var f = document.fboardlist;

    if (sw == 'copy')
        str = "복사";
    else
        str = "이동";

    var sub_win = window.open("", "move", "left=50, top=50, width=500, height=550, scrollbars=1");

    f.sw.value = sw;
    f.target = "move";
    f.action = "./move.php";
    f.submit();
}
</script>
<?php } ?>
<!-- } 게시판 목록 끝 -->
