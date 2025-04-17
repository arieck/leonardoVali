# leonardoVali
 Service wrapper for IGC file validation at LeonardoXC

This is a service wrapper for LeonardoXC to run IGC file validations against http://vali.fai-civl.org.

To use the script, it needs to be deployed at your own LeonardoXC Server.

You can also create another Virtual Server instance or subdomain, and let run the script outside from your LeonardoXC.

When the Script is deployed at your LeonardoXC (or another Webserver) and you can access it, then
Configure your LeonardoXC ./site/config_custom.php

Example Configuration:

// validate against the G-record

$CONF_use_validation=1;

$CONF_use_custom_validation=0;

$CONF['validation']['user_internal_server']=0;

$CONF['validation']['server_url']="http://[yourserver.domain]/vali/leonardoVali.php";

Example calls for testing:
OK: http://[yourserver.domain]/vali/leonardoVali.php?file=http://[yourserver.domain]/vali/sample1.igc
FAIL: http://[yourserver.domain]/vali/leonardoVali.php?file=http://[yourserver.domain]/vali/sample2.igc

Example using curl command:
/usr/bin/curl -s http://[yourserver.domain]/vali/leonardoVali.php?file=http://[yourserver.domain]rg/vali/sample1.igc

The soure code for this wrapper can be downloaded here.

Setup:
You need to create a directory /vali at your webserver document root
and then a /vali/cache directory
and then copy the downloaded script into the /vali directory.
Your Webserver need write permissions for the /vali/cache directory.
Then test it, using curl command.

Last step: configure the list of IP's who are allowed to download igc files.
The 2 IP's below, are the WXC Server needs, when your LeonardoXC is connected to the WXC.

  $CONF['auth']['download_igc']['allowed_ips']=array('78.46.202.91','78.46.202.94');


