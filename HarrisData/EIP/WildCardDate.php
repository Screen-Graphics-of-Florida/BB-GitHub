<?php
for (;;) {
    if (strpos($wildCardSearch, '{CYMD') > 0) {
        if (strpos($wildCardSearch, '{CYMD}') > 0) {
            $date = '1' . date("ymd");
            $wildCardSearch = str_replace("{CYMD}", $date, $wildCardSearch);
        } else {
            $startPos = strpos($wildCardSearch, '{CYMD');
            $endPos = strpos($wildCardSearch, '}', $startPos + 5);
            $cymd = substr($wildCardSearch, $startPos, $endPos - $startPos + 1);
            $pos = strpos($cymd, '}');
            $offset = substr($cymd, 5, strpos($cymd, '}') - 5);
            $newdate = date("ymd", strtotime($offset, time()));
            $date = '1' . $newdate;
            $dateDisplay = date("m-d-y", strtotime($offset, time())) . " (TODAY " . $offset . ")";
            $todayDesc = "TODAY " . $offset;
            $wildCardDisplay = str_replace($todayDesc, $dateDisplay, $wildCardDisplay);
            $wildCardSearch = str_replace($cymd, $date, $wildCardSearch);
        }
        continue;
    } elseif (strpos($wildCardSearch, '{ISO') > 0) {
        if (strpos($wildCardSearch, '{ISO}') > 0) {
            $date = date('Y-m-d', time());
            $wildCardSearch = str_replace("{ISO}", $date, $wildCardSearch);
        } else {
            $startPos = strpos($wildCardSearch, '{ISO');
            $endPos = strpos($wildCardSearch, '}', $startPos + 4);
            $iso = substr($wildCardSearch, $startPos, $endPos - $startPos + 1);
            $pos = strpos($iso, '}');
            $offset = substr($iso, 4, strpos($iso, '}') - 4);
            $newdate = date("Y-m-d", strtotime($offset, time()));
            $dateDisplay = date("m-d-Y", strtotime($offset, time())) . " (TODAY " . $offset . ")";
            $todayDesc = "TODAY " . $offset;
            $wildCardDisplay = str_replace($todayDesc, $dateDisplay, $wildCardDisplay);
            $wildCardSearch = str_replace($iso, $newdate, $wildCardSearch);
        }
        continue;
    } elseif (strpos($wildCardSearch, '{TSTP') > 0) {
        if (strpos($wildCardSearch, '{TSTP}') > 0) {
            $timestamp = date('Y-m-d-H.i.s');
            $wildCardSearch = str_replace("{TSTP}", $timestamp, $wildCardSearch);
        } else {
            $startPos = strpos($wildCardSearch, '{TSTP');
            $endPos = strpos($wildCardSearch, '}', $startPos + 5);
            $iso = substr($wildCardSearch, $startPos, $endPos - $startPos + 1);
            $pos = strpos($iso, '}');
            $offset = substr($iso, 5, strpos($iso, '}') - 5);
            $newdate = date("Y-m-d-H.i.s", strtotime($offset));
            $dateDisplay = date("Y-m-d-H.i.s", strtotime($offset)) . " (NOW " . $offset . ")";
            $todayDesc = "NOW " . $offset;
            $wildCardDisplay = str_replace($todayDesc, $dateDisplay, $wildCardDisplay);
            $wildCardSearch = str_replace($iso, $newdate, $wildCardSearch);
        }
        continue;
    }
    break;
}
?>