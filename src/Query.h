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

#ifndef _QUERY_H_
#define _QUERY_H_

#include <memory>
#include "Resource.h"
#include "gridstore.h"
#include "RowSet.h"
#include "GSException.h"
using namespace std;

namespace griddb {

/**
 * Convert from GSQuery
 */
class Query : public Resource {
	GSQuery *mQuery;
public:
	Query(GSQuery *query);
	~Query();
	RowSet* fetch(bool forUpdate);
	RowSet* get_row_set();
	void set_fetch_option_integer(GSFetchOption fetchOption, int32_t value);
	void set_fetch_option_long(GSFetchOption fetchOption, int64_t value);
	void close();
	GSQuery* gs_ptr();
private:

};
}

#endif /* _QUERY_H_ */
