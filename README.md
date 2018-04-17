*************************************
Victor:
It's ok.
Every PHP-programmer must write his own framework one day :-)

OnYourLips:
You'd usually prefer to treat it as your teenage masturbation and not to talk about it.


https://bash.im/quote/436733

but..it's here, so let it be here :)
*************************************

# The Pagificator (beta)

This is very simple but yet powerful templating library written in PHP (5.3+ required). It allows to separate server-side and client-side development but still offers some space to think outside the template. 
Lately such kind of work is done mostly by client-side templating; this library handles different approach - its main purpose is to serve the dynamical creation of static HTML pages in content management systems (I wrote it for my in-progress photo gallery CMS, which will generate static pages for albums and which allows to reuse the generated HTML and thus drop the server and client loads).

**Benefits**
* No new syntax to learn - backend is written in vanilla PHP, templates are pure HTML. The only new syntax construct is double curly braces used for {{placeholders}} of server data inside HTML templates
* No logic mess inside HTML templates (like loops or conditions) - your backend handles all of it
* No salad-code - just organize your data in arrays, Pagificator will take it from there

**Key features:**
* Taking templates and filling them with data produced by your backend logic
* Nesting HTML elements recursively according to data - no max limit of nested elements
* Producing custom elements, which don't exist in the initial template at all
* Nesting custom elements inside other custom elements (useful for creating select boxes, for example). No limitations on depth of nesting
* Replacing single placeholder with multiple custom elements
* Helper functions, that come handy when building nested elements from DB resultset
* Writing the resulting code straight to file or returning it as a code string for dynamic pages

## Installation
```bash
$ composer require pagificator/pagificator
```
or just copy the contents of src/ folder to your project.

