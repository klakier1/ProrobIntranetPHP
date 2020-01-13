<?php
if (PHP_SAPI == 'cli-server') {
	// To help the built-in PHP dev server, check if the request was actually for
	// something which should probably be served as a static file
	$url  = parse_url($_SERVER['REQUEST_URI']);
	$file = __DIR__ . $url['path'];
	if (is_file($file)) {
		return false;
	}
}

include_once '../includes/constants.php';
require '../vendor/autoload.php';
require '../includes/responseProcess.php';
require '../includes/dbOperation.php';
require '../includes/token.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

use Klakier\ErrorHandlerProvider;
use Klakier\PageNotFoundHandler;
use Klakier\YamlUtils;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Yaml\Tag\TaggedValue;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

$app = new \Slim\App([
	// 'settings' => [
	// 	'displayErrorDetails' => true
	// ]
]);

$container = $app->getContainer();
$container['phpErrorHandler'] = new ErrorHandlerProvider();
$container['errorHandler'] = new ErrorHandlerProvider();
$container['notFoundHandler'] = new PageNotFoundHandler();

// Register middleware
require '../src/middleware.php';

/* $app->get('/hello/{name}', function (Request $request, Response $response, array $args) {
	$name = $args['name'];
	$response->getBody()->write("Hello, $name");
}); */

$app->get('/test', function (Request $request, Response $response, array $args) {

	$spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load('../client/templates/template_week.xlsx');

	$worksheet = $spreadsheet->getActiveSheet();

	$worksheet->getCell('A1')->setValue('John');
	$worksheet->getCell('A2')->setValue('Smith');

	$writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xls');
	$writer->save('write.xls');

	// $value = Yaml::parseFile('test2.yml', Yaml::PARSE_CUSTOM_TAGS);
	// $val = YamlUtils::taggedValueToArray($value);
	// $val = Yaml::parseFile('test2.yml');
	// return $response = standardResponse($response, 200, false, 'Yaml parsing test', ['data' => $val]);
});

/*
	endpoint: login
	parameters: email, password
	method: POST
*/
$app->post('/login', function (Request $request, Response $response, array $args) {
	if (!haveEmptyParameters(array('email', 'password'), $request, $response)) {
		$request_data = $request->getParsedBody();
		$db = new DbOperation;
		$user_id = -1;
		$role = "";
		$result = $db->login(
			$request_data['email'],
			$request_data['password'],
			$user_id,
			$role
		);

		if ($result == USER_NOT_FOUND) {
			return $response = standardResponse($response, 422, true, 'User not found');
		} else if ($result == USER_NOT_ACTIVE) {
			return $response = standardResponse($response, 422, true, 'User not active');
		} else if ($result == USER_PASSWORD_DO_NOT_MATCH) {
			return $response = standardResponse($response, 401, true, 'Wrong password');
		} else if ($result == USER_AUTHENTICATED) {
			$token = getToken($user_id, $role);
			if (isset($request_data['cookie']) && $request_data['cookie'] == true) {
				$expire = time() + 60 * 60 * 24 * 30; //expire in 30 days
				setcookie("token", $token, $expire, '/', '', false, true);
			}
			return $response = standardResponse($response, 200, false, 'Token generated', ['token' => $token]);
		} else if ($result == DB_ERROR) {
			return $response = standardResponse($response, 500, true, 'Database error');
		}
	} else {
		return $response;
	}
});

