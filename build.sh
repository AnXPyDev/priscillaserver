#!/bin/sh

ROOT="$(realpath .)"

[ $MSYSTEM ] && ROOT="$(cygpath -m $ROOT)"

prep -D "ROOT=$ROOT" -i httpd.tpl.conf -o httpd.conf