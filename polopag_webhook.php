<?php
// Desativa a exibição de erros no navegador
error_reporting(0);
ini_set('display_errors', 0);

// Path para o config.lua
$config_path = '/caminho/seguro/config.lua';

// Coluna dos coins
$coins_column = "coins_transferable";

function read_config($config_path)
{
    $config = [];
    if (!file_exists($config_path)) {
        die("Arquivo de configuração não encontrado.");
    }

    $file_content = file_get_contents($config_path);

    preg_match('/mysqlHost\s*=\s*"(.*)"/', $file_content, $host_match);
    preg_match('/mysqlUser\s*=\s*"(.*)"/', $file_content, $user_match);
    preg_match('/mysqlPass\s*=\s*"(.*)"/', $file_content, $password_match);
    preg_match('/mysqlDatabase\s*=\s*"(.*)"/', $file_content, $database_match);
    preg_match('/mysqlPort\s*=\s*(\d+)/', $file_content, $port_match);

    $config['host'] = $host_match[1];
    $config['user'] = $user_match[1];
    $config['password'] = $password_match[1];
    $config['database'] = $database_match[1];
    $config['port'] = $port_match[1];

    return $config;
}

function connect_to_database($config)
{
    try {
        $dsn = "mysql:host={$config['host']};dbname={$config['database']};port={$config['port']};charset=utf8mb4";
        $pdo = new PDO($dsn, $config['user'], $config['password']);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch (PDOException $e) {
        die("Erro ao conectar ao banco de dados: " . $e->getMessage());
    }
}

function update_account_coins($coins_column, $account_id, $points, $pdo)
{
    try {
        $query = "UPDATE accounts SET $coins_column = $coins_column + :points WHERE id = :account_id";
        $stmt = $pdo->prepare($query);
        $stmt->execute([
            ':points' => $points,
            ':account_id' => $account_id
        ]);

        if ($stmt->rowCount() > 0) {
            return true;
        } else {
            return false;
        }
    } catch (PDOException $e) {
        return false;
    }
}

function process_webhook($coins_column, $data, $pdo)
{
    if (!isset($data['status'], $data['internalId'])) {
        http_response_code(400);
        echo json_encode(["message" => "Dados inválidos. 'internalId' é obrigatório."]);
        return;
    }

    $status = filter_var($data['status'], FILTER_SANITIZE_STRING);
    $internalId = filter_var($data['internalId'], FILTER_SANITIZE_STRING);

    try {
        $query = "SELECT * FROM polopag_transacoes WHERE internalId = :internalId AND status != 'APROVADO'";
        $stmt = $pdo->prepare($query);
        $stmt->execute([':internalId' => $internalId]);

        if ($stmt->rowCount() > 0) {
            $transaction = $stmt->fetch(PDO::FETCH_ASSOC);
            $account_id = $transaction['account_id'];
            $points = $transaction['points'];

            $query = "UPDATE polopag_transacoes SET status = :status WHERE internalId = :internalId";
            $stmt = $pdo->prepare($query);
            $stmt->execute([
                ':status' => $status,
                ':internalId' => $internalId
            ]);

            if ($stmt->rowCount() > 0) {
                if (update_account_coins($coins_column, $account_id, $points, $pdo)) {
                    http_response_code(200);
                    echo json_encode(["message" => "Status atualizado e coins incrementados com sucesso."]);
                } else {
                    http_response_code(500);
                    echo json_encode(["message" => "Erro ao incrementar os coins na conta."]);
                }
            } else {
                http_response_code(500);
                echo json_encode(["message" => "Erro ao atualizar o status da transação."]);
            }
        } else {
            http_response_code(404);
            echo json_encode(["message" => "Nenhuma transação encontrada com o 'internalId' fornecido ou já aprovada."]);
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(["message" => "Erro ao processar a transação.", "error" => $e->getMessage()]);
    }
}

$config = read_config($config_path);

$pdo = connect_to_database($config);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_SERVER['CONTENT_TYPE'] === 'application/json') {
    $data = json_decode(file_get_contents('php://input'), true);

    if (json_last_error() === JSON_ERROR_NONE) {
        process_webhook($coins_column, $data, $pdo);
    } else {
        http_response_code(400);
        echo json_encode(["message" => "Erro no formato do JSON recebido."]);
    }
} else {
    http_response_code(405);
    echo json_encode(["message" => "Método não permitido. Apenas POST com conteúdo JSON é aceito."]);
}
