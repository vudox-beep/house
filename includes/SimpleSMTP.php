<?php
class SimpleSMTP {
    private $host;
    private $port;
    private $user;
    private $pass;
    private $debug = false;
    private $localhost = 'localhost';

    public function __construct($host, $port, $user, $pass) {
        $this->host = $host;
        $this->port = $port;
        $this->user = $user;
        $this->pass = $pass;
        // Try to get the actual server name, fallback to localhost
        if (isset($_SERVER['SERVER_NAME'])) {
            $this->localhost = $_SERVER['SERVER_NAME'];
        }
    }

    public function send($to, $subject, $body, $fromName) {
        $socket = fsockopen($this->host, $this->port, $errno, $errstr, 30);
        if (!$socket) {
            return false;
        }

        $this->server_parse($socket, "220");

        // HELLO
        fwrite($socket, "EHLO " . $this->localhost . "\r\n");
        $this->server_parse($socket, "250");

        // STARTTLS
        fwrite($socket, "STARTTLS\r\n");
        $this->server_parse($socket, "220");

        stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);

        // HELLO AGAIN (Encrypted)
        fwrite($socket, "EHLO " . $this->localhost . "\r\n");
        $this->server_parse($socket, "250");

        // AUTH
        fwrite($socket, "AUTH LOGIN\r\n");
        $this->server_parse($socket, "334");

        fwrite($socket, base64_encode($this->user) . "\r\n");
        $this->server_parse($socket, "334");

        fwrite($socket, base64_encode($this->pass) . "\r\n");
        $this->server_parse($socket, "235");

        // MAIL FROM
        fwrite($socket, "MAIL FROM: <" . $this->user . ">\r\n");
        $this->server_parse($socket, "250");

        // RCPT TO
        fwrite($socket, "RCPT TO: <" . $to . ">\r\n");
        $this->server_parse($socket, "250");

        // DATA
        fwrite($socket, "DATA\r\n");
        $this->server_parse($socket, "354");

        // HEADERS
        $headers  = "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        $headers .= "Date: " . date("r") . "\r\n"; // RFC 2822 date
        $headers .= "From: " . $fromName . " <" . $this->user . ">\r\n";
        $headers .= "To: <" . $to . ">\r\n";
        $headers .= "Subject: " . $subject . "\r\n";
        $headers .= "Reply-To: " . $fromName . " <" . $this->user . ">\r\n";
        $headers .= "Message-ID: <" . md5(uniqid(time())) . "@" . $this->localhost . ">\r\n";
        $headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";
        $headers .= "X-Priority: 3\r\n"; // Normal priority

        fwrite($socket, $headers . "\r\n" . $body . "\r\n.\r\n");
        $this->server_parse($socket, "250");

        // QUIT
        fwrite($socket, "QUIT\r\n");
        fclose($socket);

        return true;
    }

    private function server_parse($socket, $response) {
        $server_response = "";
        while (substr($server_response, 3, 1) != ' ') {
            if (!($server_response = fgets($socket, 256))) {
                return false;
            }
        }

        if (!(substr($server_response, 0, 3) == $response)) {
            return false;
        }
    }
}
?>