<?php

/**
 * MageWorx
 * Search Suite
 *
 * @category   MageWorx
 * @package    MageWorx_SearchSuite
 * @copyright  Copyright (c) 2015 MageWorx (http://www.mageworx.com/)
 */
class MageWorx_SearchSuite_Model_Resource_CatalogSearch_Fulltext_Engine extends MageWorx_SearchSuite_Model_Resource_CatalogSearch_Fulltext_Engine_Abstract {

    public function prepareResultForEngine($fulltextModel, $queryText, $query) {
        $helper = Mage::helper('mageworx_searchsuite');
        $adapter = $fulltextModel->_getWriteAdapter();
        $bind = array();
        $like = array();
        // ------------- start changes
        $searchAccuracy = $helper->getSearchAccuracy();
        $multiplier = $helper->getPriorityMultiplier();


        $relevanceRatio = array(pow($multiplier, 4), pow($multiplier, 3), pow($multiplier, 2), $multiplier, 1);

        $select = $adapter->select();
        $words = $this->filterQueryWords($queryText, $query->getStoreId());
        $queryText = join(' ', $words);
        $mainTableAlias = 's';
        $mainTable = $fulltextModel->getMainTable();

        $indexMinLength = 4; // ft_min_word_len (db var)
        $minWords = array(); // length word < ft_min_word_len
        $indexWords = array(); // length > ft_min_word_len
        $manyWords = false; // true if searching > 1 words
        $allWords = array(); // filtered words
        // fix if query = 1 symbol
        $minLenQuery = 2;
        if (strlen(trim($helper->prepareValue($queryText))) == 1) {
            $minLenQuery = 1;
        }

        foreach ($words as $word) {
            if (strlen($word) >= $minLenQuery) { // length word must be > 1 symbol
                if (strlen($word) < $indexMinLength) {
                    $minWords[] = $word;
                } else {
                    $indexWords[] = $word;
                }
                $allWords[] = $word;
            }
        }
        $queryPrepared = false;
        if (count($indexWords) == 0 && count($allWords) > 0) { /* search without fulltext index */
            $ifcase = array();
            $likes = array();

            if ($searchAccuracy == 'exact')
                $minWords = array($helper->prepareValue($queryText));

            if (count($minWords) > 1) {
                $manyWords = true;
            }
            for ($i = 1; $i <= 5; $i++) {
                $like = array();
                foreach ($minWords as $word) {
                    $like[] = $fulltextModel->getCILike_compatible($mainTableAlias . '.data_index' . $i, $word, array('position' => 'any'));
                }
                if ($like) {
                    if ($searchAccuracy == 'all' || $searchAccuracy == 'exact') {
                        $or = join(' AND ', $like);
                    } else { // any
                        $or = join(' OR ', $like);
                    }
                    $likes[] = '(' . $or . ')';
                    $if = array();
                    $if[] = $fulltextModel->getCheckSql_compatible($or, 1, 0);
                    if ($manyWords == true) {
                        $if[] = $fulltextModel->getCheckSql_compatible(join(' AND ', $like), 1, 0);
                    }
                    if (count($words) > 1) {
                        $ifcase[] = '((' . join(' + ', $if) . ') * ' . $relevanceRatio[$i - 1] . ' * ' . $fulltextModel->getCheckSql_compatible($fulltextModel->getCILike_compatible($mainTableAlias . '.data_index' . $i, join(' ', $words), array('position' => 'any')), 2, 1) . ')';
                    } else {
                        $ifcase[] = '((' . join(' + ', $if) . ') * ' . $relevanceRatio[$i - 1] . ')';
                    }
                }
            }

            $where = '(' . join(' OR ', $likes) . ')';
            $fields = array(
                'query_id' => new Zend_Db_Expr($query->getId()),
                'product_id',
                'relevance' => new Zend_Db_Expr(join(' + ', $ifcase)),
            );
            $select = $select
                    ->from(array($mainTableAlias => $mainTable), $fields)
                    ->joinInner(array('e' => $fulltextModel->getTable('catalog/product')), 'e.entity_id = s.product_id', array())
                    ->where($mainTableAlias . '.store_id = ?', (int) $query->getStoreId())
                    ->where($where);
            $queryPrepared = true;
        } else if (count($minWords) == 0 && count($allWords) > 0) { /* search with fulltext index */
            if (count($indexWords) < count($words)) {
                $manyWords = true;
            }
            $union = array();
            $cond = '';

            for ($i = 1; $i <= 5; $i++) {
                $fields = array(
                    'product_id',
                    'rel' => '',
                );
                $f = array();
                $f[] = '1';
                if ($i == 1 || $searchAccuracy == 'all' || $searchAccuracy == 'exact') { // for first index - like only!
                    $like = array();

                    if ($searchAccuracy == 'exact')
                        $indexWords = array($helper->prepareValue($queryText));
                    foreach ($indexWords as $word) {
                        $like[] = $fulltextModel->getCILike_compatible($mainTableAlias . '.data_index' . $i, $word, array('position' => 'any'));
                    }
                    if ($searchAccuracy == 'all' || $searchAccuracy == 'exact') {
                        $cond = '(' . join(' AND ', $like) . ')';
                    } else { // any
                        $cond = '(' . join(' OR ', $like) . ')';
                    }
                } else {

                    $cond = new Zend_Db_Expr('MATCH (' . $mainTableAlias . '.data_index' . $i . ') AGAINST (:queryinwhere IN BOOLEAN MODE)');
                }
                $f[] = 'MATCH (' . $mainTableAlias . '.data_index' . $i . ') AGAINST (:query)';

                if ($manyWords)
                    $fields['rel'] = new Zend_Db_Expr('(( ' . join(' + ', $f)
                            . ' ) * ' . $relevanceRatio[$i - 1]
                            . ' * ' . $fulltextModel->getCheckSql_compatible($fulltextModel->getCILike_compatible($mainTableAlias . '.data_index' . $i, join(' ', $words), array('position' => 'any')), 2, 1) . ')');
                else
                    $fields['rel'] = new Zend_Db_Expr('(( ' . join(' + ', $f) . ' ) * ' . $relevanceRatio[$i - 1] . ')');
                $s = $adapter->select()
                        ->from(array($mainTableAlias => $mainTable), $fields)
                        ->where($mainTableAlias . '.store_id = ?', (int) $query->getStoreId())
                        ->where($cond);
//                need optimization when count products more then million
//                    if ($actionName == 'suggest' && $controllerName == 'ajax') {
//                        $s = $s
//                            ->limit(1000)
//                            ->order('rel desc');
//                    }
                $union[] = $s;
            }
            $fields = array(
                'query_id' => new Zend_Db_Expr($query->getId()),
                'product_id',
                'relevance' => new Zend_Db_Expr('SUM(rel)'),
            );
            $select = $select
                    ->from(array($mainTableAlias => $adapter->select()->union($union)), $fields)
                    ->joinInner(array('e' => $fulltextModel->getTable('catalog/product')), 'e.entity_id = s.product_id', array())
                    ->group('product_id');
            $queryinwhere = array();
            foreach ($indexWords as $value) {
                $queryinwhere[] = $value . '*';
            }
            $bind[':queryinwhere'] = join(' ', $queryinwhere);
            $bind[':query'] = join(' ', $indexWords);
            $queryPrepared = true;
        } else if (count($allWords) > 0) { /* combine like and fultext index */

            $union = array();
            $cond = '';

            for ($i = 1; $i <= 5; $i++) {
                $fields = array(
                    'product_id',
                    'rel' => '',
                );

                $f = array();
                $f[] = '( 1 + MATCH (' . $mainTableAlias . '.data_index' . $i . ') AGAINST (:query))';
                $like = array();
                foreach ($minWords as $word) {
                    $like[] = $fulltextModel->getCILike_compatible($mainTableAlias . '.data_index' . $i, $word, array('position' => 'any'));
                }
                $rel = join(' AND ', $like);

                if ($i == 1 || $searchAccuracy == 'all' || $searchAccuracy == 'exact') { // for first index - like only!
                    if ($searchAccuracy == 'exact')
                        $indexWords = array($helper->prepareValue($queryText));
                    foreach ($indexWords as $word) {
                        $like[] = $fulltextModel->getCILike_compatible($mainTableAlias . '.data_index' . $i, $word, array('position' => 'any'));
                    }
                    if ($searchAccuracy == 'all' || $searchAccuracy == 'exact') {
                        $cond = '(' . join(' AND ', $like) . ')';
                    } else {
                        $cond = '(' . join(' OR ', $like) . ')';
                    }
                } else {
                    $cond = new Zend_Db_Expr('MATCH (' . $mainTableAlias . '.data_index' . $i . ') AGAINST (:queryinwhere IN BOOLEAN MODE)');
                }

                $f[] = $fulltextModel->getCheckSql_compatible($rel, 2, 1);


                $f[] = $fulltextModel->getCheckSql_compatible($fulltextModel->getCILike_compatible($mainTableAlias . '.data_index' . $i, join(' ', $words), array('position' => 'any')), 2, 1);
                $f[] = $relevanceRatio[$i - 1];
                $fields['rel'] = new Zend_Db_Expr('( ' . join(' * ', $f) . ' )');

                $s = $adapter->select()
                        ->from(array($mainTableAlias => $mainTable), $fields)
                        ->where($mainTableAlias . '.store_id = ?', (int) $query->getStoreId())
                        ->where($cond);
//                need optimization when count products more then million
//                    if ($actionName == 'suggest' && $controllerName == 'ajax') {
//                        $s = $s
//                            ->limit(1000)
//                            ->order('rel desc');
//                    }
                $union[] = $s;
            }
            $fields = array(
                'query_id' => new Zend_Db_Expr($query->getId()),
                'product_id',
                'relevance' => new Zend_Db_Expr('SUM(rel)'),
            );
            $select = $select
                    ->from(array($mainTableAlias => $adapter->select()->union($union)), $fields)
                    ->joinInner(array('e' => $fulltextModel->getTable('catalog/product')), 'e.entity_id = s.product_id', array())
                    ->group('product_id');

            $queryinwhere = array();
            foreach ($allWords as $value) {
                $queryinwhere[] = $value . '*';
            }
            $bind[':queryinwhere'] = join(' ', $queryinwhere);
            $bind[':query'] = join(' ', $allWords);
            $queryPrepared = true;
        }
        //die($select->__toString()); // for debug :)
        if ($queryPrepared) {
            $sql = $fulltextModel->insertFromSelect_compatible($select, $fulltextModel->getTable('catalogsearch/result'));
            $adapter->query($sql, $bind);
        }
        return true;
    }

}
