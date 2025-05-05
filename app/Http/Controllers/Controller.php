<?php

namespace App\Http\Controllers;
use Session;
abstract class Controller
{
  public function h_flash($message,$level= 'info')
 {
   Session::flash('flash_message',$message);
   Session::flash('flash_message_level',$level);
 }
}
