. ./scripts/lib.sh

COOKIE=seed.cookie

USER=benko
PASSWORD=heslo123

CLIENTCONFIG="$(cat resources/priscillatest.json)"

echo $CLIENTCONFIG

post user/register '{"username": "'$USER'", "password": "'$PASSWORD'"}'
post user/login '{"username": "'$USER'", "password": "'$PASSWORD'"}'
post user/addRoomConfig '@resources/priscillatest.json'
post user/createRoom '{"name": "Python test 1", "config": "priscillatest"}'
post user/logout '{}'