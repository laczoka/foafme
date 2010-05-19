<?php
//-----------------------------------------------------------------------------------------------------------------------------------
//
// Filename   : Logger.php
// Date       : 5th May 2010
// Version    : 0.1
//
// Copyright 2008-2010 foaf.me
//
// This program is free software: you can redistribute it and/or modify
// it under the terms of the GNU Affero General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU Affero General Public License for more details.
//
// You should have received a copy of the GNU Affero General Public License
// along with this program. If not, see <http://www.gnu.org/licenses/>.
//
// "Everything should be made as simple as possible, but no simpler."
// -- Albert Einstein
//
//-----------------------------------------------------------------------------------------------------------------------------------
/**
 * @author Laszlo Torok
 */
class Logger {
    const DEBUG_MODE = 'debug_mode';
    const DEBUG_OUT_DIR = 'debug_out_dir';
    
    private $enabled;
    private $directory_output;
    private $logfile;
    private $loghandle;
    private $lastmsgts = 0;
    private $messages = array();

    private static $instance = NULL;

    public function  __construct($dir_out = '/tmp/', $enabled = false) {
        $this->enabled = $enabled;
        $this->directory_output = $dir_out;
        $ts = time();
        $logfilebase = 'perflog';
        $this->logfile = $this->directory_output.$logfilebase.'.'.$ts.'.log';
        $this->loghandle = fopen($this->logfile, 'w');
    }
    public function logWithTs($message)
    {
        if ($this->enabled) {
            $ts = microtime(true);
            $time_elapsed = $this->lastmsgts ? ($ts - $this->lastmsgts) : 0 ;
            $this->lastmsgts = $ts;

            $this->messages[] = array(
                'text'=> $message,
                'ts' => $time_elapsed
                );
        }
    }

    public function  __destruct() {
        foreach ($this->messages as $msg) {
             fwrite($this->loghandle, $msg['text']."\t\t".((float)$msg['ts']*1000)." msec\n");
        }
        fclose($this->loghandle);
    }

    public static function getLogger() {
        if (NULL == self::$instance) {
            self::$instance = new Logger(
                         $GLOBALS['config'][self::DEBUG_OUT_DIR],
                         $GLOBALS['config'][self::DEBUG_MODE]);
        }
        return self::$instance;
    }

    private function diff_microtime($mt_old,$mt_new)
    {
        list($old_usec, $old_sec) = explode(' ',$mt_old);
        list($new_usec, $new_sec) = explode(' ',$mt_new);
        $old_mt = ((float)$old_usec + (float)$old_sec);
        $new_mt = ((float)$new_usec + (float)$new_sec);
        return $new_mt - $old_mt;
    }

}

class StackedLogger {
    const DEBUG_MODE = 'debug_mode';
    const DEBUG_OUT_DIR = 'debug_out_dir';

    const STARTOP = 1;
    const STOPOP = 0;
    const TABSTR = '   ';
    const TAB = 3;

    private static $instance = NULL;

    private $msg_stack = array();
    private $log = '';
    private $last_op;
    private $enabled;

    public function __construct($output_dir = '/tmp/', $enabled = false)
    {
        $this->enabled = $enabled;
        $this->output_dir = $output_dir;
        $ts = time();
        $logfilebase = $__SERVER['SERVER_NAME'] ? $__SERVER['SERVER_NAME'] : 'perflog';
        $this->logfile = $this->output_dir.$logfilebase.'.'.$ts.'.log';
    }

    public function start($message)
    {
        if ($this->enabled) {
            $timestamp = microtime(true);
            array_push($this->msg_stack, array($message,$timestamp));
            if (self::STARTOP == $this->last_op)
                    $this->log.= "\n";
            $this->log.= $this->indent.$message;
            $this->last_op = self::STARTOP;
            $this->indent.= self::TABSTR;
        }
    }

    public function stop()
    {
        if ($this->enabled) {
            $ts = microtime(true);

            $this->indent =
                substr($this->indent, 0, -(self::TAB));

            if (count($this->msg_stack) < 1)
                    return;
            $msg = array_pop($this->msg_stack);
            $delta = ((float)$ts - (float)$msg[1]);
            $this->log.= (self::STARTOP == $this->last_op) ?
                         ' '.$delta.' sec'."\n" :
                         $this->indent.$msg[0].' '.$delta.' sec'."\n";
            $this->last_op = self::STOPOP;
        }
    }

    public function  __destruct() {
        if ($this->enabled) {
            $loghandle = fopen($this->logfile, 'w');
            $this->log = $_SERVER['REQUEST_URI'].'\n\n'.$this->log;
            fwrite($loghandle, $this->log);
            fclose($loghandle);
        }
    }

    public static function getLogger() {
        if (NULL == self::$instance) {
            self::$instance = new StackedLogger(
                         $GLOBALS['config'][self::DEBUG_OUT_DIR],
                         $GLOBALS['config'][self::DEBUG_MODE]);
        }
        return self::$instance;
    }
}

?>
