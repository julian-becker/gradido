#!/bin/bash
if [ ! -d "./src/cpp/proto" ] ; then
	mkdir ./src/cpp/proto
fi
if [ ! -d "./src/cpp/proto/gradido" ] ; then
	mkdir ./src/cpp/proto/gradido
fi
PROTOC_PATH=./dependencies/grpc/_build/third_party/protobuf/Release
CPP_PLUGIN_PATH=./dependencies/grpc/_build/Release
$PROTOC_PATH/protoc --cpp_out=./src/cpp/proto --proto_path=./src/proto ./src/proto/gradido/*.proto

if [ ! -d "./src/cpp/proto/hedera" ] ; then 
	mkdir ./src/cpp/proto/hedera 
fi

GOOGLE_PROTOBUF_INCLUDES=./dependencies/grpc/third_party/protobuf/src
$PROTOC_PATH/protoc --plugin=protoc-gen-grpc=$CPP_PLUGIN_PATH/grpc_cpp_plugin.exe --cpp_out=./src/cpp/proto/hedera --grpc_out=./src/cpp/proto/hedera --proto_path=$GOOGLE_PROTOBUF_INCLUDES --proto_path=./src/proto/hedera/hedera-protobuf/src/main/proto ./src/proto/hedera/hedera-protobuf/src/main/proto/*.proto


