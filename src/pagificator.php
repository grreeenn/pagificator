<?php

class Pagificator {
  private $pageContent,
          $pageType,
          $maxDepth,
          $error=array('error'=>false);


  function __construct ($pageContent) {
    $this->pageContent = $pageContent;
    if (isset($pageContent['pageType']))
      $this->pageType = $pageContent['pageType'];
    else
      throw new Exception('Pagificator initialization failed: page type not set');

    if (isset($pageContent['maxDepth']))
      $this->maxDepth = $pageContent['maxDepth'];
    else
      throw new Exception('Pagificator initialization failed: max depth not set');

    if (!isset($pageContent['pageLang']))
      throw new Exception('Pagificator initialization failed: page language not set or not supported');
  }

  public function getErrors() {
    return $this->error;
  }
  
  //returns an HTML code produced from given data as a string
  public function getCodeString () {
    $html = '';
    $fileContent = 'No content so far';

    if ($this->pageContent['pageLang']=='html')
      $fileContent = self::buildHTML(array($this->pageContent),$html);

    return $fileContent;
  }

  //creates a file from HTML code produced from given data
  public function populateFile($pathToFile='/')
  {
    $html = '';
    if ($this->pageContent['pageLang']=='html')
      $fileContent = self::buildHTML(array($this->pageContent),$html);
    // else if ($this->pageContent['pageLang']=='css')
    //  $fileContent = buildCSS();
    
    $file = fopen($pathToFile, 'w');
    fwrite($file, $fileContent);
    fclose($file);
  }

  /*
  This recursive function builds the HTML from the page content given (first time called from populateFile())


  It gets a multidimensional array with page content, puts the content instead of placeholders in HTML templates from /templates/ folder and concatenates the resulted strings one by one till there's a complete page, which is being returned to populateFile() then.

  The array structure is like this:
Array
(
  [0] => Array
    (
      [albumName] => Samle Album 1
      [albumDescription] => Sample description
      [depth] => 1
      [itemName] => album
      [itemsToReplace] => Array
        (
          [0] => albumName
          [1] => albumDescription
        )

      [items] => Array
        (
          [0] => Array
            (
              [pictureID] => 6
              [pictureCaption] => Sample Picture1
              [pictureCreatedDate] => 2017-06-22 22:24:04
              [pictureVisible] => 1
              [depth] => 2
              [itemName] => picture
              [picturePreviewURL] => /pictures/3/previews/6.jpg
              [pictureFullsizeURL] => /pictures/3/fullsize/6.jpg
              [itemsToReplace] => Array
                (
                  [0] => pictureID
                  [1] => picturePreviewURL
                  [2] => pictureFullsizeURL
                  [3] => pictureCaption
                  [4] => pictureCreatedDate
                )

              [customElements] => Array
                (
                  [0] => Array
                    (
                      [type] => button
                      [placeholder] => editPicture
                      [elements] => Array
                        (
                          [0] => Array
                            (
                              [attributes] => Array
                                (
                                  [type] => button
                                  [id] => editPictureButton-6
                                  [class] => adminButton
                                )

                              [fill] => Edit picture
                            )
                        )
                    )

                  [1] => Array
                    (
                      [type] => button
                      [placeholder] => deletePicture
                      [elements] => Array
                        (
                          [0] => Array
                            (
                              [attributes] => Array
                                (
                                  [type] => button
                                  [id] => deletePictureButton-6
                                  [class] => adminButton
                                )

                              [fill] => Delete picture
                            )
                        )
                    )
                )
            )

          [1] => Array
            (
              [pictureID] => 7
              [pictureCaption] => Sample Picture2
              [pictureCreatedDate] => 2017-06-22 22:24:04
              [pictureVisible] => 1
              [depth] => 2
              [itemName] => picture
              [picturePreviewURL] => /pictures/3/previews/7.jpg
              [pictureFullsizeURL] => /pictures/3/fullsize/7.jpg
              [itemsToReplace] => Array
                (
                  [0] => pictureID
                  [1] => picturePreviewURL
                  [2] => pictureFullsizeURL
                  [3] => pictureCaption
                  [4] => pictureCreatedDate
                )

              [customElements] => Array
                (
                  [0] => Array
                    (
                      [type] => button
                      [placeholder] => editPicture
                      [elements] => Array
                        (
                          [0] => Array
                            (
                              [attributes] => Array
                                (
                                  [type] => button
                                  [id] => editPictureButton-7
                                  [class] => adminButton
                                )

                              [fill] => Edit picture
                            )
                        )
                    )

                  [1] => Array
                    (
                      [type] => button
                      [placeholder] => deletePicture
                      [elements] => Array
                        (
                          [0] => Array
                            (
                              [attributes] => Array
                                (
                                  [type] => button
                                  [id] => deletePictureButton-7
                                  [class] => adminButton
                                )

                              [fill] => Delete picture
                            )
                        )
                    )
                )
            )
        )
    )
)



  Where [itemName] is the name of the template that need to be processed in this iteration, [items] is an array of sub-items (that will be procecced in the next iteration), [itemsToReplace] is an array of values that need to be replaced in the HTML template by corresponding values of the current array level, [customElements] is an array of custom elements to be added and [depth] is current level depth relative to the overall array and [maxDepth] is maximum depth of this array.
  
  The template HTML files contain single HTML object that need to be created, with {{placeholders}} in double curly braces that are being replaced by this array's first level content and, concidering that it's not the last level, contain a placeholder {{content}}, that's been replaced by the content generated by the next recursive iteration of the function.
  */
  private function buildHTML ($content, &$html)
  {
      if (is_array($content)===false)
        throw new Exception ('Content given is not an array');


      if (isset($content[0]['itemName']))
        $pathToTemplate = "templates/".$this->pageType."/".$content[0]['itemName'].".html";
      else 
        throw new Exception ("No item name set - can't identify template file");

      if (isset($content[0]['depth']))
        $level = $content[0]['depth'];
      else
        throw new Exception ('No depth set (item name '.$content[0]['itemName'].')');

      $i=0;
      foreach ($content as $item)
      {
        $nextBlock = '';
        $template = file_get_contents($pathToTemplate);

        //fill template with plain data from the array
        if (is_array($item['itemsToReplace']))
        {
          foreach ($item['itemsToReplace'] as $value)
          {
            $placeholder="{{".$value."}}";
            if (isset($item[$value]))
              $template = str_replace($placeholder,$item[$value],$template);
            else 
              throw new Exception ('No replacement value for '.$value.' at level '.$level.' position '.$i);
          }
        }

        //fill template with custom elements required
        if (isset($item['customElements']) && is_array($item['customElements']))
        {
          foreach ($item['customElements'] as $element)
          {
            $placeholder = '{{'.$element['placeholder'].'}}';
            $elementCode = self::buildElement($element);
            if (!is_array($elementCode))
             $template = str_replace($placeholder,$elementCode,$template);
            else if ($elementCode['error']===true)
              throw $elementCode['errorObject'];
          }
        }

        if ($level < $this->maxDepth)
        { 
          $emptyString = '';
          $nextBlock = self::buildHTML($item['items'],$emptyString);
          if (is_array($nextBlock))
            throw $nextBlock['errorObject'];
          else  
            $huemplate = str_replace("{{content}}", $nextBlock, $template);
        }
        else if ($level == $this->maxDepth)
          $huemplate = $template;

        $html .= $huemplate;
      
        $i++;
      }

      //clear all unused placeholders
      $html = preg_replace("/\{{2}(.*?)\}{2}/is", '', $html);

      return $html; 
  }

