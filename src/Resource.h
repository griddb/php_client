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

#ifndef _RESOURCE_H_
#define _RESOURCE_H_

#include <string>
#include "gridstore.h"

using namespace std;

namespace griddb {

class Resource {
	void *mResource;

public:
	Resource(void *resource);
	~Resource();

	size_t get_error_stack_size();
	GSResult get_error_code(size_t stackIndex);
	string format_error_message(size_t stackIndex, size_t bufSize);
	string format_error_location(size_t stackIndex, size_t bufSize);
};

} /* namespace griddb */

#endif /* SRC_RESOURCE_H_ */
