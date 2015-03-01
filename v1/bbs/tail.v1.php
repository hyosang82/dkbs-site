<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

?>
        </td>
    </tr>
</table>

<script type="text/javascript">
    var _board = "<?=$bo_table?>";

    if(_board.split("_")[0] == "photo") {
        $("#photo_sub").show();
    }else {
        if(_board == "div_data" || _board == "event" || _board == "diary") {
            $("#doc_sub").show();
        }
    }
</script>
