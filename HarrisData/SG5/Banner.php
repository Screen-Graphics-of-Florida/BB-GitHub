<?php

if (($formatToPrint != "" ) && ($formatToPrint != "N")){require_once ($fmtBanner);
} elseif ($popUpWin == "Y")                            {require_once ($popBanner);
} else                                                 {require_once ($banner);
}

?>