<?php
/**
 * Created by PhpStorm.
 * User: imam.bux
 * Date: 30.04.18
 * Time: 10:20
 */

// Has configurations for server, database, application (everything)
$config = include('config.php');
$ldap_config = $config['ldap'];

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

    public static function getInstance() {
		// if no instance then make one
        if (!self::$instance) {
            self::$instance = new self();
		}
        return self::$instance;
    }

    private function __construct() {
        if (!extension_loaded('ldap')) {
            echo "Please enable LDAP extension on PHP.";
		}

		global $ldap_config;
        $this->use_ssl = $ldap_config['use_ssl'];
		$this->host = $this->use_ssl ? 'ldaps://' : 'ldap://' . $ldap_config['host'];
        $this->port = $this->use_ssl ? $ldap_config['ssl_port'] : $ldap_config['port'];
        $this->version = $ldap_config['version'];
        $this->use_start_tls = $ldap_config['$use_start_tls'];

        $this->connect();
    }

    public function __destruct() {
    	// Clear connection from LDAP server
        if ($this->connection) {
            $this->disconnect();
		}
    }

    /**
     * Connects to LDAP Server, nothing else, no authentication.
     */
    private function connect() {
        if (!$this->connection) {
            $this->connection = ldap_connect($this->host, $this->port);
            ldap_set_option($this->connection, LDAP_OPT_PROTOCOL_VERSION, $this->version);

            if ($this->use_start_tls) {
                ldap_start_tls($this->connection);
			}
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