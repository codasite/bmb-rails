#! /bin/bash

parent_path=$( cd "$(dirname "${BASH_SOURCE[0]}")" ; pwd -P )
cd "$parent_path"

sed -i -e 's/utf8mb4_0900_ai_ci/utf8mb4_unicode_520_ci/g' ../import/1bmb_import.sql