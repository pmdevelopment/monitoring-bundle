<?php

namespace PM\Bundle\MonitoringBundle\Controller;

use DateTime;
use Doctrine\ORM\QueryBuilder;
use PM\Bundle\MonitoringBundle\Entity\Statistics;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

/**
 * Description of StatisticController
 *
 * @author sjoder
 */
class StatisticController extends Controller {

   /**
    * @Route("/statistics/json/{from}/{to}/{filter}")
    * @Route("/statistics/json/{from}/{to}")
    * 
    * @param string $from
    * @param string $to
    * @param string $types
    */
   public function jsonAction($from, $to, $filter = null) {

      $start = new DateTime($from);
      $end = new DateTime($to);

      $result = array();

      /* @var $entriesQb QueryBuilder */
      $entriesQb = $this->getDoctrine()->getRepository("PMMonitoringBundle:Statistics")->createQueryBuilder("s");

      $entries = $entriesQb
              ->select("s")
              ->where($entriesQb->expr()->between("s.created", "'{$start->format('Y-m-d H:i:s')}'", "'{$end->format("Y-m-d H:i:s")}'"))
              ->orderBy("s.created", "ASC")
              ->getQuery()
              ->getResult()
      ;

      $filters = array();
      if ($filter) {
         $filters = explode("-", $filter);
      } else {
         $snmpObjects = $this->container->getParameter("pm_monitoring_objects");
         foreach ($snmpObjects as $name => $objectId) {
            $filters[] = $name;
         }
      }

      $head = array("Date");
      foreach ($filters as $f) {
         $head[] = $f;
      }
      $result[] = $head;


      /* @var $entry Statistics */
      foreach ($entries as $entry) {

         $line = array(
             $entry->getCreated()->format("H:i")
         );

         $entryData = $entry->getData();
         foreach ($filters as $f) {
            if (isset($entryData[$f]))
               $line[] = floatval($entryData[$f]);
            else
               $line[] = 0;
         }


         $result[] = $line;
      }

      return new Response(json_encode($result));
   }

}
