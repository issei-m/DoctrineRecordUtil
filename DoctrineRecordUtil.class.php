<?php

/**
 * DoctrineRecordUtil
 *
 * @package    Doctrine
 * @subpackage Record
 * @author     Issei Murasawa <issei.m7@gmail.com>
 * @version    1.0.1
 */
class DoctrineRecordUtil
{
    /**
     * 与えられた配列を元にDoctrine_Recordを生成する
     *
     * @param Doctrine_Record $record ソースとなる初期化済みのDoctrine_Recordインスタンス
     * @param array           $data   レコードのカラム、リレーション情報を格納した配列
     * @param boolean         $save   trueなら $record->save() する
     *
     * @return Doctrine_Record
     */
    public static function create(Doctrine_Record $record, $data, $save = true)
    {
        $relations = $record->getTable()->getRelations();

        foreach ($data as $key => $value) {
            if (isset($relations[$key])) {
                $relation = $relations[$key];

                if (!$relation->isOneToOne()) {
                    foreach (isset($value[0]) ? $value : array($value) as $v) {
                        $record->get($key)
                               ->add(self::_createForRelation($relation, $v));
                    }
                    continue;
                }

                $value = self::_createForRelation($relation, $value);
            }

            $record->set($key, $value);
        }

        if ($save) {
            $record->save();
        }

        return $record;
    }

    /**
     * 受け取ったクラス名毎に実行する処理を決定する
     *
     * @param string  $class Doctrine_Recordのクラス名
     * @param array   $data
     * @param boolean $save
     *
     * @return Doctrine_Record
     *
     * @see DoctrineRecordUtil::create()
     */
    protected static function dispatch($class, $data, $save = true)
    {
        $method = 'create' . $class;

        if (version_compare(PHP_VERSION, '5.3', '>=')) {
            $called = get_called_class();

            if (method_exists($called, $method)) {
                return $called::$method($data, $save);
            }
        }
        elseif (method_exists('self', $method)) {
            return self::$method($data, $save);
        }

        return self::create(new $class(), $data, $save);
    }

    /**
     * リレーションシップを構築するDoctrine_Recordを作成する
     * 作成時には self::create{__CLASS__}() が用いられる
     *
     * @param Doctrine_Relation $relation
     * @param Doctrine_Record   $data
     *
     * @return Doctrine_Record
     *
     * @see DoctrineRecordUtil::create()
     */
    protected static function _createForRelation(Doctrine_Relation $relation, $data)
    {
        if ($data instanceof Doctrine_Record) {
            return $data;
        }

        return self::dispatch($relation->getClass(), $data, false);
    }

    /**
     * ※PHP 5.3以降で利用可能
     *
     * 未定義の create{__CLASS__}() コールを処理する
     * ::create() のエイリアスとして動作し、第1引数に__CLASS__を初期化したインスタンスが渡される. (以降の引数は通常通り)
     *
     * @return Doctrine_Record
     *
     * @throws Exception create{__CLASS__}() 以外の形式でコールされた場合にスロー
     *
     * @see DoctrineRecordUtil::create()
     */
    public static function __callStatic($name, $arguments)
    {
        if (substr($name, 0, 6) !== 'create') {
            throw new Exception('Call to undefined method ' . $name . '()');
        }

        array_unshift($arguments, substr($name, 6));

        return forward_static_call_array(array('self', 'dispatch'), $arguments);
    }
}
