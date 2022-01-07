<?php

class TestException extends Exception
{
  protected $exceptionMessage;

  public function __construct($message, $exceptionMessage, $code = 0, Throwable $previous = null) {
    $this->exceptionMessage = $exceptionMessage;
    parent::__construct($message, $code, $previous);
  }

  public function __toString() {
    return __CLASS__ . ": [$this->exceptionMessage]: $this->message\n";
  }

  public function getExceptionMessage() {
    return $this->exceptionMessage;
  }
}

function describe($description, $func) {
  try {
    echo "describe() $description</br>";
    $func();
  } catch (TestException $ex) {
    echo "[FAIL] describe() $description -> " . $ex->getMessage() . "  at " . $ex->getExceptionMessage() . "</br>";
  }
}

function it($description, $func) {
  try {
    echo "it() $description</br>";
    $func();
  } catch (Exception $ex) {
    throw new TestException(
      "it() $description failed" . "</br>", 
      $ex->getMessage() . " (file: " . $ex->getTrace()[0]["file"] . ", line " . $ex->getTrace()[0]["line"] . ")<br/>");
  }
}

function assertStrict($v1, $v2): bool
{
  if ($v1 === $v2) {
    return true;
  } else {
    throw new Exception("assertStrict() $v1 (" . gettype($v1) . ") !== $v2 (" . gettype($v2) . ")");
  }
}

function assertNotStrict($v1, $v2): bool
{
  if ($v1 == $v2) {
    return true;
  } else {
    throw new Exception("assertNotStrict() $v1 != $v2");
  }
}

function assertObject($obj1, $obj2): bool
{
  if (json_encode($obj1) === json_encode($obj2)) {
    return true;
  } else {
    throw new Exception("assertObject() " . json_encode($obj1) . " !== " . json_encode($obj2));
  }
}

function request($method, $url, $json = NULL): array
{
  $req = curl_init();

  curl_setopt($req, CURLOPT_URL, $url);
  curl_setopt($req, CURLOPT_CUSTOMREQUEST, $method);
  curl_setopt($req, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($req, CURLOPT_FRESH_CONNECT, true);

  if ($json) {
    $body = json_encode($json, JSON_UNESCAPED_UNICODE);
  
    curl_setopt($req, CURLOPT_POSTFIELDS, $body);
    curl_setopt($req, CURLOPT_HTTPHEADER, array(
      'Content-Type: application/json',
      'Content-Length: ' . strlen($body))
    );
  }

  $response = curl_exec($req);
  $responseInfo = curl_getinfo($req);

  curl_close($req);

  return ["data" => $response, "info" => $responseInfo];
}

