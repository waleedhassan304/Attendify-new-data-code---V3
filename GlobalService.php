<?php

namespace Learner\GlobalObjects;

use Doctrine\ORM\EntityManager;
use Learner\AppBundle\Entity\LmsBranding;
use Learner\AppBundle\Entity\LmsSettingsElements;

use Learner\GlobalModels\GlobalSettingsModel;


class GlobalService
{

    private static $instance;
    public $base_path;
    public $base_url;
    public $base_email;
    public $display_footer_email;
    public $http_link;
    public $learner_landing_page;
    public $brand_obj;
    public $year;
    public $dt;
    public $tm;
    public $ts;
    public $lock_upon_completion = 'not_being_used';
    public $PHPSesObj;
    protected $Conn = NULL;
    protected $DoctMgr = NULL;
    protected $localserver;

    public function __construct($obj = NULL, $DocMgr = NULL)
    {

        $this->Conn = $obj;
        $this->DoctMgr = $DocMgr;
        $this->localserver  =   "localhost:8888/";
    }

    public function setDoctrine($obj = NULL, $DocMgr = NULL)
    {

        $this->Conn = $obj;
        $this->DoctMgr = $DocMgr;
    }

    public static function singletonObject()
    {
        if (!isset(self::$instance)) {
            $object = __CLASS__;
            self::$instance = new $object;
        }
        return self::$instance;
    }

    public function setClassVaribales($var = NULL)
    {

        if (!empty($var['PHPSesObj'])) {
            $this->PHPSesObj = $var['PHPSesObj'];
        }
    }

    public function defaultRedirect()
    {

        $redirect = NULL;
        if ($_SERVER['REMOTE_ADDR'] == "127.0.0.1" || $_SERVER['REMOTE_ADDR'] == "::1") {
            $redirect = "http://" . $this->localserver; //"http://localhost/boostsf/web/app_dev.php/";
        } else {
            $server = explode(".", $_SERVER['HTTP_HOST']);
            $sub_domain = $server[0];
            $redirect = "http://{$sub_domain}.{$server[1]}.{$server[2]}/";
            $redirect = "http://{$_SERVER['HTTP_HOST']}/";
        }
        return $redirect;
    }

    public function CheckUserSession($std, $expire_after = 30)
    {

        if (empty($std)) {

            $redirect = $this->logout_landing_page();
            if (!empty($redirect)) {
                echo "<script> window.location='{$redirect}'; </script>";
            }

            die("Session expired");
        }

        if (!empty($_SESSION['ses_time_1'])) {
            $inactive = round(abs($_SESSION['ses_time_1'] - time()) / 60, 0);

            if ($inactive < $expire_after) {
                $_SESSION['ses_time_1'] = $_SESSION['ses_time_2'] = time();
            }
        }
    }


    public function RedirectToLoginPage()
    {

        $redirect = $this->logout_landing_page();
        if (!empty($redirect)) {
            echo "<script> window.location='{$redirect}'; </script>";
        }
        die("Session expired");
    }


    public function generateHash($password = NULL)
    {

        $return = false;

        if (!empty($password)) {
            $cost = 10;
            $salt = strtr(base64_encode(mcrypt_create_iv(16, MCRYPT_DEV_URANDOM)), '+', '.');
            $salt = sprintf("$2a$%02d$", $cost) . $salt;
            $hash = crypt($password, $salt);
            return $hash;
        }

        return $return;
    }

    public function validateHash($password = NULL, $hash = NULL)
    {

        if (!$password || !$hash) {
            return false;
        }

        if ($hash == crypt($password, $hash)) {
            return true;
        } else {
            return false;
        }
    }

    public function logout_landing_page()
    {

        $return = $this->defaultRedirect();

        $sql = "SELECT * FROM lms_settings_elements ";
        $sql .= "WHERE set_elm_name='logout-page' ";
        $tmp = $this->Conn->fetchAll($sql);
        if (!empty($tmp)) {
            $return = $tmp[0]['set_elm_val'];
        }

        return $return;
    }

    public function createTmpFile($data = "Nothing", $path = null, $tmpFile = "zz")
    {

        if (!empty($path)) {
            $fh = fopen($path . $tmpFile, 'w');
            fwrite($fh, $data);
            fclose($fh);
        } else {
            $myFile = "zz.txt";
            $fh = fopen($myFile, 'w');
            fwrite($fh, $data);
            fclose($fh);
        }
    }

