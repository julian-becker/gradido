<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: gradido/BasicTypes.proto

namespace Proto\Gradido;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * copyed from hedera topic id
 *
 * Generated from protobuf message <code>proto.gradido.HederaID</code>
 */
class HederaID extends \Google\Protobuf\Internal\Message
{
    /**
     * The shard number (nonnegative)
     *
     * Generated from protobuf field <code>int64 shardNum = 1;</code>
     */
    private $shardNum = 0;
    /**
     * The realm number (nonnegative)
     *
     * Generated from protobuf field <code>int64 realmNum = 2;</code>
     */
    private $realmNum = 0;
    /**
     * Unique topic identifier within a realm (nonnegative).
     *
     * Generated from protobuf field <code>int64 topicNum = 3;</code>
     */
    private $topicNum = 0;

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type int|string $shardNum
     *           The shard number (nonnegative)
     *     @type int|string $realmNum
     *           The realm number (nonnegative)
     *     @type int|string $topicNum
     *           Unique topic identifier within a realm (nonnegative).
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\Gradido\BasicTypes::initOnce();
        parent::__construct($data);
    }

    /**
     * The shard number (nonnegative)
     *
     * Generated from protobuf field <code>int64 shardNum = 1;</code>
     * @return int|string
     */
    public function getShardNum()
    {
        return $this->shardNum;
    }

    /**
     * The shard number (nonnegative)
     *
     * Generated from protobuf field <code>int64 shardNum = 1;</code>
     * @param int|string $var
     * @return $this
     */
    public function setShardNum($var)
    {
        GPBUtil::checkInt64($var);
        $this->shardNum = $var;

        return $this;
    }

    /**
     * The realm number (nonnegative)
     *
     * Generated from protobuf field <code>int64 realmNum = 2;</code>
     * @return int|string
     */
    public function getRealmNum()
    {
        return $this->realmNum;
    }

    /**
     * The realm number (nonnegative)
     *
     * Generated from protobuf field <code>int64 realmNum = 2;</code>
     * @param int|string $var
     * @return $this
     */
    public function setRealmNum($var)
    {
        GPBUtil::checkInt64($var);
        $this->realmNum = $var;

        return $this;
    }

    /**
     * Unique topic identifier within a realm (nonnegative).
     *
     * Generated from protobuf field <code>int64 topicNum = 3;</code>
     * @return int|string
     */
    public function getTopicNum()
    {
        return $this->topicNum;
    }

    /**
     * Unique topic identifier within a realm (nonnegative).
     *
     * Generated from protobuf field <code>int64 topicNum = 3;</code>
     * @param int|string $var
     * @return $this
     */
    public function setTopicNum($var)
    {
        GPBUtil::checkInt64($var);
        $this->topicNum = $var;

        return $this;
    }

}

