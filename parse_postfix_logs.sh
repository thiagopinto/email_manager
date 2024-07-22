#!/bin/bash
# run_postfix_parser.sh

# Verificar qual diretório existe e definir o PROJECT_DIR
if [ -d "/home/yuansolucoes.com.br/email_manager" ]; then
    PROJECT_DIR="/home/yuansolucoes.com.br/email_manager"
elif [ -d "/home/yuansolucoes.com/email_manager" ]; then
    PROJECT_DIR="/home/yuansolucoes.com/email_manager"
else
    echo "Nenhum diretório encontrado. Verifique os caminhos."
    exit 1
fi

LOG_FILE="/var/log/mail.log"

# Executar o comando Symfony como yuans5732
su - yuans5732 -c "php $PROJECT_DIR/bin/console app:parse-postfix-logs $LOG_FILE"

# Limpar as filas de emails deferred como root
postsuper -d ALL deferred
