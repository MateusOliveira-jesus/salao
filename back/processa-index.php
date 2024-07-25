<?php include_once('../incl/geral.php');

// Verificar se o formulário foi submetido
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['agendar'])) {
    // Coletar dados do formulário
    $servico_id = $_POST['servico'];
    $funcionario_id = $_POST['funcionario'];
    $data_hora = $_POST['data_hora'];
    // Definir o status do agendamento
    $status = 'Pendente';

    // Definir o ID do cliente (substitua pelo ID real do cliente se aplicável)
    $id_cliente = 1;

    try {
        // Verificar se a data e hora são posteriores à data e hora atual
        if (strtotime($data_hora) < time()) {
            $aviso = "Não é possível agendar para datas e horas anteriores ao momento atual.";
        } else {
            // Consulta SQL para obter a duração estimada do serviço
            $query_duracao = "SELECT duracao_minutos FROM servicos WHERE id = :servico_id";
            $params_duracao = ['servico_id' => $servico_id];
            $resultado_duracao = select("servicos", "WHERE id = :servico_id", $params_duracao);

            if ($resultado_duracao && count($resultado_duracao) > 0) {
                $duracao_servico = $resultado_duracao[0]['duracao_minutos'];
                $data_hora_final = date('Y-m-d H:i:s', strtotime("+$duracao_servico minutes", strtotime($data_hora)));

                // Verificar disponibilidade do funcionário
                $disponivel = isTimeSlotAvailable($funcionario_id, $data_hora);

                if (!$disponivel) {
                    $aviso = "O funcionário selecionado já está agendado para esta data e hora. Por favor, escolha outro horário.";
                } else {
                    // Inserir o agendamento
                    $query_agendar = "INSERT INTO agendamentos (id_cliente, id_servico, id_funcionario, data_hora, status) VALUES (:id_cliente, :servico_id, :funcionario_id, :data_hora, :status)";
                    $params_agendar = [
                        'id_cliente' => $id_cliente,
                        'servico_id' => $servico_id,
                        'funcionario_id' => $funcionario_id,
                        'data_hora' => $data_hora,
                        'status' => $status
                    ];

                    if (executeQuery($query_agendar, $params_agendar)) {
                        $aviso = "Agendamento realizado com sucesso!";
                    } else {
                        $aviso = "Erro ao realizar o agendamento.";
                    }
                }
            } else {
                $aviso = "Erro ao obter a duração do serviço.";
            }
        }
    } catch (Exception $e) {
        $aviso = "Erro ao acessar o banco de dados: " . $e->getMessage();
    }
} else {
    $aviso = "Por favor, preencha o formulário e envie para agendar um serviço.";
}

// Redirecionar para a página inicial com mensagem de aviso
header("Location: ../index.php?aviso=" . urlencode($aviso));
exit();
