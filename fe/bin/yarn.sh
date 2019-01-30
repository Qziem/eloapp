#!/bin/bash

ENV_PATH=`dirname $0`/../../
source $ENV_PATH.env
FE_PATH=$SOURCE_DIR/fe
cd $FE_PATH

docker run --rm -it -w /home/node/app -v $FE_PATH:/home/node/app -u node node:10 yarn $*

exit $?
