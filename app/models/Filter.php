<?php

class Filter extends \Asatru\Database\Model {
    /**
     * @param $expression
     * @param $type
     * @return bool
     * @throws \Exception
     */
    public static function check($expression, $type)
    {
        try {
            $items = static::raw('SELECT * FROM `@THIS` WHERE type = ? AND active = 1', [$type]);
            
            foreach ($items as $item) {
                if (preg_match($item->get('expression'), $expression)) {
                    return true;
                }
            }

            return false;
        } catch (\Exception $e) {
            throw $e;
        }
    }
}