  /*
  this function builds HTML elements by given parameters.
  $elements is a single member of customElements, which may contain multiple elements of the same type which replace the same placeholder in the template - in this case all of the specific element info need to be put into [elements] subarray - only the [type] remains in the array root
  
  each element may contain unlimited number of nested elements; if [fill] is an array it means that there are nested elements and the function will run on them recursively.

  There's an simplified scheme for creating simple single elements - no [elements] needed, [type], [attributes] and [fill] may be put alongside the type in the root of an array. [fill] may also contain nested elements in this case

  the array structure:
  [customElements] => Array
    (
      [0] => Array
      (
        [type] => ul
        [placeholder] => myList
        [elements] => Array
        (
          [0] => Array
          (
            [attributes] => Array
            (
              [class] => list
              [id] => list-11
            )

            [fill] => Array
              (
              [0] => Array
                (
                [type] => li
                [elements] => Array
                  (
                  [0] => Array
                    (
                    [attributes] => Array
                      (
                      [class] => listItem
                      )

                    [fill] => li 1
                    )

                    [1] => Array
                    (
                      [attributes] => Array
                      (
                        [class] => listItem
                      )

                      [fill] => li 2
                    )

                    [2] => Array
                    (
                      [attributes] => Array
                        (
                        [class] => listItem
                        )

                      [fill] => Array
                        (
                        [0] => li 3
                        [1] => Array
                        (
                          [type] => span
                          [elements] => Array
                            (
                              [0] => Array
                              (
                                [attributes] => Array
                                  (
                                    [class] => nested
                                  )

                                [fill] => SPAN!!
                              )
                            )
                        )

                        [2] => Array
                        (
                          [type] => br
                        )

                        [3] => afterspan
                        )
                    )
                  )
                )
              [1] => blablablaaaa22
            )
          )
        )
      )

    [1] => Array
    (
      [type] => button
      [placeholder] => deletePicture
      [elements] => Array
        (
        [0] => Array
          (
          [attributes] => Array
            (
            [type] => button
            [id] => deletePictureButton-11
            [class] => adminButton
            )

          [fill] => Delete picture
          )
        )
      )
    )
  */
  static function buildElement($elements)
  {
      if (is_array($elements))
      {
        if (isset($elements['type']))
          $type = $elements['type'];
        else
          throw new Exception ('Custom element: no element type set');

        //result var
        $elementCode = ''; 

        //elements with no closing tag
        $voidElements = array('area','base','br','col','command','embed','hr','img','input','keygen','link','meta','param','source','track','wbr');

        //if there are multiple elements need to be placed in place of one placeholder, go through them
        if (isset($elements['elements'])&&is_array($elements['elements']))
        {
          foreach ($elements['elements'] as $element)
          {
            $attributes = (isset($element['attributes'])?$element['attributes']:'');

            $fill = $element['fill'];

            $elementCode .= '<'.$type.' ';
            if (is_array($attributes))
            {
              foreach ($attributes as $attribute=>$value)
              {
                if ($attribute!=='{simpleAttr}')
                  $elementCode .= $attribute.'="'.$value.'" ';
                else
                  $elementCode .= $value;
              }
            }


            if (in_array($type, $voidElements))
              $elementCode .= ' />';
            else
            {
              $elementCode .= '>';
              if (is_array($fill)) //for nested elements (like, ex, li or option)
              {
                foreach ($fill as $nestedElement)
                {
                  //$fill may be an array containing nested elements
                  if (is_array($nestedElement))
                    $elementCode .= self::buildElement($nestedElement);
                  //otherwise it acts like a text between opening and closing tags of an element
                  else
                    $elementCode .= $nestedElement;
                }
                $elementCode .= '</'.$type.'>';
              }
              else
                $elementCode .= $fill.'</'.$type.'>';
            }
          }
        }
        /*
          simplified scheme for single element
          only element type is mandatory
          if fill or attributes applicable, put them into $element itself instead of ['elements'] subarray member
          fill may be an array with nested elements
        */
        else
        {
          $elementCode .= '<'.$type.' ';
          if (isset($elements['attributes'])&&is_array($elements['attributes']))
          {
            foreach ($elements['attributes'] as $attribute=>$value)
            {
              if ($attribute!=='{simpleAttr}')
                $elementCode .= $attribute.'="'.$value.'" ';
              else
                $elementCode .= $value;
            }
          }

          if (in_array($type, $voidElements))
            $elementCode .= ' />';
          else if (isset($elements['fill'])&&is_array($elements['fill'])===false)
          {
            $elementCode .= '>';
            $elementCode .= $elements['fill'];
            $elementCode .= '</'.$type.'>';
          }          
          else if (isset($elements['fill'])&&is_array($elements['fill']))
          {
            $elementCode .= '>';
            foreach ($elements['fill'] as $nestedElement)
            {
              //$nestedElement may be an array containing nested elements
              if (is_array($nestedElement))
                $elementCode .= self::buildElement($nestedElement);
              //otherwise it's a text between opening and closing tags of an element
              else
                $elementCode .= $nestedElement;
            }
            $elementCode .= '</'.$type.'>';
          }
          else if (!isset($elements['fill']))
            $elementCode .= '></'.$type.'>';
        }
        return $elementCode;
      }
      else 
        throw new Exception ('Custom element: input parameter must be an array');    
  }

