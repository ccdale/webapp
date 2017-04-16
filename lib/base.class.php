<?php

/*
 * vim: set expandtab tabstop=4 shiftwidth=2 softtabstop=4 foldmethod=marker:
 * 
 * base.class.php
 *
 * Started: Friday 24 May 2013, 23:41:08
 * Last Modified: Sunday 16 April 2017, 10:11:50
 *
 * Copyright (c) 2016 Chris Allison chris.charles.allison+vh@gmail.com
 *
 * This file is part of villagehall.
 * 
 * villagehall is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * villagehall is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with villagehall.  If not, see <http://www.gnu.org/licenses/>.
 */

class Base
{
  protected $log=false;
  private $canlog=false;

  public function __construct($log=false)/*{{{*/
  {
    if($log && is_object($log) && (get_class($log)=="Logging" || get_class($log)=="LogFile")){
      $this->log=$log;
      $this->canlog=true;
    }
  }/*}}}*/
  public function __destruct()/*{{{*/
  {
  }/*}}}*/
  public function logg($msg,$level=LOG_INFO) /*{{{*/
  {
    if($this->canlog){
      $this->log->message($msg,$level);
    }
  }/*}}}*/
  public function ValidFile($fqf)/*{{{*/
  {
    $ret=false;
    if($this->ValidStr($fqf)){
      if(file_exists($fqf)){
        $ret=true;
      }
    }
    return $ret;
  }/*}}}*/
  public function ValidString($str)/*{{{*/
  {
    return $this->ValidStr($str);
  }/*}}}*/
  public function ValidStr($str) /*{{{*/
  {
    if(is_string($str)){
      return strlen($str);
    }else{
      return false;
    }
  }/*}}}*/
  public function ValidArray($arr)/*{{{*/
  {
    if(is_array($arr)){
      return count($arr);
    }else{
      return false;
    }
  }/*}}}*/
  public function hmsToSec($hms) // {{{
  {
    if($this->ValidStr($hms)){
      $i=0;
      if(strpos($hms,".")!==false){
        $tarr=explode(".",$hms);
        $ii=intval($tarr[1]);
        if($ii>499){
          $i=1;
        }
      }
      $arr=explode(":",$hms);
      $cn=0;
      if(is_array($arr) && (3==($cn=count($arr)))){
        return ($arr[0]*3600)+($arr[1]*60)+$arr[2]+$i;
      }elseif(2==$cn){
        return ($arr[0]*60)+$arr[1]+$i;
      }else{
        return "";
      }
    }else{
      return false;
    }
  } // }}}
  public function secToHMS($sec,$showdays=false) // {{{
  {
    $days=0;
    if($showdays){
      $days=intval($sec/86400);
      $sec=$sec%86400;
    }
    $hrs=intval($sec/3600);
    $rem=$sec%3600;
    $mins=intval($rem/60);
    $rem=$rem%60;
    if($days==1){
      $daysstring="day";
    }else{
      $daysstring="days";
    }
    if($showdays){
      $tmp=sprintf("%d $daysstring, %02d:%02d:%02d",$days,$hrs,$mins,$rem);
    }else{
      $tmp=sprintf("%02d:%02d:%02d",$hrs,$mins,$rem);
    }
    return $tmp;
  } // }}}
  public function secToHMSString($sec)/*{{{*/
  {
    $txt="";
    $hrs=intval($sec/3600);
    if($hrs>0){
      if($hrs>1){
        $txt=$hrs . " hours";
      }else{
        $txt=$hrs . " hour";
      }
    }
    $rem=$sec%3600;
    $mins=intval($rem/60);
    if($mins>0){
      $and=strlen($txt)?" and ":"";
      if($mins>1){
        $txt.=$and . $mins . " minutes";
      }else{
        $txt.=$and . $mins . " minute";
      }
    }
    return $txt;
    /*
    $txt="";
    $tstr=$this->secToHMS($sec,$showdays);
    if($showdays){
      $arr=explode(",",$tstr);
      $txt.=$arr[0] . ", ";
      $tstr=$arr[1];
    }
    $arr=explode(":",$tstr);
    $num=intval($arr[0]);
    $xtra=$num>1?"s":"";
    $txt.=$num . " hour" . $xtra;
    $num=intval($arr[1]);
    if($num>0){
      $xtra=$num>1?"s":"";
      $txt.=" and " . $num . " minute" . $xtra;
    }
    return $txt;
     */
  }/*}}}*/
  private function loghelper($msg,$level)/*{{{*/
  {
    if($level==LOG_DEBUG){
      $class=get_class($this);
      $msg="Class " . $class . ": " . $msg;
    }
    $this->logg($msg,$level);
  }/*}}}*/
  protected function info($msg) // {{{
  {
    $this->loghelper($msg,LOG_INFO);
  } // }}}
  protected function debug($msg) // {{{
  {
    $this->loghelper($msg,LOG_DEBUG);
  } // }}}
  protected function notice($msg)/*{{{*/
  {
    $this->loghelper($msg,LOG_NOTICE);
  }/*}}}*/
  protected function warn($msg) // {{{
  {
    $this->loghelper($msg,LOG_WARNING);
  } // }}}
  protected function warning($msg) // {{{
  {
    $this->loghelper($msg,LOG_WARNING);
  } // }}}
  protected function error($msg) // {{{
  {
    $this->loghelper($msg,LOG_ERR);
  } // }}}
  /** unixPath {{{
   * ensures that $path is in the correct unix format for a directory
   *
   * changes backslash "\" path identifiers to forwardslash (windows->unix)
   * adds a trailing backslash if necessary.
   * 
   * @param mixed $path 
   * @access public
   * @return string
   */
  public function unixPath($path)
  {
    $tpath=str_replace(chr(92),'/',$path);
    if(substr($tpath,-1)=="/"){
      return $path;
    }else{
      return $path . "/";
    }
  } /*}}}*/
  /* get and post variables */
  /** // {{{ funtion GP($var,$trusted=false)
   * returns the contents of the GET or POST variable
   * favours POST over GET
   */
  public function GP($var,$trusted=false)
  {
    $op=false;
    if(isset($_GET[$var])){
      $op=$_GET[$var];
    }
    if(isset($_POST[$var]))
    {
      $op=$_POST[$var];
    }
    if(is_string($op))
    {
      $op=trim(urldecode($op));
      if(!$trusted){
        $op=htmlspecialchars($op,ENT_QUOTES);
      }
      // $op=addslashes($op);
    }
    return $op;
  } // }}}
  /** // {{{ function getDefaultInt($var,$default)
   * returns the contents of the GET or POST variable
   * as an int, or the default value if it isn't set
   */
  public function getDefaultInt($var,$default)
  {
    $op=false;
    if(false!==($tmp=GP($var))){
      $tmp=intval($tmp);
      if($tmp>0){
        $op=$tmp;
      }else{
        $op=$default;
      }
    }else{
      $op=$default;
    }
    return $op;
  } // }}}
  /* // {{{ function GPA($namearr)
   * returns an array containing 
   * selected entries from the $_GET and $_POST arrays
   * the selected keys are in $namearr
   */
  public function GPA($namearr)
  {
    $arr=false;
    if(is_array($namearr) && count($namearr))
    {
      reset($namearr);
      while(list(,$v)=each($namearr))
      {
        $arr[$v]=GP($v);
      }
    }else{
      if(is_string($namearr))
      {
        $arr[$namearr]=GP($namearr);
      }
    }
    return $arr;
  } // }}}
  public function GPAll($trusted=false) // {{{
  {
    $op=array();
    $arr=array_merge($_GET,$_POST);
    foreach($arr as $key=>$val){
      if(is_string($val)){
        $op[$key]=trim(urldecode($val));
        if(!$trusted){
          $op[$key]=htmlspecialchars($op[$key],ENT_QUOTES);
        }
      }
    }
    return $op;
  } // }}}
  public function GPType($var,$type="string",$trusted=false)/*{{{*/
  {
    $op=GP($var,$trusted);
    switch($type){
    case "int":
      $op=intval($op);
      break;
    case "string":
      $op="$op";
    }
    return $op;
  }/*}}}*/
  /* php sessions */
  public function resetPHPSession()/*{{{*/
  {
    session_unset();
    session_destroy();
    session_start();
  }/*}}}*/
}
?>
