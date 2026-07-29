#!/usr/bin/env bash

set -u
set -o pipefail

BASE_URL="https://oris.ceskyorientak.cz/API/"
FIRST_ID="${FIRST_ID:-1}"
LAST_ID="${LAST_ID:-10245}"
OUTPUT_DIR="${OUTPUT_DIR:-oris_events}"
DELAY="${DELAY:-0.1}"
FAILURE_LOG="${OUTPUT_DIR}/failed_ids.txt"

if ! command -v curl >/dev/null 2>&1; then
    echo "Error: curl is required." >&2
    exit 1
fi

if ! [[ "$FIRST_ID" =~ ^[0-9]+$ && "$LAST_ID" =~ ^[0-9]+$ ]] ||
   (( FIRST_ID < 1 || FIRST_ID > LAST_ID )); then
    echo "Error: FIRST_ID and LAST_ID must define a valid positive range." >&2
    exit 1
fi

mkdir -p "$OUTPUT_DIR"
: > "$FAILURE_LOG"

downloaded=0
skipped=0
failed=0

for ((id = FIRST_ID; id <= LAST_ID; id++)); do
    output="${OUTPUT_DIR}/event_${id}.xml"
    temporary="${output}.part"

    if [[ -s "$output" ]]; then
        ((skipped++))
        continue
    fi

    printf 'Downloading event %d/%d...\r' "$id" "$LAST_ID"

    if curl --silent --show-error --location \
        --retry 4 --retry-delay 2 --retry-all-errors \
        --connect-timeout 15 --max-time 60 \
        --get \
        --data-urlencode "format=xml" \
        --data-urlencode "method=getEvent" \
        --data-urlencode "id=${id}" \
        --output "$temporary" \
        "$BASE_URL" &&
       [[ -s "$temporary" ]]; then
        mv "$temporary" "$output"
        ((downloaded++))
    else
        rm -f "$temporary"
        printf '%d\n' "$id" >> "$FAILURE_LOG"
        ((failed++))
    fi

    if [[ "$DELAY" != "0" ]]; then
        sleep "$DELAY"
    fi
done

printf '\nDone: %d downloaded, %d already present, %d failed.\n' \
    "$downloaded" "$skipped" "$failed"

if (( failed == 0 )); then
    rm -f "$FAILURE_LOG"
else
    printf 'Failed IDs are listed in %s\n' "$FAILURE_LOG" >&2
    exit 1
fi
