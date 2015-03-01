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


?>

<?php
    //관리권한 있는지 체크
    $adm = sql_fetch("SELECT COUNT(*) AS CNT FROM g5_auth WHERE mb_id='{$member['mb_id']}'");
    $show_adm = false;
    if($adm["CNT"] > 0) {
        $show_adm = true;
    }

    if($is_admin == "super" || $is_admin == "group" || $is_admin == "board") {
        $show_adm = true;
    }
?>


<link rel="stylesheet" type="text/css" href="<?=G5_BBS_URL?>/style.v1.css" />


<table width="1150" height="100%" cellpadding="0" cellspacing="0">
    <tr>
        <td id="left_wrap">
            <table width="100%" height="100%" cellpadding="0" cellspacing="0">
                <tr><td valign="top">
                    <img id="main_logo" src="/v1/img_dkbs/default_logo.png" width="160" height="40" />
<?
    if($is_member) {
?>
                    <div id="left_links">
                        <a href="<?=G5_BBS_URL?>/memo.php" target="_blank"><img src="/v1/img_dkbs/btn_mail<?=($memo_not_read>0 ? "_new":"")?>.png" /></a><?
                        //빈칸방지
                        ?><a href="<?=G5_BBS_URL?>/member_confirm.php?url=<?=G5_BBS_URL?>/register_form.php"><img src="/v1/img_dkbs/btn_setting.png" /></a><?
                        ?><a href="<?=G5_BBS_URL?>/logout.php"><img src="/v1/img_dkbs/btn_logout.png" /></a><?
        $icon_cnt = 0;
        if($show_adm) {
            $icon_cnt++;
                        ?><a href="<?=G5_ADMIN_URL?>"><img src="/v1/img_dkbs/btn_admin.png" /></a><?
        }

        if($member["mb_id"] == "hyosang82") {
            $icon_cnt++;
                        ?><a href="<?=G5_BBS_URL?>/board.php?bo_table=op_hist"><img src="/v1/img_dkbs/btn_admlog.png" /></a><?
        }

        if(($icon_cnt % 3) != 0) {
            for($i=0;$i<3-($icon_cnt%3);$i++) {
                        ?><img src="/v1/img_dkbs/btn_empty.png" /><?
            }
        }
                        ?>
                    </div>
                    <div id="menu_items">
                        <span class="menu_item_1 ico_photo link" onclick="$('#photo_sub').toggle(500);">사진</span>
                        <ul id="photo_sub" class="menu_depth_2">
<?
    $curyear = date("Y");
    for($i=1981;$i<=$curyear;$i++) {
?>
                            <li><a href="<?=G5_BBS_URL?>/board.php?bo_table=photo_<?=$i?>"><?=$i?>년</a></li>
<?
    }
?>
                            <li><a href="<?=G5_BBS_URL?>/board.php?bo_table=photo_9999">미분류</a></li>
                        </ul>
                        <span class="menu_item_1 ico_doc link" onclick="$('#doc_sub').toggle(500);">문서</span>
                        <ul id="doc_sub" class="menu_depth_2">
                            <li><a href="<?=G5_BBS_URL?>/board.php?bo_table=div_data">부서별자료</a></li>
                            <li><a href="<?=G5_BBS_URL?>/board.php?bo_table=event">행사관련자료</a></li>
                            <li><a href="<?=G5_BBS_URL?>/board.php?bo_table=diary">생활일지</a></li>
                        </ul>
                    </div>
<?
    }else {
?>
                        <span class="menu_item_1 ico_login link"><a href="<?=G5_BBS_URL?>/login.php">로그인</a></span>
<?
    }
?>
                </td></tr>
                <tr><td style="padding-top:30px;">
                    <?=visit("dkbs_v1")?>
                </td></tr>
                <tr><td height="100%"></td></tr>
            </table>
        </td>
        <td id="main_area">

