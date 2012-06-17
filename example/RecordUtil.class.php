<?php

class RecordUtil extends DoctrineRecordUtil
{
    /**
     * Create a User record
     *
     * @return User
     */
    public static function createUser($data = array(), $save = true)
    {
        $default = array(
            'name' => '%uniqid%'
        );
        $data = array_merge($default, $data);

        return parent::create(new User(), self::replace($data, uniqid('account-', true)), $save);
    }

    /**
     * 配列のuniqidを再帰的に置換する
     */
    protected static function replace($data, $uniqid)
    {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $data[$key] = self::replace($value, $uniqid);
            }
        }
        elseif (is_string($data)) {
            $data = str_replace('%uniqid%', $uniqid, $data);
        }

        return $data;
    }
}