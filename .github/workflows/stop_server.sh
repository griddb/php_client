#!/bin/sh -xe

export GS_HOME=$PWD
export GS_LOG=$PWD/log

sudo su - gsadm -c "gs_stopcluster -u $GRIDDB_USERNAME/$GRIDDB_PASSWORD"
sudo su - gsadm -c "gs_stopnode -u $GRIDDB_USERNAME/$GRIDDB_PASSWORD"
