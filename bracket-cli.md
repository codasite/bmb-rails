1. get your user
	`wp user get <username or email>`
2. create the bracket file
3. run the create command
	`cat bracket.json | jq -c '.' | wp wpbb bracket create --author=<your user id>`
