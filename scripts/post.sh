post_action() {
    curl -s -X POST http://localhost/supervisor/$1 -d "$2"
    echo
}