    /*
      public function courseExpiryDays($key='course_expiry_days'){

      $return = NULL;
      $sql = "SELECT * FROM lms_settings_elements ";
      $sql.= "WHERE set_elm_name='{$key}' ";

      $tmp = $this->Conn->fetchAll( $sql );
      if( !empty($tmp) ){
      $return = $tmp[0]['set_elm_val'];
      }

      return $return;

      }
     */

    public function changeMemoryLimit($memory = '512M')
    {
        ini_set('memory_limit', $memory);
    }


    public function isMobile()
    {
        return preg_match("/(android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|mini|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i", $_SERVER["HTTP_USER_AGENT"]);
    }

    public function getBrowser()
    {

        $u_agent = $_SERVER['HTTP_USER_AGENT'];
        $bname = 'Unknown';
        $platform = 'Unknown';
        $version = "";

        //First get the platform?
        if (preg_match('/linux/i', $u_agent)) {
            $platform = 'linux';
        } elseif (preg_match('/macintosh|mac os x/i', $u_agent)) {
            $platform = 'mac';
        } elseif (preg_match('/windows|win32/i', $u_agent)) {
            $platform = 'windows';
        }

        // Next get the name of the useragent yes seperately and for good reason
        if (preg_match('/MSIE/i', $u_agent) && !preg_match('/Opera/i', $u_agent)) {
            $bname = 'Internet Explorer';
            $ub = "MSIE";
        } elseif (preg_match('/Firefox/i', $u_agent)) {
            $bname = 'Mozilla Firefox';
            $ub = "Firefox";
        } elseif (preg_match('/Chrome/i', $u_agent)) {
            $bname = 'Google Chrome';
            $ub = "Chrome";
        } elseif (preg_match('/Safari/i', $u_agent)) {
            $bname = 'Apple Safari';
            $ub = "Safari";
        } elseif (preg_match('/Opera/i', $u_agent)) {
            $bname = 'Opera';
            $ub = "Opera";
        } elseif (preg_match('/Netscape/i', $u_agent)) {
            $bname = 'Netscape';
            $ub = "Netscape";
        } else {
            $ub = "not found";
        }

        // finally get the correct version number
        $known = array('Version', $ub, 'other');
        $pattern = '#(?<browser>' . join('|', $known) .
            ')[/ ]+(?<version>[0-9.|a-zA-Z.]*)#';
        if (!preg_match_all($pattern, $u_agent, $matches)) {
            // we have no matching number just continue
        }

        // see how many we have
        $i = count($matches['browser']);
        if ($i != 1) {
            //we will have two since we are not using 'other' argument yet
            //see if version is before or after the name
            if (strripos($u_agent, "Version") < strripos($u_agent, $ub)) {
                $version = $matches['version'][0];
            } else {
                //$version = $matches['version'][1];
                $version = 1;
            }
        } else {
            $version = $matches['version'][0];
        }

        // check if we have a number
        if ($version == null || $version == "") {
            $version = "?";
        }

        return array(
            'userAgent' => $u_agent,
            'name' => $bname,
            'version' => $version,
            'platform' => $platform,
            'pattern' => $pattern
        );
    }

