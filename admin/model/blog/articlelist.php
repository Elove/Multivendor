<?php

class ModelBlogArticlelist extends Model
{
    public function addArticlesList($data) {
        $this->db->query("INSERT INTO " . DB_PREFIX . "article_list SET name = '" . $this->db->escape($data['name']) . "', status = '" . (int)$data['status'] . "'");

        $article_list_id = $this->db->getLastId();

        $this->cache->delete('article_list');

        return $article_list_id;
    }

    public function addArticleToList($article_list_id, $articleIds) {
        foreach($articleIds as $articleId) {
            $sql = "INSERT INTO " . DB_PREFIX . "article_to_list SET article_list_id = '". (int) $article_list_id . "', article_id = '" . (int) $articleId . "'";

            $this->db->query($sql);
        }

        $this->cache->delete('article_to_list');

        return;
    }

    public function editArticleList($article_list_id, $data = array()) {
        $sql = "UPDATE " . DB_PREFIX . "article_list SET name = '" . $this->db->escape($data['name']) . "', status = '" . (int) $data['status'] . "' WHERE article_list_id = '" . (int) $article_list_id . "'";

        $this->db->query($sql);

        $this->db->query("DELETE FROM " . DB_PREFIX . "article_to_list WHERE article_list_id = '" . (int)$article_list_id . "'");

        foreach($data['article'] as $articleId) {
            $sql = "INSERT INTO " . DB_PREFIX . "article_to_list SET article_list_id = '". (int) $article_list_id . "', article_id = '" . (int) $articleId . "'";

            $this->db->query($sql);
        }

        $this->cache->delete('article_to_list');

        return;
    }

    public function copyArticlesList($article_list_id) {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "article_list WHERE article_list_id = '" . (int)$article_list_id . "'");

        if ($query->num_rows) {
            $data = array();
            $result = $query->row;

            $data['name'] = $result['name'];
            $data['status'] = $result['status'];
            $this->addArticlesList($data);
        }
    }

    public function deleteArticlesList($article_list_id) {
        $this->db->query("DELETE FROM " . DB_PREFIX . "article_list WHERE article_list_id = '" . (int)$article_list_id . "'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "article_to_list WHERE article_list_id = '" . (int)$article_list_id . "'");
        $this->cache->delete('article_list');
    }

    public function getArticleList($article_list_id) {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "article_list WHERE article_list_id = '" . (int)$article_list_id . "'");

        return $query->row;
    }

    public function getArticleToList($article_list_id) {
        $query = $this->db->query("SELECT article_id FROM " . DB_PREFIX . "article_to_list WHERE article_list_id = '" . (int)$article_list_id . "'");

        return $query->rows;
    }

    public function getAllArticlesList($data = array()) {
        $sql = "SELECT * FROM " . DB_PREFIX . "article_list";

        if (isset($data['start']) || isset($data['limit'])) {
            if ($data['start'] < 0) {
                $data['start'] = 0;
            }

            if ($data['limit'] < 1) {
                $data['limit'] = 20;
            }

            $sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
        }

        $query = $this->db->query($sql);

        return $query->rows;
    }

    public function getTotalArticlesList() {
        $sql = "SELECT COUNT(DISTINCT " . DB_PREFIX . "article_list.article_list_id) AS total FROM " . DB_PREFIX . "article_list";

        $query = $this->db->query($sql);

        return $query->row['total'];
    }
}