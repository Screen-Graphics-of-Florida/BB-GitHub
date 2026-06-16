<?php
if ($sortVar == "sort") {print "\n <th class=\"colhdr$sortVar\" $helpCursor><span title=\"$orderByDisplay\">{$sortPoint}$columnHdr</span></th> ";}
else                    {print "\n <th class=\"colhdr\">{$sortPoint}$columnHdr</th> ";}
?>