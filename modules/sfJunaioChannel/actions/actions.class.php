<?php

/**
 * base actions.
 * 
 * @package    sfJunaioChannelPlugin
 * @subpackage base
 * @author     Your name here
 * @version    SVN: $Id: actions.class.php 12534 2008-11-01 13:38:27Z Kris.Wallsmith $
 */
class sfJunaioChannelActions extends sfActions
{
   public function executeSubscribe(sfWebRequest $request) {
      $response = $this->getResponse();
      $response->setContentType('text/xml');
      $response->setContent('<result>Todo executeSubscribe</result>');

      return sfView::NONE;
   }

   public function executeEvent(sfWebRequest $request) {
      $response = $this->getResponse();
      $response->setContentType('text/xml');
      $response->setContent('<result>Todo executeEvent</result>');

      return sfView::NONE;
   }

   public function executeSearch(sfWebRequest $request) {
      $uid = $request->getGetParameter("uid");
      $l = $request->getGetParameter("l");
      $p = $request->getGetParameter("p");
      $m = $request->getGetParameter("m");

      $cords = explode(",", $l);

      $pois = Doctrine::getTable('PoiBase')->findAll();

      $tmp = new PoiBase();
      $tmp->name = "Temp";
      $tmp->latitude = $cords[0];
      $tmp->longitude = $cords[1];

      require_once(realpath(dirname(__FILE__)."/../../../lib/")."/sfJunaioXmlHelper.class.php");

      $xmlHelper = new sfJunaioXmlHelper();
      $xmlHelper->initResponce();
      foreach ($pois as $poi) {
         if ($tmp->getDistance($poi) < $poi->getMaxdistance()) {
            $xmlHelper->initPoi($poi);
            $xmlHelper->addNode("text",  "name");
            $xmlHelper->addNode("text",  "mime-type");
            $xmlHelper->addNode("text",  "description");
            $xmlHelper->addNode("int",   "minaccuracy");
            $xmlHelper->addNode("int",   "maxdistance");
            $xmlHelper->addNode("int",   "perimeter");
            $xmlHelper->addNode("float", "o");
            $xmlHelper->addNode("uri",   "thumbnail");
            $xmlHelper->addNode("uri",   "icon");
            $xmlHelper->addNode("text",  "phone");
            $xmlHelper->addNode("text",  "mail");
            $xmlHelper->addNode("uri",   "homepage");
            $xmlHelper->addNode("uri",   "mainresource");
            $xmlHelper->addNode("float", "l");
            $xmlHelper->addNode("date",  "updated_at");
            $xmlHelper->addPoi();
         }
      }

      /*
       * test me
       *
       * http://sfjunaio.local/channel_dev.php/sfJunaioChannel/pois/search/?&uid=cd62aa53358bb927d794e6bd92135f98&l=37.77522,-122.420082,0&p=1000&m=20
       * 
       */

      $response = $this->getResponse();
      $response->setContentType('text/xml');
      $response->setContent($xmlHelper->renderResponce());
      return sfView::NONE;
   }
}
