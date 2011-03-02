<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
	<meta http-equiv="content-type" content="text/html; charset=utf-8" />
	<meta name="Description" content="" />
	<title>ColorRating Example</title>

	<!-- Core Files.  Change the hyperlink references to reflect your site structure.  Note, this must also be updated in the ratings.js file. -->
	<link rel="stylesheet" type="text/css" href="rating/rating.css" media="screen"/>
	<script type="text/javascript" src="http://www.google.com/jsapi"></script>
	<script type="text/javascript">google.load("jquery", "1.3.2");</script>
	<script type="text/javascript" src="rating/rating.js"></script>
	<?php include('rating/rating.php'); ?>

</head>
<body>

	<!-- Replace 'test' with your table name. Replace 'item' with a unique name, such as item-1, item-2, etc.  Repeat this for every item you want to score. -->
	<?php rating_form("test",'item'); ?>
	<?php rating_form("test",'item'); ?>
	<?php rating_form("test",'item'); ?>
	
	<!-- How to implement highest rated items, the example grabs the top 5 highest rated items -->
	<?php
	$toprated = highest_rated("question",5);
	foreach ($toprated as $item) {
		$itemId = $item['itemId'];
		$rating = $item['rating'];
		$votes = $item['votes'];
		print "$itemId - $rating - $votes<br />";
	}
	?>
</body>
</html>