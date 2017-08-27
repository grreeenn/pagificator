<?php
//require_once "fileGenerator.php";

$firstLevel = array(
				 "depth"=>0,
				 "itemName" => 'container',
				 "pageType" => "demo",
				 "pageLang" => "html",
				 "itemsToReplace" => array(),
				 "items" => array()
	);
$secondLevel = array (
				'depth' => 1,
				'itemName' => 'category',
				'itemsToReplace' => array('categoryID','categoryName'),
				'items' => array()
	);

//var_dump($firstLevel);
?>

<!DOCTYPE html>
<html>
<head>
	<title>The Pagificator</title>
</head>
<body>
	<div class="container">
		<h1> The Pagificator demo page</h1>
		<p>The Pagificator is a simple templating engine in PHP. It gets the arrays of data and bounds it to placeholders in HTML templates. Please choose the parameters below, click Generate and look how it works:</p>
		<form action="demo.php" method="post">
			<label style="display:block;">
				<p class="formCaption">Amount of items on page. You can set up to 1000 items, but I only have 19 - so they'll repeat themselves</p>
				<input type="number" min="1" max="1000" value="1" name="qty">
			</label>
			
			<label style="display:block;">
				<input type="checkbox" name="addList" />
				<p class="checkboxCaption" style="display:inline;">Add custom list to description</p>
			</label>
			
			<label style="display:block;">
				<input type="checkbox" name="addSelect" />
				<p class="checkboxCaption" style="display:inline;">Add custom listbox to each item</p>
			</label>
			
			<input type="submit" value="Generate">
		</form>
		<p>Note, that it's just a simple demo page; please see the full documentation at <a href="https://github.com/grreeenn/pagificator">the official Github page</a>.</p>
	</div>
	
</body>
</html>