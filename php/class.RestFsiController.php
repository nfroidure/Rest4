<?php
class RestFsiController extends RestFslikeController
{
  static $ctrInf;
  public static function getCtrInf()
  {
    $ctrInf=new stdClass();
    $ctrInf->description='Give information on the filesystem.';

    return $ctrInf;
  }
  public function __construct(RestRequest $request)
  {
    // Checking uri nodes validity
    $this->checkUriInputs($request);
    // Reject folders
    if ($request->isFolder) {
      throw new RestException(RestCodes::HTTP_301,
        'Redirecting to the right uri for this ressource.', '',
        array('Location'=>'/fsi'.($request->filePath ?
          substr($request->filePath,0,strlen($request->filePath)-1):'')));
    } else {
      $driver=new RestFsiDriver($request);
    }
    parent::__construct($driver);
  }
  public function getResponse()
  {
    $response=parent::getResponse();
    $response->setHeader('Cache-Control','public, max-age=31536000');

    return $response;
  }
}

