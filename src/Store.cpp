/*
   Copyright (c) 2017 TOSHIBA Digital Solutions Corporation

   Licensed under the Apache License, Version 2.0 (the "License");
   you may not use this file except in compliance with the License.
   You may obtain a copy of the License at

       http://www.apache.org/licenses/LICENSE-2.0

   Unless required by applicable law or agreed to in writing, software
   distributed under the License is distributed on an "AS IS" BASIS,
   WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
   See the License for the specific language governing permissions and
   limitations under the License.
*/

#include "Store.h"
#include "GSException.h"

namespace griddb {
	Store::Store(GSGridStore *store) : Resource(store), mStore(store) {
	}

	Store::~Store() {
		close();
	}

	/**
	 * Delete container with specified name
	 */
	void Store::drop_container(const char* name) {
		GSResult ret = gsDropContainer(mStore, name);

		if(ret != GS_RESULT_OK) {
			throw GSException(ret);
		}
	}

	/**
	 * Return information object of a specific container
	 */
	ContainerInfo* Store::get_container_info(
			const char* containerName) {
		GSContainerInfo containerInfo;
		GSChar bExists;

		GSResult ret = gsGetContainerInfo(mStore, containerName, &containerInfo, &bExists);

		if(ret != GS_RESULT_OK) {
			throw GSException(ret);
		}

		return new ContainerInfo(&containerInfo);
	}

	/**
	 * Put container. Convert from method gsPutContainerGeneral()
	 */
	Container* Store::put_container(const char* containerName,
			const GSColumnInfo* props, int propsCount, GSContainerType containerType, bool modifiable,
			GSBool rowKeyAssigned, GSBool columnOrderIgnorable ,int32_t rowExpirationTime ,
			GSTimeUnit rowExpirationTimeUnit, int32_t expirationDivisionCount) {

		// Create Container information
		GSContainerInfo containerInfo = { containerName,
				containerType, propsCount, props,
				rowKeyAssigned,
				columnOrderIgnorable
				};
		GSTimeSeriesProperties timeSeriesProp;
		if (rowExpirationTime && rowExpirationTimeUnit
				&& expirationDivisionCount) {
			timeSeriesProp = { rowExpirationTime,
					rowExpirationTimeUnit,
					-1, //compressionWindowSize : unlimited
					0, //compressionWindowSizeUnit
					GS_COMPRESSION_NO, //compressionMethod: no compress
					0, //compressionListSize
					0, //compressionList
					expirationDivisionCount };
			containerInfo.timeSeriesProperties = (const GSTimeSeriesProperties *) &timeSeriesProp;
		}
		GSContainer* pContainer;
		GSBool gsModifiable = (modifiable == true ? GS_TRUE : GS_FALSE);
		GSResult ret = gsPutContainerGeneral(mStore, containerName, &containerInfo,
				gsModifiable, &pContainer);

		if (ret != GS_RESULT_OK) {
			throw GSException(ret);
		}

		return new Container(pContainer);
	}

	/**
	 * Get container object with corresponding name
	 */
	Container* Store::get_container(const char* containerName) {
		GSContainer* pContainer;

		GSResult ret = gsGetContainerGeneral(mStore, containerName, &pContainer);

		if(ret != GS_RESULT_OK) {
			throw GSException(ret);
		}

		return new Container(pContainer);
	}

	/**
	 * Query execution and fetch is carried out on a specified arbitrary number of Query, with the request unit enlarged as much as possible.
	 */
	void Store::fetch_all(GSQuery* const* queryList, size_t queryCount) {
		GSResult ret = gsFetchAll(mStore, queryList, queryCount);
		if(ret != GS_RESULT_OK) {
			throw GSException(ret);
		}
	}

	/**
	 * Get Partition controller. Convert from C-API: gsGetPartitionController
	 */
	PartitionController* Store::get_partition_controller() {
	 	GSPartitionController* partitionController;

		GSResult ret = gsGetPartitionController(mStore, &partitionController);

		if(ret != GS_RESULT_OK) {
			throw GSException(ret);
		}

		return new PartitionController(partitionController);
	}

	/**
	 * Create row key predicate. Convert from C-API: gsCreateRowKeyPredicate
	 */
	RowKeyPredicate* Store::create_row_key_predicate(GSType keyType) {
		GSRowKeyPredicate* predicate;

		GSResult ret = gsCreateRowKeyPredicate(mStore, keyType, &predicate);

		if(ret != GS_RESULT_OK) {
			throw GSException(ret);
		}

		return new RowKeyPredicate(predicate);
	}

	/**
	 * New creation or update operation is carried out on an arbitrary number of rows of multiple Containers, with the request unit enlarged as much as possible.
	 */
	void Store::put_multi_container_row(const GSContainerRowEntry* entryList,
			size_t entryCount) {
		GSResult ret = gsPutMultipleContainerRows(mStore, entryList, entryCount);
		if(ret != GS_RESULT_OK) {
			throw GSException(ret);
		}
	}

	void Store::get_multi_container_row(
				const GSRowKeyPredicateEntry* const * predicateList,
				size_t predicateCount,
				const GSContainerRowEntry **entryList, size_t *entryCount) {
		GSResult ret = gsGetMultipleContainerRows(mStore, predicateList, predicateCount, entryList, entryCount);
		if(ret != GS_RESULT_OK) {
			throw GSException(ret);
		}
	}

	/**
	 * Release all resources created by this gridstore object
	 */
	void Store::close() {
		if(mStore != NULL) {
			gsCloseGridStore(&mStore, GS_FALSE);
			mStore = NULL;
		}
	}
}