## How does it work?
**TL:DR**
```php
try {
  $pageObject = new Pagificator($yourDataArray);
  $html = $pageObject->getCodeString(); //(or $pageObject->populateFile(path/to/write);)
catch (Throwable $e) {
//your debug logic here
}
```
For practical code examples and array structures see demo files - you'll get both data structures and work results and in front of you. 
You can also play around with a [test app on OpenShift](http://bit.ly/2wQ1g1p)

### Simple templating
#### Data structure
The Pagificator needs your data to be organized in a certain way.

**Members of the basic array structure are:**
* [itemName] - type of the current item; Pagificator will look for template named with value of this parameter
* [itemsToReplace] - int-indexed array of placeholders to replace
* your data items, while key corresponds with one of placeholders in [itemsToReplace] - it's value will be bonded inside the template
* [depth] - current nesting depth
* [customElements] - if exist, Pagificator will look for custom elements that need to be created and yet to exist in the template
* [items] - array with items nested inside the current item. Each item should be an array with the same format as this one.

_Please note, that the first level of the data array should, in addition to other elements, contain also these ones:_
* [pageType] - will be picked at object construcion and used to choose a sub-folder to locate template files (/templates/pageType/)
* [maxDepth] - maximum nesting level
* [pageLang] - language of the page. At the moment must contain 'HTML', in future versions CSS will be supported also
```php
/*
This array will make Pagificator to take templates/pagificatorDemo/container.html, put there logo and header instead of corresponding placeholders
Afterwards it will iterate through [items] - for each member it will find templates/pagificatorDemo/sub-item.html, replace placeholders and put the whole result instead of {{content}} placeholder in previous (container.html) template
*/

$yourPageDataArray = array (
                  'pageType'      => 'pagificatorDemo',
                  'pageLang'      => 'html',
                  'maxDepth'      => 1,
                  'depth'         => 0,
                  'itemName'      => 'container',
                  'itemsToReplace'=> array('linkToLogo','headerText')
                  'linkToLogo'    => '/images/logo.png',
                  'headerText     => 'Hello World!',
                  'customElements'=> array() //read further to custom elements chapter
                  'items'         => array(
                                      array(
                                       'itemName'      => 'sub-item',
                                       'depth'         => 1,
                                       'itemsToReplace'=> array('itemID','itemPicture','itemDescription','itemPrice'),
                                       'itemID'        => 1,
                                       'itemPicture'   => '/images/items/item1.png',
                                       'itemDescription=> 'This is the first item',
                                       'itemPrice'     => '42 USD'
                                       ),
                                       array(
                                       'itemName'      => 'sub-item',
                                       'depth'         => 1,
                                       'itemsToReplace'=> array('itemID','itemPicture','itemDescription','itemPrice'),
                                       'itemID'        => 2,
                                       'itemPicture'   => '/images/items/item2.png',
                                       'itemDescription=> 'This is the second item',
                                       'itemPrice'     => '256 USD',
                                       'customElements'=> array()
                                       )
                                      )
                   );      
```

#### Template structure
Templates are being stored at pagificator/templates/[pageType]/ folder. Each template is an HTML code snippet which represents a single structural element of certain level of nesting. Inside each snippet, in the places which should represent server data, should appear a {{placeholder}} in double curly braces which corresponds with the peace of data in the data array you provided. If this element should have more nested elements, it should contain {{content}} placeholder - it will be replaced with nested elements (provided in [items] subarray).
Unused placeholders will be wiped out of the final code. You can see templates examples in the project's templates/demo/ folder.

This how container.html from data structure example may look like:
```html
<!DOCTYPE html>
<html>
  <head>
	  <title>The Pagificator</title>
  </head>
  <body>
    <div class="header"><img src="{{linkToLogo}}" class="logo" /></div>
    <div class="mainContent">
      <h1>{{headerText}}</h1>
      {{content}} <!--nested content from [items] will come here-->
      {{locationSpecificWarning}} <!--next chapter will explain how this is been filled-->
    </div>
  </body>
</html>
```
and sub-item.html:
```html
<div class="singleItemContainer">
	<figure class="itemFigure" id="item-{{itemID}}">
		<img src="{{itemPicture}}" class="itemPicture" />
		<figcaption class="itemDescriptionAndPrice">
			<p class="itemDescription">{{itemDescription}}</p>
			<p class="itemPrice">{{itemPrice}}</p>
		</figcaption>
	</figure>
	{{customRadios}}
</div>
```
The resulting HTML code will come after the Custom Elements chapter.

### Custom elements
```php
$elementCode = Pagificator::buildElement($yourElementDataArray);
```

The Pagificator also allows to create custom elements, not just to fill those which already exist in template with data.
For this purpose an subarray called [customElements] should be added to the item you need it in. [customElements] should be an array of arrays each member of which represents a custom element. 

There are two possible structures for a member of [customElements]: simple and complex. Simple is used for a single element with no nested elements inside, while comples structure is used to create multiple elements to replace one placeholder.

**The simple structure is:**
* [placeholder] - name of a placeholder in the template that this element would replace (mandatory for the first nesting level)
* [type] - element type (mandatory)
* [attributes] - HTML attributes of the element
* [fill] - the text between closing and opening tags. If a member does not contain any [fill], the element won't have a closing tag. [fill] may be either a string or an array. In case it's a string, it will be simply concatinated between the opening and closing tags. In case that it's an array, it may contain both strings (which will be concatenated one after another) and arrays with additional elements which will be processed recursively and resulting code will be concatinated.
```php
//this will produce a paragraph with text 'You've been warned.", and a nested span with "Don't tell you haven't" written in bold
$yourElementDataArray = array (
                          'placeholder' => 'locationSpecificWarning',
                          'type'        => 'p',
                          'attributes'  => array('class'=>'warning'),
                          'fill'        => array (
                                              "You've been warned. ",
                                              array (
                                                'type'      => 'span',
                                               'attributes'=> array ('style'=>'font-weight:bold'),
                                                'fill'      => "Don't tell you haven't"
                                              )
                                           )
                        );
//add it to the page data array, so it will be rendered and placed instead of corresponding placeholder in container.html
$yourPageDataArray['customElements'][]=$yourElementDataArray;
```

The **complex structure** may contain multiple elements, each one of which may contain multiple nested elements. In this scheme only [type] and [placeholder] remain in the first level of the element array while the rest of data resides in [elements] subarray.
**[elements]** is an array of arrays, each member of which represents separate element of the same type; it contains  [attributes] and [fill] for each one of the elements.
```php
//this will produce 3 radios with their labels that will be placed instead of one placeholder
$yourElementDataArray = array (
                          'type'        => 'label',
                          'placeholder' => 'placeForRadios',
                          'elements'    => array ()
                         );
for ($i=0;$i<5;$i++)
{
  $label = array();
	$label['attributes'] = array ('class'=>'radioAndLabel')
	$radio = array();
	$radio['type'] = 'input';
  $radio['attributes'] = array ('type'=>'radio','name'=>'superRadio','value'=>$i);
	$label['fill'][] = $radio;
	$label['fill'][] = 'Custom radio #'.$i;
  $yourElementDataArray['elements'][]=$label; 
}
//add it to the page data array to, for example, the second item of the second level, so it will be rendered and placed instead of corresponding placeholder in sub-item.html
$yourPageDataArray['items'][1]['customElements'][]=$yourElementDataArray;
```

After you ordered your data, just place it in [customElements] subarray in your $pageDataArray (it may reside in any item there) to render it together with the whole page or call Pagificator::buildElement($yourElementDataArray) to get an element string code in return (for an AJAX request for example). It's a static method, so it may be called without creating a Pagificator object.

#### Result
The above examples combined together will output the following HTML code:
```html
<!DOCTYPE html>
<html>
  <head>
	  <title>The Pagificator</title>
  </head>
  <body>
    <div class="header"><img src="{{linkToLogo}}" class="logo" /></div>
    <div class="mainContent">
      <h1>Hello World!</h1>
      <div class="singleItemContainer">
				<figure class="itemFigure" id="item-1">
					<img src="/images/items/item1.png" class="itemPicture" />
					<figcaption class="itemDescriptionAndPrice">
						<p class="itemDescription">This is the first item</p>
						<p class="itemPrice">42 USD</p>
					</figcaption>
				</figure>
			</div>
			<div class="singleItemContainer">
				<figure class="itemFigure" id="item-2">
					<img src="/images/items/item2.png" class="itemPicture" />
					<figcaption class="itemDescriptionAndPrice">
						<p class="itemDescription">This is the second item</p>
						<p class="itemPrice">256 USD</p>
					</figcaption>
				</figure>
				<!-- we've put the custom radios for the second iem only -->
				<label class="radioAndLabel">
					<input type="radio" name="superRadio" value="0" />
				</label>
				<label class="radioAndLabel">
					<input type="radio" name="superRadio" value="1" />
				</label>
				<label class="radioAndLabel">
					<input type="radio" name="superRadio" value="2" />
				</label>
			</div>
      <p class="warning">You've been warned. <span style="font-weight:bold;">Don't tell you haven't</span></p>
    </div>
  </body>
</html>
```
### Helper functions
If you need to generate lists and select boxes from DB results (quite a common usage case), there are two out-of-the box helper functions for creating array structures for lists and select boxed straight from the DB result. Both methods are static (callable without creating an object).

* __buildListArray($type, $listAttributes, $memberAttributes, $members)__ - where type is a list type (ul/ol), $listAttributes are HTML attributes applied to the list itself, $memberAttributes are attributes applied to each list member (both are key=>value arrays), and $members is your DB result (array of arrays). Only the first column of each row will be used - filled into the list items.
* __buildListboxArray($attributes, $options, $selectedValue)__ - where $attributes are HTML attributes for the <select> element, $options is your DB result (only first two columns will be used; first for value, second for option text) and $selectedValue is an element that need to be preselected (optional).

The next upgrade to helper function will bring more flexibility of choosing which columns to consider, the number of helper functions will also grow with time and demands.

Contributions and new features ideas are most welcome :)
