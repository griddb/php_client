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

#include "Container.h"
#include "GSException.h"

namespace griddb {

	Container::Container(GSContainer *container) : Resource(container), mContainer(container) {
	}

	Container::~Container() {
		close();
	}

	/**
	 * Creates a specified type of index on the specified Column.
	 */
	void Container::create_index(const char* columnName,
			GSIndexTypeFlags indexType) {
		GSResult ret = gsCreateIndex(mContainer, columnName, indexType);

		if(ret != GS_RESULT_OK) {
			throw GSException(ret);
		}
	}

	/**
	 * Removes the specified type of index among indexes on the specified Column.
	 */
	void Container::drop_index(const char* columName, GSIndexTypeFlags indexType) {
		GSResult ret = gsDropIndex(mContainer, columName, indexType);

		if(ret != GS_RESULT_OK) {
			throw GSException(ret);
		}
	}

	/**
	 * Writes the results of earlier updates to a non-volatile storage medium, such as SSD, so as to prevent the data from being lost even if all cluster nodes stop suddenly.
	 */
	void Container::flush() {
		GSResult ret = gsFlush(mContainer);

		if(ret != GS_RESULT_OK) {
			throw GSException(ret);
		}
	}

	/**
	 * Create new row.
	 */
	Row* Container::create_row() {
		GSRow *row;

		GSResult ret = gsCreateRowByContainer(mContainer, &row);
		if(ret != GS_RESULT_OK) {
			throw GSException(ret);
		}

		return new Row(row);
	}

	/**
	 * Put row to database.
	 */
	bool Container::put_row(Row* row) {
		GSBool bExists;

		GSResult ret = gsPutRow(mContainer, NULL, row->gs_ptr(), &bExists);

		if(ret != GS_RESULT_OK) {
			throw GSException(ret);
		}

		return bExists;

	}

	/**
	 * Get current container type
	 */
	GSContainerType Container::get_type() {
		GSContainerType containerType;
		GSResult ret = gsGetContainerType(mContainer, &containerType);

		if(ret != GS_RESULT_OK) {
			throw GSException(ret);
		}

		return containerType;
	}

	/**
	 * Rolls back the result of the current transaction and starts a new transaction in the manual commit mode.
	 */
	void Container::abort() {
		GSResult ret = gsAbort(mContainer);

		if(ret != GS_RESULT_OK) {
			throw GSException(ret);
		}
	}

	/**
	 * Create query from input string.
	 */
	Query* Container::query(const char* queryString) {
		GSQuery *pQuery;
		gsQuery(mContainer, queryString, (&pQuery));
		return new Query(pQuery);
	}

	/**
	 * Set auto commit to true or false.
	 */
	void Container::set_auto_commit(bool enabled){
		GSBool gsEnabled;
		gsEnabled = (enabled == true ? GS_TRUE:GS_FALSE);
		gsSetAutoCommit(mContainer, gsEnabled);
	}

	/**
	 * Commit changes to database when autocommit is set to false.
	 */
	void Container::commit() {
		GSResult ret = gsCommit(mContainer);
		if(ret != GS_RESULT_OK) {
			throw GSException(ret);
		}
	}

	/**
	 * Returns the content of a Row corresponding to the specified Row key according to the specified option.
	 */
	bool Container::get_row_by_integer(int32_t key, bool forUpdate, Row* row) {
		GSBool exists;

		GSResult ret = gsGetRowByInteger(mContainer, key, row->gs_ptr(), forUpdate, &exists);
		if(ret != GS_RESULT_OK) {
			throw GSException(ret);
		}

		return (bool) exists;
	}

	/**
	 * Returns the content of a Row corresponding to the specified Row key according to the specified option.
	 */
	bool Container::get_row_by_long(int64_t key, bool forUpdate, Row* row) {
		GSBool exists;

		GSResult ret = gsGetRowByLong(mContainer, key, row->gs_ptr(), forUpdate, &exists);
		if(ret != GS_RESULT_OK) {
			throw GSException(ret);
		}

		return (bool) exists;
	}

	/**
	 * Returns the content of a Row corresponding to the specified Row key according to the specified option.
	 */
	bool Container::get_row_by_timestamp(GSTimestamp key, bool forUpdate, Row* row) {
		GSBool exists;

		GSResult ret = gsGetRowByTimestamp(mContainer, key, row->gs_ptr(), forUpdate, &exists);
		if(ret != GS_RESULT_OK) {
			throw GSException(ret);
		}

		return (bool) exists;
	}

