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

#ifndef _ROWSET_H_
#define _ROWSET_H_

#include "Resource.h"
#include "gridstore.h"
#include "Row.h"
#include "AggregationResult.h"

#include <memory>
#include <string>

using namespace std;

namespace griddb {

/**
 * Convert from GSRowSet
 */
class RowSet : public Resource {
	GSRowSet *mRowSet;
public:
	RowSet(GSRowSet *rowSet);
	~RowSet();

	// Iterator
	bool has_next();
	void get_next(Row* row);
	void update_current(Row* row);
	int32_t get_size();
	void delete_current();
	GSRowSetType get_type();
	AggregationResult* get_next_aggregation();
	GSQueryAnalysisEntry get_next_query_analysis();

private:
	void close();
};
}

#endif /* _ROWSET_H_ */
