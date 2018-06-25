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

#ifndef _CONTAINER_H_
#define _CONTAINER_H_

#include <memory>
#include "Resource.h"
#include "Row.h"
#include "Query.h"
using namespace std;

namespace griddb {

	class Container : public Resource {

		GSContainer *mContainer;

	public:
		Container(GSContainer *container);
		~Container();

		void create_index(const char* columnName, GSIndexTypeFlags indexType);
		void drop_index(const char* columName, GSIndexTypeFlags indexType);
		void flush();
		Row* create_row();
		bool put_row(Row* row);
		bool put_multi_row(const void *const *rowObjs, size_t rowCount);
		Query* query(const char *queryString);
		GSContainerType get_type();
		void abort();
		void set_auto_commit(bool enabled);
		void commit();
		bool get_row_by_integer(int32_t key, bool forUpdate, Row* row);
		bool get_row_by_long(int64_t key, bool forUpdate, Row* row);
		bool get_row_by_timestamp(GSTimestamp key, bool forUpdate, Row* row);
		bool get_row_by_string(const GSChar* key, bool forUpdate, Row* row);
		bool put_row_by_integer(int32_t key, Row* row);
		bool put_row_by_long(int64_t key, Row* row);
		bool put_row_by_timestamp(GSTimestamp key, Row* row);
		bool put_row_by_string(const GSChar *key, Row* row);
		bool delete_row_by_integer(int32_t key);
		bool delete_row_by_long(int64_t key);
		bool delete_row_by_timestamp(GSTimestamp key);
		bool delete_row_by_string(const GSChar *key);

	private:
		void close();
	};
}

#endif /* _CONTAINER_H_ */
