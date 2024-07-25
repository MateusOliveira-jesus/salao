<?php
$h1 = 'Página inicial';
$desc = 'Explore nossos serviços e veja seus agendamentos.';
$keyword = 'global list, agendamentos, serviços';

// Incluir o cabeçalho da página
include 'incl/head.php';
$aviso = isset($_GET['aviso']) ? $_GET['aviso'] : ' ';

// Consulta para obter os serviços e funcionários
$servicos = select("servicos");
$funcionarios = select("funcionarios");

// Consulta para obter os agendamentos existentes
$agendamentos = select("agendamentos");

// Agrupar os agendamentos por data e funcionário
$agendamentosPorFuncionario = [];
foreach ($agendamentos as $agendamento) {
    $data = explode(' ', $agendamento['data_hora'])[0];
    $funcionarioId = $agendamento['id_funcionario'];
    if (!isset($agendamentosPorFuncionario[$funcionarioId])) {
        $agendamentosPorFuncionario[$funcionarioId] = [];
    }
    $agendamentosPorFuncionario[$funcionarioId][] = $data;
}
//secondary
//secondary
// $agendamentosSecondary = array_map(function($agendamento) {
//     list($data, $hora) = explode(' ', $agendamento['data_hora']);
//     return [
//         'data' => $data,
//         'horario' => $hora,
//         'id_funcionario' => $agendamento['id_funcionario']
//     ];
// }, $agendamentos);
// $agendamentosSecondary = [
//     ['data' => '2024-07-23', 'horario' => '09:00', 'id_funcionario' => '7',  'duracao_servico'=>'60'],
//     ['data' => '2024-07-23', 'horario' => '14:00', 'id_funcionario' => '7',  'duracao_servico'=>'60'],
//     ['data' => '2024-07-23', 'horario' => '11:00', 'id_funcionario' => '7',  'duracao_servico'=>'60'],
//     ['data' => '2024-07-23', 'horario' => '12:30', 'id_funcionario' => '7',  'duracao_servico'=>'60'],
//     ['data' => '2024-07-23', 'horario' => '13:30', 'id_funcionario' => '7',  'duracao_servico'=>'60'],
//     // Adicione mais agendamentos conforme necessário
// ];$duracoesServicos = [];

foreach ($servicos as $servico) {
    $duracoesServicos[$servico['id']] = $servico['duracao_minutos'];
}

$agendamentosSecondary = array_map(function ($agendamento) use ($duracoesServicos) {
    list($data, $hora) = explode(' ', $agendamento['data_hora']);
    $duracaoServico = $duracoesServicos[$agendamento['id_servico']] ?? 60; // Padrão para 60 minutos se não encontrado

    return [
        'data' => $data,
        'horario' => $hora,
        'id_funcionario' => $agendamento['id_funcionario'],
        'duracao_servico' => $duracaoServico
    ];
}, $agendamentos);
var_dump($agendamentosSecondary);
$agendamentosSecondaryJson = json_encode($agendamentosSecondary);

// Organizar os agendamentos por ID de funcionário
$agendamentosFuncionarioData = [];
foreach ($agendamentosSecondary as $agendamento) {
    $id = $agendamento['id_funcionario'];
    $dataHora = $agendamento['data'] . 'T' . $agendamento['horario'];
    if (!isset($agendamentosFuncionarioData[$id])) {
        $agendamentosFuncionarioData[$id] = [];
    }
    $agendamentosFuncionarioData[$id][] = [$dataHora, $agendamento['duracao_servico']];
}

$agendamentosJson = json_encode($agendamentosFuncionarioData);

?>
<script>
    
    // Passar os dados PHP para o JavaScript
    const agendamentosFuncionarioDataSecondary = <?php echo $agendamentosJson; ?>;
    console.log('Dados JSON dos agendamentos:', agendamentosFuncionarioDataSecondary);

    const funcionarios = <?= json_encode($funcionarios) ?>;
    const servicos = <?= json_encode($servicos) ?>;
    const agendamentosPorFuncionario = <?= json_encode($agendamentosPorFuncionario) ?>;

    // Você pode adicionar código JavaScript adicional aqui para manipular os dados.
</script>
</head>

