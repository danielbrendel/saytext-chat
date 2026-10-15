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
                return json_encode(Utils::getServerInfo());
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

    /**
	 * Handles URL: /api/serverlist
	 * 
	 * @param Asatru\Controller\ControllerArg $request
	 * @return Asatru\View\JsonHandler
	 */
	public function serverlist($request)
	{
		try {
            $serverlisthost = env('APP_SERVERLISTHOST');

            if ((!is_string($serverlisthost)) || (empty($serverlisthost))) {
                throw new \Exception('Invalid host specified: ' . print_r($serverlisthost, true));
            }

            $data = Utils::getServerInfo();

            $response = Utils::remoteRequest($serverlisthost . '/api/serverlist/add', [
                'post' => [
                    'data' => json_encode($data)
                ]
            ]);

            if ($response['info']['http_code'] !== 200) {
                throw new \Exception('Remote host returned error ' . strval($response['info']['http_code']));
            }
            
            return json([
                'code' => 200
            ]);
        } catch (\Exception $e) {
            return json([
                'code' => 500,
                'msg' => $e->getMessage()
            ]);
        }
	}
}
    