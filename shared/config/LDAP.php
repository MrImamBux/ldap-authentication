<?php
/**
 * Created by PhpStorm.
 * User: imam.bux
 * Date: 30.04.18
 * Time: 10:20
 */

class LDAP {
    // Holds an instance of itself
    private static $instance;

    // Holds LDAP attributes to connect LDAP Server
    private $host;
    private $port;
    private $version;
    private $use_ssl;
    private $use_start_tls;

    // Holds LDAP connection
    private $connection;

    /**
     * The only method to create an instance of this LDAP. Yes! This class uses singleton pattern
     * @param string $host
     * @param int $port
     * @param int $version
     * @param bool $use_ssl
     * @param bool $use_start_tls
     * @return LDAP
     */
    public static function getInstance($host = "127.0.0.1", $port = 389, $version = 3, $use_ssl = false, $use_start_tls = false) {
        if(!self::$instance) // if no instance then make one
            self::$instance = new self($host, $port, $version, $use_ssl, $use_start_tls);
        return self::$instance;
    }

    /**
     * @param string $host
     * @param int    $port
     * @param int    $version
     * @param boolean   $use_ssl
     * @param boolean   $use_start_tls
     */
    private function __construct($host, $port, $version, $use_ssl, $use_start_tls) {
        if (!extension_loaded('ldap'))
            echo "Please enable LDAP extension on PHP.";

        $this->host = $host;
        $this->port = $port;
        $this->version = $version;
        $this->use_ssl = $use_ssl;
        $this->use_start_tls = $use_start_tls;
        $this->connect();
    }

    public function __destruct() {
        if($this->connection)
            $this->disconnect();
    }

    /**
     * Connects to LDAP Server, nothing else, no authentication.
     */
    private function connect() {
        if (!$this->connection) {
            $host = $this->host;
            if ($this->use_ssl)
                $host = 'ldaps://' . $host;

            $this->connection = ldap_connect($host, $this->port);
            ldap_set_option($this->connection, LDAP_OPT_PROTOCOL_VERSION, $this->version);

            if ($this->use_start_tls)
                ldap_start_tls($this->connection);
        }
    }

    /**
     * Provides LDAP connection, returns old one if already available. Yes, it is Singleton too, bro!
     * @return mixed
     */
    public function getConnection() {
        return $this->connection;
    }

    private function disconnect() {
        if ($this->connection && is_resource($this->connection)) {
            ldap_unbind($this->connection);
        }
        $this->connection = null;
    }

	/**
	 * @return string
	 */
	public function getHost(): string {
		return $this->host;
	}

	/**
	 * @return int
	 */
	public function getPort(): int {
		return $this->port;
	}

	/**
	 * @return int
	 */
	public function getVersion(): int {
		return $this->version;
	}

	/**
	 * @return bool
	 */
	public function isUseSsl(): bool {
		return $this->use_ssl;
	}

	/**
	 * @return bool
	 */
	public function isUseStartTls(): bool {
		return $this->use_start_tls;
	}

    // Magic method clone is empty to prevent duplication of me
    private function __clone() {}
}