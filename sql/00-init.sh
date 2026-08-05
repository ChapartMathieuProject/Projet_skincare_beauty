#!/bin/bash
# Contournement du bug entrypoint MySQL (--comments + DELIMITER, cf. docker-library/mysql#746) :
# on importe le script SQL manuellement, sans l option --comments.
echo ">> [00-init.sh] Import de creation_database.sql"
mysql --default-character-set=utf8mb4 -uroot -p"${MYSQL_ROOT_PASSWORD}" --protocol=socket -hlocalhost < /docker-entrypoint-initdb.d/data/creation_database.sql
echo ">> [00-init.sh] Import termine"
