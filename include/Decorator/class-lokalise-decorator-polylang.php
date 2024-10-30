<?php

class Lokalise_Decorator_Polylang extends Abstract_Lokalise_Decorator
{
    const TAX_TRANSLATIONS = 'post_translations';

    /**
     * @var string[]
     */
    private $langs = [];
    /**
     * @var PLL_Admin|PLL_Frontend|PLL_REST_Request|PLL_Settings
     */
    private $pll;

    public function __construct()
    {
        $this->pll = Pll();

        /**
         * @var PLL_Language[] $langs
         */
        $langs = $this->pll->model->get_languages_list();

        foreach ($langs as $lang) {
            $this->langs[$lang->slug] = $lang->get_locale();
        }
    }


    public function decorateResponse($response)
    {
        $memo = [];
        $data = $response->get_data();
        if ($this->isDataList($data)) {
            $data = array_map(function ($item) use ($memo) {
                return $this->updateResponseData($item, $memo);
            }, $data);
        } else {
            $data = $this->updateResponseData($data, $memo);
        }
        $response->set_data($data);

        return $response;
    }

    private function updateResponseData($item, array &$memo)
    {
        $itemId = $item['id'];

        $term = $this->getPostTerm($itemId, $memo);
        $metadata = ($term instanceof WP_Term) ? $this->getPostMetadata($term, $memo) : [];

        return array_merge($item, $metadata);
    }

    private function getPostTerm($itemId, array &$memo = null)
    {
        if (isset($memo[$itemId])) {
            $term = $memo[$itemId];
        } else {
            $post = $this->pll->model->post;
            $term = $post->get_object_term($itemId, self::TAX_TRANSLATIONS);
        }

        return $term ?: $this->createTranslationGroup($itemId);
    }

    /**
     * By default, Polylang does not create groups for not translated (yet) posts
     * Duplicating behaviour of polylang/include/translated-object.php save_translations method
     * in order to create translation group.
     *
     * @param int $itemId post ID
     *
     * @return WP_Term
     */
    private function createTranslationGroup($itemId)
    {
        $post = $this->pll->model->post;
        $translations = pll_get_post_translations($itemId);

        /** @noinspection NonSecureUniqidUsageInspection */
        wp_insert_term(
            $group = uniqid('pll_'),
            self::TAX_TRANSLATIONS,
            ['description' => maybe_serialize($translations)]
        );

        // Link all translations to the new term.
        foreach ($translations as $p) {
            wp_set_object_terms($p, $group, self::TAX_TRANSLATIONS);
        }

        return $post->get_object_term($itemId, self::TAX_TRANSLATIONS);
    }

    /**
     * @param WP_Term $term
     * @param array $memo
     *
     * @return array
     */
    private function getPostMetadata(WP_Term $term, array &$memo)
    {
        $postTranslations = maybe_unserialize($term->description);
        $ids = [];

        foreach ($postTranslations as $localeSlug => $postTranslation) {
            $locale = $this->langs[$localeSlug];
            $ids[$locale] = $postTranslation;

            $memo[$postTranslation] = $term;
        }

        return [
            'pll_term_id' => $term->term_id,
            'pll_translations' => $ids,
        ];
    }

    private function getLanguageSlugByLocale($locale) {
        foreach ($this->langs as $slug => $langLocale) {
            if($langLocale === $locale) {
                return $slug;
            }
        }

        return null;
    }

    public function decorateRequest($request, $response)
    {
        $params = $request->get_params();

        if (!empty($params['id'])) {
            // updating the record
            return $response;
        }

        $locale = isset($params['locale']) ? $params['locale'] : null;
        $pllTermId = isset($params['pll_term_id']) ? $params['pll_term_id'] : null;

        if (empty($locale) && empty($pllTermId)) {
            // missing required params
            return $response;
        }

        $record = $response->get_data();
        $term = get_term($pllTermId);
        $langSlug = $this->getLanguageSlugByLocale($locale);

        if (!$term || !$langSlug) {
            return $response;
        }

        $translations = maybe_unserialize($term->description);
        $translations[$langSlug] = $record['id'];

        wp_update_term(
            $pllTermId,
            self::TAX_TRANSLATIONS,
            [
                'description' => maybe_serialize($translations)
            ]
        );


        pll_set_post_language($record['id'], $langSlug);

        foreach ( $translations as $p ) {
            wp_set_object_terms( $p, $term->term_id, self::TAX_TRANSLATIONS );
        }

        return $response;
    }
}
