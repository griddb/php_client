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

#include "AggregationResult.h"

namespace griddb {

	AggregationResult::AggregationResult(GSAggregationResult* aggResult) : Resource(aggResult), mAggResult(aggResult) {
	}

	AggregationResult::~AggregationResult() {
		close();
	}

	/**
	 *Obtains the result of aggregating numeric-type values in LONG type (Long).
	 */
	long AggregationResult::get_long() {
		long value;

		GSBool ret = gsGetAggregationValue(mAggResult, &value, GS_TYPE_LONG);
		if(ret == GS_FALSE) {
			throw GSException("Value with type long cannot be retrieved from Aggregation result");
		}

		return value;
	}

	/**
	 *Obtains the result of aggregating numeric-type values in DOUBLE type (Double).
	 */
	double AggregationResult::get_double() {
		double value;

		GSBool ret = gsGetAggregationValue(mAggResult, &value, GS_TYPE_DOUBLE);
		if(ret == GS_FALSE) {
			throw GSException("Value with type double cannot be retrieved from Aggregation result");
		}

		return value;
	}

	/**
	 *Obtains the result of aggregating numeric-type values in GSTIMESTAMP type (GSTimestamp).
	 */
	GSTimestamp AggregationResult::get_timestamp() {
		GSTimestamp value;

		GSBool ret = gsGetAggregationValue(mAggResult, &value, GS_TYPE_TIMESTAMP);
		if(ret == GS_FALSE) {
			throw GSException("Value with type Timestamp cannot be retrieved from Aggregation result");
		}

		return value;
	}

	void AggregationResult::close() {
		if(mAggResult != NULL) {
			gsCloseAggregationResult(&mAggResult);
		}
		mAggResult = NULL;
	}

} /* namespace griddb */
