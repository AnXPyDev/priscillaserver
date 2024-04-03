#!/bin/sh

. ./scripts/post.sh

SESSION="$(post_action user/login '{"username": "benko123", "password": "heslo123"}' | jq -r .session)"

echo "$SESSION"

post_action user/info '{"session": "'$SESSION'"}'
post_action user/logout '{"session": "'$SESSION'"}'
post_action user/info '{"session": "'$SESSION'"}'

