<?php

declare(strict_types=1);

require_once 'funcoes.php';

$mensagemErro = '';
$pessoaArray = [];
$postAcao = null;

try {

    $postAcao = filter_input(INPUT_POST, 'ACAO', FILTER_UNSAFE_RAW);
    $postId   = filter_input(INPUT_POST, 'ID_PESSOA', FILTER_VALIDATE_INT);

    //INCLUIR
    if ($postAcao === 'Incluir') {
        $acaoDescricaoOk = 'Incluído';
        $ok = pessoaIncluir(pessoaDados());
        redirect('index.php?sucesso=Incluir');
    }
    //ALTERAR
    elseif ($postAcao == 'Alterar') {
        if ($postId) {
            $acaoDescricaoOk = 'Alterado';
            $ok = pessoaAlterar(pessoaDados(), intval($_POST['ID_PESSOA']));
            redirect('index.php?sucesso=Alterar');
        }
    }
    //EXCLUIR
    elseif ($postAcao == 'Excluir') {
        if ($postId) {
            $ok = pessoaExcluir(intval($_POST['ID_PESSOA']));
            if ($ok) {
                $acaoDescricaoOk = 'Excluído';
                redirect('index.php?sucesso=Excluir');
            }
            $ok = false;
            $mensagemErro = 'Registro não encontrado para exclusão.';
        } else {
            $mensagemErro = 'ID inválido para exclusão.';
        }
    }
    //CANCELAR
    elseif ($postAcao == 'Cancelar') {
        redirect('index.php');
    }
} catch (Exception $ex) {
    error_log($ex->getMessage());
    $mensagemErro = APP_DEBUG
        ? $ex->getMessage()
        : 'Ocorreu um erro interno. Tente novamente em instantes.';
}

// SUSCESSO
$getSucesso = filter_input(INPUT_GET, 'sucesso', FILTER_UNSAFE_RAW);
if ($postAcao === 'Editar') {
    $getSucesso = null;
}
if ($getSucesso) {
    $ok = true;
    $postAcao = $getSucesso;
    $acaoDescricaoOk = ($getSucesso === 'Incluir') ? 'Incluído' : (($getSucesso === 'Alterar') ? 'Alterado' : 'Excluído');
}

?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CRUD PHP/Mysql</title>
</head>

<style>
    label {
        font-weight: bold;
        display: block;
    }

    select,
    textarea {
        width: 173px;
    }
</style>

<body>
    <div style="width: 100%; max-width: 900px; margin: 0 auto; font-family: sans-serif; padding-top: 2rem;">
        <?php

        if ($postAcao && $ok) {
            echo "<h3 style='color: green;'>$acaoDescricaoOk com sucesso!</h3>";
        } elseif ($mensagemErro) {
            echo "<h3 style='color: red;'>Não foi possível executar a ação! $mensagemErro</h3>";
        }
        ?>
        <table border="1" style="width: 100%; min-width: 500px; border-collapse: collapse;">
            <tr style="vertical-align: top;">

                <!-- PESSOAS -->
                <td style="text-align: right; padding: 1rem; width: 50%;">
                    <h2 style="text-align: center;">Pessoas</h2>
                    <?php
                    $pessoaQuery = pessoaListar();
                    if (!$pessoaQuery) {
                        echo '<h5 style="text-align: center; color: blue;">Não foi possível carregar a lista.</h5>';
                    } elseif ($pessoaQuery->rowCount() === 0) {
                        echo '<h5 style="text-align: center; color: blue;">Não existem pessoas para listar!</h5>';
                    }
                    if ($pessoaQuery) {
                        while ($pessoaFetch = $pessoaQuery->fetch(PDO::FETCH_ASSOC)) {
                            $pessoaArray[$pessoaFetch['ID_PESSOA']] = $pessoaFetch;
                            echo htmlspecialchars($pessoaFetch['NOME']);
                    ?>
                            <!-- AÇÕES -->
                            <form method="POST" style="display: inline; margin-left: 0.5rem;">
                                <input type="hidden" name="ID_PESSOA" value="<?php echo (int)$pessoaFetch['ID_PESSOA'] ?>">
                                <input name="ACAO" value="Editar" type="submit">
                                <input name="ACAO" value="Excluir" type="submit">
                            </form>
                            <hr style="border: 0; border-top: 1px solid #ccc; margin: 0.5rem 0;">
                    <?php
                        }
                    }

                    // MANUTENÇÃO
                    $getEditarId = filter_input(INPUT_POST, 'ID_PESSOA', FILTER_VALIDATE_INT);
                    $pessoaAlterar = ($postAcao === 'Editar' && $getEditarId && isset($pessoaArray[$getEditarId]))
                        ? $pessoaArray[$getEditarId]
                        : null;
                    $acaoDescricao = ($pessoaAlterar ? 'Alterar' : 'Incluir');
                    ?>
                </td>
                <!-- FORMULÁRIO -->
                <td style="text-align: center; padding: 1rem; width: 50%;">
                    <h2><?php echo $acaoDescricao ?> Pessoa</h2>
                    <form method="POST">
                        <input type="text" name="ID_PESSOA" value="<?php echo  @$pessoaAlterar['ID_PESSOA'] ?>" hidden>

                        <label for="nome_input">Nome: </label>
                        <input id="nome_input" name="NOME" value="<?php echo $pessoaAlterar ? e($pessoaAlterar['NOME']) : '' ?>" maxlength="100" required>
                        <br><br>

                        <label for="uf_input">UF:</label>
                        <select id="uf_input" name="UF" required>
                            <option></option>
                            <option value="SC" <?php echo (@$pessoaAlterar['UF'] == 'SC' ? 'selected' : '') ?>>SC</option>
                            <option value="OU" <?php echo (@$pessoaAlterar['UF'] == 'OU' ? 'selected' : '') ?>>Outro</option>
                        </select>
                        <br><br>

                        <label for="observacao_label">Observação:</label>
                        <textarea id="observacao_label" maxlength="1000" style="height: 100px" name="OBSERVACAO"><?php echo  @$pessoaAlterar['OBSERVACAO'] ?></textarea>
                        <br><br>

                        <hr>
                        <button type="submit" name="ACAO" value="<?php echo $acaoDescricao ?>"><?php echo $acaoDescricao ?></button>
                        <button type="submit" name="ACAO" value="Cancelar">Cancelar</button>
                    </form>
                </td>
            </tr>
        </table>
    </div>
</body>

</html>