<?php
/**
 * BoxBilling
 *
 * @copyright BoxBilling, Inc (http://www.boxbilling.com)
 * @license   Apache-2.0
 *
 * Copyright BoxBilling, Inc
 * This source file is subject to the Apache-2.0 License that is bundled
 * with this source code in the file LICENSE
 */

/**
 * Knowledge base API
 */

namespace Box\Mod\Kb\Api;

class Admin extends \Api_Abstract
{
    /**
     * Get paginated list of knowledge base articles
     *
     * @return array
     */
    public function article_get_list($data)
    {
        $status = isset($data['status']) ? $data['status'] : null;
        $search = isset($data['search']) ? $data['search'] : null;
        $cat    = isset($data['cat']) ? $data['cat'] : null;

        $pager = $this->getService()->searchArticles($status, $search, $cat);

        foreach ($pager['list'] as $key => $item) {
            $article               = $this->di['db']->getExistingModelById('KbArticle', $item['id'], 'KB Article not found');
            $pager['list'][$key] = $this->getService()->toApiArray($article);
        }

        return $pager;
    }

    /**
     * Get knowledge base article
     *
     * @param int $id - knowledge base article ID
     *
     * @return array
     */
    public function article_get($data)
    {
        if (!isset($data['id'])) {
            throw new \Box_Exception('Article id not passed');
        }

        $model = $this->di['db']->findOne('KbArticle', 'id = ?', array($data['id']));

        if (!$model instanceof \Model_KbArticle) {
            throw new \Box_Exception('Article not found');
        }

        return $this->getService()->toApiArray($model, true, $this->getIdentity());
    }

    /**
     * Create new knowledge base article
     *
     * @param int $kb_article_category_id - knowledge base category ID
     * @param string $title - knowledge base article title
     *
     * @optional string $status - knowledge base article status
     * @optional string $content - knowledge base article content
     *
     * @return array
     */
    public function article_create($data)
    {
        if (!isset($data['kb_article_category_id'])) {
            throw new \Box_Exception('Article category id not passed');
        }

        if (!isset($data['title'])) {
            throw new \Box_Exception('Article title not passed');
        }
        $articleCategoryId = $data['kb_article_category_id'];
        $title             = $data['title'];
        $status            = isset($data['status']) ? $data['status'] : \Model_KbArticle::DRAFT;
        $content           = isset($data['content']) ? $data['content'] : NULL;

        return $this->getService()->createArticle($articleCategoryId, $title, $status, $content);
    }

    /**
     * Update knowledge base article
     *
     * @param int $id - knowledge base article ID
     *
     * @optional string $title - knowledge base article title
     * @optional int $kb_article_category_id - knowledge base category ID
     * @optional string $slug - knowledge base article slug
     * @optional string $status - knowledge base article status
     * @optional string $content - knowledge base article content
     * @optional int $views - knowledge base article views counter
     *
     * @return bool
     */
    public function article_update($data)
    {
        if (!isset($data['id'])) {
            throw new \Box_Exception('Article id not passed');
        }

        $articleCategoryId = isset($data['kb_article_category_id']) ? $data['kb_article_category_id'] : null;
        $title             = isset($data['title']) ? $data['title'] : null;
        $slug              = isset($data['slug']) ? $data['slug'] : null;
        $status            = isset($data['status']) ? $data['status'] : null;
        $content           = isset($data['content']) ? $data['content'] : null;
        $views             = isset($data['views']) ? $data['views'] : null;


        return $this->getService()->updateArticle($data['id'], $articleCategoryId, $title, $slug, $status, $content, $views);
    }

    /**
     * Delete knowledge base article
     *
     * @param int $id - knowledge base article ID
     *
     * @return bool
     */
    public function article_delete($data)
    {
        if (!isset($data['id'])) {
            throw new \Box_Exception('Article id is missing');
        }

        $model = $this->di['db']->findOne('KbArticle', 'id = ?', array($data['id']));

        if (!$model instanceof \Model_KbArticle) {
            throw new \Box_Exception('Article not found');
        }

        $this->getService()->rm($model);

        return true;
    }

    /**
     * Get paginated list of knowledge base categories
     *
     * @return array
     */
    public function category_get_list($data)
    {
        list($sql, $bindings) = $this->getService()->categoryGetSearchQuery($data);
        $per_page = isset($data['per_page']) ? $data['per_page'] : $this->di['pager']->getPer_page();
        $pager = $this->di['pager']->getAdvancedResultSet($sql, $bindings, $per_page);

        foreach ($pager['list'] as $key => $item) {
            $category              = $this->di['db']->getExistingModelById('KbArticleCategory', $item['id'], 'KB Article not found');
            $pager['list'][$key] = $this->getService()->categoryToApiArray($category, $this->getIdentity());
        }

        return $pager;
    }

    /**
     * Get knowledge base category
     *
     * @param int $id - knowledge base category ID
     *
     * @return array
     */
    public function category_get($data)
    {
        if (!isset($data['id'])) {
            throw new \Box_Exception('Caegory id not passed');
        }

        $model = $this->di['db']->findOne('KbArticleCategory', 'id = ?', array($data['id']));

        if (!$model instanceof \Model_KbArticleCategory) {
            throw new \Box_Exception('Article Category not found');
        }

        return $this->getService()->categoryToApiArray($model);
    }

    /**
     * Create new knowledge base category
     *
     * @param string $title - knowledge base category title
     *
     * @optional string $description - knowledge base category description
     *
     * @return array
     */
    public function category_create($data)
    {
        if (!isset($data['title'])) {
            throw new \Box_Exception('Category title not passed');
        }

        $title       = $data['title'];
        $description = isset($data['description']) ? $data['description'] : NULL;

        return $this->getService()->createCategory($title, $description);
    }

    /**
     * Update knowledge base category
     *
     * @param int $id - knowledge base category ID
     *
     * @optional string $title - knowledge base category title
     * @optional string $slug  - knowledge base category slug
     * @optional string $description - knowledge base category description
     *
     * @return array
     */
    public function category_update($data)
    {
        if (!isset($data['id'])) {
            throw new \Box_Exception('Category id not passed');
        }

        $model = $this->di['db']->findOne('KbArticleCategory', 'id = ?', array($data['id']));

        if (!$model instanceof \Model_KbArticleCategory) {
            throw new \Box_Exception('Article Category not found');
        }

        $title       = isset($data['title']) ? $data['title'] : NULL;
        $slug        = isset($data['slug']) ? $data['slug'] : NULL;
        $description = isset($data['description']) ? $data['description'] : NULL;

        return $this->getService()->updateCategory($model, $title, $slug, $description);
    }

    /**
     * Delete knowledge base category
     *
     * @param int $id - knowledge base category ID
     *
     * @return bool
     */
    public function category_delete($data)
    {
        if (!isset($data['id'])) {
            throw new \Box_Exception('Category id is missing');
        }

        $model = $this->di['db']->findOne('KbArticleCategory', 'id = ?', array($data['id']));

        if (!$model instanceof \Model_KbArticleCategory) {
            throw new \Box_Exception('Category not found');
        }

        return $this->getService()->categoryRm($model);
    }

    /**
     * Get knowledge base categories id, title pairs
     *
     * @return array
     */
    public function category_get_pairs($data)
    {
        return $this->getService()->categoryGetPairs();
    }
}