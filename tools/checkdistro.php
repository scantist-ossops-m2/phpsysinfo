<?php
echo "<!DOCTYPE html>";
echo "<head>";
echo "<meta charset=\"UTF-8\">";
echo "<title> </title>";
echo "</head>";
echo "<body>";

define('PSI_APP_ROOT', dirname(__FILE__).'/..');
define('PSI_DEBUG', false);
require_once PSI_APP_ROOT.'/includes/interface/class.PSI_Interface_OS.inc.php';
require_once PSI_APP_ROOT.'/includes/os/class.OS.inc.php';
require_once PSI_APP_ROOT.'/includes/to/class.System.inc.php';
require_once PSI_APP_ROOT.'/includes/os/class.Linux.inc.php';

$log_file = "";
$lsb = true; //enable detection lsb_release -a
$lsbfile = true; //enable detection /etc/lsb-release
$other = true; //enable other detection

class PSI_Error
{
    public static function singleton()
    {
    }
}

class CommonFunctions
{
    private static function _parse_log_file($string)
    {
        global $log_file;
        if (file_exists($log_file)) {
            $contents = @file_get_contents($log_file);
            $contents = preg_replace("/\r\n/", "\n", $contents);
            if ($contents && preg_match("/^\-\-\-\-\-\-\-\-\-\-".preg_quote($string, '/')."\-\-\-\-\-\-\-\-\-\-\n/m", $contents, $matches, PREG_OFFSET_CAPTURE)) {
                $findIndex = $matches[0][1];
                if (preg_match("/\n/m", $contents, $matches, PREG_OFFSET_CAPTURE, $findIndex)) {
                    $startIndex = $matches[0][1]+1;
                    if (preg_match("/^\-\-\-\-\-\-\-\-\-\-/m", $contents, $matches, PREG_OFFSET_CAPTURE, $startIndex)) {
                        $stopIndex = $matches[0][1];

                        return substr($contents, $startIndex, $stopIndex-$startIndex);
                    } else {
                        return substr($contents, $startIndex);
                    }
                }
            }
        }

        return false;
    }

    public static function rfts($strFileName, &$strRet, $intLines = 0, $intBytes = 4096, $booErrorRep = true)
    {
        global $lsbfile;
        global $other;
        if ($strFileName=="/etc/lsb-release") {
            $test = $lsbfile;
        } else {
            $test = $other;
        }
        if ($test) {
            $strRet=self::_parse_log_file($strFileName);
            if ($strRet && ($intLines == 1) && (strpos($strRet, "\n") !== false)) {
                $strRet=trim(substr($strRet, 0, strpos($strRet, "\n")));
            }

            return $strRet;
        } else {
            return false;
        }
    }

    public static function executeProgram($strProgramname, $strArgs, &$strBuffer, $booErrorRep = true, $timeout = 30)
    {
        global $lsb;
        global $other;
        $strBuffer = '';
        if ($strProgramname=='lsb_release') {
            return $lsb && ($strBuffer = self::_parse_log_file('lsb_release -a'));
        } else {
            return $other && ($strBuffer = self::_parse_log_file($strProgramname));
        }
    }

    public static function fileexists($strFileName)
    {
        global $log_file;
        global $lsbfile;
        global $other;
        if ($strFileName=="/etc/lsb-release") {
            $test = $lsbfile;
        } else {
            $test = $other;
        }

        return $test && file_exists($log_file) && ($contents = @file_get_contents($log_file)) && preg_match("/^\-\-\-\-\-\-\-\-\-\-".preg_quote($strFileName, '/')."\-\-\-\-\-\-\-\-\-\-\r?\n/m", $contents);
    }
}

class _Linux extends Linux
{
    public function build()
    {
        parent::_distro();
    }
}

$system = new _Linux('none');
$dirs = scandir(PSI_APP_ROOT.'/sample/distrotest');
if (($dirs !== false) && (count($dirs) > 0)) {
    $dirs = array_diff($dirs, array('.', '..'));
    if (($dirs !== false) && (count($dirs) > 0)) {
        natcasesort($dirs);
        echo "<table cellpadding=\"2\" border=\"1\"  CELLSPACING=\"0\">";
        echo "<tr>";
        echo "<td>Distrotest sample</td>";
        echo "<td>Distro Name</td>";
        echo "<td>Distro Icon</td>";
        echo "<td>Distro Name (no lsb_release)</td>";
        echo "<td>Distro Icon (no lsb_release)</td>";
        echo "<td>Distro Name (no lsb_release and no /etc/lsb-release)</td>";
        echo "<td>Distro Icon (no lsb_release and no /etc/lsb-release)</td>";
        echo "</tr>";
        foreach ($dirs as $entry) {
            $files = scandir(PSI_APP_ROOT."/sample/distrotest/$entry");
            if (($files !== false) && (count($files) > 0)) {
                $files = array_diff($files, array('.', '..'));
                if (($files !== false) && (count($files) > 0)) {
                    natcasesort($files);
                    foreach ($files as $sentry) {
                        $log_file=PSI_APP_ROOT.'/sample/distrotest/'.$entry.'/'.$sentry;
                        echo "<tr>";
                        echo "<td>".$entry.'/'.$sentry."</td>";

                        $lsb = true;
                        $lsbfile = true;
                        $sys=$system->getSys();
                        $distro=$sys->getDistribution();
                        $icon=$sys->getDistributionIcon();
                        if ($icon == '') $icon="unknown.png";
                        if ($icon != $entry.'.png')
                            echo "<td style='color:red'>";
                        else
                            echo "<td>";
                        echo $distro."</td>";
                        if ($icon != $entry.'.png')
                            echo "<td style='color:red'>";
                        else
                            echo "<td>";
                        echo "<img src=\"../gfx/images/".$icon."\" height=\"16\" width=\"16\"/>";
                        echo $icon."</td>";
                        $sys->setDistribution("");
                        $sys->setDistributionIcon("");

                        $lsb = false;
                        $lsbfile = true;
                        $sys=$system->getSys();
                        $distro=$sys->getDistribution();
                        $icon=$sys->getDistributionIcon();
                        if ($icon == '') $icon="unknown.png";
                        if ($icon != $entry.'.png')
                            echo "<td style='color:red'>";
                        else
                            echo "<td>";
                        echo $distro."</td>";
                        if ($icon != $entry.'.png')
                            echo "<td style='color:red'>";
                        else
                            echo "<td>";
                        echo "<img src=\"../gfx/images/".$icon."\" height=\"16\" width=\"16\"/>";
                        echo $icon."</td>";
                        $sys->setDistribution("");
                        $sys->setDistributionIcon("");

                        $lsb = false;
                        $lsbfile = false;
                        $sys=$system->getSys();
                        $distro=$sys->getDistribution();
                        $icon=$sys->getDistributionIcon();
                        if ($icon == '') $icon="unknown.png";
                        if ($icon != $entry.'.png')
                            echo "<td style='color:red'>";
                        else
                            echo "<td>";
                        echo $distro."</td>";
                        if ($icon != $entry.'.png')
                            echo "<td style='color:red'>";
                        else
                            echo "<td>";
                        echo "<img src=\"../gfx/images/".$icon."\" height=\"16\" width=\"16\"/>";
                        echo $icon."</td>";
                        $sys->setDistribution("");
                        $sys->setDistributionIcon("");

                        echo "</tr>";
                    }
                }
            }
        }
        echo "</table>";
    }
}
echo "</body>";
