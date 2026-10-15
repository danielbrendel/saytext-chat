<?php

class Activity extends \Asatru\Database\Model {
    /**
     * @return void
     * @throws \Exception
     */
    public static function hit()
    {
        try {
            $token = md5(session_id());

            $exists = static::raw('SELECT * FROM `@THIS` WHERE token = ?', [$token])->first();
            if (!$exists) {
                static::raw('INSERT INTO `@THIS` (token) VALUES(?)', [$token]);
            } else {
                static::raw('UPDATE `@THIS` SET updated_at = CURRENT_TIMESTAMP WHERE token = ?', [$token]);
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * @return int
     * @throws \Exception
     */
    public static function online()
    {
        try {
            $row = static::raw('SELECT DISTINCT COUNT(token) AS count FROM `@THIS` WHERE updated_at >= NOW() - INTERVAL 5 MINUTE')->first();
            if ($row) {
                return $row->get('count');
            }

            return 0;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * @return array
     * @throws \Exception
     */
    public static function users()
    {
        try {
            $result = [];

            $tokens = static::raw('SELECT * FROM `@THIS` WHERE updated_at >= NOW() - INTERVAL 5 MINUTE');
            if (!$tokens) {
                return $result;
            }

            foreach ($tokens as $token) {
                $chat = Chat::raw('SELECT * FROM `Chat` WHERE token = ? ORDER BY id DESC LIMIT 1', [$token->get('token')])->first();
                if ($chat) {
                    $result[] = [
                        'name' => $chat->get('username'),
                        'team' => $chat->get('team')
                    ];
                }
            }

            return $result;
        } catch (\Exception $e) {
            throw $e;
        }
    }
}