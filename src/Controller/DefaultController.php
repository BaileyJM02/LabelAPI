<?php declare(strict_types=1);

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

use App\Label\DefaultLabel;

class DefaultController
{

	// Private function for a success response template
	public function success($data = '{}') : Response
	{
		return new Response(
			'{"status": "success","data": '.$data.',"code": '.Response::HTTP_OK.'}',
			Response::HTTP_OK,
			['content-type' => 'application/json']
		);
	}

	// Private function for an error response template
	public function error($code) : Response
	{
		// ensure it is an integer so it checks against the cases properly
		switch ((int)$code)
		{
			case 1001:
				// Already exists with that name
				$message = "Already exists a label with that path.";
				break;
			case 1002:
				// Colour code invalid - not a HEX code
				$message = "Invalid colour, must be a HEX code.";
				break;
			case 1003:
				// Called when there is a general error with the database - hope for the best and retry. More information should be provided else where
				$message = "Database error, please retry.";
				break;
			case 1004:
				// No label was in the URL during the request
				$message = "No label given.";
				break;
			case 1005:
				// A label value wasn't a string
				$message = "A type of non-string was given as a value.";
				break;
			case 1006:
				// A label value was less than 2 chars
				$message = "A string with a length of less than two was given as a value.";
				break;
			case 1007:
				// A "name": "val" was not present
				$message = "No name value given.";
				break;
			case 1008:
				// A "slug": "val" was not present
				$message = "No slug value given.";
				break;
			case 1009:
				// A "path": "val" was not present
				$message = "No path value given.";
				break;
			case 1010:
				// A "backgroundcolor": "val" was not present
				$message = "No background color value given.";
				break;
			case 1011:
				// A "textcolor": "val" was not present
				$message = "No text color value given.";
				break;


			// Used if we somehow call an error without specifying a code.
			default:
				$message = "An unknown error occurred, please retry.";
				break;
		}
		return new Response(
			'{"status": "error", "code": '.$code.', "message": "'.$message.'"}',
			Response::HTTP_BAD_REQUEST,
			['content-type' => 'application/json']
		);
	}

	/*
		/_test/api/...
	*/

	public function test_api_error($code) : Response
    {
		// Call the error response with code
        return $this->error($code);
    }

	public function test_api_success() : Response
    {
		$data = '{"name": "testing"}';

		// Call the success response with data
        return $this->success($data);
    }

	/*
		/api/...
	*/

    public function index() : Response
    {
        return new Response('Please go to /api/label for the demo');
	}

	public function api_label_not_given() : Response
    {
        return $this->error(1004);
	}

	public function api_get(Request $request) : Response
    {
        return $this->success('{"label": "'.$label.'"}');
	}

	public function api_create(Request $request) : Response
    {
		$json = $request->getContent();
		$data = json_decode($json);
		$label = new DefaultLabel;
		$valid = $label->isValid($data);
		if ($valid !== true)
		{
			return $this->error($valid);
		}
		// Create will override Label with LabelStruct
		$label->create($data);
		$response = json_encode($data);
        return $this->success($response);
	}

	public function api_update(Request $request) : Response
    {
        return $this->success('{"label": "'.$label.'"}');
	}

	public function api_delete(Request $request) : Response
    {
        return $this->success('{"label": "'.$label.'"}');
	}
}


?>