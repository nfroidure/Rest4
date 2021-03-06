<?php
class RestUriController extends RestController
{
  static $ctrInf;
  public static function getCtrInf()
  {
    $ctrInf=new stdClass();
    $ctrInf->description='URI testing purpose.';

    return $ctrInf;
  }
  public function __construct(RestRequest $request)
  {
    // Checking uri nodes validity
    if ($request->uriNodes->count()>4) {
      throw new RestException(RestCodes::HTTP_400,
        'Too many nodes in that uri.');
    }
    // Launching the driver
    $driver=new RestUriDriver($request);
    parent::__construct($driver);
  }
  public function getResponse()
  {
    $response=parent::getResponse();
    $response->setHeader('X-Rest-Cache','None');

    return $response;
  }
}

