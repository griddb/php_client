#!/bin/sh -xe

params="$GRIDDB_NOTIFICATION_ADDRESS $GRIDDB_NOTIFICATION_PORT $GRIDDB_CLUSTER_NAME $GRIDDB_USERNAME $GRIDDB_PASSWORD"
test_folder=sample

test_files=$(cat <<-END
	BlobData.php
	Connect.php
	ContainerInformation.php
	ContainerNames.php
	CreateCollection.php
	CreateIndex.php
	CreateTimeSeries.php
	GetRow.php
	PutRow.php
	RemoveRowByRowkey.php
	RemoveRowByTQL.php
	TimeSeriesRowExpiration.php
	TQLAggregation.php
	TQLSelect.php
	TQLTimeseries.php
	UpdateRowByTQL.php
	sample1.php
	sample2.php
	sample3.php
END
)

for test_file in $test_files; do
	php $test_folder/$test_file $params
done
