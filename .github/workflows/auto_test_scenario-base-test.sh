#!/bin/sh -xe

test_folder=test/testCode

test_files=$(cat <<-END
	BS001ContainerBasicScenario.php
	BS004ContainerPutGet.php
	BS004ContainerPutGetNullSpec.php
	BS005ContainerIndex.php
	BS007AggregationWithDoubleTimestamp.php
	BS008ContainerPutGetRemoveSpec.php
	BS009RowSetManualCommitSpec.php
	BS010QueryWithLimitSpec.php
	BS012ErrorUtilitySpec.php
	BS013PartitionController.php
	BS015ContainerInfoSetGetSpec.php
	BS016ExpirationInfoSetGetSpec.php
	BS018EnumSpec.php
	BS019AttributeSpec.php
	BS021KeywordParametersSpec.php
END
)

for test_file in $test_files; do
	phpunit $test_folder/$test_file
done
