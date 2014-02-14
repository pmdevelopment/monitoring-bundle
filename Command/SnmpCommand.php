<?php

namespace PM\Bundle\MonitoringBundle\Command;

use DateTime;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\ORM\QueryBuilder;
use PM\Bundle\MonitoringBundle\Entity\Statistics;
use SNMP;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Description of SnmpCommand
 *
 * @author sjoder
 */
class SnmpCommand extends ContainerAwareCommand {

   private $log;
   private $timeStart;

   public function configure() {

      $this
              ->setName('pm:monitoring:cronjob')
              ->setDescription('Update Monitoring data')
      ;
   }

   public function __construct($name = null) {
      parent::__construct($name);

      $this->log = array();
      $this->timeStart = microtime(true);

      $now = date('Y-m-d H:i:s');

      $this->log[] = "<info>Start: $now</info>";
   }

   /**
    * 
    * @param OutputInterface $output
    */
   private function finish($output) {
      $timeEnd = microtime(true);
      $end = date('Y-m-d H:i:s');

      $this->log[] = "<info>End: $end</info>";
      $this->log[] = "Runtime: " . ($timeEnd - $this->timeStart);

      foreach ($this->log as $l) {
         $output->writeln($l);
      }
   }

   protected function execute(InputInterface $input, OutputInterface $output) {

      $snmpConfig = $this->getContainer()->getParameter("pm_monitoring_snmp");

      $this->log[] = "Open community {$snmpConfig['community']} on host {$snmpConfig['host']}";

      $snmp = new SNMP(SNMP::VERSION_2C, $snmpConfig['host'], $snmpConfig['community']);
      $snmp->valueretrieval = 1;

      $snmpObjects = $this->getContainer()->getParameter("pm_monitoring_objects");

      $data = array();

      foreach ($snmpObjects as $name => $objectId) {

         if (is_array($objectId)) {
            $result = 0;
            foreach ($objectId as $objectIdSub) {
               $resultSub = $snmp->get($objectIdSub);
               if (0 < $resultSub)
                  $result += $result;
            }
         } else
            $result = $snmp->get($objectId);

         $data[$name] = $result;

         $this->log[] = " - $name: $result";
      }


      $statistic = new Statistics();
      $statistic
              ->setCreated(new DateTime)
              ->setData($data)
      ;

      /* @var $doctrine Registry */
      $doctrine = $this->getContainer()->get("doctrine");

      $doctrine->getManager()->persist($statistic);
      $doctrine->getManager()->flush();

      $snmp->close();


      /*
       * Cleanup
       */
      $removeTime = new DateTime("-" . $this->getContainer()->getParameter("pm_monitoring_cleanup"));

      /* @var $entriesQb QueryBuilder */
      $entriesQb = $doctrine->getRepository("PMMonitoringBundle:Statistics")->createQueryBuilder('s');
      $entries = $entriesQb
              ->select('s')
              ->where($entriesQb->expr()->lt("s.created", "'{$removeTime->format('Y-m-d H:i:s')}'"))
              ->getQuery()
              ->getResult()
      ;

      $this->log[] = "Remove " . count($entries) . " old entries";

      foreach ($entries as $e) {
         $doctrine->getManager()->remove($e);
      }

      $doctrine->getManager()->flush();

      $this->finish($output);
   }

}
