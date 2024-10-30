<?php

class Lokalise_Decorator_Wpml extends Abstract_Lokalise_Decorator
{
    /**
     * @var wpdb
     */
    private $wpdb;

    /**
     * @param wpdb $wpdb
     */
    public function __construct($wpdb)
    {
        $this->wpdb = $wpdb;
    }

    public function decorateResponse($response)
    {
        if (!$this->tableExists('icl_translations')) {
            return $response;
        }

        $data = $response->get_data();
        if ($this->isDataList($data)) {
            $data = array_map(function ($item) {
                if (!isset($item['type'], $item['id'])) {
                    return $item;
                }

                return $this->updateResponseData($item, sprintf('post_%s', $item['type']));
            }, $data);
        } elseif (isset($data['type'])) {
            $data = $this->updateResponseData($data, sprintf('post_%s', $data['type']));
        }
        $response->set_data($data);

        return $response;
    }

    private function updateResponseData($item, $resourceType)
    {
        $translationIds = $this->getTranslationIds($item['id'], $resourceType);

        $trId = null;
        $originalIds = [];
        foreach ($translationIds as $translationId) {
            $originalIds[$translationId['language_code']] = (int)$translationId['element_id'];

            if (!empty($translationId['trid']) && $trId === null) {
                $trId = (int)$translationId['trid'];
            }
        }

        $item['wpml_trid'] = $trId;
        $item['wpml_ids'] = $originalIds;

        return $item;
    }

    private function getTranslationIds($elementId, $elementType)
    {
        $result = $this->wpdb->get_results($this->wpdb->prepare(
            "SELECT
             IF(sub.element_id, sub.element_id, sub.trid) AS element_id,
             sub.trid,
             sub.language_code
            FROM {$this->wpdb->prefix}icl_translations main
            LEFT JOIN {$this->wpdb->prefix}icl_translations sub ON sub.trid = main.trid AND sub.element_type = main.element_type
            WHERE main.element_type = %s
              AND ((main.element_id IS NULL AND main.trid = %d) OR (main.element_id = %d))
            GROUP BY sub.element_type, IF(sub.element_id, sub.element_id, sub.trid), sub.trid",
            $elementType,
            $elementId,
            $elementId
        ), ARRAY_A);

        return $result;
    }

    public function decorateRequest($request, $response)
    {
        if (!$this->tableExists('icl_translations')) {
            return $response;
        }

        $params = $request->get_params();
        if (empty($params['lang']) || empty($params['trid'])) {
            return $response;
        }

        $record = $response->get_data();
        $this->updateTrid(
            (int)$params['trid'],
            $record['id'],
            sprintf('post_%s', $record['type']),
            $params['lang']
        );

        return $response;
    }

    private function updateTrid($trid, $contentId, $contentType, $languageCode)
    {
        $existing = $this->wpdb->get_var($this->wpdb->prepare(
            "SELECT translation_id
            FROM {$this->wpdb->prefix}icl_translations
            WHERE trid = %d
              AND element_id IS NULL
              AND element_type = %s
              AND language_code = %s",
            $trid,
            $contentType,
            $languageCode
        ));

        if ($existing) {
            // delete historical record for this language if it exists
            // otherwise unique constraint will prevent updating newly created record
            $this->wpdb->query($this->wpdb->prepare(
                "DELETE FROM {$this->wpdb->prefix}icl_translations
                WHERE translation_id = %d",
                $existing
            ));
        }

        $this->wpdb->query($this->wpdb->prepare(
            "UPDATE {$this->wpdb->prefix}icl_translations
            SET trid = %d
            WHERE element_id = %d
              AND element_type = %s
              AND language_code = %s",
            $trid,
            $contentId,
            $contentType,
            $languageCode
        ));
    }

    private function tableExists($tableName)
    {
        $dbname = $this->wpdb->dbname;
        if (empty($dbname)) {
            $result = $this->wpdb->get_row(
                "SELECT 1
                FROM {$this->wpdb->prefix}{$tableName}
                LIMIT 1"
            );
            return $result !== false;
        }

        $result = $this->wpdb->get_row($this->wpdb->prepare(
            "SELECT * 
            FROM information_schema.tables
            WHERE table_schema = %s
            AND table_name = %s
            LIMIT 1",
            $dbname,
            $this->wpdb->prefix . $tableName
        ));
        return $result !== false;
    }
}
