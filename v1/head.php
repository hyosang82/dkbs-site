<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

date_default_timezone_set("Asia/Seoul");

include_once(G5_PATH.'/head.sub.php');
include_once(G5_LIB_PATH.'/latest.lib.php');
include_once(G5_LIB_PATH.'/outlogin.lib.php');
include_once(G5_LIB_PATH.'/poll.lib.php');
include_once(G5_LIB_PATH.'/visit.lib.php');
include_once(G5_LIB_PATH.'/connect.lib.php');
include_once(G5_LIB_PATH.'/popular.lib.php');

// 상단 파일 경로 지정 : 이 코드는 가능한 삭제하지 마십시오.
if ($config['cf_include_head']) {
    if (!@include_once($config['cf_include_head'])) {
        die('기본환경 설정에서 상단 파일 경로가 잘못 설정되어 있습니다.');
    }
    return; // 이 코드의 아래는 실행을 하지 않습니다.
}

if (G5_IS_MOBILE) {
    include_once(G5_MOBILE_PATH.'/head.php');
    return;
}
?>

<!-- 상단 시작 { -->
<script type="text/javascript">
function goPage(url) {
	location.href = url;
}
</script>

<div id="hd">
    <?php
    if(defined('_INDEX_')) { // index에서만 실행
        include G5_BBS_PATH.'/newwin.inc.php'; // 팝업레이어
    }
    ?>

    <div id="hd_wrapper">
		<?php
		//관리권한 있는지 체크
		$adm = sql_fetch("SELECT COUNT(*) AS CNT FROM g5_auth WHERE mb_id='{$member['mb_id']}'");
		$show_adm = false;
		if($adm["CNT"] > 0) {
			$show_adm = true;
		}
		?>

        <div id="logo">
            <a href="<?php echo G5_URL ?>"><img src="<?php echo G5_IMG_URL ?>/logo.jpg" alt="<?php echo $config['cf_title']; ?>"></a>
        </div>

        <ul id="tnb">
            <?php if ($is_member) {  ?>
            <?php if ($is_admin || $show_adm) {  ?>
            <li><a href="<?php echo G5_ADMIN_URL ?>"><b>관리자</b></a></li>
            <?php }  ?>
	        <li><a href="<?php echo G5_BBS_URL ?>/memo.php" target="_blank" id="ol_after_memo" class="win_memo">쪽지 <strong><?php echo $memo_not_read ?></strong></a></li>
            <li><a href="<?php echo G5_BBS_URL ?>/member_confirm.php?url=<?php echo G5_BBS_URL ?>/register_form.php">정보수정</a></li>
            <li><a href="<?php echo G5_BBS_URL ?>/logout.php">로그아웃</a></li>
			<?php if ($member["mb_id"] == "hyosang82") { ?>
			<li><a href="<?php echo G5_BBS_URL ?>/board.php?bo_table=op_hist">운영기록</a></li>
			<?php } ?>
            <?php } else {  ?>
            <li><a href="<?php echo G5_BBS_URL ?>/register.php">회원가입</a></li>
            <li><a href="<?php echo G5_BBS_URL ?>/login.php"><b>로그인</b></a></li>
            <?php }  ?>
			<!--
            <li><a href="<?php echo G5_BBS_URL ?>/faq.php">FAQ</a></li>
            <li><a href="<?php echo G5_BBS_URL ?>/qalist.php">1:1문의</a></li>
			-->
            <li><a href="<?php echo G5_BBS_URL ?>/current_connect.php">접속자 <?php echo connect(); // 현재 접속자수  ?></a></li>
            <li><a href="<?php echo G5_BBS_URL ?>/new.php">새글</a></li>
        </ul>
    </div>

    <hr>


    <nav id="gnb">
        <h2>메인메뉴</h2>
        <ul id="gnb_1dul">
			<?php if($is_member) { ?>
            <li class="gnb_1dli" style="z-index:1;">
                <a href="#" class="gnb_1da">사진 (1981~1989)</a>
				<ul class="gnb_2dul">
				<?php 
				for($i=1981;$i<=1989;$i++) {
				?>
					<li class="gnb_2dli"><a href="<?php echo G5_BBS_URL ?>/board.php?bo_table=photo_<?php echo $i?>" class="gnb_2da"><?php echo $i?>년</a></li>
				<?php } ?>
				</ul>
            </li>
            <li class="gnb_1dli" style="z-index:1;">
                <a href="#" class="gnb_1da">사진 (1990~1999)</a>
				<ul class="gnb_2dul">
				<?php 
				for($i=1990;$i<=1999;$i++) {
				?>
					<li class="gnb_2dli"><a href="<?php echo G5_BBS_URL ?>/board.php?bo_table=photo_<?php echo $i?>" class="gnb_2da"><?php echo $i?>년</a></li>
				<?php } ?>
				</ul>
            </li>
            <li class="gnb_1dli" style="z-index:1;">
                <a href="#" class="gnb_1da">사진 (2000~2009)</a>
				<ul class="gnb_2dul">
				<?php 
				for($i=2000;$i<=2009;$i++) {
				?>
					<li class="gnb_2dli"><a href="<?php echo G5_BBS_URL ?>/board.php?bo_table=photo_<?php echo $i?>" class="gnb_2da"><?php echo $i?>년</a></li>
				<?php } ?>
				</ul>
            </li>
            <li class="gnb_1dli" style="z-index:1;">
                <a href="#" class="gnb_1da">사진 (2010~)</a>
				<ul class="gnb_2dul">
				<?php 
				$curyear = date("Y");
				for($i=2010;$i<=$curyear;$i++) {
				?>
					<li class="gnb_2dli"><a href="<?php echo G5_BBS_URL ?>/board.php?bo_table=photo_<?php echo $i?>" class="gnb_2da"><?php echo $i?>년</a></li>
				<?php } ?>
					<li class="gnb_2dli"><a href="<?php echo G5_BBS_URL ?>/board.php?bo_table=photo_9999" class="gnb_2da">미분류</a></li>
				</ul>
            </li>
            <li class="gnb_1dli" style="z-index:1;">
                <a href="#" class="gnb_1da">문서</a>
                <ul class="gnb_2dul">
                    <li class="gnb_2dli"><a href="<?php echo G5_BBS_URL ?>/board.php?bo_table=div_data" class="gnb_2da">부서별자료</a></li>
                    <li class="gnb_2dli"><a href="<?php echo G5_BBS_URL ?>/board.php?bo_table=event" class="gnb_2da">행사관련자료</a></li>
                    <li class="gnb_2dli"><a href="<?php echo G5_BBS_URL ?>/board.php?bo_table=diary" class="gnb_2da">생활일지</a></li>
                </ul>
            </li>
			<?php } else { ?>
			<li id="gnb_empty">메뉴영역은 로그인 후 표시됩니다</li>
			<?php } ?>
        </ul>
    </nav>
</div>

<div id="wrapper">
	<!--컨텐츠 시작 -->
    <div id="container">
        <?php if ((!$bo_table || $w == 's' ) && !defined("_INDEX_")) { ?><div id="container_title"><?php echo $g5['title'] ?></div><?php } ?>
