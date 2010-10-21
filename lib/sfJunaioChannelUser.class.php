<?php

if (!function_exists('getallheaders'))
{
   function getallheaders() {
      foreach ($_SERVER as $name => $value) {
         if (substr($name, 0, 5) == 'HTTP_') {
            $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
         }
      }
      return $headers;
   }
}

class sfJunaioChannelUser extends sfUser
{
   public function checkAuthentication() {
      $_HEADERS = getallheaders();

      // Check if authorization header is there
      if (!isset($_HEADERS['Authorization']))
         return FALSE;

      $sAuthentication = $_HEADERS['Authorization'];

      // Check if authorization header is of "junaio" type
      if(strpos($sAuthentication, 'junaio') != 0)
         return FALSE;

      // Check date header
      $sDate = $_HEADERS['Date'];
      $iParsedDate = strtotime($sDate);
      $iNow = time();
      if($iParsedDate < $iNow - sfConfig::get('app_junaio_auth_date_tolerance') || $iParsedDate > $iNow + sfConfig::get('app_junaio_auth_date_tolerance'))
         return FALSE;

      // Prepare signature variables
      $aTokens = explode(' ', $sAuthentication);
      if (!isset($aTokens[1]) || trim($aTokens[1]) == '')
         return FALSE;

      $sRequestSignature = base64_decode(trim($aTokens[1]));

      // Build server request signature
      $sServerRequestSignature = sha1(
      sfConfig::get('app_junaio_api_key') . sha1(
         sfConfig::get('app_junaio_api_key') .
            $_SERVER['REQUEST_METHOD'] . "\n" .
            $_SERVER['REQUEST_URI'] . "\n" .
            'Date: ' . $sDate . "\n"
         )
      );

      // Compare request signature
      if(strcmp($sRequestSignature, $sServerRequestSignature) !== 0)
         return FALSE;
      else
         return TRUE;
   }

   public function isAuthenticated() {
      $config = sfContext::getInstance()->getConfiguration();
      if (!$this->checkAuthentication() && $config->getEnvironment() != "dev") {
         header('HTTP/1.1 401 Unauthorized');
         exit();
      }
      return true;
   }
}
