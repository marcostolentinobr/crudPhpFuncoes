<?php

declare(strict_types=1);

//Configurações do banco de dados
const DB_HOST    = 'localhost';
const DB_NAME    = 'crud';
const DB_USER    = 'root';
const DB_PASS    = '';
const DB_CHARSET = 'utf8mb4';

const APP_DEBUG = true;  // false em produção

// CONEXÃO
function pdo()
{
    static $pdo = null;

    if ($pdo === null) {
        try {
            $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } catch (PDOException $e) {
            error_log('Erro de Conexão: ' . $e->getMessage());
            die('Desculpe, estamos passando por uma manutenção técnica no momento.');
        }
    }

    return $pdo;
}

// Escapa saída para prevenir XSS.
function e(mixed $value)
{
    return htmlspecialchars(isset($value) ? $value : '', ENT_QUOTES, 'UTF-8');
}

// Redireciona e encerra a execução.
function redirect(string $url)
{
    header("Location: $url");
    exit;
}

function pessoaDados()
{
    $pessoaDados = [
        ':NOME' => @$_POST['NOME'],
        ':UF' => @$_POST['UF'],
        ':OBSERVACAO' => @$_POST['OBSERVACAO']
    ];
    return $pessoaDados;
}

function pessoaIncluir(array $pessoaDados)
{
    $PDO = pdo();
    $pesIncluir = $PDO->prepare('
        INSERT INTO PESSOA (NOME,   UF,  OBSERVACAO) 
                    VALUES (:NOME, :UF, :OBSERVACAO)
    ');
    return $pesIncluir->execute($pessoaDados);
}

function pessoaAlterar(array $pessoaDados, int $ID_PESSOA)
{
    $PDO = pdo();
    $pesAlterar = $PDO->prepare('
        UPDATE PESSOA SET NOME = :NOME, 
                            UF = :UF, 
                    OBSERVACAO = :OBSERVACAO 
                WHERE ID_PESSOA = :ID_PESSOA
    ');
    $pessoaDados[':ID_PESSOA'] = $ID_PESSOA;
    return $pesAlterar->execute($pessoaDados);
}

function pessoaExcluir(int $ID_PESSOA)
{
    $PDO = pdo();
    $pesExcluir = $PDO->prepare('
        DELETE FROM PESSOA 
                WHERE ID_PESSOA = :ID_PESSOA
    ');
    return $pesExcluir->execute([
        ':ID_PESSOA' => $ID_PESSOA
    ]);
}

function pessoaListar()
{
    $PDO = pdo();
    $sql = "
        SELECT *
          FROM PESSOA
      ORDER BY NOME
    ";
    return $PDO->query($sql);
}
