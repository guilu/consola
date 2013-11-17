<?php
/**
 * Created by PhpStorm.
 * User: diegobarrioh
 * Date: 14/11/13
 * Time: 18:04
 */

namespace diegobarrioh\Console\Util;

/**
 * Ftp client using ftp extension.
 */
class Ftp
{
    private $ftpParameters  = array();
    private $ftpResource    = null;

    /**
     * @param array $parameters
     *
     * @throws \Exception
     */
    public function __construct(array $parameters = array())
    {
        if (!extension_loaded('ftp')) {
            throw new \Exception("PHP extension FTP is not loaded.");
        } else {
            $this->setFtpParameters($parameters);
        }
    }

    /**
     * @param array $ftpParameters
     */
    public function setFtpParameters(array $ftpParameters)
    {
        $this->ftpParameters = $ftpParameters;
    }

    /**
     * @return array
     */
    public function getFtpParameters()
    {
        return $this->ftpParameters;
    }

    /**
     * @return bool
     * @throws \RuntimeException
     */
    public function connectAndLogin()
    {
        if (!$this->isConnected()) {
            $ftpConnId  = @ftp_connect($this->getFtpHost());
            $loginResult = @ftp_login($ftpConnId, $this->getFtpUser(), $this->getFtpPass());

            if ((!$ftpConnId) || (!$loginResult)) {
                throw new \RuntimeException('Cannnot ftp_connect() or ftp_login() to '.$this->getFtpHost());
            }
            $this->ftpResource = $ftpConnId;
            ftp_pasv($this->ftpResource, true);

            return true;
        } else {
            return true;
        }
    }

    /**
     * @return bool
     */
    public function isConnected()
    {
        return is_resource($this->ftpResource);
    }

    /**
     * @return null
     */
    public function getFtpResource()
    {
        return $this->ftpResource;
    }

    /**
     * @return string
     */
    public function getFtpHost()
    {
        if (isset($this->ftpParameters['host'])) {
            return $this->ftpParameters['host'];
        } else {
            return '';
        }
    }

    /**
     * @param string $host
     */
    public function setFtpHost($host)
    {
        $this->ftpParameters['host'] = $host;
    }

    /**
     * @return string
     */
    public function getFtpUser()
    {
        if (isset($this->ftpParameters['user'])) {
            return $this->ftpParameters['user'];
        } else {
            return '';
        }
    }

    /**
     * @param string $user
     */
    public function setFtpUser($user)
    {
        $this->ftpParameters['user'] = $user;
    }

    /**
     * @return string
     */
    public function getFtpPass()
    {
        if (isset($this->ftpParameters['pass'])) {
            return $this->ftpParameters['pass'];
        } else {
            return '';
        }
    }

    /**
     * @param string $pass
     */
    public function setFtpPass($pass)
    {
        $this->ftpParameters['pass'] = $pass;
    }

    /**
     * @param string $url
     *
     * @return bool
     */
    public static function isValidFtpUrl($url)
    {
        if (0 !== strpos($url, 'ftp://')) {
            return false;
        }
        $parsedUrl = parse_url($url);
        if ($parsedUrl['scheme'] === 'ftp') {
            return true;
        }

        return false;
    }

    /**
     * @param string $dir
     */
    public function chDir($dir)
    {
        ftp_chdir($this->getFtpResource(), $dir);
    }

    /**
     * @param string $dir
     *
     * @return array
     */
    public function nList($dir)
    {
        return ftp_nlist($this->getFtpResource(), $dir);
    }

    /**
     * @param string $dir
     *
     * @return array
     */
    public function rawList($dir)
    {
        return ftp_rawlist($this->getFtpResource(), $dir);
    }

    /**
     * Descarga un fichero remoto a local.
     *
     * @param string $localFile
     * @param string $remoteFile
     *
     * @return bool
     */
    public function get($localFile, $remoteFile)
    {
        return ftp_get($this->getFtpResource(), $localFile, $remoteFile, FTP_BINARY);
    }

    /**
     * Obtiene los datos de un fichero. Admite descarga parcial.
     *
     * @param string $remoteFile
     * @param int    $mode
     * @param null   $resumePos
     *
     * @return bool|string
     */
    public function get_contents($remoteFile, $mode, $resumePos = null)
    {
        $pipes=stream_socket_pair(STREAM_PF_UNIX, STREAM_SOCK_STREAM, STREAM_IPPROTO_IP);
        if ($pipes === false) {
            return false;
        }
        if (!stream_set_blocking($pipes[1], 0)) {
            fclose($pipes[0]); fclose($pipes[1]);

            return false;
        }
        $fail=false;
        $data='';
        if (is_null($resumePos)) {
            $ret=ftp_nb_fget($this->getFtpResource(), $pipes[0], $remoteFile, $mode);
        } else {
            $ret=ftp_nb_fget($this->getFtpResource(), $pipes[0], $remoteFile, $mode, $resumePos);
        }
        while ($ret==FTP_MOREDATA) {
            while (!$fail && !feof($pipes[1])) {
                $r=fread($pipes[1], 8192);
                if ($r === '') {
                    break;
                }
                if ($r === false) {
                    $fail = true; break;
                }
                $data.=$r;
            }
            $ret=ftp_nb_continue($this->getFtpResource());
        }
        while (!$fail && !feof($pipes[1])) {
            $r = fread($pipes[1], 8192);
            if ($r === '') {
                break;
            }
            if ($r === false) {
                $fail=true; break;
            }
            $data.=$r;
        }
        fclose($pipes[0]);
        fclose($pipes[1]);
        if ($fail || $ret!=FTP_FINISHED) {
            return false;
        }

        return $data;
    }

    /**
     * Obtiene de la conexion ftp con el servidor el fichero pasado a str.
     * @param string $fichero
     *
     * @return string
     * @throws \Exception
     */
    public  function getStrContents($fichero)
    {
        $salida = null;
        if ($this->ftpResource) {
            try {
                $tempHandle = fopen('php://temp', 'r+');
                if (ftp_fget($this->ftpResource, $tempHandle, $fichero, FTP_ASCII, 0)) {
                    rewind($tempHandle);

                    return stream_get_contents($tempHandle);
                } else {
                    throw new \Exception(" error streaming file");
                }
            } catch (\Exception $e) {

                throw new \Exception(" error streaming file");
            }
        } else {

            throw new \Exception("there is no conexion...");
        }
    }

    /**
     * @param string $remoteFile
     *
     * @return bool
     */
    public function exists($remoteFile)
    {
        //hay servidores que no soportan size...
        if (ftp_size($this->getFtpResource(), $remoteFile)>-1) {
            //si con size lo encuentra ya está...
            return true;
        } else {
            //intentamos buscar el fichero en el listado del directorio
            $info = pathinfo($remoteFile);
            $haystack = $this->rawList($info["dirname"]);
            if (in_array($remoteFile, $haystack)) {
                return true;
            } else {
                return false;
            }
        }
    }

}