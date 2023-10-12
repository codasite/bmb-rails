#!/bin/bash
echo "Pulling latest wordpress from server...	"
parent_path=$( cd "$(dirname "${BASH_SOURCE[0]}")" ; pwd -P )
cd "$parent_path"
mkdir -p ../downloads

scp -r wpbb@147.182.190.133:~/wordpress.zip ../downloads/
scp -r wpbb@147.182.190.133:~/wordpress_dump.zip ../downloads/