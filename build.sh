#!/bin/sh

ROOT="$(realpath .)"

[ $MSYSTEM ] && ROOT="$(cygpath -m $ROOT)"

LIB_EXT="so"

[ "$OS" = "Windows_NT" ] && LIB_EXT="dll"

export ROOT LIB_EXT;
export LOCATION=supervisor;

prep -i httpd.tpl.conf -o httpd.conf