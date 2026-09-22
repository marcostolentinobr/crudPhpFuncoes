<?php

declare(strict_types=1);

require_once __DIR__ . '/funcoes.php';

$mensagemErro = '';
$pessoaArray = [];
$postAcao = null;
$ok = false;
$acaoDescricaoOk = '';

try {
    $postAcao = filter_input(INPUT_POST, 'ACAO', FILTER_UNSAFE_RAW);
    $postId = filter_input(INPUT_POST, 'ID_PESSOA', FILTER_VALIDATE_INT);

    // INCLUIR
    if ($postAcao === 'Incluir') {
        $resultado = pessoaDados();
        if (!$resultado['ok']) {
            $mensagemErro = $resultado['erro'];
        } elseif (pessoaIncluir($resultado['dados'])) {
            redirect('index.php?sucesso=Incluir');
        } else {
            $mensagemErro = 'Não foi possível incluir o registro.';
        }
    }
    // ALTERAR
    elseif ($postAcao === 'Alterar') {
        if (!$postId) {
            $mensagemErro = 'ID inválido para alteração.';
        } else {
            $resultado = pessoaDados();
            if (!$resultado['ok']) {
                $mensagemErro = $resultado['erro'];
            } elseif (pessoaAlterar($resultado['dados'], (int) $postId)) {
                redirect('index.php?sucesso=Alterar');
            } else {
                $mensagemErro = 'Não foi possível alterar o registro.';
            }
        }
    }
    // EXCLUIR
    elseif ($postAcao === 'Excluir') {
        if ($postId) {
            if (pessoaExcluir((int) $postId)) {
                redirect('index.php?sucesso=Excluir');
            }
            $mensagemErro = 'Registro não encontrado para exclusão.';
        } else {
            $mensagemErro = 'ID inválido para exclusão.';
        }
    }
    // CANCELAR
    elseif ($postAcao === 'Cancelar') {
        redirect('index.php');
    }
} catch (Exception $ex) {
    error_log($ex->getMessage());
    $mensagemErro = APP_DEBUG
        ? $ex->getMessage()
        : 'Ocorreu um erro interno. Tente novamente em instantes.';
}

// SUCESSO (PRG)
$getSucesso = filter_input(INPUT_GET, 'sucesso', FILTER_UNSAFE_RAW);
if ($postAcao === 'Editar') {
    $getSucesso = null;
}
if (in_array($getSucesso, ['Incluir', 'Alterar', 'Excluir'], true)) {
    $ok = true;
    $postAcao = $getSucesso;
    $acaoDescricaoOk = ($getSucesso === 'Incluir')
        ? 'Incluído'
        : (($getSucesso === 'Alterar') ? 'Alterado' : 'Excluído');
}

?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CRUD PHP/Mysql</title>
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
</head>

<body>
    <div style="width: 100%; max-width: 900px; margin: 0 auto; font-family: sans-serif; padding-top: 2rem;">
        <?php if ($ok): ?>
            <h3 style="color: green;"><?php echo e($acaoDescricaoOk) ?> com sucesso!</h3>
        <?php elseif ($mensagemErro): ?>
            <h3 style="color: red;">Não foi possível executar a ação! <?php echo e($mensagemErro) ?></h3>
        <?php endif; ?>

        <table border="1" style="width: 100%; min-width: 500px; border-collapse: collapse;">
            <tr style="vertical-align: top;">

                <!-- PESSOAS -->
                <td style="text-align: right; padding: 1rem; width: 50%;">
                    <h2 style="text-align: center;">Pessoas</h2>
                    <?php
                    $pessoaQuery = pessoaListar(); 
                    if (empty($pessoaQuery)) {
                        echo '<h5 style="text-align: center; color: blue;">Não existem pessoas para listar!</h5>';
                    }
                    foreach ($pessoaQuery as $pessoaFetch) {
                        $pessoaArray[$pessoaFetch['ID_PESSOA']] = $pessoaFetch;
                        echo e($pessoaFetch['NOME']);
                    ?>
                        <!-- AÇÕES -->
                        <form method="POST" style="display: inline; margin-left: 0.5rem;">
                            <input type="hidden" name="ID_PESSOA" value="<?php echo (int) $pessoaFetch['ID_PESSOA'] ?>">
                            <input name="ACAO" value="Editar" type="submit">
                            <input name="ACAO" value="Excluir" type="submit">
                        </form>
                        <hr style="border: 0; border-top: 1px solid #ccc; margin: 0.5rem 0;">
                    <?php
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
                    <h2><?php echo e($acaoDescricao) ?> Pessoa</h2>
                    <form method="POST">
                        <input type="hidden" name="ID_PESSOA" value="<?php echo (int) (isset($pessoaAlterar['ID_PESSOA']) ? $pessoaAlterar['ID_PESSOA'] : 0) ?>">

                        <label for="nome_input">Nome: </label>
                        <input id="nome_input" name="NOME"
                               value="<?php echo $pessoaAlterar ? e($pessoaAlterar['NOME']) : '' ?>"
                               maxlength="100" required>
                        <br><br>

                        <label for="uf_input">UF:</label>
                        <select id="uf_input" name="UF" required>
                            <option value=""></option>
                            <?php
                            $ufAtual = isset($pessoaAlterar['UF']) ? $pessoaAlterar['UF'] : '';
                            foreach (UF_PERMITIDAS as $ufCodigo => $ufLabel):
                            ?>
                                <option value="<?php echo e($ufCodigo) ?>"
                                    <?php echo ($ufAtual === $ufCodigo) ? 'selected' : '' ?>>
                                    <?php echo e($ufLabel) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <br><br>

                        <label for="observacao_input">Observação:</label>
                        <textarea id="observacao_input" maxlength="1000" style="height: 100px"
                                  name="OBSERVACAO"><?php echo e(isset($pessoaAlterar['OBSERVACAO']) ? $pessoaAlterar['OBSERVACAO'] : '') ?></textarea>
                        <br><br>

                        <hr>
                        <button type="submit" name="ACAO" value="<?php echo e($acaoDescricao) ?>"><?php echo e($acaoDescricao) ?></button>
                        <button type="submit" name="ACAO" value="Cancelar">Cancelar</button>
                    </form>
                </td>
            </tr>
        </table>
    </div>
</body>

</html>
