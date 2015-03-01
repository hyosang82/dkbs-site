<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

global $is_admin;

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.$visit_skin_url.'/style.css">', 0);
?>

<!-- 접속자집계 시작 { -->
<section id="visit">
    <div>
        <ul>
            <li>오늘 :: <?=number_format($visit[1])?></li>
            <li>어제 :: <?=number_format($visit[2])?></li>
            <li>최대 :: <?=number_format($visit[3])?></li>
            <li>전체 :: <?=number_format($visit[4])?></li>
            <?php if ($is_admin == "super") {  ?><li><a href="<?php echo G5_ADMIN_URL ?>/visit_list.php">상세보기</a></li><?php } ?>
        </ul>
    </div>
</section>
<!-- } 접속자집계 끝 -->