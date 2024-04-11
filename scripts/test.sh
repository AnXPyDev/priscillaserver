#!/bin/sh

. ./scripts/post.sh

SESSION="$(post_action user/login '{"username": "benko123", "password": "heslo123"}' | tee /dev/stderr | jq -r .session)"

echo "$SESSION"

post_action user/info '{"session": "'$SESSION'"}'

JOIN_CODE="$(post_action user/createroom '{"session": "'$SESSION'", "config": "priscillatest"}' | tee /dev/stderr | jq -r .join_code)"


post_action client/joinroom '{"name": "Jozef Komaromy", "join_code": "'"$JOIN_CODE"'"}'

