<?php

abstract class Abstract_Lokalise_Decorator implements Lokalise_Decorator
{
    protected function isDataList($data)
    {
        // only non-list data contain ID
        return !isset($data['id']);
    }
}
