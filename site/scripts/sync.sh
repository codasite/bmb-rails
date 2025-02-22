#!/bin/bash

# Syncing Trellis & Bedrock-based WordPress environments with WP-CLI aliases
# Version 1.2.0
# Copyright (c) Ben Word

DEVDIR="web/app/uploads/"
DEVSITE="https://backmybracket.test"

PRODDIR="web@backmybracket.com:/srv/www/backmybracket.com/shared/uploads/"
PRODSITE="https://backmybracket.com"

STAGDIR="web@dev.backmybracket.com:/srv/www/backmybracket.com/shared/uploads/"
STAGSITE="https://dev.backmybracket.com"

LOCAL=false
SKIP_DB=false
SKIP_ASSETS=false
SQL_FILE=""
POSITIONAL_ARGS=()

while [[ $# -gt 0 ]]; do
  case $1 in
    --skip-db)
      SKIP_DB=true
      shift
      ;;
    --skip-assets)
      SKIP_ASSETS=true
      shift
      ;;
    --local)
      LOCAL=true
      shift
      ;;
    --sql-file)
      # Store absolute path of SQL file
      SQL_FILE="$(cd "$(dirname "$2")" && pwd)/$(basename "$2")"
      shift 2
      ;;
    --*)
      echo "Unknown option $1"
      exit 1
      ;;
    *)
      POSITIONAL_ARGS+=("$1")
      shift
      ;;
  esac
done

set -- "${POSITIONAL_ARGS[@]}"

if [ $# != 2 ]
then
  echo "Usage: $0 [[--skip-db] [--skip-assets] [--local] [--sql-file SQL_FILE]] [ENV_FROM] [ENV_TO]"
exit;
fi

FROM=$1
TO=$2

bold=$(tput bold)
normal=$(tput sgr0)

case "$1-$2" in
  production-development) DIR="down ⬇️ "          FROMSITE=$PRODSITE; FROMDIR=$PRODDIR; TOSITE=$DEVSITE;  TODIR=$DEVDIR; ;;
  staging-development)    DIR="down ⬇️ "          FROMSITE=$STAGSITE; FROMDIR=$STAGDIR; TOSITE=$DEVSITE;  TODIR=$DEVDIR; ;;
  development-production) DIR="up ⬆️ "            FROMSITE=$DEVSITE;  FROMDIR=$DEVDIR;  TOSITE=$PRODSITE; TODIR=$PRODDIR; ;;
  development-staging)    DIR="up ⬆️ "            FROMSITE=$DEVSITE;  FROMDIR=$DEVDIR;  TOSITE=$STAGSITE; TODIR=$STAGDIR; ;;
  production-staging)     DIR="horizontally ↔️ ";  FROMSITE=$PRODSITE; FROMDIR=$PRODDIR; TOSITE=$STAGSITE; TODIR=$STAGDIR; ;;
  staging-production)     DIR="horizontally ↔️ ";  FROMSITE=$STAGSITE; FROMDIR=$STAGDIR; TOSITE=$PRODSITE; TODIR=$PRODDIR; ;;
  *) echo "usage: $0 [[--skip-db] [--skip-assets] [--local] [--sql-file SQL_FILE]] production development | staging development | development staging | development production | staging production | production staging" && exit 1 ;;
esac

if [ "$SKIP_DB" = false ]
then
  DB_MESSAGE=" - ${bold}reset the $TO database${normal} ($TOSITE)"
fi

if [ "$SKIP_ASSETS" = false ]
then
  ASSETS_MESSAGE=" - sync ${bold}$DIR${normal} from $FROM ($FROMSITE)?"
fi

if [ "$SKIP_DB" = true ] && [ "$SKIP_ASSETS" = true ]
then
  echo "Nothing to synchronize."
  exit;
fi

echo
echo "Would you really like to "
echo $DB_MESSAGE
echo $ASSETS_MESSAGE
read -r -p " [y/N] " response

