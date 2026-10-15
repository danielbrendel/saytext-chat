<?php

class ApiController extends BaseController {
    /**
	 * Perform base initialization
	 * 
	 * @return void
	 */
	public function __construct()
	{
        $method = $_SERVER['REQUEST_METHOD'];

        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS');

        if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS'])) {
            header('Access-Control-Allow-Headers: ' . $_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']);
        }

        if ($method === 'OPTIONS') {
		    http_response_code(200);
            exit();
        }
	}

    /**
	 * Handles URL: /api/status
	 * 
	 * @param Asatru\Controller\ControllerArg $request
	 * @return Asatru\View\JsonHandler
	 */
	public function status($request)
	{
		try {
            $data = Cache::remember('api.status', 300, function() {
                $data = [];

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

                return json_encode($data);
            });

            return json([
                'code' => 200,
                'data' => json_decode($data)
            ]);
        } catch (\Exception $e) {
            return json([
                'code' => 500,
                'msg' => $e->getMessage()
            ]);
        }
	}

    /**
	 * Handles URL: /api/checkname
	 * 
	 * @param Asatru\Controller\ControllerArg $request
	 * @return Asatru\View\JsonHandler
	 */
	public function checkname($request)
	{
		try {
            $username = $request->params()->query('username');

            $status = Chat::isNameAvailable($username);

            return json([
                'code' => 200,
                'data' => [
                    'username' => $username,
                    'status' => $status
                ]
            ]);
        } catch (\Exception $e) {
            return json([
                'code' => 500,
                'msg' => $e->getMessage()
            ]);
        }
	}
}
    