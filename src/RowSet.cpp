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

#include "RowSet.h"
#include "GSException.h"

namespace griddb {
	RowSet::RowSet(GSRowSet *rowSet): Resource(rowSet), mRowSet(rowSet){

	}

	/**
	 * Check if RowSet has next row data. Convert from gsHasNextRow.
	 */
	bool RowSet::has_next() {
		return (bool) gsHasNextRow(mRowSet);
	}

	RowSet::~RowSet() {
		close();
	}

	void RowSet::update_current(Row* row) {
		GSResult ret = gsUpdateCurrentRow(mRowSet, row->gs_ptr());
		if (ret != GS_RESULT_OK) {
			throw new GSException(ret);
		}
	}

	/**
	 * Get next row data. Convert from gsGetNextRow.
	 */
	void RowSet::get_next(Row* row) {
		GSResult ret = gsGetNextRow(mRowSet, row->gs_ptr());
		if (ret != GS_RESULT_OK) {
			throw new GSException(ret);
		}
	}

	/**
	 * Return size of this rowset
	 */
	int32_t RowSet::get_size() {
		int32_t size;
		size = gsGetRowSetSize(mRowSet);

		return size;
	}

	/**
	 * Delete current row data. Convert from C-API: gsDeleteCurrentRow.
	 */
	void RowSet::delete_current() {
		GSResult ret = gsDeleteCurrentRow(mRowSet);
		if (ret != GS_RESULT_OK) {
			throw new GSException(ret);
		}
	}

	/**
	 * Get current row type. Convert from C-API: gsGetRowSetType.
	 */
	GSRowSetType RowSet::get_type() {
		GSRowSetType ret = gsGetRowSetType(mRowSet);
		return ret;
	}

	/**
	 * Moves to the next Row in a Row set and returns the aggregation result at the moved position.
	 */
	AggregationResult* RowSet::get_next_aggregation() {
		GSAggregationResult* pAggResult;

		GSResult ret = gsGetNextAggregation(mRowSet, &pAggResult);
		if(ret != GS_RESULT_OK) {
			throw GSException(ret);
		}

		return new AggregationResult(pAggResult);
	}

	/**
	 * Get query analysis. Convert from C-APi gsGetNextQueryAnalysis
	 */
	GSQueryAnalysisEntry RowSet::get_next_query_analysis() {
		GSQueryAnalysisEntry queryAnalysis;
		GSResult ret = gsGetNextQueryAnalysis(mRowSet, &queryAnalysis);
		if (ret != GS_RESULT_OK) {
			throw GSException(ret);
		}
		return queryAnalysis;
	}

	/**
	 * Close rowset.
	 */
	void RowSet::close() {
		if (mRowSet != NULL) {
			gsCloseRowSet(&mRowSet);
			mRowSet = NULL;
		}
	}

}
