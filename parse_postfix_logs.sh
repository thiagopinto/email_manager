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
php $PROJECT_DIR/bin/console app:parse-postfix-logs $LOG_FILE

#!/bin/bash

# Obtém os IDs das mensagens na fila deferred
deferred_ids=$(mailq | awk '$7 == "deferred" {print $1}' | tr -d '*')

# Para cada ID de mensagem, verifique se contém "Connection timed out"
for id in $deferred_ids; do
    if ! postcat -q $id | grep -q "Connection timed out"; then
        postsuper -d $id
        echo "Email $id deleted"
    fi
done

