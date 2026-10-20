<?php

class Utils {
    /**
     * @return array
     * @throws \Exception
     */
    public static function getServerInfo()
    {
        try {
            $data = [];

            $data['sv_host'] = url();
            $data['sv_name'] = env('APP_SERVERNAME');
            $data['sv_timezone'] = env('APP_TIMEZONE');
            $data['sv_debug'] = env('APP_DEBUG');

            $data['sv_topic'] = BBCode::trio(env('APP_SERVERTOPIC'));

            $preview = env('APP_SERVERPREVIEW', '');
            if ((!empty($preview)) && (is_file(public_path() . '/img/' . $preview))) {
                $data['sv_preview'] = asset('img/' . env('APP_SERVERPREVIEW', ''));
            } else {
                $data['sv_preview'] = null;
            }

            $data['users'] = [
                'count' => Activity::online(),
                'users' => Activity::users()
            ];

            return $data;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * @param $url
     * @param $params
     * @return array
     * @throws \Exception
     */
    public static function remoteRequest($url, $params = null)
    {
        try {
            $curl = curl_init();

            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

            if (isset($params['curl_opt']['header'])) {
                curl_setopt($curl, CURLOPT_HEADER, $params['curl_opt']['header']);
            }

            if (isset($params['curl_opt']['follow_location'])) {
                curl_setopt($curl, CURLOPT_FOLLOWLOCATION, $params['curl_opt']['follow_location']);
            }

            if ((isset($params['post'])) && ($params['post'])) {
                curl_setopt($curl, CURLOPT_POST, true);
	            curl_setopt($curl, CURLOPT_POSTFIELDS, $params['post']);
            }

            if ((isset($params['header'])) && ($params['header'])) {
                curl_setopt($curl, CURLOPT_HTTPHEADER, $params['header']);
            }

            $output = curl_exec($curl);
            $info = curl_getinfo($curl);

            if (curl_error($curl)) {
                throw new \Exception(curl_error($curl));
            }

            curl_close($curl);

            return [
                'info' => $info,
                'data' => $output
            ];
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