<body>
    <?php include('incl/menu.php'); ?>

    <main class="wrapper">
        <div class="container">
            <section class="intro">
                <h2>Bem-vindo ao GlobalList</h2>
                <p>Aqui você encontra uma lista global de serviços e informações importantes.</p>
            </section>
            <section class="agendamentos">
                <h2>Agendamentos</h2>
                <h3><?= isset($aviso) ? htmlspecialchars($aviso) : '' ?></h3>
                <!-- Formulário de Agendamento -->
                <form action="back/processa-index.php" class="row gap-20" method="post">
                    <div class="col-12">
                        <label for="servico">Escolha um serviço:</label>
                        <select name="servico" id="servico" required>
                            <?php foreach ($servicos as $servico) : ?>
                                <option data-id-funcionario="<?= htmlspecialchars($servico['id']) ?>" value="<?= htmlspecialchars($servico['id']) ?>">
                                    <?= htmlspecialchars($servico['nome']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12">
                        <label for="funcionario">Escolha um funcionário:</label>
                        <select name="funcionario" id="funcionario" required>
                            <?php foreach ($funcionarios as $funcionario) : ?>
                                <option value="<?= htmlspecialchars($funcionario['id']) ?>">
                                    <?= htmlspecialchars($funcionario['nome']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12">
                        <input type="hidden" id="selectedDateTime" name="data_hora">
                        <div class="col-8">
                            <div class="calendar">
                                <div class="calendar-header">
                                    <button id="prevMonth" type="button">&#9664;</button>
                                    <span id="monthYear"></span>
                                    <button id="nextMonth" type="button">&#9654;</button>
                                </div>
                                <table id="calendarTable">
                                    <thead>
                                        <tr>
                                            <th>Dom</th>
                                            <th>Seg</th>
                                            <th>Ter</th>
                                            <th>Qua</th>
                                            <th>Qui</th>
                                            <th>Sex</th>
                                            <th>Sáb</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                                <div id="eventForm" class="hidden">
                                    <select id="eventTime" placeholder="Hora do Evento">
                                        <option value="00:00">00:00</option>
                                        <option value="00:30">00:30</option>
                                        <option value="01:00">01:00</option>
                                        <option value="01:30">01:30</option>
                                        <option value="02:00">02:00</option>
                                        <option value="02:30">02:30</option>
                                        <option value="03:00">03:00</option>
                                        <option value="03:30">03:30</option>
                                        <option value="04:00">04:00</option>
                                        <option value="04:30">04:30</option>
                                        <option value="05:00">05:00</option>
                                        <option value="05:30">05:30</option>
                                        <option value="06:00">06:00</option>
                                        <option value="06:30">06:30</option>
                                        <option value="07:00">07:00</option>
                                        <option value="07:30">07:30</option>
                                        <option value="08:00">08:00</option>
                                        <option value="08:30">08:30</option>
                                        <option value="09:00">09:00</option>
                                        <option value="09:30">09:30</option>
                                        <option value="10:00">10:00</option>
                                        <option value="10:30">10:30</option>
                                        <option value="11:00">11:00</option>
                                        <option value="11:30">11:30</option>
                                        <option value="12:00">12:00</option>
                                        <option value="12:30">12:30</option>
                                        <option value="13:00">13:00</option>
                                        <option value="13:30">13:30</option>
                                        <option value="14:00">14:00</option>
                                        <option value="14:30">14:30</option>
                                        <option value="15:00">15:00</option>
                                        <option value="15:30">15:30</option>
                                        <option value="16:00">16:00</option>
                                        <option value="16:30">16:30</option>
                                        <option value="17:00">17:00</option>
                                        <option value="17:30">17:30</option>
                                        <option value="18:00">18:00</option>
                                        <option value="18:30">18:30</option>
                                        <option value="19:00">19:00</option>
                                        <option value="19:30">19:30</option>
                                        <option value="20:00">20:00</option>
                                        <option value="20:30">20:30</option>
                                        <option value="21:00">21:00</option>
                                        <option value="21:30">21:30</option>
                                        <option value="22:00">22:00</option>
                                        <option value="22:30">22:30</option>
                                        <option value="23:00">23:00</option>
                                        <option value="23:30">23:30</option>
                                    </select>

                                    <button id="saveEvent" type="button">Salvar</button>
                                    <button id="cancelEvent" type="button">Cancelar</button>
                                </div>

                            </div>

                        </div>
                        <div><button type="submit" name="agendar" class="btn btn--outline" value="Agendar">Agendar</button></div>
                    </div>
                </form>
            </section>
            <ul class="list " id="agendamentoList">
                <?php foreach ($agendamentos as $key => $value) : ?>
                    <?php list($data, $hora) = explode(' ', $value['data_hora']) ?>
                    <li class="primary-color"><?= titleCase('Data Agendamento'); ?> <span class="light bg-primary-color pl-5 pr-5"><?= htmlspecialchars($data) ?></span></li>
                    <li class="primary-color"><?= titleCase('Hora Agendamento'); ?> <span class="light bg-primary-color pl-5 pr-5"><?= htmlspecialchars($hora) ?></span></li>
                    <li class="primary-color"><?= titleCase('Data e Hora Agendamento'); ?> <span class="light bg-primary-color pl-5 pr-5"><?= htmlspecialchars($value['data_hora']) ?></span></li>
                    <li class="primary-color"><?= titleCase('ID Serviço'); ?> <span class="light bg-primary-color pl-5 pr-5"><?= htmlspecialchars($value['id_servico']) ?></span></li>
                    <li class="primary-color"><?= titleCase('ID Funcionário'); ?> <span class="light bg-primary-color pl-5 pr-5"><?= htmlspecialchars($value['id_funcionario']) ?></span></li>
                    <li class="primary-color"><?= titleCase('ID Cliente'); ?> <span class="light bg-primary-color pl-5 pr-5"><?= htmlspecialchars($value['id_cliente']) ?></span></li>
                    <li class="primary-color"><?= titleCase('ID Agendamento'); ?> <span class="light bg-primary-color pl-5 pr-5"><?= htmlspecialchars($value['id']) ?></span></li>
                    <li class="primary-color"><?= titleCase('Status Agendamento'); ?> <span class="light bg-primary-color pl-5 pr-5"><?= htmlspecialchars($value['status']) ?></span></li>
                    <?php foreach ($servicos as $servico) : ?>
                        <?php if ($servico['id'] == $value['id_servico']) : ?>
                            <li class="primary-color"><?= titleCase('ID Serviço:') ?> <span class="light bg-primary-color pl-5 pr-5"><?= htmlspecialchars($servico['id']) ?></span></li>
                            <li class="primary-color"><?= titleCase('Nome Serviço:') ?> <span class="light bg-primary-color pl-5 pr-5"><?= htmlspecialchars($servico['nome']) ?></span></li>
                            <li class="primary-color"><?= titleCase('Duração Serviço:') ?> <span class="light bg-primary-color pl-5 pr-5"><?= htmlspecialchars($servico['duracao_minutos']) ?></span></li>
                            <li class="primary-color"><?= titleCase('Valor Serviço:') ?> <span class="light bg-primary-color pl-5 pr-5"><?= htmlspecialchars($servico['valor_servico']) ?></span></li>
                            <li class="primary-color"><?= titleCase('Status Serviço:') ?> <span class="light bg-primary-color pl-5 pr-5"><?= htmlspecialchars($servico['status']) ?></span></li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                    <?php foreach ($funcionarios as $funcionario) : ?>
                        <?php if ($funcionario['id'] == $value['id_funcionario']) : ?>
                            <li class="primary-color"><?= titleCase('ID Funcionário') ?> <span class="light bg-primary-color pl-5 pr-5"><?= htmlspecialchars($funcionario['id']) ?></span></li>
                            <li class="primary-color"><?= titleCase('Nome Funcionário') ?> <span class="light bg-primary-color pl-5 pr-5"><?= htmlspecialchars($funcionario['nome']) ?></span></li>
                            <li class="primary-color"><?= titleCase('Cargo Funcionário') ?> <span class="light bg-primary-color pl-5 pr-5"><?= htmlspecialchars($funcionario['cargo']) ?></span></li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                    <br>
                <?php endforeach; ?>
            </ul>
        </div>
    </main>

    <script>
        <?php include('js/script-calendario.js') ?>
    </script>
</body>

</html>