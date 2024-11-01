<?php
/**
 * Quick disable in case of conflict/whitescreen without requiring SFTP access
 */

if(array_key_exists('disable', $_GET)){
  if(!strpos(basename(__DIR__), '-disabled')){
    rename (dirname(__FILE__), dirname(__FILE__).'-disabled');
    echo 'Plugin Disabled';
  }else{
    echo 'Already Disabled.';
  }
}

if(array_key_exists('enable', $_GET)){
  if(strpos(basename(__DIR__), '-disabled')){
    $dirname = str_replace('-disabled','',dirname(__FILE__));
    rename (dirname(__FILE__),$dirname);
    echo 'Plugin Enabled.';
  }else{
    echo 'Already Enabled.';
  }
}