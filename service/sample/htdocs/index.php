<?php
	header('Content-type: application/json');

	echo "{access_code: \"".addslashes($_GET['code'])."\"}";
