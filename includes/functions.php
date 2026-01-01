<?php
// includes/functions.php

// 以后用来格式化时间或者处理字符串的函数可以放在这里
function format_date($date) {
    return date('Y-m-d', strtotime($date));
}