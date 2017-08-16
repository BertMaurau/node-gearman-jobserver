<?php

$GearmanManager = new GearmanManager("10.32.91.27");
echo '<pre>';
print_r($GearmanManager -> getStatus());
echo '</pre>';
echo '<hr>';
echo '<pre>';
print_r($GearmanManager -> getWorkers());
echo '</pre>';
echo '<hr>';
echo '<pre>';
print_r($GearmanManager -> getVersion());
echo '</pre>';

class GearmanManager {

   protected $conn = null;
   protected $shutdown = false;

   static protected $multiByteSupport = null;

   public function __construct($server, $timeout = 5) {
      if (strpos($server, ':')) {
         list($host, $port) = explode(':', $server);
      } else {
         $host = $server;
         $port = 4730;
      }

      $errCode = 0;
      $errMsg = '';
      $this -> conn = @fsockopen($host, $port, $errCode, $errMsg, $timeout);
      if ($this -> conn === false) {
         die(print_r('Could not connect to ' . $host . ':' . $port));
      }
   }

   public function getVersion() {
      $this -> sendCommand('version');
      $res = fgets($this -> conn, 4096);
      $this -> checkForError($res);
      return trim($res);
   }
   public function getStatus() {
      $this -> sendCommand('status');
      $res = $this -> recvCommand();

      $status = array();
      $tmp = explode("\n", $res);
      foreach ($tmp as $t) {
         if (!$this -> stringLength($t)) {
            continue;
         }

         list($func, $inQueue, $jobsRunning, $capable) = explode("\t", $t);

         $status[$func] = array('in_queue' => $inQueue, 'jobs_running' => $jobsRunning, 'capable_workers' => $capable);
      }

      return $status;
   }
   public function getWorkers() {
      $this -> sendCommand('workers');
      $res = $this -> recvCommand();
      $workers = array();
      $tmp = explode("\n", $res);
      foreach ($tmp as $t) {
         if (!$this -> stringLength($t)) {
            continue;
         }

         // Abilities might be empty ; prevent PHP Notice if they are.
         $parts = explode(":", $t);
         $info = isset($parts[0]) ? trim($parts[0]) : "";
         $abilities = isset($parts[1]) ? trim($parts[1]) : "";

         list($fd, $ip, $id) = explode(' ', $info);

         $workers[] = array('fd' => $fd, 'ip' => $ip, 'id' => $id, 'abilities' => empty($abilities) ? array() : explode(' ', $abilities));
      }

      return $workers;
   }

   protected function sendCommand($cmd) {
      if ($this -> shutdown) {
         die(print_r('This server has been shut down'));
      }

      fwrite($this -> conn, $cmd . "\r\n", $this -> stringLength($cmd . "\r\n"));
   }

   protected function recvCommand() {
      $ret = '';
      while (true) {
         $data = fgets($this -> conn, 4096);
         $this -> checkForError($data);
         if ($data == ".\n") {
            break;
         }

         $ret .= $data;
      }

      return $ret;
   }

   protected function checkForError($data) {
      $data = trim($data);
      if (preg_match('/^ERR/', $data)) {
         list(, $code, $msg) = explode(' ', $data);
         die(print_r($msg.urldecode($code)));
      }
   }
   public function disconnect() {
      if (is_resource($this -> conn)) {
         fclose($this -> conn);
      }
   }

   public function __destruct() {
      $this -> disconnect();
   }

   static public function stringLength($value)
    {
        if (is_null(self::$multiByteSupport)) {
            self::$multiByteSupport = intval(ini_get('mbstring.func_overload'));
        }

        if (self::$multiByteSupport & 2) {
            return mb_strlen($value, '8bit');
        }
        return strlen($value);
    }

}
?>