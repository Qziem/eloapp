#!/bin/bash

cd `dirname $0`/../../
docker-compose exec elo_fe yarn $*

exit $?
