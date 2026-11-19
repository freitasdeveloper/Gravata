# Sistema de Agenda - Instruções de Instalação

## 1. Criar Tabelas no Banco de Dados

Execute o arquivo SQL `criar_tabela_agenda.sql` no seu banco de dados MySQL/MariaDB:

```bash
mysql -u root -p sistema_juridico < criar_tabela_agenda.sql
```

Ou importe manualmente através do phpMyAdmin ou outro cliente MySQL.

## 2. Configurar Processamento Automático de Notificações

Para que os lembretes por email sejam enviados automaticamente 1 dia antes dos eventos, configure um cron job:

### Linux/Mac:
```bash
# Edite o crontab
crontab -e

# Adicione a linha abaixo (executa todo dia às 8h da manhã)
0 8 * * * /usr/bin/php /caminho/completo/para/Gravata/processar_notificacoes_agenda.php >> /caminho/logs/agenda.log 2>&1
```

### Windows (Task Scheduler):
1. Abra o Agendador de Tarefas
2. Crie uma nova tarefa
3. Configure para executar diariamente às 8h
4. Ação: Iniciar um programa
5. Programa: `C:\xampp\php\php.exe`
6. Argumentos: `C:\xampp\htdocs\Gravata\processar_notificacoes_agenda.php`

### Processamento Manual:
Você também pode executar manualmente:
```bash
php processar_notificacoes_agenda.php
```

## 3. Configurar Email (config.php)

Atualize as configurações de email no arquivo `config.php`:

```php
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_USER', 'seu-email@gmail.com');
define('SMTP_PASS', 'sua-senha-app');
define('SMTP_PORT', 587);
define('EMAIL_FROM', 'noreply@legatus.com');
define('EMAIL_FROM_NAME', 'Legatus');
```

**Nota:** Para Gmail, você precisa criar uma "Senha de App" nas configurações de segurança da sua conta Google.

## 4. Funcionalidades Implementadas

✅ Agenda dinâmica com calendário FullCalendar
✅ Múltiplos tipos de eventos (Reunião, Consultoria, Audiência, Visita, etc.)
✅ Adicionar, editar e excluir eventos
✅ Filtros por data, tipo e status
✅ Sistema de lembretes por email (1 dia antes)
✅ Estrutura preparada para WhatsApp (futuro)
✅ Estatísticas de eventos
✅ Calendário visual interativo

## 5. Tipos de Eventos Disponíveis

- Reunião
- Consultoria
- Audiência
- Visita
- Compromisso
- Ligação
- Tarefa
- Outros

Os tipos podem ser personalizados editando a tabela `tipos_eventos_agenda` no banco de dados.

## 6. Status dos Eventos

- **Agendado**: Evento futuro
- **Em Andamento**: Evento que está acontecendo
- **Concluído**: Evento finalizado
- **Cancelado**: Evento cancelado

## 7. Próximas Implementações

- [ ] Integração com WhatsApp para lembretes
- [ ] Sincronização com Google Calendar
- [ ] Exportação de eventos (iCal)
- [ ] Notificações push no navegador
- [ ] Recorrência de eventos

