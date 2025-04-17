<?php
/*
 * DO NOT ALTER OR REMOVE COPYRIGHT NOTICES OR THIS HEADER.
 *
 * OPENSOURCE  CDDL 1.0 licensed
 *
 * Copyright (c) 2012 Andreas Rieck, for FAI WXC. All rights reserved.
 *
 * The contents of this file are subject to the terms
 * of the Common Development and Distribution License
 * (the License). You may not use this file except in
 * compliance with the License.
 *
 * You can obtain a copy of the License at
 * http://www.opensource.org/licenses/CDDL-1.0
 * See the License for the specific language governing
 * permission and limitations under the License.
 *
 * When distributing Covered Code, include this CDDL
 * Header Notice in each file
 * If applicable, add the following below the CDDL Header,
 * with the fields enclosed by brackets [] replaced by
 * your own identifying information:
 * "Portions Copyrighted [year] [name of copyright owner]"
*/

$tmpName = substr(str_shuffle(str_repeat('ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789',10)),0,10);
$tmpName .= ".igc";

if(!isset($_GET['file'])) {
  if(!isset($_POST['file'])) {
    // print usage and die
    echo "<html><head><title>leonardoVali wrapper</title></head><body>";
    echo '<font face="Arial" color="#666666" size="+1">';
    echo "This is a service wrapper for LeonardoXC to run IGC file validations against <b>http://vali.fai-civl.org</b>.<br><br>"; 
    echo "To use the script, it needs to be deployed at your own LeonardoXC Server.<br><br>";
    echo "You can also create another Virtual Server instance or subdomain, and let run the script outside from your LeonardoXC.<br><br>";
    echo "When the Script is deployed at your LeonardoXC (or another Webserver) and you can access it, then<br>";
    echo "Configure your LeonardoXC <i>./site/config_custom.php</i><br>";
    echo "<br>";
    echo "<i>Example Configuration:</i><br>";
    echo "</font>";
    echo "<pre>";
    echo "  // validate against the G-record \n";
    echo '  $CONF_use_validation=1;'; echo "\n";
    echo '  $CONF_use_custom_validation=0;'; echo "\n"; 
    echo '  $CONF[\'validation\'][\'user_internal_server\']=0;'; echo "\n";
    echo '  $CONF[\'validation\'][\'server_url\']="http://[yourserver.domain]/vali/leonardoVali.php";'; echo "\n";
    echo "\n";
    echo "</pre>"; 
    echo '<font face="Arial" color="#666666" size="+1">';
    echo "<br><i>Example calls for testing:</i><br>";
    echo "  <b>OK:</b> http://[yourserver.domain]/vali/leonardoVali.php?file=http://[yourserver.domain]/vali/sample1.igc <br>";
    echo "  <b>FAIL:</b> http://[yourserver.domain]/vali/leonardoVali.php?file=http://[yourserver.domain]/vali/sample2.igc <br>";
    echo "<br>";
    echo "<i>Example using curl command:</i><br>";
    echo "/usr/bin/curl -s http://[yourserver.domain]/vali/leonardoVali.php?file=http://[yourserver.domain]rg/vali/sample1.igc<br>";
    echo "<br>";
    echo 'The soure code for this wrapper can be downloaded <a href="http://vali.fai-civl.org/leonardoVali/leonardoVali.zip">here</a>.<br>';
    echo "<br>";
    echo "</font>";
    echo '<font face="Arial" color="#666666" size="+1">';
    echo "Setup:<br>";
    echo "You need to create a directory <i>/vali</i> at your webserver document root<br>"; 
    echo "and then a <i>/vali/cache</i> directory<br>";
    echo "and then copy the downloaded script into the <i>/vali</i> directory.<br>";
    echo "Your Webserver need write permissions for the <i>/vali/cache</i> directory.<br>";
    echo "Then test it, using curl command.<br>";
    echo "<br>";
    echo "Last step: configure the list of IP's who are allowed to download igc files.<br>";
    echo "The 2 IP's below, are the WXC Server needs, when your LeonardoXC is connected to the WXC.<br>";
    echo "</font>";
    echo "<pre>";
    echo '  $CONF[\'auth\'][\'download_igc\'][\'allowed_ips\']=array(\'78.46.202.91\',\'78.46.202.94\');';
    echo "</pre>";
    echo "<br>";
    echo "</body></html>";
    die();
  }
} 

if(isset($_GET['file'])) { // caller use _GET METHOD instead of _POST us the file
  $get_file = (get_magic_quotes_gpc()) ? $_GET['file'] : addslashes($_GET['file']);
  $urlParts = pathinfo($get_file);
  $get_file = $urlParts['dirname'].'/'.rawurlencode($urlParts['basename']); 
  $get_file_ext = strtolower(substr($get_file, -4));
  if($get_file_ext == ".igc") {
    if ($fpr = fopen($get_file, 'r')) { // copy file from remote URL into cache 
       $fpw = fopen('./cache/'.$tmpName, 'w');
       while ($line = fread($fpr, 1024)) {
          fwrite($fpw, $line);
       }
       fclose($fpr);
       fclose($fpw);
       if(file_exists('./cache/'.$tmpName)) {
         $result = igcCurl("./cache/".$tmpName);
         if($result[0] == "passed") {
           $ok = 1;
           $valProgram = $result[1];
         } else {
           $ok = 0;
         } 
       } else {
         $error = 1;
       }
    } else {
       // unable to open remote URL
       $error = 1;
    }
  } else {
    // not a igc file extension on URL
    $error = 1;
  }
} else {
 // assuming when _GET is not used, then file is maybe posted
 if (is_uploaded_file($_FILES['file']['tmp_name'])) {
   $get_file_ext = strtolower(substr($_FILES['file']['name'], -4));
   if($get_file_ext == ".igc") {
      move_uploaded_file($_FILES['file']['tmp_name'], "./cache/".$tmpName);
      $result = igcCurl("./cache/".$tmpName);
      if($result[0] == "passed") {
        $ok = 1;
        $valProgram = $result[1];
      } else {
        $ok = 0;
      }
   } else {
      $error = 1;
   }
 } else {
   $error = 1;
 }
}

if($error == 1) {
   echo "ERROR";
} else {
   // using same return values found within $LEO-INSTALL/server/validate/validate.php
   if ($ok) {
      echo "VALI:OK\n$valProgram";
   } else {
      echo "VALI:NOK";
   }
}

// cleanup
// note: the call below will cleanup the current known temp file only. 
// when the Service run into a timeout, the non validated igc files remain in cache for ever
unlink("./cache/".$tmpName);
echo "\n";

function igcCurl($file) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL,"vali.fai-civl.org/cgi-bin/vali.cgi");
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_VERBOSE, 0);

    // curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible;) WXC/leonardoVali");
    curl_setopt($ch, CURLOPT_URL, "http://vali.fai-civl.org/cgi-bin/vali.cgi");
    curl_setopt($ch, CURLOPT_TIMEOUT, 300); // times out after 30s
    $postvars = array(
       "nohtml"=>"yes",
       "igcfile"=>"@".$file
    );
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postvars);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $valiResponse = curl_exec($ch);
    curl_error($ch);
    $strFound = strpos($valiResponse, "IGC file OK");
    if($strFound === false) {
       // failed
       $ret = array( 0 => "failed", 1 => "" );
    } else {
       // passed
       // extract the validation exe file 
       $tmp = preg_split('/[()]/', $valiResponse);
       $tmp = preg_split('/[.]/', $tmp[1]);
       $ret = array( 0 => "passed", 1 => strtolower($tmp[0]));
    }
    return $ret;
}
?>
