<?php

// Função para obter a conexão com o banco de dados
function getConnection()
{
    $host = 'localhost';  // Endereço do servidor de banco de dados
    $db = 'local_salao';  // Nome do banco de dados
    $user = 'root';       // Nome de usuário do banco de dados
    $pass = '';           // Senha do banco de dados
    $charset = 'utf8mb4'; // Charset utilizado

    $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    try {
        return new PDO($dsn, $user, $pass, $options);
    } catch (\PDOException $e) {
        throw new \PDOException($e->getMessage(), (int)$e->getCode());
    }
}

function select($table, $where = null, $params = null)
{
    $pdo = getConnection();

    $query = "SELECT * FROM {$table}";
    if ($where) {
        $query .= " {$where}";
    }

    $stmt = $pdo->prepare($query);

    if ($params) {
        foreach ($params as $key => $value) {
            $stmt->bindValue(":{$key}", $value);
        }
    }

    $stmt->execute();

    return $stmt->fetchAll();
}

function executeQuery($query, $params = [])
{
    $pdo = getConnection();

    try {
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);

        return $stmt;
    } catch (PDOException $e) {
        throw new Exception("Erro ao executar a consulta: " . $e->getMessage());
    }
}

// Função para atualizar registros (UPDATE)
function exeUpdate($table, $data, $where, $params = null)
{
    $pdo = getConnection();

    $setPart = [];
    foreach ($data as $column => $value) {
        $setPart[] = "{$column} = :{$column}";
    }
    $setQuery = implode(', ', $setPart);
    $query = "UPDATE {$table} SET {$setQuery} WHERE {$where}";

    $stmt = $pdo->prepare($query);

    foreach ($data as $column => $value) {
        $stmt->bindValue(":{$column}", $value);
    }
    if ($params) {
        parse_str($params, $paramArray);
        foreach ($paramArray as $key => $value) {
            $stmt->bindValue(":{$key}", $value);
        }
    }

    return $stmt->execute();
}

// Função para deletar registros (DELETE)
function exeDelete($table, $where, $params = null)
{
    $pdo = getConnection();

    $query = "DELETE FROM {$table} WHERE {$where}";

    $stmt = $pdo->prepare($query);

    if ($params) {
        parse_str($params, $paramArray);
        foreach ($paramArray as $key => $value) {
            $stmt->bindValue(":{$key}", $value);
        }
    }

    return $stmt->execute();
}

// Função para calcular a data e hora final baseada na data inicial e na duração estimada do serviço
function calcularDataHoraFinal($data_hora_inicial, $duracao_estimada_minutos)
{
    return date('Y-m-d H:i:s', strtotime($data_hora_inicial) + ($duracao_estimada_minutos * 60));
}

// Função para verificar a disponibilidade do horário
function isTimeSlotAvailable($funcionario_id, $data_hora)
{
    $pdo = getConnection(); // Obter a conexão PDO

    // Consulta SQL para verificar a disponibilidade do horário
    $query = "SELECT COUNT(*) FROM agendamentos 
              WHERE id_funcionario = :funcionario_id 
              AND data_hora = :data_hora";

    $stmt = $pdo->prepare($query);
    $stmt->bindValue(':funcionario_id', $funcionario_id, PDO::PARAM_INT);
    $stmt->bindValue(':data_hora', $data_hora, PDO::PARAM_STR);

    $stmt->execute();
    $count = $stmt->fetchColumn();

    return $count == 0; // Retorna true se não houver agendamentos
}

function titleCase($string)
{
    $noUp =  [
        'a', 'ante', 'após', 'até', 'com', 'contra', 'de', 'da', 'desde', 'em', 'entre',
        'para', 'perante', 'por', 'sem', 'sob', 'sobre', 'trás', 'e'
    ];

    $string = strtolower($string);

    $words = explode(' ', $string);

    foreach ($words as $key => $word) {
        if (!in_array($word, $noUp) || $key == 0) {
            $words[$key] = ucfirst($word);
        }
    }
    return implode(' ', $words);
}

// Função para obter horários já agendados
function getBookedSlots($funcionario_id, $date)
{
    $pdo = getConnection();

    $query = "SELECT data_hora 
              FROM agendamentos 
              WHERE id_funcionario = :funcionario_id 
              AND DATE(data_hora) = :date";

    $stmt = $pdo->prepare($query);
    $stmt->bindValue(':funcionario_id', $funcionario_id, PDO::PARAM_INT);
    $stmt->bindValue(':date', $date, PDO::PARAM_STR);

    try {
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
        return [];
    }
}
?>
