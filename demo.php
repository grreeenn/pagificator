<?php
error_reporting(E_ALL); ini_set('display_errors', '1'); 

require_once('pagificator.php');

$itemsQty = (isset($_POST['itemsQty'])?(int)$_POST['itemsQty']:1);
$spanQty = (isset($_POST['spanQty'])?(int)$_POST['spanQty']:1);
$addList = (bool)(isset($_POST['addList'])?$_POST['addList']:0);
$addSelect = (bool)(isset($_POST['addSelect'])?$_POST['addSelect']:0);
$output='beeboozee';
$summaryString = $itemsQty." items".($addList?", custom list at the beginning":"").($addSelect?", custom select box in each unit.":".");

//this is our array of data for items producing. Those are Vangers mechos'es of course
$mechoses = array(
					'Acid Monk' => 'https://vignette2.wikia.nocookie.net/vangers/images/d/d1/Fostral_Monk.gif/revision/latest?cb=20120218103011&path-prefix=ru',
					'Sanitar' => 'https://vignette4.wikia.nocookie.net/vangers/images/b/b8/Fostral_Sanitar.gif/revision/latest?cb=20120218104627&path-prefix=ru',
					'Old Demon' => 'https://vignette3.wikia.nocookie.net/vangers/images/6/69/Demon_-_fostral.gif/revision/latest?cb=20120218133030&path-prefix=ru',
					'Frog' => 'https://vignette3.wikia.nocookie.net/vangers/images/f/fd/Mech162.gif/revision/latest?cb=20120218203010&path-prefix=ru',
					'Reanimator' => 'https://vignette2.wikia.nocookie.net/vangers/images/6/63/Mech143.gif/revision/latest?cb=20120218200003&path-prefix=ru',
					'Chervonets' => 'https://vignette2.wikia.nocookie.net/vangers/images/f/fc/Mech352.gif/revision/latest?cb=20120218210842&path-prefix=ru',
					'Piercer' => 'https://vignette2.wikia.nocookie.net/vangers/images/7/73/Mech132.gif/revision/latest?cb=20120218194720&path-prefix=ru',
					'Rivet' => 'https://vignette1.wikia.nocookie.net/vangers/images/b/bf/Rivet_-_fostral.gif/revision/latest?cb=20120218192317&path-prefix=ru',
					'Bullet' => 'https://vignette1.wikia.nocookie.net/vangers/images/0/05/Bullet_-_fostral.gif/revision/latest?cb=20120218191434&path-prefix=ru',
					'Crazy Conoval' => 'https://vignette4.wikia.nocookie.net/vangers/images/0/05/Conoval_-_fostral.gif/revision/latest?cb=20120218184616&path-prefix=ru',
					'Arcanoid' => 'https://vignette4.wikia.nocookie.net/vangers/images/3/31/Arcanoid_-_fostral.gif/revision/latest?cb=20120218182624&path-prefix=ru',
					'AtTractor' => 'https://vignette2.wikia.nocookie.net/vangers/images/1/1d/Attractor_-_fostral.gif/revision/latest?cb=20120218142111&path-prefix=ru',
					'Mommy' => 'https://vignette1.wikia.nocookie.net/vangers/images/b/b2/Lady_fostral.gif/revision/latest?cb=20120218123427&path-prefix=ru',
					'Reaper' => 'https://vignette2.wikia.nocookie.net/vangers/images/9/92/Reaper_Fostral.gif/revision/latest?cb=20120218115523&path-prefix=ru',
					'Shrot' => 'https://vignette2.wikia.nocookie.net/vangers/images/5/5d/Shrot.gif/revision/latest?cb=20120218095038&path-prefix=ru',
					'Iron Shadow' => 'https://vignette2.wikia.nocookie.net/vangers/images/1/12/Shadow_-_fostral.gif/revision/latest?cb=20120218135240&path-prefix=ru',
					'Sandbuster' => 'https://vignette2.wikia.nocookie.net/vangers/images/e/e5/Sandbuster_-_fostral.gif/revision/latest?cb=20120218144806&path-prefix=ru',
					'Rotorwing' => 'https://vignette3.wikia.nocookie.net/vangers/images/7/79/Mech172.gif/revision/latest?cb=20120218203803&path-prefix=ru',
					'The Last of the Mohicans' => 'https://vignette2.wikia.nocookie.net/vangers/images/d/dc/Mech152.gif/revision/latest?cb=20120218201206&path-prefix=ru'
				);

