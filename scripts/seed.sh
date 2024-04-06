#!/bin/sh

. ./scripts/post.sh

post_action operator/deploy '{}'
post_action operator/adduser '{"username": "benko123", "password": "heslo123"}'
post_action operator/addconfig '{"name": "priscillatest", "data": '"$(cat assets/priscillatest.json)"'}'