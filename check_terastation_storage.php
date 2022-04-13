<?php
// check_terastation_storage.php
// Check storage capacity of Buffalo TeraStations via SNMP
// 
// V0.2
// Updated to use more accurate vendor specific OIDs.
// Should now be compatible with all SNMP enabled TeraStation models.
// This has been tested to work with models: TS.XE4, TS.XE8, and TS5000 Series.
// 
// The following OIDs are used.
// Raid Array1 total size in GB: iso.3.6.1.4.1.5227.27.1.3.1.3.1
// Raid Array1 amount used in %: iso.3.6.1.4.1.5227.27.1.3.1.4.1
// *If you have more than one array, just increment the last digit.
// 
// USAGE: php check_terastation_storage.php HOST COMMUNITY WARNING CRITICAL
// HOST=IP or FQDN of the target TeraStation
// COMMUNITY=SNMP Community name
// WARNING=Level of amount free to trigger warning in percentage.
// CRITICAL=Level of amount free to trigger critical in percentage.
// EXAMPLE: php check_terastation_storage.php 192.168.1.1 public 5 2
// 
// Include in commands.cfg
// define command{ 
// command_name check_terastation_storage
// command_line php /path/to/check_terastation_storage.php $HOSTADDRESS$ $ARG1$ $ARG2$ $ARG3$
// }
// 
// This plugin requires php5-snmp to be installed.
// -Kyle M
// 
// Reviewed and edited by Joshua K Roberson


// Check if all arguments are supplied
if(count($argv) < 4)
  DisplayMessage(0, "Incomplete statement.\r\nUSAGE: check_terastation_storage.php HOST COMMUNITY WARNING CRITICAL\r\n");

//Assign supplied arguments
list(,$host, $community, $warning, $critical,) = $argv;
$warning=(float)$warning;
$critical=(float)$critical;

//If warning less than critial, give usage example and exit.
if($warning < $critical)
  DisplayMessage(0, "The WARNING value cannot be lower than the CRITICAL value.\r\nUSAGE: check_terastation_storage.php HOST COMMUNITY WARNING CRITICAL\r\n");
elseif( empty($host) || empty($community) )
  DisplayMessage(0, "Error, host and/or community is empty.\r\nUSAGE: check_terastation_storage.php HOST COMMUNITY WARNING CRITICAL\r\n");

// Test connection, SNMP availability, and valid Community.
GetSnmpObjValue($host, $community, 'iso.3.6.1.2.1.1.1.0');

// Get storage size in GB
$storageSize = GetSnmpObjValue($host, $community, 'iso.3.6.1.4.1.5227.27.1.3.1.3.1'); // Ex. INTEGER: 1234567890
$storageSize = GetSnmpObjValueInteger($storageSize);
if( $storageSize <= 0 )
  DisplayMessage(0, 'Unexpected value: '.$storageSize.' :: Storage size should be greater than 0. Possibly wrong OID for this device.');

// Get storage used (Percentage)
$storageUsedPcnt = GetSnmpObjValue($host, $community, 'iso.3.6.1.4.1.5227.27.1.3.1.4.1'); // Ex. INTEGER: 1234567890
$storageUsedPcnt = GetSnmpObjValueInteger($storageUsedPcnt);

//Some 1st grade math to find the percentage free...
$storageFreePcnt = round((100 - $storageUsedPcnt), 2);
//Some 6th grade math to find GB free...
$storageFree = round(($storageSize * ($storageFreePcnt/100)),0);
//A little more 1st grade maths to get GB used...
$storageUsed=($storageSize-$storageFree);

// SNMP returns GB, reduce to TB if necessary to make human friendly.
$storageSizeH = humanBytes($storageSize);
$storageUsedH = humanBytes($storageUsed);

//The meat of the script.
//If % is below supplied WARNING or CRITICAL levels, generate appropriate messages and exit codes.
//Else just return current values and exit 0.
if($storageFreePcnt <= $critical)
  DisplayMessage(2, 'Critical - Storage Usage - Total:'.$storageSizeH.' - Used:'.$storageUsedH.' - Free:'.$storageFreePcnt.'%');
elseif($storageFreePcnt <= $warning)
  DisplayMessage(1, 'Warning - Storage Usage - Total:'.$storageSizeH.' - Used:'.$storageUsedH.' - Free:'.$storageFreePcnt.'%');
else
  DisplayMessage(0, 'Storage Usage - Total:'.$storageSizeH.' - Used:'.$storageUsedH.' - Free:'.$storageFreePcnt.'%');



// Display message and exit with proper integer to trigger Nagios OK, Critical, Warning.
function DisplayMessage($exitInt, $exitMsg) {
  echo $exitMsg;
  exit($exitInt);
} // DisplayMessage()


// Connect and return object value.
// If the host doesn't respond to simple SNMP query, exit.
function GetSnmpObjValue($host, $community, $oid) {
  $ret = @snmpget($host, $community, $oid);
  if( $ret === false )
    DisplayMessage(2, 'Cannot reach host: '.$host.', community: '.$community.', OID: '.$oid.'. Possibly offline, SNMP is not enabled, COMMUNITY string is invalid, or wrong OID for this device.');
  return $ret;
} // GetSnmpObjValue()


// Check if returned SNMP object value is an integer, strip 'INTEGER: ' from it and return value.
function GetSnmpObjValueInteger($SnmpObjValue) {
  $ret = strstr($SnmpObjValue, 'INTEGER: ');
  if( $ret === false )
    DisplayMessage(0, 'Unexpected value: '.$ret.' :: Possibly wrong OID for this device.');
  list(,$ret) = explode(' ',$ret);
  return $ret;
} // GetSnmpObjValueInteger()


// SNMP returns GB, if greater than 1024 divide and append TB, else just append GB
function humanBytes($GB, $precision = 2) {
 if($GB >= 1024) {
  $GB=round($GB/1024,$precision).' TB';
 }else{
  $GB.=' GB';
 }
 return $GB;
}

?>
