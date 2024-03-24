#!/bin/sh

PORT=3000

COOKIE=cookie

post() {
    printf "$1 $2 -> "
    curl -b $COOKIE -c $COOKIE -X POST "http://localhost:$PORT/$1" -H "Content-Type: application/json" -d "$2";
    echo;
}

USER=benko
PASSWORD=heslo123

post user/register '{"username": "'$USER'", "password": "'$PASSWORD'"}'
post user/login '{"username": "'$USER'", "password": "'$PASSWORD'"}'
post user/info '{}'
post user/createRoom '{"name": "Python test 1"}'
post user/logout '{}'