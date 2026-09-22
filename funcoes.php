<?php

declare(strict_types=1);

// Configurações do banco de dados
const DB_HOST    = 'localhost';
const DB_NAME    = 'crud';
const DB_USER    = 'root';
const DB_PASS    = '';
const DB_CHARSET = 'utf8mb4';

const APP_DEBUG = true;  // false em produção

const UF_PERMITIDAS = ['SC', 'OU'];

// CONEXÃO
function pdo(): PDO
{
    static $pdo = null;

    if ($pdo === null) {
        try {
            $pdo = new PDO(
                'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET,
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]
            );
        } catch (PDOException $e) {
            error_log('Erro de Conexão: ' . $e->getMessage());
            die('Desculpe, estamos passando por uma manutenção técnica no momento.');
        }
    }

    return $pdo;
}

/** Escapa saída para prevenir XSS. */
function e($value): string
{
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
}

/** Redireciona e encerra a execução. */
function redirect(string $url): void
{
    header("Location: $url");
    exit;
}

/**
 * Lê e valida os dados do formulário.
 * @return array{ok: true, dados: array}|array{ok: false, erro: string}
 */
function pessoaDados(): array
{
    $nome = trim((string) ($_POST['NOME'] ?? ''));
    $uf = strtoupper(trim((string) ($_POST['UF'] ?? '')));
    $observacao = trim((string) ($_POST['OBSERVACAO'] ?? ''));

    if ($nome === '' || mb_strlen($nome) > 100) {
        return ['ok' => false, 'erro' => 'Nome inválido.'];
    }

    if (!in_array($uf, UF_PERMITIDAS, true)) {
        return ['ok' => false, 'erro' => 'UF inválida.'];
    }

    if (mb_strlen($observacao) > 1000) {
        return ['ok' => false, 'erro' => 'Observação muito longa.'];
    }

    return [
        'ok' => true,
        'dados' => [
            ':NOME' => $nome,
            ':UF' => $uf,
            ':OBSERVACAO' => $observacao !== '' ? $observacao : null,
        ],
    ];
}

function pessoaIncluir(array $pessoaDados): bool
{
    $pdo = pdo();
    $stmt = $pdo->prepare('
        INSERT INTO PESSOA (NOME, UF, OBSERVACAO)
                    VALUES (:NOME, :UF, :OBSERVACAO)
    ');
    return $stmt->execute($pessoaDados);
}

function pessoaAlterar(array $pessoaDados, int $idPessoa): bool
{
    $pdo = pdo();
    $stmt = $pdo->prepare('
        UPDATE PESSOA
           SET NOME = :NOME,
               UF = :UF,
               OBSERVACAO = :OBSERVACAO
         WHERE ID_PESSOA = :ID_PESSOA
    ');
    $pessoaDados[':ID_PESSOA'] = $idPessoa;
    return $stmt->execute($pessoaDados);
}

function pessoaExcluir(int $idPessoa): bool
{
    $pdo = pdo();
    $stmt = $pdo->prepare('
        DELETE FROM PESSOA
         WHERE ID_PESSOA = :ID_PESSOA
    ');
    $stmt->execute([':ID_PESSOA' => $idPessoa]);
    return $stmt->rowCount() > 0;
}

function pessoaListar(): PDOStatement
{
    $pdo = pdo();
    return $pdo->query('
        SELECT ID_PESSOA, NOME, UF, OBSERVACAO
          FROM PESSOA
      ORDER BY NOME
    ');
}
