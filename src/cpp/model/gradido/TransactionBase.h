/*!
*
* \author: einhornimmond
*
* \date: 25.10.19
*
* \brief: Interface for Transaction Objects
*/
#ifndef GRADIDO_LOGIN_SERVER_MODEL_TRANSACTION_BASE_INCLUDE
#define GRADIDO_LOGIN_SERVER_MODEL_TRANSACTION_BASE_INCLUDE

#pragma warning(disable:4800)

#include "../lib/NotificationList.h"
#include "../proto/gradido/BasicTypes.pb.h"
#include "../SingletonManager/MemoryManager.h"

namespace model {
	namespace gradido {
		class TransactionBase : public NotificationList, public UniLib::lib::MultithreadContainer
		{
		public:
			TransactionBase(const std::string& memo);
			//! \return 0 if ok, < 0 if error, > 0 if not implemented 
			virtual int prepare() = 0;

			static std::string amountToString(google::protobuf::int64 amount);
			inline const std::string& getMemo() const { return mMemo; }

		protected:
			std::string mMemo;
		};
	}
}



#endif //GRADIDO_LOGIN_SERVER_MODEL_TRANSACTION_BASE_INCLUDE