    public function getGlobalVars($flag = NULL)
    {

        $global_var = NULL;

        $tmp = NULL;
        $global_var['baseEmail'] = "support@redwoodelearning.com";
        $sql = "SELECT * FROM lms_brand_emails WHERE brnd_key='brand_email' ";
        $tmp = $this->Conn->fetchAll($sql);
        if (!empty($tmp)) {
            $global_var['baseEmail'] = $tmp[0]['brnd_val'];
        }

        $global_var['baseEmailName'] = "support";
        $sql = "SELECT * FROM lms_brand_emails WHERE brnd_key='brand_email_name' ";
        $tmp = $this->Conn->fetchAll($sql);
        if (!empty($tmp)) {
            $global_var['baseEmailName'] = $tmp[0]['brnd_val'];
        }

        $tmp = NULL;
        $global_var['footerEmailDisplay'] = "show";
        $sql = "SELECT * FROM lms_settings_elements WHERE set_elm_name='footer_email_display' ";
        $tmp = $this->Conn->fetchAll($sql);
        if (!empty($tmp)) {
            $global_var['footerEmailDisplay'] = $tmp[0]['set_elm_val'];
        }


        $tmp = NULL;
        $global_var['httpLink'] = 'http';
        $sql = "SELECT * FROM lms_settings_elements WHERE set_elm_name='http_link' ";
        $tmp = $this->Conn->fetchAll($sql);
        if (!empty($tmp)) {
            $global_var['httpLink'] = $tmp[0]['set_elm_val'];
        }


        $tmp = NULL;
        $global_var['landingPage'] = "learner-dashboard";
        $sql = "SELECT * FROM lms_settings_elements ";
        $sql .= "WHERE set_elm_name='learner_landing_page' AND set_elm_status='active' ";
        $tmp = $this->Conn->fetchAll($sql);
        if (!empty($tmp)) {
            $global_var['landingPage'] = $tmp[0]['set_elm_val'];
        }

        $tmp = NULL;
        $global_var['landingPage'] = "learner-dashboard";
        $sql = "SELECT * FROM lms_settings_elements ";
        $sql .= "WHERE set_elm_name='learner_landing_page_mobile' AND set_elm_status='active' ";
        $tmp = $this->Conn->fetchAll($sql);
        if (!empty($tmp)) {
            $global_var['landingPageMobile'] = $tmp[0]['set_elm_val'];
        }

        $tmp = NULL;
        $global_var['ssoRedirect'] = "learner-dashboard";
        $sql = "SELECT * FROM lms_settings_elements ";
        $sql .= "WHERE set_elm_name='sso_redirect' AND set_elm_status='active' ";
        $tmp = $this->Conn->fetchAll($sql);
        if (!empty($tmp)) {
            $global_var['ssoRedirect'] = $tmp[0]['set_elm_val'];
        }

        $tmp = NULL;
        $global_var['termsPage'] = 'ok';
        $sql = "SELECT * FROM lms_settings_elements ";
        $sql .= "WHERE set_elm_name='terms_page' AND set_elm_status='active' ";
        $tmp = $this->Conn->fetchAll($sql);
        if (!empty($tmp)) {
            $global_var['termsPage'] = TRUE;
        }


        $tmp = NULL;
        $global_var['brand_data'] = NULL;
        $sql = "SELECT * FROM lms_branding ";
        $tmp = $this->Conn->fetchAll($sql);
        if ($tmp) {
            foreach ($tmp as $t) {
                $global_var['brand_data'][$t['brnd_key']] = $t['brnd_val'];
            }
        }


        $tmp = NULL;
        $global_var['learner_change_pwd'] = 'active';
        $sql = "SELECT * FROM lms_settings_elements WHERE set_elm_name='learner_change_pwd' ";
        $tmp = $this->Conn->fetchAll($sql);
        if (!empty($tmp)) {
            $global_var['learner_change_pwd'] = $tmp[0]['set_elm_status'];
        }


        if ($_SERVER['REMOTE_ADDR'] == "127.0.0.1" || $_SERVER['REMOTE_ADDR'] == "::1") {

            $global_var['sesRoot'] = "{$global_var['httpLink']}://" . $this->localserver;
            $global_var['Root'] = $global_var['sesRoot'];
            $global_var['sesRootPath'] = "{$global_var['httpLink']}://localhost:81/vibe/boostLMS/boostsf/";
            $global_var['RootPath'] = $global_var['sesRootPath'];
            $global_var['sesSubDomain'] = "localhost/cmha";
            $global_var['sub_domain'] = "localhost/cmha";
        } else {

            $server = explode(".", $_SERVER['HTTP_HOST']);
            $GlbSetObj = new GlobalSettingsModel($this->Conn, $this->DoctMgr);
            $tmp_res   = $GlbSetObj->getKeyVal('account_type');


            if (!empty($tmp_res['dataSet']) && $tmp_res['dataSet'] == 'full-domain') {
                $sub_domain = $domain_name = $server[0];
                /*
              $session->set("SesRoot", "{$globalVars['httpLink']}://{$domain_name}.{$server[1]}/");
              $session->set("SesRootPath", "{$globalVars['httpLink']}://{$domain_name}.{$server[1]}/");
              $session->set("SesSubDomain", $domain_name);
              */

                /*
              $global_var['sesRoot'] = "{$global_var['httpLink']}://{$domain_name}.{$server[1]}/";
              $global_var['sesRootPath'] = "{$global_var['httpLink']}://{$domain_name}.{$server[1]}/";
              $global_var['RootPath'] = $global_var['sesRootPath'];
              $global_var['sesSubDomain'] = $sub_domain;
              $global_var['sub_domain'] = $sub_domain;
              */

                $global_var['sesRoot'] = "{$global_var['httpLink']}://{$_SERVER['HTTP_HOST']}/";
                $global_var['sesRootPath'] = "{$global_var['httpLink']}://{$_SERVER['HTTP_HOST']}/";
                $global_var['RootPath'] = $global_var['sesRootPath'];
                $global_var['sesSubDomain'] = $sub_domain;
                $global_var['sub_domain'] = $sub_domain;
            } else {

                $server = explode(".", $_SERVER['HTTP_HOST']);
                $sub_domain = $server[0];
                /*
              $session->set("SesRoot", "{$globalVars['httpLink']}://{$sub_domain}.{$server[1]}.{$server[2]}/");
              $session->set("SesRootPath", "{$globalVars['httpLink']}://{$sub_domain}.{$server[1]}.{$server[2]}/");
              $session->set("SesSubDomain", $sub_domain);
              */

                $global_var['sesRoot'] = "{$global_var['httpLink']}://{$_SERVER['HTTP_HOST']}/";
                $global_var['sesRootPath'] = "{$global_var['httpLink']}://{$_SERVER['HTTP_HOST']}/";
                $global_var['RootPath'] = $global_var['sesRootPath'];
                $global_var['sesSubDomain'] = $sub_domain;
                $global_var['sub_domain'] = $sub_domain;
            }
        }


        if ($flag == "live") {
            $server = explode(".", $_SERVER['HTTP_HOST']);
            $sub_domain = $server[0];

            if (!empty($tmp_res['dataSet']) && $tmp_res['dataSet'] == 'full-domain') {

                /*
             $global_var['sesRoot'] = "{$global_var['httpLink']}://{$domain_name}.{$server[1]}/";
             $global_var['sesRootPath'] = "{$global_var['httpLink']}://{$domain_name}.{$server[1]}/";
             $global_var['RootPath'] = $global_var['sesRootPath'];
             */
                $global_var['sesRoot'] = "{$global_var['httpLink']}://{$_SERVER['HTTP_HOST']}/";
                $global_var['sesRootPath'] = "{$global_var['httpLink']}://{$_SERVER['HTTP_HOST']}/";
                $global_var['RootPath'] = $global_var['sesRootPath'];

                $global_var['sesSubDomain'] = $sub_domain;
                $global_var['sub_domain'] = $sub_domain;
            } else {

                /*
             $global_var['sesRoot'] = "{$global_var['httpLink']}://{$sub_domain}.{$server[1]}.{$server[2]}/";
             $global_var['sesRootPath'] = "{$global_var['httpLink']}://{$sub_domain}.{$server[1]}.{$server[2]}/";
             $global_var['RootPath'] = $global_var['sesRootPath'];
             $global_var['sesSubDomain'] = $sub_domain;
             $global_var['sub_domain'] = $sub_domain;
             */

                $global_var['sesRoot'] = "{$global_var['httpLink']}://{$_SERVER['HTTP_HOST']}/";
                $global_var['sesRootPath'] = "{$global_var['httpLink']}://{$_SERVER['HTTP_HOST']}/";
                $global_var['RootPath'] = $global_var['sesRootPath'];
                $global_var['sesSubDomain'] = $sub_domain;
                $global_var['sub_domain'] = $sub_domain;
            }
        }


        $tmp = NULL;
        $global_var['logoutPage'] = $global_var['sesRoot'];
        $sql = "SELECT * FROM lms_settings_elements ";
        $sql .= "WHERE set_elm_name='logout-page' AND set_elm_status='active' ";
        $tmp = $this->Conn->fetchAll($sql);
        if (!empty($tmp)) {
            $global_var['logoutPage'] = $tmp[0]['set_elm_val'];
        }



        return $global_var;
    }










