<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of sfJunaioXmlHelper
 *
 * @author bretschn
 */
class sfJunaioXmlHelper {

   public function addModel() {
      switch (substr($this->poiNow->mtype, 6)) {
         case "md2" :
            $this->nodeListNow['mainresource'] = sprintf("http://%s/uploads/model/%s", $_SERVER['HTTP_HOST'], $this->poiNow->model);
            $this->nodeListNow['resources'] = sprintf("<resource>http://%s/uploads/texture/%s</resource>", $_SERVER['HTTP_HOST'], $this->poiNow->texture);
         break;
         case "obj" :
            $this->nodeListNow['mainresource'] = sprintf("http://%s/uploads/model/%s", $_SERVER['HTTP_HOST'], $this->poiNow->model);
            $this->nodeListNow['resources'] = sprintf("<resource>http://%s/uploads/texture/%s</resource>", $_SERVER['HTTP_HOST'], $this->poiNow->texture);
         break;
      }
   }

   private $poiNow = null;
   public function initPoi($poi) {
      $this->poiNow = $poi;
   }

   public function addPoi($arg = "") {
      if ($arg != "")
         $arg = " " . $arg;
      $data = "";
      foreach ($this->nodeListNow as $k => $v)
         $data .= sprintf("\n    <%s>%s</%s>", $k, $v, $k);
      $this->responce[] = sprintf('  <poi id="%d"%s interactionfeedback="none">%s  </poi>', $this->poiNow->id, $arg, $data."\n");
      $this->poiNow = null;
      $this->nodeListNow = null;
   }

   private $nodeListNow = array ();
   public function addRawNode($nodename, $data) {
      $this->nodeListNow[$nodename] = $data;
   }

   public function addNode($type, $nodename) {
      switch ($nodename) {
         case "scale" :
            $this->nodeListNow["s"] = $this->poiNow->$nodename;
         break;
         case "l" :
            $this->nodeListNow[$nodename] = sprintf("%.2f,%.2f,%.2f", $this->poiNow->latitude, $this->poiNow->longitude, $this->poiNow->altitude);
         break;
         case "translation" :
            $this->nodeListNow[$nodename] = sprintf("%.2f,%.2f,%.2f", $this->poiNow->translation_x, $this->poiNow->translation_y, $this->poiNow->translation_z);
         break;
         case "o" :
            $this->nodeListNow[$nodename] = sprintf("%.2f,%.2f,%.2f", $this->poiNow->orientation_x, $this->poiNow->orientation_y, $this->poiNow->orientation_z);
         break;
         case "homepage" :
            if ($this->poiNow->$nodename != "")
               $this->nodeListNow[$nodename] = sprintf("http://%s", $this->poiNow->$nodename);
         break;
         case "mime-type" :
            $this->nodeListNow['mime-type'] = sprintf("%s", $this->poiNow->mtype);
         break;
         case "mainresource" :
            if ($this->poiNow->mtype != "text/plain")
               $this->nodeListNow[$nodename] = sprintf("<![CDATA[http://%s/%s]]", $_SERVER['HTTP_HOST'], $this->poiNow->$nodename);
         break;
         case "updated_at" :
            if (!is_null($this->poiNow->updated_at))
               $this->nodeListNow["date"] = strftime("%Y-%m-%d", strtotime($this->poiNow->updated_at));
         break;
         default :
            switch ($type) {
               case "bool" :
                  $this->nodeListNow[$nodename] = $this->poiNow->$nodename ? "true" : "false";
               break;
               case "text" :
                  if ($this->poiNow->$nodename != "" || ($this->poiNow->mtype == "text/plain") && $nodename == "description")
                     $this->nodeListNow[$nodename] = sprintf("<![CDATA[%s]]>", $this->poiNow->$nodename);
               break;
               case "int" :
                  if ($this->poiNow->$nodename > 0)
                     $this->nodeListNow[$nodename] = sprintf("%.2f", $this->poiNow->$nodename);
               break;
               case "uri" :
                  if ($this->poiNow->$nodename != "")
                     $this->nodeListNow[$nodename] = sprintf("http://%s%s", $_SERVER['HTTP_HOST'], $this->poiNow->$nodename);
               break;
            }
         break;
      }
   }

   private $responce = null;
   public function initResponce() {
      $this->responce = array ();
   }

   public function renderResponce($trackingUrl = null) {
      if ($trackingUrl == null)
         $responce = sprintf("<results>\n%s\n</results>", implode("\n", $this->responce));
      else
         $responce = sprintf('<results trackingurl="%s">%s</results>', $trackingUrl, "\n".implode("\n", $this->responce)."\n");
      $this->responce = null;
      return $responce;
   }
}
?>
