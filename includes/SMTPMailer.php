<?php
/**
 * BLACK_PROTOCOL — SMTP Mailer
 * Envoi d'emails via Gmail SMTP (port 465, TLS implicite)
 * Sans dépendance externe — stream_socket_client + OpenSSL
 */
require_once __DIR__ . "/smtp_config.php";

class SMTPMailer
{
    private string $host;
    private string $user;
    private string $pass;
    private $socket = null;
    private string $log = "";
    private bool $debug = false;

    public function __construct(bool $debug = false)
    {
        $this->host = SMTP_HOST;
        $this->user = SMTP_USER;
        $this->pass = SMTP_PASS;
        $this->debug = $debug;
    }

    /**
     * Envoie un email HTML
     */
    public function send(
        string $to,
        string $subject,
        string $htmlBody,
        string $textBody = "",
    ): array {
        try {
            $this->log = "";

            if ($this->pass === "MOT_DE_PASSE_APPLICATION_ICI") {
                return [
                    "success" => false,
                    "message" =>
                        "SMTP non configuré. Configurez includes/smtp_config.php",
                ];
            }

            // Connexion TLS directe (port 465)
            $ctx = stream_context_create([
                "ssl" => [
                    "verify_peer" => false,
                    "verify_peer_name" => false,
                    "allow_self_signed" => true,
                ],
            ]);
            $this->socket = @stream_socket_client(
                "tls://" . $this->host . ":465",
                $errno,
                $errstr,
                15,
                STREAM_CLIENT_CONNECT,
                $ctx,
            );
            if (!$this->socket) {
                return [
                    "success" => false,
                    "message" => "Connexion impossible: {$errstr} ({$errno})",
                ];
            }

            // Read banner
            $this->readResponse();

            // EHLO
            $this->sendCmd("EHLO localhost");
            $this->readResponse();

            // AUTH LOGIN
            $this->sendCmd("AUTH LOGIN");
            $this->readResponse();

            $this->sendCmd(base64_encode($this->user));
            $this->readResponse();

            $this->sendCmd(base64_encode($this->pass));
            $auth = $this->readResponse();
            if (substr($auth, 0, 3) !== "235") {
                throw new Exception("Auth échouée: {$auth}");
            }

            // MAIL FROM
            $this->sendCmd("MAIL FROM:<" . SMTP_FROM_EMAIL . ">");
            $this->readResponse();

            // RCPT TO
            $this->sendCmd("RCPT TO:<{$to}>");
            $this->readResponse();

            // DATA
            $this->sendCmd("DATA");
            $this->readResponse();

            // Build message
            $boundary = "BP_" . md5(time() . rand());
            $msg = "";
            $msg .=
                "From: =?UTF-8?B?" .
                base64_encode(SMTP_FROM_NAME) .
                "?= <" .
                SMTP_FROM_EMAIL .
                ">\r\n";
            $msg .= "To: <{$to}>\r\n";
            $msg .= "Subject: =?UTF-8?B?" . base64_encode($subject) . "?=\r\n";
            $msg .= "MIME-Version: 1.0\r\n";
            $msg .= "Content-Type: multipart/alternative; boundary=\"{$boundary}\"\r\n";
            $msg .= "Date: " . date("r") . "\r\n";
            $msg .= "X-Mailer: BLACK_PROTOCOL\r\n";
            $msg .=
                "List-Unsubscribe: <mailto:" .
                SMTP_FROM_EMAIL .
                "?subject=unsubscribe>\r\n";
            $msg .= "\r\n";

            // Text part
            if (empty($textBody)) {
                $textBody = strip_tags($htmlBody);
            }
            $msg .= "--{$boundary}\r\n";
            $msg .= "Content-Type: text/plain; charset=UTF-8\r\n\r\n";
            $msg .= $textBody . "\r\n\r\n";

            // HTML part
            $msg .= "--{$boundary}\r\n";
            $msg .= "Content-Type: text/html; charset=UTF-8\r\n\r\n";
            $msg .= $htmlBody . "\r\n\r\n";
            $msg .= "--{$boundary}--\r\n";

            // Send message body + end marker
            $this->sendCmd($msg . ".");
            $dataResp = $this->readResponse();
            if (substr($dataResp, 0, 3) !== "250") {
                throw new Exception("Data refusé: {$dataResp}");
            }

            // QUIT
            $this->sendCmd("QUIT");
            $this->readResponse();

            if (is_resource($this->socket)) {
                fclose($this->socket);
            }

            return ["success" => true, "message" => "Email envoyé à {$to}"];
        } catch (Exception $e) {
            if (is_resource($this->socket)) {
                fclose($this->socket);
            }
            return [
                "success" => false,
                "message" => "Erreur: " . $e->getMessage(),
            ];
        }
    }

    /**
     * Envoie une commande SMTP
     */
    private function sendCmd(string $cmd): void
    {
        $cmd .= "\r\n";
        fwrite($this->socket, $cmd);
        if ($this->debug) {
            $this->log .= ">> " . trim($cmd) . "\n";
        }
    }

    /**
     * Lit une réponse SMTP complète (mode blocking avec timeout)
     */
    private function readResponse(): string
    {
        $response = "";

        while (true) {
            $line = fgets($this->socket, 515);
            if ($line === false || $line === "") {
                if (!empty($response)) {
                    break;
                }
                usleep(50000);
                continue;
            }

            $response .= $line;

            // En SMTP, une réponse multi-ligne utilise '-' après le code (ex: 250-SIZE)
            // La dernière ligne a un espace après le code (ex: 250 OK)
            // On vérifie le 4ème caractère : espace = fin, tiret = continuation
            $trimmed = rtrim($line, "\r\n");
            if (strlen($trimmed) >= 4 && $trimmed[3] === " ") {
                break;
            }
            // Réponse courte (3 digits uniquement)
            if (
                strlen($trimmed) >= 3 &&
                is_numeric(substr($trimmed, 0, 3)) &&
                strlen($trimmed) === 3
            ) {
                break;
            }
        }

        if ($this->debug) {
            $this->log .= "<< " . trim($response) . "\n\n";
        }

        return $response;
    }

    public function getLog(): string
    {
        return $this->log;
    }
}
