<?php
/*
+----------------------------------------------+
|                                              |
|         PHP apache log parser class          |
|                                              |
+----------------------------------------------+
| Filename   : apache-log-parser.php           |
| Created    : 21-Sep-05 23:28 GMT             |
| Created By : Sam Clarke                      |
| Email      : admin@free-webmaster-help.com   |
| Version    : 1.0                             |
|                                              |
+----------------------------------------------+


LICENSE

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License (GPL)
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

To read the license please visit http://www.gnu.org/copyleft/gpl.html

*/

class ApacheLogParser
{

  var $bad_rows; // Number of bad rows
  var $fp; // File pointer

  function format_log_line($line)
  {
    //preg_match("/^(\S+) (\S+) (\S+) \[([^:]+):(\d+:\d+:\d+) ([^\]]+)\] \"(\S+) (.*?) (\S+)\" (\S+) (\S+)/", $line, $matches); // pattern to format the line
    preg_match("/^(\S+) (\S+) (\S+) \[([^:]+):(\d+:\d+:\d+) ([^\]]+)\] \"(\S+) (.*?) (\S+)\" (\S+) (\S+) \"(.*)\" \"(.*)\"/", $line, $matches); // pattern to format the line
    return $matches;
  }

  function format_line($line)
  {
    $logs = $this->format_log_line($line); // format the line

    if (isset($logs[0])) // check that it formated OK
    {
      $formated_log = array(); // make an array to store the lin info in
      $formated_log['ip'] = $logs[1];
      $formated_log['identity'] = $logs[2];
      $formated_log['user'] = $logs[2];
      $formated_log['date'] = $logs[4];
      $formated_log['time'] = $logs[5];
      $formated_log['timezone'] = $logs[6];
      $formated_log['method'] = $logs[7];
      $formated_log['path'] = $logs[8];
      $formated_log['protocol'] = $logs[9];
      $formated_log['status'] = $logs[10];
      $formated_log['bytes'] = $logs[11];
      $formated_log['referer'] = $logs[12];
      $formated_log['agent'] = $logs[13];
      return $formated_log; // return the array of info
    }
    else
    {
      $this->badRows++; // if the row is not in the right format add it to the bad rows
      return false;
    }
  }

  function open_log_file($file_name)
  {
    $this->fp = fopen($file_name, 'r'); // open the file
    if (!$this->fp)
    {
      return false; // return false on fail
    }
    return true; // return true on sucsess
  }

  function close_log_file()
  {
    return fclose($this->fp); // close the file
  }

  // gets a line from the log file
  function get_line()
  {
    return fgets($this->fp);
  }

}
?>
