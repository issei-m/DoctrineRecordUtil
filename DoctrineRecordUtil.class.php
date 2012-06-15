<?php

/**
 * DoctrineRecordUtil
 *
 * @package    Doctrine
 * @subpackage Record
 * @author     Issei Murasawa <issei.m7@gmail.com>
 * @version    1.0
 */
class DoctrineRecordUtil
{
    /**
     * 与えられた配列を元にDoctrine_Recordを生成する
     *
     * @param Doctrine_Record $record 元となる初期化済みのDoctrine_Recordインスタンス
     * @param array           $data   レコードのカラム、リレーション情報を格納した配列
     * @param boolean         $save   渡されたDoctrine_Recordをsave()するかどうか
     * @return Doctrine_Record
     */
    public static function create(Doctrine_Record $record, $data, $save = true)
    {
        $relations = $record->getTable()->getRelations();

        foreach ($data as $key => $value) {
            if (isset($relations[$key])) {
                $relation = $relations[$key];

                if (!$relation->isOneToOne() && $relation->getType() !== Doctrine_Relation::ONE) {
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
     * リレーションシップ構築に必要なDoctrine_Recordを作成する
     * Doctrine_Recordの作成には self::create{__CLASS__}() が用いられる
     *
     * @see DoctrineRecordUtil::create()
     * @param Doctrine_Relation $relation
     * @param Doctrine_Record   $data
     * @return Doctrine_Record
     */
    protected static function _createForRelation(Doctrine_Relation $relation, $data)
    {
        if ($data instanceof Doctrine_Record) {
            return $data;
        }

        $method = 'create' . ucfirst($relation->getClass());

        return self::$method($data, false);
    }

    /**
     * 未定義の create{__CLASS__}() コールを処理する
     * ::create()のエイリアスとして動作し、第1引数に__CLASS__を初期化したインスタンスが渡される. (以降の引数は通常通り)
     * ※PHP 5.3以降で利用可能
     *
     * @see DoctrineRecordUtil::create()
     * @return Doctrine_Record
     * @throws Exception
     */
    public static function __callStatic($name, $arguments)
    {
        if (substr($name, 0, 6) !== 'create') {
            throw new Exception('Call to undefined method ' . $name . '()');
        }

        $class  = substr($name, 6);
        $called = get_called_class();
        $method = 'create' . $class;
        $params = array(isset($arguments[0]) ? $arguments[0] : array());

        if (isset($arguments[1])) {
            $params[1] = $arguments[1];
        }

        if (!method_exists($called, $method)) {
            $called = 'self';
            $method = 'create';
            array_unshift($params, new $class);
        }

        return forward_static_call_array(array($called, $method), $params);
    }
}
