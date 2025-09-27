#!/bin/bash
set -e

psql -v ON_ERROR_STOP=1 --username "$POSTGRES_USER" <<-EOSQL
  SELECT 'CREATE DATABASE backend_db'
    WHERE NOT EXISTS (SELECT FROM pg_database WHERE datname = 'backend_db')\gexec

  DO \$\$
  BEGIN
    IF NOT EXISTS (SELECT FROM pg_roles WHERE rolname = 'backend_user') THEN
      CREATE ROLE backend_user WITH LOGIN PASSWORD 'backendpassword';
    END IF;
  END
  \$\$;

  GRANT ALL PRIVILEGES ON DATABASE backend_db TO backend_user;
EOSQL