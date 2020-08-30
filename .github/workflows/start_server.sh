#!/bin/sh -xe

# Install GridDB sever
wget https://github.com/griddb/griddb/releases/download/v4.5.0/griddb_4.5.0_amd64.deb
sudo dpkg -i griddb_4.5.0_amd64.deb

# Start server
sudo su - gsadm -c "gs_passwd admin -p $GRIDDB_PASSWORD"
sudo su - gsadm -c "sed -i 's/\"clusterName\":\"\"/\"clusterName\":\"$GRIDDB_CLUSTER_NAME\"/g' conf/gs_cluster.json"
sudo su - gsadm -c "export no_proxy=127.0.0.1"
sudo su - gsadm -c "gs_startnode -u $GRIDDB_USERNAME/$GRIDDB_PASSWORD -w"
sudo su - gsadm -c "gs_joincluster -c $GRIDDB_CLUSTER_NAME -u $GRIDDB_USERNAME/$GRIDDB_PASSWORD"
