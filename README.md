# The Pagificator
This is very simple, but yet powerful templating engine written in PHP, which allows to separate server-side and client-side development, but still offers some space to think outside the template. 

**Key features:**
* Taking templates and filling them with data produced by your backend
* Nesting HTML elements recursively according to data - no limit of maximum nested elements
* Creating custom elements, which don't exist in the initial template at all
* Nesting custom elements inside other custom elements (useful for creating select boxes, for example). No limitations on depth of nesting
* Replacing single placeholder with multiple elements of the same type
* Helper functions, that come handy when building nested elements from DB resultset
* Writing the resulting code straight to file

## How does it work?
**TL:DR**
``` $pageObject = new Pagificator($yourDataArray);
$html = $pageObject->getCodeString(); ```

For practical code examples see demo files - you'll get both results and data structures in front of you.

### Simple templating
#### Data structure
The Pagificator needs your data to be organized in a certain way.

**The basic array structure is:**
* [itemName] - type of the current item; Pagificator will look for template named with value of this parameter
* [itemsToReplace] - zero-based array of placeholders to replace
* your data items, while key corresponds with one of placeholders in [itemsToReplace] - it's value will be bonded inside the template
* [depth] - current nesting depth
* [customElements] - if exist, Pagificator will look for custom elements that need to be created and yet to exist in the template
* [items] - array with items nested inside the current item. Each item should be an array with the same format as this one.

_Please note, that the first level of the data array should, in addition to other elements, contain also these ones:_
* [pageType] - will be picked at object construcion and used to choose a sub-folder to locate template files (/templates/pageType/)
* [maxDepth] - maximum nesting level

#### Template structure
Templates are being stored at pagificator/templates/pageType/ folder. Each template is an HTML code snippet which represents a single structural element of certain level of nesting. Inside each snippet, in the places which should represent server data, should appear a {{placeholder}} in double curly braces which corresponds with the peace of data in the data array you provided. If this element should have more nested elements, it should contain {{content}} placeholder - it will be replaced with nested elements (provided in [items] subarray).
Unused placeholders will be wiped out of the final code. You can see templates examples in the project's templates/demo/ folder.

### Custom elements
The Pagificator also allows to create custom elements, not just to fill those which already exist in template with data.
For this purpose an subarray called [customElements] should be added to the item you need it in. [customElements] should be an array of arrays each member of which represents a custom element. 
There are two possible structures for a member of [customElements]: simple and complex. Simple is used for a single element with no nested elements inside, while comples structure is used to create multiple elements to replace one placeholder.
**The simple structure is:**
* [placeholder] - name of a placeholder in the template that this element would replace (mandatory)
* [type] - element type (mandatory)
* [attributes] - HTML attributes of the element
* [fill] - the text between closing and opening tags. If a member does not contain any [fill], the element won't have a closing tag.

The **complex structure** may contain multiple elements, each one of which may contain multiple nested elements. In this scheme only [type] and [placeholder] remain in the first level of the element array while the rest of data resides in [elements] subarray.
**[elements]** is an array of arrays, each member of which represents separate element of the same type; it contains  [attributes] and [fill] for each one of the elements.
In this case, **[fill]** may be either a string or an array. In case it's a string, it will be simply concatinated between the opening and closing tags. In case that it's an array, it may contain both strings (which will be concatenated one after another) and arrays with additional elements while each of them have either simple or complex element array structure described above.

There are lots of template engines, and its closest conceptual relative seem to be {dwoo}. I considered using {dwoo} in my (in-progress) photo gallery CMS, but found some lack of features; although I needed something extra-simple - so I decided to write one of my own, so here we are.

The principle is simple: get your backend arrange your data in arrays of certain structure, put HTML templates with placeholders for your data for each type of items you need - and the Pagificator will handle the rest.

**Key features:**
* Taking templates and filling them with data produced by your backend logic
* Nesting HTML elements recursively according to data - no limit of maximum nested element
* Creating custom elements, which don't exist in the initial template at all
* Nesting custom elements inside other custom elements (useful for creating select boxes, for example). No limitations on depth of nesting
* Replacing single placeholder with multiple elements of the same type
* Helper functions, that come handy when building nested elements from DB resultset
* Writing the resulting code straight to file

