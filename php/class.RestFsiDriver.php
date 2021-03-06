<?php
class RestFsiDriver extends RestVarsDriver
{
  static $drvInf;
  public static function getDrvInf($methods=0)
  {
    $drvInf=parent::getDrvInf(RestMethods::GET);
    $drvInf->name='Fsi: File Info Driver';
    $drvInf->description='Expose a folder content.';
    $drvInf->usage='/fsi/path/foldername'.$drvInf->usage;
    $drvInf->methods->get->queryParams=new MergeArrayObject();
    $drvInf->methods->get->queryParams[0]=new stdClass();
    $drvInf->methods->get->queryParams[0]->name='mode';
    $drvInf->methods->get->queryParams[0]->values=new MergeArrayObject();
    $drvInf->methods->get->queryParams[0]->values[0]=
      $drvInf->methods->get->queryParams[0]->value='normal';
    $drvInf->methods->get->queryParams[0]->values[1]='light';
    $drvInf->methods->get->queryParams[1]=new stdClass();
    $drvInf->methods->get->queryParams[1]->name='format';
    $drvInf->methods->get->queryParams[1]->values=new MergeArrayObject();
    $drvInf->methods->get->queryParams[1]->values[0]=
      $drvInf->methods->get->queryParams[1]->value='normal';
    $drvInf->methods->get->queryParams[1]->values[1]='datauri';

    return $drvInf;
  }
  public function head()
  {
    if(!file_exists('.'.$this->request->filePath.$this->request->fileName)) {
      throw new RestException(RestCodes::HTTP_410,
        'No folder found for the given uri'
        .' (/fsi'.$this->request->filePath.$this->request->fileName.')');
    }
    if(!is_dir('.'.$this->request->filePath.$this->request->fileName)) {
      throw new RestException(RestCodes::HTTP_500,
        'The given uri seems to not be a folder'
        .' (/fsi'.$this->request->filePath.$this->request->fileName.')');
    }

    return new RestVarsResponse(RestCodes::HTTP_200,
      array('Content-Type' => xcUtils::getMimeFromExt($this->request->fileExt)));
  }
  public function get()
  {
    $response=$this->head();
    if ($response->code==RestCodes::HTTP_200) {
      $response->vars->files=new MergeArrayObject();
      $tempList=new MergeArrayObject();
      $foldername = (!$this->request->filePath ? '/' :
        $this->request->filePath . $this->request->fileName
        . ($this->request->fileName ? '/' : ''));
      $folder = opendir('.'.$foldername);
      while ($filename = readdir($folder)) {
        if (($filename!='..'||$this->request->filePath)) {
          if ($this->queryParams->mode=='light'
            &&($filename=='.'||$filename=='..')) {
            continue;
          }
          $entry=new stdClass();
          $entry->name = xcUtilsInput::filterValue($filename,'text','cdata');
          if(is_dir('.' . $foldername . $filename)) {
            $entry->isDir = true;
          } else {
            if ($this->queryParams->format=='datauri') {
              $entry->content='data:'.xcUtils::getMimeFromFilename($filename)
                .';base64,'.base64_encode(file_get_contents(
                  '.'.$foldername.$filename));
            } else {
              $entry->mime = xcUtils::getMimeFromFilename($filename);
              $entry->size = @filesize('.' . $foldername . $filename);
            }
            $entry->isDir = false;
          }
          $entry->lastModified = @filemtime('.' . $foldername . $filename);
          $tempList->append($entry);
        }
      }

      $tempList->uasort(function ($a, $b) {
        if ($a->name == $b->name) {
          return 0;
        }

        return ($a->name < $b->name) ? -1 : 1;
      });

      foreach ($tempList as $file) {
        $response->vars->files->append($file);
      }
    }
    $response->setHeader('X-Rest-Uncacheback',
      '/fs'.$this->request->filePath.$this->request->fileName);

    return $response;
  }
}