  /////////////////////////////////////////////////////////////////////////////////////////////////////
 ///////////////////////////////////////HELPER FUNCTIONS//////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////

  //this function takes DB result and turns it into array which may be used by buildHTML for creating a custom listbox.
  //first column of each result row will be treated as an option value and second - as an option display name. 
  //$selectedValue parameter serve to pre-select one of the options
  static function buildListboxArray($attributes, $options, $selectedValue=null) 
  {
    $elementArray = array("type"=>"select","attributes"=>$attributes,'fill'=>array());

    $elementArray['fill'][0]['type'] = "option";

    $elementArray['fill'][0]['elements'] = array();

    if (is_array($options))
    {
      foreach ($options as $option) 
      {
        $value = $option[array_keys($option)[0]];
        $fill = $option[array_keys($option)[1]];

        $optionArray = array('attributes'=>array('value'=>$value));
        if ($value==$selectedValue)
          $optionArray['attributes']['{simpleAttr}'] = 'selected';
        $optionArray['fill'] = $fill;

        $elementArray['fill'][0]['elements'][] = $optionArray;
      }
    }
    return $elementArray;
  }

  //this function will turn a DB result to a list with list items inside.
  //only the first column of the result will be considered
  //listAttributes will be bond to ul/ol, memberAttributes will be bonded to li's
  static function buildListArray ($type, $listAttributes, $memberAttributes, $members)
  {
    if (($type=="ul"||$type=="ol")&&is_array($members))
    {
      $elementArray = array("type"=>$type,"attributes"=>$listAttributes,'fill'=>array(array()));
      $elementArray['fill'] = array(array());

      $elementArray['fill'][0]['type'] = "li";
      $elementArray['fill'][0]['elements'] = array();

      foreach ($members as $member)
      {
        $fill = $member[array_keys($member)[0]];
        $memberArray = array('attributes'=>$memberAttributes);
        $memberArray['fill'] = $fill;
        $elementArray['fill'][0]['elements'][] = $memberArray;
      }
      return $elementArray;
    }
    else
      throw new Exception ('buildListArray: invalid input. Type: '.$type.', $members are '.(is_array($members)?'an array':'not an array'));
    
  }

}

?>
