#!/bin/bash

cd `dirname $0`/../../
docker-compose exec --user=composer elo_api composer $*

exit $?
