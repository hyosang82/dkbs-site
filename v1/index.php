<?php
define('_INDEX_', true);
include_once('./_common.php');

// 초기화면 파일 경로 지정 : 이 코드는 가능한 삭제하지 마십시오.
if ($config['cf_include_index']) {
    if (!@include_once($config['cf_include_index'])) {
        die('기본환경 설정에서 초기화면 파일 경로가 잘못 설정되어 있습니다.');
    }
    return; // 이 코드의 아래는 실행을 하지 않습니다.
}

if(!$is_member) {
	goto_url(G5_BBS_URL."/login.php");
	return;
}

if (G5_IS_MOBILE) {
    include_once(G5_MOBILE_PATH.'/index.php');
    return;
}

include_once('./_head.php');
?>

<h2 class="sound_only">최신글</h2>
<!-- 최신글 시작 { -->



<?php
	echo latest("dkbs_intro", "notice", 5, 25);
?>

<!-- } 최신글 끝 -->

<?php
include_once('./_tail.php');
?>