$app->group('/api', function (\Slim\App $app) {

	/*
		endpoint: user
		parameters: email, password, name, surname
		method: POST
	*/
	$app->post('/user', function (Request $request, Response $response) {
		if ($request->getAttribute("role") == TOKEN_ADMIN) {
			/* Admin authorized */
			if (!haveEmptyParameters(array(
				'email',
				'pass',
				'role',
				'active',
				'first_name',
				'last_name',
				'title',
				'phone',
				'days_availabe',
				'notify'
			), $request, $response)) {

				$request_data = $request->getParsedBody();
				$db = new DbOperation;
				$result = $db->createUser(
					$request_data['email'],
					$request_data['pass'],
					$request_data['role'],
					$request_data['active'],
					$request_data['first_name'],
					$request_data['last_name'],
					$request_data['title'],
					$request_data['phone'],
					$request_data['days_availabe'],
					$request_data['notify']
				);

				if ($result == USER_CREATED) {
					return $response = standardResponse($response, 201, false, 'User created successfully');
				} else if ($result == USER_FAILURE) {
					return $response = standardResponse($response, 422, true, 'Some error occurred');
				} else if ($result == USER_EXISTS) {
					return $response = standardResponse($response, 422, true, 'User Already Exists');
				} else if ($result == DB_ERROR) {
					return $response = standardResponse($response, 500, true, 'Database error');
				}
			} else {
				return $response;
			}
		} else {
			/* No scope so respond with 401 Unauthorized */
			return $response = standardResponse($response, 401, true, 'No admin privileges');
		}
	});

	/*
		endpoint: user
		parameters:
		method: GET
	*/
	$app->get('/user[/{params:.*}]', function (Request $request, Response $response, $args) {
		//get arguments
		$token = $request->getAttribute("decoded_token_data");
		$role = $request->getAttribute("role");
		$params = [];
		if (count($args) != 0)
			$params = array_filter(explode('/', $args['params']));
		$result = null;
		$ret = null;
		$db = new DbOperation;

		switch ($role) {
			case TOKEN_ADMIN: {
					/* Admin authorized  */
					/* no args          - all users, all data*************************************************** */
					if (count($params) == 0) {
						$result = $db->getAllUsers($ret);
						break;  //if no args, get all users and break,
						//otherwise don't break and go to TOKEN_EMPLOYEE case
					}
					/****************************************************************************************** */
				}
			case TOKEN_EMPLOYEE: {
					/* /user/id/[0-9]   -one user, short data************************************************** */
					if (count($params) == 2 && $params[0] == 'id') {
						$request_id = intval($params[1]);
						if ($token['id'] == $request_id || $role == TOKEN_ADMIN) //admin can get any user
						{
							$result = $db->getUserShort($request_id, $ret);
						}
					} else if (count($params) == 0) {
						$result = $db->getAllUsersShort($ret);
					}
					/**************************************************************************************** */

					break;
				}
			case TOKEN_ERROR: {
					return $response = standardResponse($response, 400, true, 'Token invalid');
				}
		}

		if ($result == GET_USERS_SUCCESS) {
			return $response = standardResponse($response, 200, false, 'Get user successfull', $ret);
		} else if ($result == GET_USERS_FAILURE) {
			return $response = standardResponse($response, 422, true, 'Some error occurred');
		} else if ($result == GET_USERS_NOT_FOUND) {
			return $response = standardResponse($response, 422, true, 'User not found');
		} else if ($result == DB_ERROR) {
			return $response = standardResponse($response, 500, true, 'Database error');
		} else {
			return $response = standardResponse($response, 400, true, 'Bad Request');
		}
	});

	/*
		endpoint: user
		parameters: user/email/... ;  user/user_id/...
		method: DELETE
	*/
	$app->delete('/user[/{params:.*}]', function (Request $request, Response $response, $args) {
		if ($request->getAttribute("role") == TOKEN_ADMIN) {
			/* Admin authorized  */
			$params = array_filter(explode('/', $args['params']));

			if (count($params) != 2)
				return $response = standardResponse($response, 400, true, 'Bad Request');

			switch ($params[0]) {
				case "email": {
						$email = $params[1];
						if (isValidEmail($email)) {
							$db = new DbOperation;
							$result = $db->deleteUsersByEmail($email);
						} else {
							return $response = standardResponse($response, 422, true, 'Invalid email');
						}
						break;
					}
				case "id": {
						$id = intval($params[1]);
						if ($id > 0) {
							$db = new DbOperation;
							$result = $db->deleteUsersById($id);
						} else {
							return $response = standardResponse($response, 422, true, 'Wrong ID');
						}
						break;
					}
				default: {
						return $response = standardResponse($response, 400, true, 'Bad Request');
					}
			}

			$ret = null;

			if ($result == DELETE_USER_SUCCESS) {
				return $response = standardResponse($response, 200, false, 'User has been deleted', $ret);
			} else if ($result == DELETE_USER_FAILURE) {
				return $response = standardResponse($response, 422, true, 'Some error occurred');
			} else if ($result == USER_NOT_FOUND) {
				return $response = standardResponse($response, 422, true, 'User not found');
			} else if ($result == DB_ERROR) {
				return $response = standardResponse($response, 500, true, 'Database error');
			}
			return $response;
		} else {
			/* No scope so respond with 401 Unauthorized */
			return $response = standardResponse($response, 401, true, 'No admin privileges');
		}
	});

	/*
		endpoint: timesheet
		parameters: timesheet/user_id/[[date_from]/date_to]
		method: GET
	*/
	$app->get('/timesheet[/{params:.*}]', function (Request $request, Response $response, $args) {
		//get arguments
		$token = $request->getAttribute("decoded_token_data");
		$params = array_filter(explode('/', $args['params']));

		switch ($role = $request->getAttribute("role")) {
			case TOKEN_ADMIN: {
					/* Admin authorized  */

					/* no args        - all users, all timesheets*************************************************** */
					if (count($params) == 0) {
						$db = new DbOperation;
						$result = $db->getTimesheet($ret);

						if ($result == GET_TIMESHEET_SUCCESS) {
							return $response = standardResponse($response, 200, false, 'Get timesheet successfull', $ret);
						} else if ($result == GET_TIMESHEET_FAILURE) {
							return $response = standardResponse($response, 422, true, 'Some error occurred');
						} else if ($result == DB_ERROR) {
							return $response = standardResponse($response, 500, true, 'Database error');
						}
					}
					/****************************************************************************************** */
				}
			case TOKEN_EMPLOYEE: {

					/* /timesheet/id/[0-9]   -one user, all data************************************************** */
					if (count($params) >= 2 && $params[0] == 'user_id' && is_numeric($params[1])) {
						$request_id = intval($params[1]);
						if ($token['id'] == $request_id || $role == TOKEN_ADMIN) //admin can get any user
						{
							//check range of dates
							$from = null;
							$to = null;

							try{
								if(count($params) == 3 || count($params) == 4){
									$from = new DateTime($params[2]);
									if(count($params) == 4){
										$to = new DateTime($params[3]);
									}
								}
							}catch(Exception $e){
								return $response = standardResponse($response, 400, true, "Wrong date format");
							}
							
							$db = new DbOperation;
							$result = $db->getTimesheetByUser($request_id, $ret, $from, $to);

							if ($result == GET_TIMESHEET_SUCCESS) {
								return $response = standardResponse($response, 200, false, 'Get timesheet successfull', $ret);
							} else if ($result == GET_TIMESHEET_FAILURE) {
								return $response = standardResponse($response, 422, true, 'Some error occurred');
							} else if ($result == DB_ERROR) {
								return $response = standardResponse($response, 500, true, 'Database error');
							}
						}
					}
					return $response = standardResponse($response, 400, true, 'Bad Request');
					/**************************************************************************************** */

					break;
				}
			case TOKEN_ERROR: {
					return $response = standardResponse($response, 400, true, 'Token invalid');
				}


		}
	});

	/*
		endpoint: timesheet
		parameters: user_id, data, from, to, customer_break, statutory_break, comments, project_id, comapny_id, status, created_at, updated_at
		method: POST
	*/

	$app->post('/timesheet', function (Request $request, Response $response) {
		$token = $request->getAttribute("decoded_token_data");
		$role = $request->getAttribute("role");
		if ($role == TOKEN_ADMIN || $role == TOKEN_EMPLOYEE) {
			if (!haveEmptyParameters(array(
				'user_id',
				'date',
				'from',
				'to',
				'customer_break',
				'statutory_break',
				//'comments',
				'project_id',
				'company_id',
				'status',
				'created_at',
				'updated_at'
				//'project'
			), $request, $response)) {

				$request_data = $request->getParsedBody();

				$nullKeys = array('comments', 'project');
				foreach ($nullKeys as $nullKey) {
					if (!array_key_exists($nullKey, $request_data)) {
						$request_data[$nullKey] = NULL;
					}
				}

				if ($role == TOKEN_EMPLOYEE && $token['id'] != $request_data['user_id'])
					return $response = standardResponse($response, 401, true, 'Only admin can add timesheet row of other user');

				$db = new DbOperation;
				$result = $db->createTimesheetRow(
					$request_data['user_id'],
					$request_data['date'],
					$request_data['from'],
					$request_data['to'],
					$request_data['customer_break'],
					$request_data['statutory_break'],
					$request_data['comments'],
					$request_data['project_id'],
					$request_data['company_id'],
					$request_data['status'],
					$request_data['created_at'],
					$request_data['updated_at'],
					$request_data['project'],
					$ret
				);

				if ($result == INSERT_TIMESHEETROW_SUCCESS) {
					return $response = standardResponse($response, 201, false, 'TimesheetRow inserted', $ret);
				} else if ($result == INSERT_TIMESHEETROW_FAILURE) {
					return $response = standardResponse($response, 422, true, 'Some error occurred');
				} else if ($result == DB_ERROR) {
					return $response = standardResponse($response, 500, true, 'Database error');
				}
			} else {
				return $response;
			}
		} else {
			return $response = standardResponse($response, 401, true, 'No privileges');
		}
	});

	/*
		endpoint: timesheet
		parameters: timesheet/id/...
		method: DELETE
	*/
	$app->delete('/timesheet/id/{id}', function (Request $request, Response $response, $args) {

		$token = $request->getAttribute("decoded_token_data");
		$role = $request->getAttribute("role");
		$db = new DbOperation;

		//check if timesheet row exist
		if ($db->getTimesheetById($args['id'], $timesheet) == GET_TIMESHEET_FAILURE)
			return $response = standardResponse($response, 422, true, 'Some error occurred');
		if ($timesheet['data_length'] == 0)
			return $response = standardResponse($response, 422, true, 'Time sheet not exist');

		switch ($role) {
			case TOKEN_EMPLOYEE: {

					//user can delete only his row
					if ($timesheet['data_length'] == 1 && $timesheet['data'][0]['user_id'] == $token['id']) {
						$result = $db->deleteTimesheetRowById($args['id']);
					} else
						return $response = standardResponse($response, 422, true, 'User ID doesnt matach');
					break;
				}
			case TOKEN_ADMIN: {
					/* Admin authorized  */
					//admin can delete any row
					$result = $db->deleteTimesheetRowById($args['id']);
					break;
				}
		}

		if ($result == DELETE_TIMESHEETROW_SUCCESS) {
			return $response = standardResponse($response, 200, false, 'Timesheet row has been deleted');
		} else if ($result == DELETE_TIMESHEETROW_FAILURE) {
			return $response = standardResponse($response, 422, true, 'Some error occurred');
		} else if ($result == DB_ERROR) {
			return $response = standardResponse($response, 500, true, 'Database error');
		} else
			return $response;
	});

	/*
		endpoint: timesheet
		parameters: timesheet
		method: PUT
	*/
	$app->put('/timesheet', function (Request $request, Response $response, $args) {
		if (!haveEmptyParameters(array(
			'id',
		), $request, $response)) {

			if (!haveIllegalParameters(array(
				'id',
				'date',
				'from',
				'to',
				'customer_break',
				'statutory_break',
				'comments',
				'project_id',
				'company_id',
				//'status',
				//'created_at',
				'updated_at',
				'project',
			), $request, $response)) {

				$role = $request->getAttribute("role");
				$token = $request->getAttribute("decoded_token_data");
				$request_data = $request->getParsedBody();

				//copy params and unset id and user_id for creating update query
				//id and user_id cannot be updated
				$query_params = $request_data;
				unset($query_params['id']);
				if (count($query_params) == 0)
					return $response = standardResponse($response, 422, true, 'Nothing to update');

				$db = new DbOperation;

				//check if timesheet row exist
				if ($db->getTimesheetById($request_data['id'], $timesheet) == GET_TIMESHEET_FAILURE)
					return $response = standardResponse($response, 422, true, 'Some error occurred, get timesheet failed');
				if ($timesheet['data_length'] == 0)
					return $response = standardResponse($response, 422, true, 'Time sheet not exist');

				switch ($role) {
					case TOKEN_EMPLOYEE: {
							//user can update only his row
							if ($timesheet['data_length'] == 1 && $timesheet['data'][0]['user_id'] == $token['id']) {
								$result = $db->updateTimesheetRowById($request_data['id'], $query_params);
							} else
								return $response = standardResponse($response, 422, true, 'User ID doesnt matach with token');
							break;
						}
					case TOKEN_ADMIN: {
							/* Admin authorized  */
							//admin can update any row
							$result = $db->updateTimesheetRowById($request_data['id'], $query_params);
							break;
						}
					default: {
							return $response = standardResponse($response, 401, true, 'No privileges');
						}
				}

				if ($result == UPDATE_TIMESHEETROW_SUCCESS)
					return $response = standardResponse($response, 200, false, 'Timesheet row has been updated');
				elseif ($result == UPDATE_TIMESHEETROW_FAILURE) {
					return $response = standardResponse($response, 422, true, 'Some error occurred');
				} else if ($result == DB_ERROR) {
					return $response = standardResponse($response, 500, true, 'Database error');
				} else
					return $response;
			} else {
				return $response;
			}
		} else {
			return $response;
		}
	});

	/*
		endpoint: countries
		parameters: countries
		method: GET
	*/
	$app->get('/countries', function (Request $request, Response $response, $args) {

		$role = $request->getAttribute("role");

		switch ($role) {
			case TOKEN_ADMIN:
			case TOKEN_EMPLOYEE: {

					$db = new DbOperation;
					$result = $db->getCountries($ret);

					if ($result == GET_COUNTRIES_SUCCESS) {
						foreach ($ret['data'] as &$country) {
							if ($country['objectives'] != null)
								$country['objectives'] = Yaml::parse($country['objectives']);
						}
						return $response = standardResponse($response, 200, false, 'Get countries successfull', $ret);
					} else if ($result == GET_COUNTRIES_FAILURE) {
						return $response = standardResponse($response, 422, true, 'Some error occurred');
					} else if ($result == DB_ERROR) {
						return $response = standardResponse($response, 500, true, 'Database error');
					}
					break;
				}
			case TOKEN_ERROR: {
					return $response = standardResponse($response, 400, true, 'Token invalid');
				}
		}
	});

	/*
		endpoint: objectives
		parameters: objectives
		method: GET
	*/
	$app->get('/objectives', function (Request $request, Response $response, $args) {

		$role = $request->getAttribute("role");

		switch ($role) {
			case TOKEN_ADMIN:
			case TOKEN_EMPLOYEE: {

					$db = new DbOperation;
					$result = $db->getCountries($retCountries);

					$objectives = [];
					foreach ($retCountries['data'] as $country) {
						if ($country['objectives'] != null) {
							$objectives = array_merge($objectives, Yaml::parse($country['objectives']));
						}
					}

					$ret = [];
					$ret['data_length'] = count($ret['data']);
					$ret['data'] = $objectives;

					if ($result == GET_COUNTRIES_SUCCESS) {
						return $response = standardResponse($response, 200, false, 'Get objectives successfull', $ret);
					} else if ($result == GET_COUNTRIES_FAILURE) {
						return $response = standardResponse($response, 422, true, 'Some error occurred');
					} else if ($result == DB_ERROR) {
						return $response = standardResponse($response, 500, true, 'Database error');
					}
					break;
				}
			case TOKEN_ERROR: {
					return $response = standardResponse($response, 400, true, 'Token invalid');
				}
		}
	});

	/*
		endpoint: delegation
		parameters: delegation/user_id/...
		method: GET
	*/
	$app->get('/delegation[/{params:.*}]', function (Request $request, Response $response, $args) {
		//get arguments
		$token = $request->getAttribute("decoded_token_data");
		$params = [];
		if (isset($args['params']))
			$params = array_filter(explode('/', $args['params']));
		$result = 0;

		switch ($role = $request->getAttribute("role")) {
			case TOKEN_ADMIN: {
					/* Admin authorized  */
					/* no args        - all users, all delegations*************************************************** */
					if (count($params) == 0) {
						$db = new DbOperation;
						$result = $db->getDelegation($ret);
					}
				}
			case TOKEN_EMPLOYEE: {

					/* /delegation/user_id/[0-9]   -one user, all data************************************************** */
					if (count($params) == 2 && $params[0] == 'user_id') {
						$request_id = intval($params[1]);
						if ($token['id'] == $request_id || $role == TOKEN_ADMIN) //admin can get any user
						{
							$db = new DbOperation;
							$result = $db->getDelegationByUser($request_id, $ret);
						}
					}
					break;
				}
			case TOKEN_ERROR: {
					return $response = standardResponse($response, 400, true, 'Token invalid');
				}
		}
		if ($result == GET_DELEGATION_SUCCESS) {
			if ($ret['data_length'] > 0) {
				foreach ($ret['data'] as &$del) {
					if (isset($del['country_spending'])) $del['country_spending'] = parseTaggedYaml($del['country_spending']);
					if (isset($del['foreign_spending'])) $del['foreign_spending'] = parseTaggedYaml($del['foreign_spending']);
					if (isset($del['currencies'])) $del['currencies'] = parseTaggedYaml($del['currencies']);
					if (isset($del['border_crossing'])) $del['border_crossing'] = parseTaggedYaml($del['border_crossing']);
				}
			}
			return $response = standardResponse($response, 200, false, 'Get delegation successfull', $ret);
		} else if ($result == GET_DELEGATION_FAILURE) {
			return $response = standardResponse($response, 422, true, 'Some error occurred');
		} else if ($result == DB_ERROR) {
			return $response = standardResponse($response, 500, true, 'Database error');
		} else {
			return $response = standardResponse($response, 400, true, 'Bad Request');
		}
	});

	/* 	$app->map(['GET', 'POST', 'PUT', 'DELETE'], '/echo', function (Request $request, Response $response, $args) {
		$log = new Logger(DEBUG_TAG);
		$log->pushHandler(new StreamHandler('php://stderr', Logger::WARNING));

		$log->addWarning("********************BODY*****************");
		echo "\n********************BODY*****************\n";

		$log->addWarning($request->getBody());
		echo $request->getBody();

		$log->addWarning("*******************PARSED BODY****************");
		echo "\n*******************PARSED BODY****************\n";

		$log->addWarning(var_dump($request->getParsedBody()));
		echo var_dump($request->getParsedBody());
		throw new Exception("test exception", 666);
		//echo "\n*******************PATH****************\n";
		//$request->getUri()->getPath();
		//echo "\n*******************REQUEST****************\n";
		//echo var_dump($request);
		return $response = standardResponse($response, 200, false, 'Echo ok');
	}); */
});

