# check_terastation_storage
Nagios plugin for checking storage capacity on Buffalo TeraStations via SNMP.

Available on the [Nagios Exchange](https://exchange.nagios.org) at the following link:  
[https://exchange.nagios.org/directory/Plugins/Hardware/Storage-Systems/SAN-and-NAS/Check-TeraStation-Storage/details](https://exchange.nagios.org/directory/Plugins/Hardware/Storage-Systems/SAN-and-NAS/Check-TeraStation-Storage/details)

## V0.2
* Updated to use more accurate vendor specific OIDs.
* Should now be compatible with all SNMP enabled TeraStation models.
* This has been tested to work with models: TS.XE4, TS.XE8, and TS5000 Series.

## The following OIDs are used.
* Raid Array1 total size in GB: iso.3.6.1.4.1.5227.27.1.3.1.3.1
* Raid Array1 amount used in %: iso.3.6.1.4.1.5227.27.1.3.1.4.1
  * _If you have more than one array, just increment the last digit._

## USAGE
**COMMAND:** php check_terastation_storage.php HOST COMMUNITY WARNING CRITICAL  
**HOST** = IP or FQDN of the target TeraStation  
**COMMUNITY** = SNMP Community name  
**WARNING** = Level of amount free to trigger warning in percentage.  
**CRITICAL** = Level of amount free to trigger critical in percentage.  
**EXAMPLE:** `php check_terastation_storage.php 192.168.1.1 public 5 2`  

## CONFIGURATION
Include in commands.cfg:
```define command{ 
command_name check_terastation_storage
 command_line php /path/to/check_terastation_storage.php $HOSTADDRESS$ $ARG1$ $ARG2$ $ARG3$
}
```

## NOTES
* This plugin requires php5-snmp to be installed.

## Credits
[Reviewed and edited by Joshua K Roberson](https://github.com/jroberson)