	/**
	 * Returns the content of a Row corresponding to the specified Row key according to the specified option.
	 */
	bool Container::get_row_by_string(const GSChar* key, bool forUpdate, Row* row) {
		GSBool exists;

		GSResult ret = gsGetRowByString(mContainer, key, row->gs_ptr(), forUpdate, &exists);
		if(ret != GS_RESULT_OK) {
			throw GSException(ret);
		}

		return (bool) exists;
	}

	/**
	 * Newly creates or updates a Row, based on the specified Row object and also the Row key specified as needed.
	 */
	bool Container::put_row_by_integer(int32_t key, Row* row) {
		GSBool exists = GS_FALSE;

		GSResult ret = gsPutRowByInteger(mContainer, key, row->gs_ptr(), &exists);
		if(ret != GS_RESULT_OK) {
			throw GSException(ret);
		}

		return (bool) exists;
	}

	/**
	 * Newly creates or updates a Row, based on the specified Row object and also the Row key specified as needed.
	 */
	bool Container::put_row_by_long(int64_t key, Row* row) {
		GSBool exists = GS_FALSE;

		GSResult ret = gsPutRowByLong(mContainer, key, row->gs_ptr(), &exists);
		if(ret != GS_RESULT_OK) {
			throw GSException(ret);
		}

		return (bool) exists;
	}

	/**
	 * Newly creates or updates a Row, based on the specified Row object and also the Row key specified as needed.
	 */
	bool Container::put_row_by_timestamp(GSTimestamp key, Row* row) {
		GSBool exists = GS_FALSE;

		GSResult ret = gsPutRowByTimestamp(mContainer, key, row->gs_ptr(), &exists);
		if(ret != GS_RESULT_OK) {
			throw GSException(ret);
		}

		return (bool) exists;
	}

	/**
	 * Newly creates or updates a Row, based on the specified Row object and also the Row key specified as needed.
	 */
	bool Container::put_row_by_string(const GSChar* key, Row* row) {
		GSBool exists = GS_FALSE;

		GSResult ret = gsPutRowByString(mContainer, key, row->gs_ptr(), &exists);
		if(ret != GS_RESULT_OK) {
			throw GSException(ret);
		}

		return (bool) exists;
	}

	/**
	 *Delete row by integer. Convert from C-API: gsDeleteRowByInteger
	 */
	bool Container::delete_row_by_integer(int32_t key) {
		GSBool exists = GS_FALSE;

		GSResult ret = gsDeleteRowByInteger(mContainer, key, &exists);
		if(ret != GS_RESULT_OK) {
			throw GSException(ret);
		}
		return (bool) exists;
	}

	/**
	 *Delete row by long. Convert from C-API: gsDeleteRowByLong
	 */
	bool Container::delete_row_by_long(int64_t key) {
		GSBool exists;

		GSResult ret = gsDeleteRowByLong(mContainer, key, &exists);
		if(ret != GS_RESULT_OK) {
			throw GSException(ret);
		}
		return (bool) exists;
	}

	/**
	 *Delete row by timestamp. Convert from C-API: gsDeleteRowByTimestamp
	 */
	bool Container::delete_row_by_timestamp(GSTimestamp key) {
		GSBool exists;

		GSResult ret = gsDeleteRowByTimestamp(mContainer, key, &exists);
		if(ret != GS_RESULT_OK) {
			throw GSException(ret);
		}
		return (bool) exists;
	}

	/**
	 *Delete row by string. Convert from C-API: gsDeleteRowByString
	 */
	bool Container::delete_row_by_string(const GSChar* key) {
		GSBool exists;

		GSResult ret = gsDeleteRowByString(mContainer, key, &exists);
		if(ret != GS_RESULT_OK) {
			throw GSException(ret);
		}
		return (bool) exists;
	}

	/**
	 * Newly creates an arbitrary number of Rows together based on the specified Row objects group.
	 */
	bool Container::put_multi_row(const void* const * rowObjs, size_t rowCount) {
		GSBool exists;

		GSResult ret = gsPutMultipleRows(mContainer, rowObjs, rowCount, &exists);
		if(ret != GS_RESULT_OK) {
			throw GSException(ret);
		}

		return exists;
	}

	/**
	 * Close container.
	 */
	void Container::close() {
		//Release container and all related resources
		if(mContainer != NULL) {
			// allRelated = FALSE, since all row object is managed by Row class
			gsCloseContainer(&mContainer, GS_FALSE);
			mContainer = NULL;
		}
	}

}


