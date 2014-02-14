monitoring-bundle
=================

SNMP Monitoring for One Server

## Install

Add to composer:

```js
  "pmdevelopment/monitoring-bundle": "dev-master"
```

Add to kernel:

```php
  new PM\Bundle\MonitoringBundle\PMMonitoringBundle(),
```

Add to config.yml:

```js
   pm_monitoring:
      cleanup: 1 month
      snmp:
         host: %snmp_host%
         community: %snmp_community%
      objects:
         load: .1.3.6.1.4.1.2021.10.1.3.1
         cpu_user: .1.3.6.1.4.1.2021.11.9.0
         cpu_system: .1.3.6.1.4.1.2021.11.10.0
         ram_total: .1.3.6.1.4.1.2021.4.5.0
         ram_used: .1.3.6.1.4.1.2021.4.6.0
```

Use other objects you wan't to monitor.


Add to parameters.yml:

```js
   snmp_host: YOURHOST
   snmp_community: YOURCOMMUNITY
```

Add to routing.yml:

```js
   pm_monitoring:
      resource: "@PMMonitoringBundle/Controller/"
      type:     annotation
      prefix:   /monitoring
```

Register Cronjob:

```
   * * * * * /usr/bin/php /var/www/app/console pm:monitoring:cronjob > /dev/null
```

Don't forget Doctrine update!

## Usage Example

You need jQuery for this one.

```html
   <div id="graphing_load" class="graph" data-title="Load" data-url="{{ path("pm_monitoring_statistic_json",{"from":"-1 day", "to":"now", "filter":"load" }) }}" style="height: 300px;"></div>
   <br />
   <div id="graphing_cpu" class="graph" data-title="CPU in Percent" data-url="{{ path("pm_monitoring_statistic_json",{"from":"-1 day", "to":"now", "filter":"cpu_user-cpu_system" }) }}" style="height: 300px;"></div>
   <br />
   <div id="graphing_ram" class="graph" data-title="RAM" data-url="{{ path("pm_monitoring_statistic_json",{"from":"-1 day", "to":"now", "filter":"ram_total-ram_used" }) }}" style="height: 300px;"></div>

   <script type="text/javascript" src="https://www.google.com/jsapi"></script>
   <script type="text/javascript">
      google.load("visualization", "1", {packages: ["corechart"]});

      function getStatistics(elem) {
         var id = $(elem).attr("id");
         var uri = $(elem).data("url");

         $.get(uri, {}, function(result) {
            var data = google.visualization.arrayToDataTable(result);
            var options = {
               title: $(elem).data('title')
            };

            var chart = new google.visualization.LineChart(document.getElementById(id));
            chart.draw(data, options);
         }, "json");
      }

      $(document).ready(function(){
         $(".graph").each(function(){
            getStatistics(this);
         });
        });
   </script>
```