<?php

function  Get_Month_Desc ($monthNbr){
	if       ($monthNbr == "01"){$monthAbr = "Jan";
	} elseif ($monthNbr == "02"){$monthAbr = "Feb";
	} elseif ($monthNbr == "03"){$monthAbr = "Mar";
	} elseif ($monthNbr == "04"){$monthAbr = "Apr";
	} elseif ($monthNbr == "05"){$monthAbr = "May";
	} elseif ($monthNbr == "06"){$monthAbr = "Jun";
	} elseif ($monthNbr == "07"){$monthAbr = "Jul";
	} elseif ($monthNbr == "08"){$monthAbr = "Aug";
	} elseif ($monthNbr == "09"){$monthAbr = "Sep";
	} elseif ($monthNbr == "10"){$monthAbr = "Oct";
	} elseif ($monthNbr == "11"){$monthAbr = "Nov";
	} elseif ($monthNbr == "12"){$monthAbr = "Dec";}
	return $monthAbr;
}

function Get_Month_Full_Desc ($monthNbr)
{
	if       ($monthNbr == "01"){$monthDesc =  "January";
	} elseif ($monthNbr == "02"){$monthDesc =  "February";
	} elseif ($monthNbr == "03"){$monthDesc =  "March";
	} elseif ($monthNbr == "04"){$monthDesc =  "April";
	} elseif ($monthNbr == "05"){$monthDesc =  "May";
	} elseif ($monthNbr == "06"){$monthDesc =  "June";
	} elseif ($monthNbr == "07"){$monthDesc =  "July";
	} elseif ($monthNbr == "08"){$monthDesc =  "August";
	} elseif ($monthNbr == "09"){$monthDesc =  "September";
	} elseif ($monthNbr == "10"){$monthDesc =  "October";
	} elseif ($monthNbr == "11"){$monthDesc =  "November";
	} elseif ($monthNbr == "12"){$monthDesc =  "December";}
	return $monthDesc;
}
?>