<?php
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

require_once __DIR__ . '/../Webler/classes/Database.php';
require dirname(__DIR__) . '/vendor/autoload.php';

class HelloSocket implements \Ratchet\WebSocket\MessageComponentInterface {
    protected $clients = [];
    protected $db;
    protected $logger;

    public function __construct() {
        global $DB;
        $this->db = $DB->getConnection();

        // Setup logging
        $this->logger = new Logger('websocket');
        // $this->logger->pushHandler(new StreamHandler(__DIR__ . '/websocket.log', Logger::DEBUG));
    }

    public function onOpen(\Ratchet\ConnectionInterface $conn) {
        try {
            $queryParams = [];
            parse_str($conn->httpRequest->getUri()->getQuery(), $queryParams);
            $appName = $queryParams['token'] ?? null;
            $password = $queryParams['password'] ?? null;

            // Validate input
            if (!$appName || !$password) {
                $this->handleAuthenticationFailure($conn, "Missing credentials");
                return;
            }

            // Authenticate
            if (!$this->authenticateApplication($appName, $password)) {
                $this->handleAuthenticationFailure($conn, "Invalid credentials for App " . $appName);
                return;
            }

            // Store app name and track connection
            $conn->appName = $appName;
            $conn->uniqueId = spl_object_id($conn);

            if (!isset($this->clients[$appName])) {
                $this->clients[$appName] = [];
            }
            $this->clients[$appName][] = $conn;

            parse_str($conn->httpRequest->getUri()->getQuery(), $queryParams);
            $this->logger->info('Connection attempt', $queryParams);

            // Log connection details
            $this->logger->info('New Connection', [
                'connection_id' => $conn->uniqueId,
                'app_name' => $appName,
                'total_connections' => count($this->clients[$appName])
            ]);


        } catch (\Exception $e) {
            echo $e;
            $this->handleConnectionError($conn, $e);
        }
    }

    private function handleAuthenticationFailure(\Ratchet\ConnectionInterface $conn, string $reason) {
        $errorResponse = json_encode([
            'type' => 'error',
            'message' => $reason
        ]);
        
        $conn->send($errorResponse);
        $conn->close();

        $this->logger->warning('Authentication Failed', [
            'reason' => $reason,
            'remote_address' => $conn->remoteAddress
        ]);
    }

    private function handleConnectionError(\Ratchet\ConnectionInterface $conn, \Exception $e) {
        $this->logger->error('Connection Error', [
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);

        $conn->send(json_encode([
            'type' => 'error', 
            'message' => 'Internal server error'
        ]));
        $conn->close();
    }

    public function authenticateApplication($applicationId, $password) {
        try {
            $stmt = $this->db->prepare("SELECT password FROM M_APPS WHERE application_name = :app_id");
            $stmt->execute([':app_id' => $applicationId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$result) {
                $this->logger->warning('App not found', ['app_id' => $applicationId]);
                return false;
            }

            return password_verify($password, $result['password']);
        } catch (PDOException $e) {
            $this->logger->error('Database Authentication Error', [
                'message' => $e->getMessage(),
                'app_id' => $applicationId
            ]);
            return false;
        }
    }

    public function onMessage(\Ratchet\ConnectionInterface $from, $msg) {
        try {
            $appName = $from->appName;

            // Validate JSON
            $messageData = json_decode($msg, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->logger->warning('Invalid JSON received', [
                    'sender' => $from->uniqueId,
                    'app_name' => $appName
                ]);
                return;
            }

            // Broadcast to all clients in the same app, except sender
            if (isset($this->clients[$appName])) {
                foreach ($this->clients[$appName] as $client) {
                    if ($from !== $client) {
                        $client->send($msg);
                    }
                }
            }

            $this->logger->info('Message Broadcast', [
                'sender' => $from->uniqueId,
                'app_name' => $appName,
                'recipients' => count($this->clients[$appName]) - 1
            ]);

        } catch (\Exception $e) {
            $this->logger->error('Message Handling Error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    public function onClose(\Ratchet\ConnectionInterface $conn) {
        $appName = $conn->appName;

        if (isset($this->clients[$appName])) {
            // Remove specific connection
            $index = array_search($conn, $this->clients[$appName]);
            if ($index !== false) {
                unset($this->clients[$appName][$index]);
                $this->clients[$appName] = array_values($this->clients[$appName]);
            }

            $this->logger->info('Connection Closed', [
                'connection_id' => $conn->uniqueId,
                'app_name' => $appName,
                'remaining_connections' => count($this->clients[$appName])
            ]);
        }
    }

    public function onError(\Ratchet\ConnectionInterface $conn, \Exception $e) {
        echo $e;
        $this->logger->error('WebSocket Error', [
            'connection_id' => $conn->uniqueId ?? 'unknown',
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);

        $conn->send(json_encode([
            'type' => 'error', 
            'message' => 'Unexpected server error'
        ]));
        $conn->close();
    }
}

try {
    $server = IoServer::factory(
        new HttpServer(
            new WsServer(
                new HelloSocket()
            )
        ),
        8080,
        '0.0.0.0'
    );

    // Log server startup
    $logger = new Logger('websocket_server');
    $logger->pushHandler(new StreamHandler(__DIR__ . '/server_startup.log', Logger::INFO));
    $logger->info('WebSocket Server Started', [
        'port' => 8080,
        'timestamp' => date('Y-m-d H:i:s')
    ]);

    echo "WebSocket server running on port 8080\n";
    $server->run();

} catch (\Exception $e) {
    // Critical error logging
    error_log("Server startup failed: " . $e->getMessage());
    echo "Failed to start WebSocket server: " . $e->getMessage() . "\n";
    exit(1);
}