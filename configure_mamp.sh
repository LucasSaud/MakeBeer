#!/bin/bash

# Script para configurar Nginx no MAMP para o MakeBeer

NGINX_CONF="/Applications/MAMP/conf/nginx/nginx.conf"
BACKUP_CONF="/Applications/MAMP/conf/nginx/nginx.conf.backup"

echo "=========================================="
echo "Configurador MAMP Nginx - MakeBeer"
echo "=========================================="
echo ""

# Verificar se MAMP está instalado
if [ ! -f "$NGINX_CONF" ]; then
    echo "❌ MAMP não encontrado em /Applications/MAMP"
    echo "Certifique-se de que o MAMP está instalado."
    exit 1
fi

# Fazer backup do arquivo original
if [ ! -f "$BACKUP_CONF" ]; then
    echo "📦 Fazendo backup da configuração original..."
    cp "$NGINX_CONF" "$BACKUP_CONF"
    echo "✅ Backup criado: $BACKUP_CONF"
else
    echo "ℹ️  Backup já existe: $BACKUP_CONF"
fi

echo ""
echo "Você precisa adicionar esta configuração ao seu nginx.conf:"
echo ""
echo "=========================================="
cat << 'EOF'

# Adicione dentro do bloco server { } existente:

location /teste {
    try_files $uri $uri/ /teste/index.php?$query_string;

    location ~ \.php$ {
        try_files $uri =404;
        fastcgi_pass unix:/Applications/MAMP/Library/logs/fastcgi/nginxtmp.socket;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
}

EOF
echo "=========================================="
echo ""
echo "📝 Instruções:"
echo "1. Abra o arquivo: $NGINX_CONF"
echo "2. Localize o bloco 'server {'"
echo "3. Adicione a configuração acima dentro dele"
echo "4. Salve o arquivo"
echo "5. Reinicie o Nginx no MAMP"
echo ""
echo "Ou execute:"
echo "  sudo nano $NGINX_CONF"
echo ""
echo "=========================================="
echo ""
echo "Deseja que eu tente adicionar automaticamente? (s/n)"
read -r resposta

if [[ "$resposta" =~ ^[Ss]$ ]]; then
    echo ""
    echo "⚠️  Esta operação requer permissão de administrador."
    echo "Digite sua senha se solicitado:"
    echo ""

    # Verificar se já existe a configuração
    if grep -q "location /teste" "$NGINX_CONF"; then
        echo "ℹ️  Configuração já existe no arquivo."
    else
        # Adicionar configuração
        sudo sed -i.bak '/server {/a\
\
    location /teste {\
        try_files $uri $uri/ /teste/index.php?$query_string;\
        \
        location ~ \\.php$ {\
            try_files $uri =404;\
            fastcgi_pass unix:/Applications/MAMP/Library/logs/fastcgi/nginxtmp.socket;\
            fastcgi_index index.php;\
            fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;\
            include fastcgi_params;\
        }\
    }
' "$NGINX_CONF"

        echo "✅ Configuração adicionada!"
        echo ""
        echo "🔄 Agora reinicie o Nginx no MAMP:"
        echo "   1. Abra o MAMP"
        echo "   2. Clique em 'Stop Servers'"
        echo "   3. Clique em 'Start Servers'"
    fi
else
    echo "Ok, adicione manualmente seguindo as instruções acima."
fi

echo ""
echo "=========================================="
echo "✅ Configuração finalizada!"
echo "=========================================="
