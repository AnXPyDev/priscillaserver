. ./.env

PORT=$FASTIFY_PORT

COOKIE=/dev/null

post() {
    printf "$1 $2 -> "
    curl -b $COOKIE -c $COOKIE -X POST "http://localhost:$PORT/$1" -H "Content-Type: application/json" -d "$2";
    echo;
}
