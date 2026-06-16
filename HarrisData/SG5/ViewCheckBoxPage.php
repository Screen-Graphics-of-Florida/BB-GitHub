<?php

$viewCheckBoxTpl = <<<VCBTEMPLATE
\n <input type="checkbox" id="{id}" name="{name}" {checked} onClick="{action}">{startSpan}{text}{endSpan}
VCBTEMPLATE;

$viewCheckBoxes = "";
$viewCheckBoxSearch[] = '{id}';
$viewCheckBoxSearch[] = '{name}';
$viewCheckBoxSearch[] = '{checked}';
$viewCheckBoxSearch[] = '{action}';
$viewCheckBoxSearch[] = '{startSpan}';
$viewCheckBoxSearch[] = '{text}';
$viewCheckBoxSearch[] = '{endSpan}';

foreach ($viewCheckBoxDef as $cx => $cv) {
	$viewCheckBoxReplace[0] = "checkBox$cx";
	$viewCheckBoxReplace[1] = "checkBox$cx";
	$viewCheckBoxReplace[2] = ($viewCheckBox[$cx]) ? "checked" : "";
	$viewCheckBoxReplace[3] = str_replace('{checkBoxNumber}', $cx, $cv[2]);
	$viewCheckBoxReplace[4] = (count($cv) == 8) ? "<span $helpCursor title=\"" . str_replace("<br>", "", $cv[6]) . "\">" : "";	
	$viewCheckBoxReplace[5] = $cv[1];
	$viewCheckBoxReplace[6] = (count($cv) == 8) ? "</span>" : "";		
	if ($cv[0] != "") {$viewCheckBoxes .= "\n <span class=\"hdrdata\">{$cv[0]}</span>";}
	$viewCheckBoxes .= str_replace($viewCheckBoxSearch, $viewCheckBoxReplace, $viewCheckBoxTpl);
}

echo "\n" . $viewCheckBoxes . "\n";
?>