if($addList)
{
	$type = 'ul';
	$listAttributes = array('class' => 'customList');
	$memberAttributes = array('class' => 'customListItem');
	//this will typically be a DB result, which represents an array of arrays (array per row)
	//in this case, even if such result have more than one column, only the firs one will be a list member
	$members = array(
					array('This'),
					array('is'),
					array('the'),
					array('custom'),
					array('unordered'),
					array('list')
				);
	//use helper function to build the list array
	$customList = Pagificator::buildListArray($type, $listAttributes, $memberAttributes, $members);
	$customList['placeholder'] = 'customList';
}

if($addSelect)
{
	$attributes = array('class' => 'customSelect');
	//this will typically be a DB result, which represents an array of arrays (array per row)
	//in this case, only two first columns of the result will be considered - first as value, second as option text
	$options = array(
					array('Value','This'),
					array('of each','is'),
					array('item is','the'),
					array('being contained','custom'),
					array('in result','select'),
					array('first column','box')
				);
	//use helper function to build the list array
	$customSelect = Pagificator::buildListboxArray($attributes, $options, 'in result');
	$customSelect['placeholder'] = 'customSelect';
}

$output = array(
		'pageType' => 'demo',
		'pageLang' => 'html',
		'itemName' => 'container',
		'itemsToReplace' => array('summary'),
		'summary' => $summaryString,
		'depth' => 0,
		'maxDepth' => 1,
		'items' => ''
	);
if ($addList)
	$output['customElements'][]=$customList;

$i=0;
$items = array();

if ($spanQty>0)
{
	$customP = array (
				'type' => 'p',
				'attributes' => array('class'=>'testParagraph'),
				'placeholder' => 'customP',
				'fill'=>array()
					);

	for ($i=0;$i<$spanQty;$i++)
	{
		$customP['fill'][] = array (
															'type' => 'span',
															'attributes' => array('class'=>'nestedSpan','style'=>'display:block;'),
															'fill'=>array('Nested ','span #',($i+1))
														);
	}
	$customP['fill'][] = ' paragraph continues after last span';
	$output['customElements'][]=$customP;
}

$arrayIndex=0;
for ($i=0;$i<$itemsQty;$i++)
{
	if ($arrayIndex==18) 
		$arrayIndex=0;

	$name = array_keys($mechoses)[$arrayIndex];
	$imageLink = $mechoses[$name];
	$mechos = array(
			'itemName'=>'mechos',
			'depth' => 1,
			'itemsToReplace' => array('picturePreviewURL','pictureID','pictureCaption'),
			'picturePreviewURL' => $imageLink,
			'pictureCaption' => $name,
			'pictureID' => $i
		);
	if ($addSelect)
		$mechos['customElements'] = array($customSelect);
	$items[] = $mechos;

	$arrayIndex++;
}

$output['items'] = $items;

try {
	$htmlGenerator = new Pagificator($output);
	$htmlCode = $htmlGenerator->getCodeString();
}
catch (Throwable $e) {
	print_r($e->getMessage());
	$htmlCode = '<p>Error occured, see array below</p>';
}

?>
<html>
	<head>
		<title>The Page</title>
		<link rel="stylesheet" href="demo.css">
	</head>
	<body>
	<?php
	echo $htmlCode;	
	echo '<p>This is an array used to construct this page:</p><pre>';
	print_r($output);
	echo '</pre>';
	?>
	</body>
</html>