function haveEmptyParameters($required_params, Request $request, Response &$response)
{
	$error = false;
	$error_params = '';
	$request_params = $request->getParsedBody();

	foreach ($required_params as $param) {
		if (!isset($request_params[$param]) || strlen($request_params[$param]) <= 0) {
			$error = true;
			$error_params .= $param . ', ';
		}
	}

	if ($error) {
		$text = 'Required parameters: ' . substr($error_params, 0, -2) . ' are missing';
		$response = standardResponse($response, 422, true, $text);
	}

	return $error;
}

function haveIllegalParameters($allowed_params, Request $request, Response &$response)
{
	$error = false;
	$error_params = '';
	$request_params = $request->getParsedBody();

	foreach (array_keys($request_params) as $param) {
		if (!in_array($param, $allowed_params, true)) {
			$error = true;
			$error_params .= $param . ', ';
		}
	}

	if ($error) {
		$text = 'Illegal parameters: ' . substr($error_params, 0, -2);
		$response = standardResponse($response, 422, true, $text);
	}

	return $error;
}

function isValidEmail(&$email)
{
	// Remove all illegal characters from email
	$email_filtred = filter_var($email, FILTER_SANITIZE_EMAIL);

	// Compare with orginal
	if (strcmp($email, $email_filtred) != 0)
		return false;

	// Validate e-mail
	if (!filter_var($email, FILTER_VALIDATE_EMAIL))
		return false;

	// In this point must be valid
	return true;
}

function parseTaggedYaml($in)
{
	if ($in != null) {
		$in = Yaml::parse($in, Yaml::PARSE_CUSTOM_TAGS);
		$in = YamlUtils::taggedValueToArray($in);
	}
	return $in;
}

$app->run();