    public static function getGlobalVarsStatic($flag = NULL)
    {

        $global_var = NULL;

        $tmp = NULL;
        $global_var['baseEmail'] = "support@redwoodelearning.com";
        $sql = "SELECT * FROM lms_brand_emails WHERE brnd_key='brand_email' ";
        $tmp = self::$StConn->fetchAll($sql);
        if (!empty($tmp)) {
            $global_var['baseEmail'] = $tmp[0]['brnd_val'];
        }


        $tmp = NULL;
        $global_var['footerEmailDisplay'] = "show";
        $sql = "SELECT * FROM lms_settings_elements WHERE set_elm_name='footer_email_display' ";
        $tmp = self::$StConn->fetchAll($sql);
        if (!empty($tmp)) {
            $global_var['footerEmailDisplay'] = $tmp[0]['set_elm_val'];
        }


        $tmp = NULL;
        $global_var['httpLink'] = 'http';
        $sql = "SELECT * FROM lms_settings_elements WHERE set_elm_name='http_link' ";
        $tmp = self::$StConn->fetchAll($sql);
        if (!empty($tmp)) {
            $global_var['httpLink'] = $tmp[0]['set_elm_val'];
        }


        $tmp = NULL;
        $global_var['landingPage'] = "learner-dashboard";
        $sql = "SELECT * FROM lms_settings_elements ";
        $sql .= "WHERE set_elm_name='learner_landing_page' AND set_elm_status='active' ";
        $tmp = self::$StConn->fetchAll($sql);
        if (!empty($tmp)) {
            $global_var['landingPage'] = $tmp[0]['set_elm_val'];
        }

        $tmp = NULL;
        $global_var['landingPage'] = "learner-dashboard";
        $sql = "SELECT * FROM lms_settings_elements ";
        $sql .= "WHERE set_elm_name='learner_landing_page_mobile' AND set_elm_status='active' ";
        $tmp = self::$StConn->fetchAll($sql);
        if (!empty($tmp)) {
            $global_var['landingPageMobile'] = $tmp[0]['set_elm_val'];
        }

        if ($_SERVER['REMOTE_ADDR'] == "127.0.0.1" || $_SERVER['REMOTE_ADDR'] == "::1") {

            $global_var['sesRoot'] = "{$global_var['httpLink']}://" . $this->localserver;
            $global_var['Root'] = $global_var['sesRoot'];
            $global_var['sesRootPath'] = "{$global_var['httpLink']}://localhost/boostsf/";
            $global_var['RootPath'] = $global_var['sesRootPath'];
            $global_var['sesSubDomain'] = "localhost";
        } else {

            $server = explode(".", $_SERVER['HTTP_HOST']);
            $sub_domain = $server[0];
            /*
            $global_var['sesRoot'] = "{$global_var['httpLink']}://{$sub_domain}.{$server[1]}.{$server[2]}/";
            $global_var['sesRootPath'] = "{$global_var['httpLink']}://{$sub_domain}.{$server[1]}.{$server[2]}/";
            $global_var['RootPath'] = $global_var['sesRootPath'];
            */
            $global_var['sesRoot'] = "{$global_var['httpLink']}://{$_SERVER['HTTP_HOST']}/";
            $global_var['sesRootPath'] = "{$global_var['httpLink']}://{$_SERVER['HTTP_HOST']}/";
            $global_var['RootPath'] = $global_var['sesRootPath'];
            $global_var['sesSubDomain'] = $sub_domain;
        }


        if ($flag == "live") {
            $server = explode(".", $_SERVER['HTTP_HOST']);
            $sub_domain = $server[0];
            /*
            $global_var['sesRoot'] = "{$global_var['httpLink']}://{$sub_domain}.{$server[1]}.{$server[2]}/";
            $global_var['sesRootPath'] = "{$global_var['httpLink']}://{$sub_domain}.{$server[1]}.{$server[2]}/";
            $global_var['RootPath'] = $global_var['sesRootPath'];
            $global_var['sesSubDomain'] = $sub_domain;
            */

            $global_var['sesRoot'] = "{$global_var['httpLink']}://{$_SERVER['HTTP_HOST']}/";
            $global_var['sesRootPath'] = "{$global_var['httpLink']}://{$_SERVER['HTTP_HOST']}/";
            $global_var['RootPath'] = $global_var['sesRootPath'];
            $global_var['sesSubDomain'] = $sub_domain;
        }


        return $global_var;
    }




    public function getSettingElements($cols = null)
    {

        $return = NULL;

        if (!empty($cols)) {

            /*
              $sql    = "SELECT * FROM lms_settings_elements WHERE set_elm_name=?";
              $parms  = array('learning_path_columns');
              $return = $this->Conn->executeQuery($sql, $parms)->fetchAll()[0];
             */

            $query = $this->Conn->createQueryBuilder();
            $query->select('settings.set_elm_name', 'settings.set_elm_val')
                ->from('lms_settings_elements', 'settings')
                ->where('settings.set_elm_name = :id')
                ->setParameter(':id', $cols);

            $return = $query->execute()->fetchAll();
            if (!empty($return)) {
                $return = $return[0]['set_elm_val'];
                $return = explode(",", $return);
            }
        }

        return $return;
    }


    public function array_utf8_encode($dat)
    {

        if (is_string($dat))
            return utf8_encode($dat);
        if (!is_array($dat))
            return $dat;
        $ret = array();
        foreach ($dat as $i => $d)
            $ret[$i] = self::array_utf8_encode($d);
        return $ret;
    }
}
