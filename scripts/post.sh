post_action() {
    printf "%s " "$1" > /dev/stderr
    curl -s -X POST http://localhost/supervisor/$1 -d "$2"
    echo
}