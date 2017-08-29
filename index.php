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
				<p class="formCaption">Amount of mechoses that will be shown on page. You can set up to 1000 of them, but I only have 19 mechoses - so they'll repeat themselves from the 20th</p>
				<input type="number" min="1" max="1000" value="1" name="itemsQty">
			</label>
			<label style="display:block;">
				<p class="formCaption">Amount of nested spans inside a custom paragraph (will appear on the page header. If set to 0, no custom paragraph will be created)</p>
				<input type="number" min="0" max="20" value="1" name="spanQty">
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