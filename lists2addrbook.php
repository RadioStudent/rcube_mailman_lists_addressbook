#!/usr/bin/php
<?php
#
# prepare cache for Roundcube mailman_lists_addressbook plugin.
#
# list Mailman lists so they can be read as Roundcube address book via mailman_lists_addressbook plugin.
#
# Borut Mrak, 2014-08-23

#print dirname(__FILE__) . "\n";
# move to the main Roundcube directory and load Roundcube classes and configuration
#print getcwd() . "\n";
define("INSTALL_PATH", dirname(__FILE__) . "/../../");
chdir(dirname(__FILE__) . "/../..");
#print getcwd() . "\n";
require_once("program/include/iniset.php");
$config = rcmail::get_instance()->config;


# Get cache location
$cachefile = $config->get('mailman_lists_addressbook_cache',false);
if(!$cachefile) {
  print "Error! \$config['mailman_lists_addressbook_cache'] not set, not creating cache.\n";
  exit(1);
}


$ab = array();
$cnt = 1;

exec("/usr/sbin/list_lists -ab", $listnames);

foreach($listnames as $list) {
  $_tmpf = tempnam(sys_get_temp_dir(), "mailman_lists_addressbook.");
  exec("/usr/sbin/config_list -o '$_tmpf' $list");

  $cfgfile = fopen($_tmpf, 'r');
  unlink("$_tmpf");

  if($cfgfile) {
    while($buffer = fgets($cfgfile,2048)) {
      preg_match('/^real_name = \'(.*)\'/', $buffer, $matches) && $real_name = $matches[1];
      preg_match('/^host_name = \'(.*)\'/', $buffer, $matches) && $host_name = $matches[1];
      preg_match('/^description = \'(.*)\'/', $buffer, $matches) && $description = $matches[1];
    }
    if(!$description) $description = "mailing lista $real_name";
    $lc_real_name = strtolower($real_name);
    $email = $lc_real_name . "@" . $host_name;
    $surname = "mailing lista";
    $ab[] = array('ID' => $cnt++, 'email' => $email, 'name' => $description, 'firstname' => $real_name, 'surname' => 'mailing lista' );
  }
}

# write to temporary file & move to the final destination
$outtmp = fopen(tempnam(sys_get_temp_dir(), "mailman_lists_addressbook_cache."), 'w');
fwrite($outtmp, serialize($ab));
rename(stream_get_meta_data($outtmp)["uri"], $cachefile);
chmod($cachefile,0644);
chown($cachefile,'list');
chgrp($cachefile,'list');

