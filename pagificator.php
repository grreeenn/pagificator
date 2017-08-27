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
      $this->error = array ('error'=>1,'errorMessage'=>'Page type not set');
    if (isset($pageContent['maxDepth']))
      $this->maxDepth = $pageContent['maxDepth'];
    else
       $this->error = array ('error'=>true,'errorMessage'=>'Max depth not set');
  }
  public function allGood() {
    return $this->error;
  }
  //this function creates an HTML file from this page object. 
  //if $returnCode is true, it will not create a file, but return the code as a string

  public function getCodeString () {
    $html = '';
    $fileContent = 'No content so far';

    if ($this->pageContent['pageLang']=='html')
      $fileContent = self::buildHTML(array($this->pageContent),$html);

    return $fileContent;
  }

  public function populateFile($pathToFile='/')
  {
    $html = '';
    if ($this->pageContent['pageLang']=='html')
      $fileContent = self::buildHTML(array($this->pageContent),$html);
    // else if ($this->pageContent['pageLang']=='css')
    //  $fileContent = buildCSS();

    //if buildHTML encountered no errors, fileContent will be string
    if (is_array($fileContent)===false)
    {
      try
      {
        $file = fopen($pathToFile, 'w');
        fwrite($file, $fileContent);
        fclose($file);
        return array("error"=>false);
      }
      catch (Throwable $e)
      {
        $error = array();
        $error['error']=true;
        $error['errorMessage'] = 'Exception in '.$e->getFile().' on line '.$e->getLine().': '. $e->getMessage();
        $error['trace'] = $e->getTraceAsString();
        return $error;
        die();
      }
    }
    else //otherwise, some errors occured, return them
      return $fileContent;
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
    try {
      $level = $content[0]['depth'];
      //$finalHTML=file_get_contents("templates/".$pageType."/container.html");;
      $pathToTemplate = "../pagificator/templates/".$this->pageType."/".$content[0]['itemName'].".html";
      
//$html .= json_encode($content);
      
      foreach ($content as $item)
      {
        if (isset($item['errorMessage']))
        {
          $html .= '<span>'.$item['errorMessage'].'</span>';
        }
        else
        {
          $nextBlock = '';
          $template = file_get_contents($pathToTemplate);
          //fill template with plain data from the array
          foreach ($item['itemsToReplace'] as $value)
          {
            $placeholder="{{".$value."}}";
            $template = str_replace($placeholder,$item[$value],$template);
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


          if ($level < $this->maxDepth&&isset($item['items'])&&is_array($item['items']))     
            $huemplate = str_replace("{{content}}", self::buildHTML($item['items'],$nextBlock), $template);
          else if ($level == $this->maxDepth)
            $huemplate = $template;

          $html .= $huemplate;
        }
      }

      //clear all unused placeholders
      $html = preg_replace("/\{{2}(.*?)\}{2}/is", '', $html);

      return $html; 
    }
    catch (Throwable $e) {
      $error = array();
      $error['error'] = true;
      $error['errorMessage'] = 'Exception in '.$e->getFile().' on line '.$e->getLine().': '. $e->getMessage();
      $error['inputCaused'] = $content;
      $error['htmlProducedTillNow'] = $html;
      $error['trace'] = $e->getTraceAsString();
      return $error;
      die();
    }
  }

  /*this function builds HTML elements by given parameters.
  $elements is a single member of customElements, which may contain multiple elements of the same type which replace the same placeholder in the template.
  
  each element may contain unlimited number of nested elements; if [fill] is an array it means that there are nested elements and the function will run on them recursively.

  There's an simplified scheme for creating simple single elements with no closing tag - no [elements] needed, only [type] and optional [attributes]

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
    try
    {
      if (is_array($elements))
      {
        $type = $elements['type'];
        $elementCode = ''; //result var

        //if it's a complex element go through its elements
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
              //only elements with closing tag may contain nested elements, so close it anyway
              $elementCode .= '</'.$type.'>';
            }
            else if ($type!=='img'||$type!=='input')
              $elementCode .= $fill.'</'.$type.'>';
          }
        }
        /*
          simplified scheme for single element
          only element type is mandatory
          if fill or attributes applicable, put them into $element itself instead of ['elements'] subarray member
        */
        else if (!isset($elements['elements']))
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
          //if element got filling, concatinate it and close element
          if (isset($elements['fill'])&&is_array($elements['fill'])===false)
          {
            $elementCode .= '>';
            $elementCode .= $elements['fill'];
            $elementCode .= '</'.$type.'>';
          }
          //else close it properly
          else if (!isset($elements['fill']))
            $elementCode .= ' />';
        }
        return $elementCode;
      }
      else return array('error'=>false);
    }
    catch (Throwable $e)
    {
      return array('error'=>true,'errorObject'=>$e);
    }
  }

  /////////////////////////////////////////////////////////////////////////////////////////////////////
 ///////////////////////////////////////HELPER FUNCTIONS//////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////

  //this function takes DB result and turns it into array which may be used by buildHTML for creating a custom listbox.
  //first column of each result row will be treated as an option value and second - as an option display name. 
  //$selectedValue parameter serve to pre-select one of the options
  static function buildListboxArray($attributes, $options, $selectedValue=null) 
  {
    $elementArray = array("type"=>"select","elements"=>array(array()));
    $elementArray['elements'][0]['fill'] = array(array());

    $elementArray['elements'][0]['attributes'] = $attributes;

    $elementArray['elements'][0]['fill'][0]['type'] = "option";

    $elementArray['elements'][0]['fill'][0]['elements'] = array();

    foreach ($options as $option) 
    {
      $value = $option[array_keys($option)[0]];
      $fill = $option[array_keys($option)[1]];

      $optionArray = array('attributes'=>array('value'=>$value));
      if ($value==$selectedValue)
        $optionArray['attributes']['{simpleAttr}'] = 'selected';
      $optionArray['fill'] = $fill;

      $elementArray['elements'][0]['fill'][0]['elements'][] = $optionArray;
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
      $elementArray = array("type"=>$type,"elements"=>array(array()));
      $elementArray['elements'][0]['fill'] = array(array());

      $elementArray['elements'][0]['attributes'] = $listAttributes;

      $elementArray['elements'][0]['fill'][0]['type'] = "li";
      $elementArray['elements'][0]['fill'][0]['elements'] = array();

      foreach ($members as $member)
      {
        $fill = $member[array_keys($member)[0]];
        $memberArray = array('attributes'=>$memberAttributes);
        $memberArray['fill'] = $fill;
        $elementArray['elements'][0]['fill'][0]['elements'][] = $memberArray;
      }
    }
    else
      $elementArray='';
    return $elementArray;
  }

}

?>
