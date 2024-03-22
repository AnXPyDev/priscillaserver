#!/bin/sh

PORT=3000

postjson() {
    curl -X POST "http://localhost:$PORT/$1" -H "Content-Type: application/json" -d "$2";
    echo;
}

postjson action/echo '{"test": "sex"}'