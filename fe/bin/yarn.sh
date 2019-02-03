#!/bin/bash

# FE_RELATIVE_PATH=`dirname $0`/..
# cd ${FE_RELATIVE_PATH}
# FE_ABSOLUTE_PATH=`pwd`;

# docker run --rm -it -w /home/node/app -v ${FE_ABSOLUTE_PATH}:/home/node/app -u node node:10 yarn $*

cd `dirname $0`/../
docker-compose exec elo_fe yarn $*

exit $?
