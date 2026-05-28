#!/bin/sh
set -eu

# Keep group write access on newly created files, including root-created logs.
umask 0002

mkdir -p /var/www/html/members/logs
chown www-data:www-data /var/www/html/members/logs
# Assign new created files to the group of logs
chmod 3775 /var/www/html/members/logs

if [ -z "${BANK_SYNC_START_DATE:-}" ]; then
    export BANK_SYNC_START_DATE="$(date -d '2 days ago' +%F)"
fi

if [ -n "${BANK_SYNC_START_DATE:-}" ]; then
    cfg_date_file="/tmp/members_cfg_date.php"

    cat > "${cfg_date_file}" <<EOF
<?php

\$g_bank_sync_start_date = '${BANK_SYNC_START_DATE}';

?>
EOF
fi

if [ -n "${BANK_MOCK_DB_NAME:-}" ] && [ -f "package.json" ]; then

    attempt=1
    max_attempts=30

    while [ "${attempt}" -le "${max_attempts}" ]; do
        if npm run --silent mock:bank:init >/tmp/mock-bank-init.log 2>&1; then
            break
        fi

        if [ "${attempt}" -eq "${max_attempts}" ]; then
            cat /tmp/mock-bank-init.log >&2
            echo "Bank mock DB initialization failed after ${max_attempts} attempts." >&2
            exit 1
        fi

        attempt=$((attempt + 1))
        sleep 1
    done
fi

if [ "${BANK_MOCK_START:-}" = "auto" ] && [ -f "package.json" ]; then
    npm run --silent mock:bank >logs/mock-bank.log 2>&1 &
fi

exec apache2-foreground
