<?php
	function load_view($name) {
		$file = addslashes(file_get_contents("views/$name.html"));
		eval("\$file = \"$file\";");
		echo $file;
	}
?>
