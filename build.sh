#!/bin/sh

ROOT="$(realpath .)"

[ $MSYSTEM ] && ROOT="$(cygpath -m $ROOT)"

prep -D "ENDPOINT_ROOT=$ROOT/public" -i httpd.tpl.conf -o httpd.conf
prep -D "VENDOR_ROOT=$ROOT/vendor" -i public/.htaccess.tpl -o public/.htaccess