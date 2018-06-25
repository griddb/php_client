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

#ifndef _STORE_H_
#define _STORE_H_

#include "Resource.h"
#include "ContainerInfo.h"
#include "Container.h"
#include "PartitionController.h"
#include "RowKeyPredicate.h"
#include <map>
#include <vector>

using namespace std;

namespace griddb {

	class Store : public Resource {
		GSGridStore *mStore;
	public:
		Store(GSGridStore* store);
		~Store();

		void drop_container(const char *name);
		ContainerInfo* get_container_info(const char *containerName);

		Container* put_container(const char* containerName,
				const GSColumnInfo* props, int propsCount, GSContainerType containerType,
				bool modifiable = false, GSBool rowKeyAssigned = GS_TRUE, GSBool columnOrderIgnorable = GS_FALSE,
				int32_t rowExpirationTime = NULL, GSTimeUnit rowExpirationTimeUnit =
						NULL, int32_t expirationDivisionCount = NULL);
		Container* get_container(const char* containerName);
		void fetch_all(GSQuery* const * queryList, size_t queryCount);
		void put_multi_container_row(const GSContainerRowEntry* entryList,
				size_t entryCount);
		void get_multi_container_row(
				const GSRowKeyPredicateEntry * const * predicateList,
				size_t predicateCount, const GSContainerRowEntry **entryList,
				size_t *entryCount);

		PartitionController* get_partition_controller();
		RowKeyPredicate* create_row_key_predicate(GSType keyType);

	private:
		void close();
	};

}

#endif /* Define _STORE_H */
