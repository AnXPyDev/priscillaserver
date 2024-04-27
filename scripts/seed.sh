#!/bin/sh

. ./scripts/post.sh

post_action operator/deploy '{}'
post_action operator/adduser '{"username": "benko123", "password": "heslo123"}'