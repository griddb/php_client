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

#ifndef _GS_EXCEPTION_H_
#define _GS_EXCEPTION_H_

#include <exception>
#include <string>
#include "gridstore.h"

using namespace std;

namespace griddb {

	/**
	 * This class creates exception corresponding to error code
	 */
	class GSException : public exception {
		int32_t mCode;
		string mMessage;
	public:
		GSException(int32_t code) : exception(), mCode(code) {
			mMessage = "Error with number " + to_string((long long int)mCode);
		}

		GSException(const char* message) : exception(), 
				mCode(-1), mMessage(message) {
		}

		GSException(int32_t code, const char* message) : exception(),
				mCode(code), mMessage(message) {
		}

		~GSException() throw() {}

		int32_t get_code() {
			return mCode;
		}

		virtual const char* what() const throw() {
			return mMessage.c_str();
		}

		/*
		 * Check timeout. Convert from C-API: gsIsTimeoutError
		 */
		bool is_timeout() {
			if (mCode != -1) {
				//Case exception with error code.
				return gsIsTimeoutError(mCode);
			}
			//Case exception with message only.
			return false;
		}
	};

}

#endif