if [[ "$response" =~ ^([yY][eE][sS]|[yY])$ ]]; then
  # Change to site directory
  cd ../ &&
  echo

  # Make sure both environments are available before we continue
  availfrom() {
    local AVAILFROM

    if [[ "$LOCAL" = true && $FROM == "development" ]]; then
      AVAILFROM=$(wp option get home 2>&1)
    else
      AVAILFROM=$(wp "@$FROM" option get home 2>&1)
    fi
    if [[ $AVAILFROM == *"Error"* ]]; then
      echo "❌  Unable to connect to $FROM"
      exit 1
    else
      echo "✅  Able to connect to $FROM"
    fi
  };
  availfrom

  availto() {
    local AVAILTO
    if [[ "$LOCAL" = true && $TO == "development" ]]; then
      AVAILTO=$(wp option get home 2>&1)
    else
      AVAILTO=$(wp "@$TO" option get home 2>&1)
    fi

    if [[ $AVAILTO == *"Error"* ]]; then
      echo "❌  Unable to connect to $TO $AVAILTO"
      exit 1
    else
      echo "✅  Able to connect to $TO"
    fi
  };
  availto

  if [ "$SKIP_DB" = false ]
  then
  echo "Syncing database..."
    # Check if importing from SQL file
    if [ -n "$SQL_FILE" ]; then
      echo "Importing from SQL file: $SQL_FILE"
      if [[ $TO == "development" ]]; then
        # For Trellis development environment, transfer file via trellis
        REMOTE_SQL_FILE="/tmp/$(basename "$SQL_FILE")"
        echo "Transferring SQL file to development machine..."
        trellis ssh development -c "rm -f $REMOTE_SQL_FILE" >/dev/null 2>&1
        scp "$SQL_FILE" "vagrant@backmybracket:$REMOTE_SQL_FILE" &&
        wp db reset --yes &&
        wp db import "$REMOTE_SQL_FILE" &&
        wp search-replace "$FROMSITE" "$TOSITE" --all-tables-with-prefix &&
        trellis ssh development -c "rm -f $REMOTE_SQL_FILE" >/dev/null 2>&1
      else
        # For remote environments, transfer and import
        REMOTE_SQL_FILE="/tmp/$(basename "$SQL_FILE")"
        echo "Transferring SQL file to remote machine..."
        if [[ "$TO" == "production" ]]; then
          scp "$SQL_FILE" "web@backmybracket.com:$REMOTE_SQL_FILE"
        elif [[ "$TO" == "staging" ]]; then
          scp "$SQL_FILE" "web@dev.backmybracket.com:$REMOTE_SQL_FILE"
        fi
        wp "@$TO" db reset --yes &&
        wp "@$TO" db import "$REMOTE_SQL_FILE" &&
        wp "@$TO" search-replace "$FROMSITE" "$TOSITE" --all-tables-with-prefix &&
        # Clean up remote SQL file
        if [[ "$TO" == "production" ]]; then
          ssh web@backmybracket.com "rm $REMOTE_SQL_FILE"
        elif [[ "$TO" == "staging" ]]; then
          ssh web@dev.backmybracket.com "rm $REMOTE_SQL_FILE"
        fi
      fi
    else
      # Existing database sync logic
      if [[ "$LOCAL" = true && $TO == "development" ]]; then
        wp db export --default-character-set=utf8mb4 &&
        wp db reset --yes &&
        wp "@$FROM" db export --default-character-set=utf8mb4 - | wp db import - &&
        wp search-replace "$FROMSITE" "$TOSITE" --all-tables-with-prefix
      elif [[ "$LOCAL" = true && $FROM == "development" ]]; then
        wp "@$TO" db export --default-character-set=utf8mb4 &&
        wp "@$TO" db reset --yes &&
        wp db export --default-character-set=utf8mb4 - | wp "@$TO" db import - &&
        wp "@$TO" search-replace "$FROMSITE" "$TOSITE" --all-tables-with-prefix
      else
        wp "@$TO" db export --default-character-set=utf8mb4 &&
        wp "@$TO" db reset --yes &&
        wp "@$FROM" db export --default-character-set=utf8mb4 - | wp "@$TO" db import - &&
        wp "@$TO" search-replace "$FROMSITE" "$TOSITE" --all-tables-with-prefix
      fi
    fi
  fi

  if [ "$SKIP_ASSETS" = false ]
  then
  echo "Syncing assets..."
    # Sync uploads directory
    chmod -R 755 web/app/uploads/ &&
    if [[ $DIR == "horizontally"* ]]; then
      [[ $FROMDIR =~ ^(.*): ]] && FROMHOST=${BASH_REMATCH[1]}
      [[ $FROMDIR =~ ^(.*):(.*)$ ]] && FROMDIR=${BASH_REMATCH[2]}
      [[ $TODIR =~ ^(.*): ]] && TOHOST=${BASH_REMATCH[1]}
      [[ $TODIR =~ ^(.*):(.*)$ ]] && TODIR=${BASH_REMATCH[2]}

      ssh -o ForwardAgent=yes $FROMHOST "rsync -aze 'ssh -o StrictHostKeyChecking=no' --progress $FROMDIR $TOHOST:$TODIR"
    else
      rsync -az --progress "$FROMDIR" "$TODIR"
    fi
  fi

  # Slack notification when sync direction is up or horizontal
  # if [[ $DIR != "down"* ]]; then
  #   USER="$(git config user.name)"
  #   curl -X POST -H "Content-type: application/json" --data "{\"attachments\":[{\"fallback\": \"\",\"color\":\"#36a64f\",\"text\":\"🔄 Sync from ${FROMSITE} to ${TOSITE} by ${USER} complete \"}],\"channel\":\"#site\"}" https://hooks.slack.com/services/xx/xx/xx
  # fi
  echo -e "\n🔄  Sync from $FROM to $TO complete.\n\n    ${bold}$TOSITE${normal}\n"
fi
