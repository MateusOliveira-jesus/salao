document.addEventListener('DOMContentLoaded', () => {
    const monthYear = document.getElementById('monthYear');
    const calendarTableBody = document.querySelector('#calendarTable tbody');
    const eventForm = document.getElementById('eventForm');
    const eventTimeInput = document.getElementById('eventTime');
    const saveEventButton = document.getElementById('saveEvent');
    const cancelEventButton = document.getElementById('cancelEvent');
    const selectedDateTimeInput = document.getElementById('selectedDateTime');
    const prevMonthButton = document.getElementById('prevMonth');
    const nextMonthButton = document.getElementById('nextMonth');
    const funcionarioSelect = document.getElementById('funcionario');
    const servicoSelect = document.getElementById('servico');
    const agendamentoList = document.getElementById('agendamentoList');

    let currentDate = new Date();
    let selectedDate;

    
    function updateCalendar() {
        console.log("Atualizando o calendário..."); // Debugging
        const firstDay = new Date(currentDate.getFullYear(), currentDate.getMonth(), 1).getDay();
        const daysInMonth = new Date(currentDate.getFullYear(), currentDate.getMonth() + 1, 0).getDate();

        monthYear.textContent = `${currentDate.toLocaleString('default', { month: 'long' })} ${currentDate.getFullYear()}`;

        calendarTableBody.innerHTML = '';
        let row = document.createElement('tr');
        for (let i = 0; i < firstDay; i++) {
            row.appendChild(document.createElement('td'));
        }

        for (let day = 1; day <= daysInMonth; day++) {
            const cell = document.createElement('td');
            cell.textContent = day;
            const cellDate = `${currentDate.getFullYear()}-${String(currentDate.getMonth() + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
            cell.dataset.date = cellDate;

            cell.addEventListener('click', () => {
                console.log(`Dia selecionado: ${cell.dataset.date}`); // Debugging
                selectDay(cell);
                selectedDate = new Date(cell.dataset.date);
                showEventForm();
            });

            row.appendChild(cell);

            if (row.children.length === 7) {
                calendarTableBody.appendChild(row);
                row = document.createElement('tr');
            }
        }
        calendarTableBody.appendChild(row);

        // Marcar datas agendadas para o funcionário selecionado
        marcarDatasAgendadas();
       marcarHorariosIndisponiveis();
    }

    function marcarDatasAgendadas() {
        const cells = document.querySelectorAll('#calendarTable tbody td');
        const funcionarioId = funcionarioSelect.value;
        const agendamentosParaFuncionario = agendamentosPorFuncionario[funcionarioId] || [];
        cells.forEach(cell => {
            const cellDate = cell.dataset.date;
            if (agendamentosParaFuncionario.includes(cellDate)) {
                cell.classList.add('agendado');
            } else {
                cell.classList.remove('agendado');
            }
        });
    }

    function selectDay(cell) {
        // Remove a classe 'select' de qualquer célula que tenha atualmente
        const previouslySelected = document.querySelector('.calendar td.select');
        if (previouslySelected) {
            previouslySelected.classList.remove('select');
        }

        // Adiciona a classe 'select' à célula clicada
        cell.classList.add('select');
    }

    function showEventForm() {
        if (selectedDate) {
            eventForm.classList.remove('hidden');
            const time = eventTimeInput.value || '00:00'; // Default time if not set
            selectedDateTimeInput.value = `${selectedDate.toISOString().split('T')[0]}T${time}`;
            marcarHorariosIndisponiveis();
        }
    }

    function saveEvent() {
        if (selectedDate) {
            const time = eventTimeInput.value || '00:00'; // Default time if not set
            const dateTime = `${selectedDate.toISOString().split('T')[0]}T${time}`;
            console.log("Saving DateTime:", dateTime); // Log the datetime being saved
            selectedDateTimeInput.value = dateTime;
            eventForm.classList.add('hidden');
            updateCalendar();
        }
    }

    function cancelEvent() {
        eventForm.classList.add('hidden');
    }

    prevMonthButton.addEventListener('click', () => {
        currentDate.setMonth(currentDate.getMonth() - 1);
        updateCalendar();
    });

    nextMonthButton.addEventListener('click', () => {
        currentDate.setMonth(currentDate.getMonth() + 1);
        updateCalendar();
    });

    saveEventButton.addEventListener('click', saveEvent);
    cancelEventButton.addEventListener('click', cancelEvent);

    funcionarioSelect.addEventListener('change', () => {
        updateCalendar();
        marcarHorariosIndisponiveis();
    });
    eventTimeInput.addEventListener('change', marcarHorariosIndisponiveis);
    updateCalendar();

    const avisoElement = document.querySelector('h3'); // Se o aviso estiver em um <h3>

    // Se o avisoElement contiver algum texto, limpe-o após 2 segundos
    if (avisoElement && avisoElement.textContent.trim() !== '') {
        setTimeout(() => {
            avisoElement.textContent = '';
        }, 2000);
    }
  function marcarHorariosIndisponiveis() {
    // Obter o funcionário selecionado
    const funcionarioId = funcionarioSelect.value;

    // Obter os agendamentos para o funcionário selecionado
    const agendamentosParaFuncionario = agendamentosFuncionarioDataSecondary[funcionarioId] || [];
    
    // Obter todas as opções de horário no seletor
    const options = eventTimeInput.querySelectorAll('option');

    // Criar um conjunto de horários indisponíveis
    const horariosIndisponiveis = new Set();

    // Iterar sobre cada agendamento do funcionário e adicionar os horários indisponíveis ao conjunto
    agendamentosParaFuncionario.forEach(agendamento => {
        const [dataHora, duracaoServicoMinutos] = agendamento; // Ajuste para o novo formato
        const horarioInicio = new Date(dataHora);
        const horarioFim = new Date(horarioInicio.getTime() + duracaoServicoMinutos * 60 * 1000); // Usa a duração do serviço

        options.forEach(option => {
            const horarioOption = `${selectedDate.toISOString().split('T')[0]}T${option.value}`;
            const horarioAtual = new Date(horarioOption);

            // Adiciona o horário ao conjunto se estiver dentro do intervalo de indisponibilidade
            if (horarioAtual >= horarioInicio && horarioAtual < horarioFim) {
                horariosIndisponiveis.add(horarioOption);
            }
        });
    });

    // Iterar sobre todas as opções de horário e desabilitar as que estão no conjunto de horários indisponíveis
    options.forEach(option => {
        const horarioOption = `${selectedDate.toISOString().split('T')[0]}T${option.value}`;
        if (horariosIndisponiveis.has(horarioOption)) {
            option.classList.add('disable'); // Adiciona a classe 'disable'
            option.disabled = true; // Também desativa o elemento para garantir que não possa ser selecionado
        } else {
            option.classList.remove('disable'); // Remove a classe 'disable'
            option.disabled = false; // Garante que o elemento esteja ativo
        }
    });
}
function marcarHorariosIndisponiveis() {
    // Obter o funcionário selecionado
    const funcionarioId = funcionarioSelect.value;

    // Obter os agendamentos para o funcionário selecionado
    const agendamentosParaFuncionario = agendamentosFuncionarioDataSecondary[funcionarioId] || [];
    
    // Obter todas as opções de horário no seletor
    const options = eventTimeInput.querySelectorAll('option');

    // Criar um conjunto de horários indisponíveis
    const horariosIndisponiveis = new Set();

    // Iterar sobre cada agendamento do funcionário e adicionar os horários indisponíveis ao conjunto
    agendamentosParaFuncionario.forEach(agendamento => {
        const [dataHora, duracaoServicoMinutos] = agendamento; // Ajuste para o novo formato
        const horarioInicio = new Date(dataHora);
        const horarioFim = new Date(horarioInicio.getTime() + duracaoServicoMinutos * 60 * 1000); // Usa a duração do serviço

        console.log(`Agendamento - Início: ${horarioInicio}, Fim: ${horarioFim}`);

        options.forEach(option => {
            const horarioOption = `${selectedDate.toISOString().split('T')[0]}T${option.value}`;
            const horarioAtual = new Date(horarioOption);

            // Adiciona o horário ao conjunto se estiver dentro do intervalo de indisponibilidade, incluindo o horário de fim
            if (horarioAtual >= horarioInicio && horarioAtual <= horarioFim) {
                console.log(`Indisponível: ${horarioOption}`);
                horariosIndisponiveis.add(horarioOption);
            }
        });
    });

    // Iterar sobre todas as opções de horário e desabilitar as que estão no conjunto de horários indisponíveis
    options.forEach(option => {
        const horarioOption = `${selectedDate.toISOString().split('T')[0]}T${option.value}`;
        if (horariosIndisponiveis.has(horarioOption)) {
            option.classList.add('disable'); // Adiciona a classe 'disable'
            option.disabled = true; // Também desativa o elemento para garantir que não possa ser selecionado
        } else {
            option.classList.remove('disable'); // Remove a classe 'disable'
            option.disabled = false; // Garante que o elemento esteja ativo
        }
    });
}

    
    
        
    
});
