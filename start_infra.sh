#!/bin/bash

# Script de démarrage infrastructure Symfony + Monitoring (Dynamique)
# Usage : ./start_infra.sh [IP_SERVEUR]

SERVER_IP=${1:-127.0.0.1}
COMPOSE_FILE="docker-compose.yml"
GREEN='\033[0;32m'
CYAN='\033[0;36m'
NC='\033[0m' # No Color

echo -e "${GREEN}🚀 Lancement des services via Docker Compose...${NC}"
docker-compose up -d --build

# Attendre quelques secondes pour que les conteneurs se stabilisent
sleep 5

echo -e "\n${GREEN}📡 Récupération des ports exposés...${NC}"

# Fonction pour obtenir le port mappé d'un service donné
get_port() {
  local service=$1
  local internal_port=$2
  docker-compose port "$service" "$internal_port" 2>/dev/null | awk -F: '{print $2}'
}

# Obtenir les ports dynamiquement
SYMFONY_PORT=$(get_port symfony 8443)
PGADMIN_PORT=$(get_port pgadmin 80)
POSTGRES_PORT=$(get_port database 5432)


echo -e "\n${CYAN}🔗 Accès aux services (${SERVER_IP}) :${NC}"

[[ -n "$SYMFONY_PORT" ]] && echo -e "🌐 Symfony (FrankenPHP)   : http://${SERVER_IP}:${SYMFONY_PORT}"
[[ -n "$PGADMIN_PORT" ]] && echo -e "🛠️  pgAdmin                : http://${SERVER_IP}:${PGADMIN_PORT} (admin@example.com / admin)"
[[ -n "$POSTGRES_PORT" ]] && echo -e "🐘 PostgreSQL              : ${SERVER_IP}:${POSTGRES_PORT} (postgres / postgres)"

echo -e "\n${GREEN}✅ Tous les services sont lancés.${NC}"
