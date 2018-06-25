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

#include "Resource.h"

namespace griddb {

Resource::Resource(void *resource) : mResource(resource) {
	}

	Resource::~Resource() {
	}

	/**
	 * Get error stack size. Convert from C-API: gsGetErrorStackSize.
	 */
	size_t Resource::get_error_stack_size() {
		return gsGetErrorStackSize(mResource);
	}

	/**
	 * Get error stack code. Convert from C-API:  gsGetErrorCode.
	 */
	GSResult Resource::get_error_code(size_t stackIndex) {
		return gsGetErrorCode(mResource, stackIndex);
	}

	/**
	 * Format error code. Convert from C-API: gsFormatErrorMessage.
	 */
	string Resource::format_error_message(size_t stackIndex, size_t bufSize) {
		char* strBuf = new char[bufSize];
		size_t stringSize = gsFormatErrorMessage(mResource, stackIndex, strBuf, bufSize);
		string ret(strBuf, stringSize);
		delete [] strBuf;
		return ret;
	}

	/**
	 * Format error location. Convert from C-API: gsFormatErrorLocation.
	 */
	string Resource::format_error_location(size_t stackIndex, size_t bufSize) {
		char* strBuf = new char[bufSize];
		size_t stringSize = gsFormatErrorLocation(mResource, stackIndex, strBuf, bufSize);
		string ret(strBuf, stringSize);
		delete [] strBuf;
		return ret;
	}

} /* namespace griddb */
