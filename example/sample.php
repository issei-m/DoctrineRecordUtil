<?php

    /*
     * 基本的な使い方. Doctrine_Record::fromArray() & ->save() と同じ
     */

    // Userを作成する
    $user = DoctrineRecordUtil::create(new User(), array('name' => 'Issei.M'));

    // ProductとUserを作成し、BoughtLogで紐付ける
    // 更にProductには新たに何人かのUserをお気に入り登録させる
    $boughtLog = DoctrineRecordUtil::create(new BoughtLog(), array(
        'Product' => array(
            'name' => 'Product A',
            'price' => 1000,
            'FavoritedUsers' => array(
                array('name' => 'Jiro Sato'),
                array('name' => 'Saburo Suzuki'),
                array('name' => 'Shiro Kimura'),
            )
        ),
        'User' => array(
            'name' => 'Goro Watanabe'
        )
    ));


    /*
     * 配列に直接Doctrine_Recordを渡す手法
     */

    $user2 = DoctrineRecordUtil::create(new User(), array(
        'FavoriteProducts' => array(
            $boughtLog->Product,
            DoctrineRecordUtil::create(new Product(), array(
                'name' => 'Product B',
                'price' => 1000,
            ))
        )
    ));