## How does it work?
**TL:DR**

$pageObject = new Pagificator($yourDataArray);
$html = $pageObject->getCodeString(); (or $pageObject->populateFile(path/to/write);)
For practical code examples and array structures see demo files - you'll get both results and data structures in front of you.

### Simple templating
#### Data structure
The Pagificator needs your data to be organized in a certain way.

**The basic array structure is:**
* [itemName] - type of the current item; Pagificator will look for template named with value of this parameter
* [itemsToReplace] - zero-based array of placeholders to replace
* your data items, while key corresponds with one of placeholders in [itemsToReplace] - it's value will be bonded inside the template
* [depth] - current nesting depth
* [customElements] - if exist, Pagificator will look for custom elements that need to be created and yet to exist in the template
* [items] - array with items nested inside the current item. Each item should be an array with the same format as this one.

_Please note, that the first level of the data array should, in addition to other elements, contain also these ones:_
* [pageType] - will be picked at object construcion and used to choose a sub-folder to locate template files (/templates/pageType/)
* [maxDepth] - maximum nesting level

#### Template structure
Templates are being stored at pagificator/templates/pageType/ folder. Each template is an HTML code snippet which represents a single structural element of certain level of nesting. Inside each snippet, in the places which should represent server data, should appear a {{placeholder}} in double curly braces which corresponds with the peace of data in the data array you provided. If this element should have more nested elements, it should contain {{content}} placeholder - it will be replaced with nested elements (provided in [items] subarray).
Unused placeholders will be wiped out of the final code. You can see templates examples in the project's templates/demo/ folder.

### Custom elements
$elementCode = Pagificator::buildElement($yourDataArray);

The Pagificator also allows to create custom elements, not just to fill those which already exist in template with data.
For this purpose an subarray called [customElements] should be added to the item you need it in. [customElements] should be an array of arrays each member of which represents a custom element. 

There are two possible structures for a member of [customElements]: simple and complex. Simple is used for a single element with no nested elements inside, while comples structure is used to create multiple elements to replace one placeholder.

**The simple structure is:**
* [placeholder] - name of a placeholder in the template that this element would replace (mandatory)
* [type] - element type (mandatory)
* [attributes] - HTML attributes of the element
* [fill] - the text between closing and opening tags. If a member does not contain any [fill], the element won't have a closing tag.

The **complex structure** may contain multiple elements, each one of which may contain multiple nested elements. In this scheme only [type] and [placeholder] remain in the first level of the element array while the rest of data resides in [elements] subarray.

**[elements]** is an array of arrays, each member of which represents separate element of the same type; it contains  [attributes] and [fill] for each one of the elements.

In this case, **[fill]** may be either a string or an array. In case it's a string, it will be simply concatinated between the opening and closing tags. In case that it's an array, it may contain both strings (which will be concatenated one after another) and arrays with additional elements while each of them have either simple or complex element array structure described above.

After you ordered your data, just call Pagificator::buildElement($yourArray) and get an element string code in return. It's a static method, so it may be called without creating a Pagificator object.

### Helper functions
Sometimes it's just bit too complicated to produce such an array structure for complex elements with nesting. So there are two out-of-the box helper functions for creating array structures for lists and select boxed straight from the DB result. Both methods are static (callable without creating an object).

* __buildListArray($type, $listAttributes, $memberAttributes, $members)__ - where type is a list type (ul/ol), $listAttributes are HTML attributes applied to the list itself, $memberAttributes are attributes applied to each list member (both are key=>value arrays), and $members is your DB result (array of arrays). Only the first column of each row will be used - filled into the list items.
* __buildListboxArray($attributes, $options, $selectedValue)__ - where $attributes are HTML attributes for the <select> element, $options is your DB result (only first two columns will be used; first for value, second for option text) and $selectedValue is an element that need to be preselected (optional).

