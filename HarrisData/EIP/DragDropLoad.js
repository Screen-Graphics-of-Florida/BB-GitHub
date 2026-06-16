<script language="JavaScript" type="text/javascript"><!--
	window.onload = function() {
		var list = document.getElementById("available");
		DragDrop.makeListContainer( list );
		list.onDragOver = function() { this.style["background"] = "#EEF"; };
		list.onDragOut = function() {this.style["background"] = "none"; };
		
		list = document.getElementById("selected");
		DragDrop.makeListContainer( list );
		list.onDragOver = function() { this.style["background"] = "#EEF"; };
		list.onDragOut = function() {this.style["background"] = "none"; };
	};
	//-->
</script> 