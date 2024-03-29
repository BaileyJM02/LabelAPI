<?php declare(strict_types=1);

namespace App\Controller;

use App\Entity\Label;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

// Extending the controller allows us to access the Doctrine instance
class LabelController extends AbstractController
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
		// and yes, there are quite a few error cases
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

			case 1012:
				// The name value was over 255 chars
				$message = "Name value can not be longer than 255 chars.";
				break;

			case 1013:
				// The slug value was over 255 chars
				$message = "Slug value can not be longer than 255 chars.";
				break;

			case 1014:
				// The path value was over 500 chars
				$message = "Path value can not be longer than 500 chars.";
				break;

			case 1015:
				// A database error occurred, we can't be sure what at this moment in time
				$message = "An error occurred within the database, please retry.";
				break;

			case 1016:
				// A label with the same path already exists
				$message = "A label with the same path already exists.";
				break;

			case 1017:
				// A label's path has to match it's name
				$message = "A label with a slug different to its name was given.";
				break;

			case 1018:
				// A label's path must include its slug
				$message = "The label's slug was not included in the label's path.";
				break;

			case 1019:
				// The label's path contains unknown labels
				$message = "Some parent labels within the path do not exists, create them first.";
				break;

			case 1020:
				// The path specified didn't match a parent label
				$message = "The parent path was not found, please ensure it is in the right order.";
				break;

			case 1021:
				// label not found
				$message = "A label could not be found at the given path.";
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

	public function isValidHex($hex) : bool
	{
		/*
			This includes the hash:
			valid: #FFFFFF
			invalid: FFFFFF
		*/
		return (preg_match('/^#?(([a-f0-9]{3}){1,2})$/i', $hex) ? true : false);
	}

	public function isValid($data)
	{
		/*
			Although I can do this via a Symfony method that extends Doctrine, it's easier
			to do this manually as we can return better errors.
		*/

		// Check we have actual values
		if ($data == "" || $data == null)
		{
			return 1004;
		} elseif (!isset($data->name))
		{
			return 1007;
		} elseif (!isset($data->slug))
		{
			return 1008;
		} elseif (!isset($data->path))
		{
			return 1009;
		} elseif (!isset($data->backgroundcolor))
		{
			return 1010;
		} elseif (!isset($data->textcolor))
		{
			return 1011;
		}

		// check each value is a string and longer then 2
		foreach ($data as $value) {
			if (!is_string($value)) {
				return 1005;
			// all need to be bigger than 2
			} elseif (strlen($value) < 2 || $value == null) {
				return 1006;
			}
		}

		// these string lengths match the database config
		if (strlen($data->name) > 255)
		{
			return 1012;
		} elseif (strlen($data->slug) > 255)
		{
			return 1013;
		} elseif (strlen($data->path) > 500)
		{
			return 1014;
		}

		// Check the colour values are RGB HEX codes
		if (!$this->isValidHex($data->textcolor))
		{
			return 1002;
		}
		if (!$this->isValidHex($data->backgroundcolor))
		{
			return 1002;
		}

		// Sort out slug and path
		$data->slug = strtolower($data->slug);
		//Make alphanumeric (removes all other characters)
		$data->slug = preg_replace("/[^a-zA-Z0-9_\s-]+/", "", $data->slug);
		//Clean up multiple dashes or whitespaces
		$data->slug = preg_replace("/[\s-]+/", " ", $data->slug);
		//Convert whitespaces and underscore to dash
		$data->slug = preg_replace("/[\s_]/", "-", $data->slug);


		// check that they match, removing the dashes we just added for the check
		if (strtolower($data->name) !== strtolower(str_replace("-"," ",$data->slug)))
		{
			return 1017;
		}

		// split the path into an array
		$path = preg_split("#/#", $data->path);

		// remove any empty, for example test//happy -> test/happy
		$path = array_filter($path);
		if (end($path) !== $data->slug)
		{
			return 1018;
		}

		// if it's a child label, check it's parents exist
		$repository = $this->getDoctrine()->getRepository(Label::class);
		// remove the current label slug
		$path = array_pop($path);
		if ($path > 1)
		{
			foreach($path as $slug)
			{
				// check slug exists so we at least know there is a label
				$found = $repository->findBy(['slug' => $slug]);
				// label doesn't exist
				if (count($found) < 1)
				{
					return 1019;
				}
			}

			// match parent path minus current label
			$parentLabel = $repository->findBy(['path' => $path]);
			if (count($parentLabel) < 1)
			{
				return 1020;
			}
		}

		// If everything passes
		return true;

	}

	/*
		LOGIC: Check whether the path exists as a parent and update all other paths, however,
		this might cause problems as slugs are able to match. FIX: Compare full paths
	*/
	public function parent($label)
	{
		$path = preg_split("#/#", $label->getPath());
		if (count($path) < 1)
		{
			return false;
		}
		// remove any empty, for example sad//happy -> sad/happy
		$path = array_filter($path);
		$path = implode("/", $path);
		// search for all paths matching the start of the parent
		$result = $this->getDoctrine()->getRepository(Label::class)->createQueryBuilder('l')
			->where(
				$this->getDoctrine()->getRepository(Label::class)->createQueryBuilder('l')
					->expr()
					->like('l.path', '?1')
			)
			->setParameter('1', ($path).'/%')
			->getQuery()
			->getResult();

		if (count($result) > 0) {
			return $result;
		}

		return false;
	}

	// a function holding the logic to change paths and update colors for children etc.
	public function fixParent($parentpath, $newPath, $data = [], $deletedLabel = false)
	{
		$path = preg_split("#/#", $parentpath);
		// remove any empty, for example sad//happy -> sad/happy
		$path = array_filter($path);
		$path = implode("/", $path);
		// search for all labels with the start of the path matching
		$result = $this->getDoctrine()->getRepository(Label::class)->createQueryBuilder('l')
			->where(
				$this->getDoctrine()->getRepository(Label::class)->createQueryBuilder('l')
					->expr()
					->like('l.path', '?1')
			)
			->setParameter('1', ($path).'/%')
			->getQuery()
			->getResult();

		// init variables
		$database = $this->getDoctrine()->getManager();
		$repository = $this->getDoctrine()->getRepository(Label::class);

		// if have a path to change
		if ($newPath !== null)
		{
			foreach ($result as &$label) {
				// This swaps the changing bit of the parent path
				$oldPath = preg_split("#/#", $label->getPath());
				$oldPath = array_filter($oldPath);
				$toBePath = preg_split("#/#", $newPath);
				$toBePath = array_filter($toBePath);

				// This means that due to the toBePath being 1 shorter (ie a label was deleted),
				// we need to add one value to the count so the old slug for the deleted label is also removed.
				if ($deletedLabel === false)
				{
					$oldPath = array_slice($oldPath, count($toBePath));
				} else {
					$oldPath = array_slice($oldPath, count($toBePath)+1);
				}

				// merge arrays
				$path = array_merge($toBePath, $oldPath);
				$path = implode("/", $path);
				// we then set the path
				$label->setPath($path);
			}
		}

		// if it's the main parent, also check whether we have a data value.
		if (count(preg_split("#/#", $parentpath)) == 1 && $data !== []) {
			foreach ($result as &$label) {
				// This swaps the color to the parents
				$label->setTextcolor($data->textcolor);
				$label->setBackgroundcolor($data->backgroundcolor);
			}
		}

		// now we push to the database, as it was watching for changes
		$database->flush();

		return true;
	}


	public function labelCreate($data)
    {
		/*
			Although this should be done prior to calling the create function,
			it's good practice to make sure the data is valid *again* before adding it
			to the database, we might have forgotten to validate it somewhere....
		*/
		$valid = $this->isValid($data);
		if ($valid !== true)
		{
			return $valid;
		}

		$database = $this->getDoctrine()->getManager();
		$repository = $this->getDoctrine()->getRepository(Label::class);

		// create a label
		$label = new Label();
        $label->setName($data->name);
		$label->setSlug($data->slug);
		$label->setPath($data->path);
		$label->setTextColor($data->textcolor);
		$label->setBackgroundColor($data->backgroundcolor);

		$path = preg_split("#/#", $label->getPath());
		$path = array_filter($path);
		array_pop($path);
		if (count($path) > 1)
		{
			$exists = $repository->findBy(['path' => $path[0]]);
			if (count($exists) < 0)
			{
				return 1019;
			}
		}

		// check for duplicates
		$dupes = $repository->findBy(['path' => $label->getPath()]);
		if (count($dupes) > 0)
		{
			return 1016;
		}

        // tell Doctrine you want to save the label
        $database->persist($label);

        // actually execute the queries
        $database->flush();

		// Return the label
        return $label;
	}

	public function labelUpdate($data)
    {
		// check we have a path
		if (!isset($data->path))
		{
			return 1009;
		}

		// check each value is a string and longer then 2
		foreach ($data as $value) {
			if (!is_string($value)) {
				return 1005;
			// all need to be bigger than 2
			} elseif (strlen($value) < 2 || $value == null) {
				return 1006;
			}
		}

		// these string lengths match the database config
		if (isset($data->name))
		{
			if (strlen($data->name) > 255)
			{
				return 1012;
			}
		}
		if (isset($data->slug))
		{
			if (preg_match('/\s/', $data->slug))
			{
				$data->slug = strtolower($data->slug);
				//Make alphanumeric (removes all other characters)
				$data->slug = preg_replace("/[^a-zA-Z0-9_\s-]+/", "", $data->slug);
				//Clean up multiple dashes or whitespaces
				$data->slug = preg_replace("/[\s-]+/", " ", $data->slug);
				//Convert whitespaces and underscore to dash
				$data->slug = preg_replace("/[\s_]/", "-", $data->slug);
			}

			if (strlen($data->slug) > 255)
			{
				return 1013;
			}
		}
		if (isset($data->name) && isset($data->slug))
		{
			// check that they match, removing the dashes we just added for the check
			if (strtolower($data->name) !== strtolower(str_replace("-"," ",$data->slug)))
			{
				return 1017;
			}
		}

		// Check the colour values are RGB HEX codes
		if (isset($data->textcolor))
		{
			if (!$this->isValidHex($data->textcolor))
			{
				return 1002;
			}
		} elseif (isset($data->backgroundcolor))
		{
			if (!$this->isValidHex($data->backgroundcolor))
			{
				return 1002;
			}
		}

		$database = $this->getDoctrine()->getManager();
		$repository = $this->getDoctrine()->getRepository(Label::class);

		$path = preg_split("#/#", $data->path);
		// remove any empty, for example test//happy -> test/happy
		$path = array_filter($path);
		$path = implode("/", $path);

		// find the label
		$label = $repository->findOneBy(['path' => $path]);
		if (!$label) {
			return 1021;
		}

		$parent = $this->parent($label);
		// Unaltered path can be later used
		$parentpath = $path;

		// change path back to array
		$path = preg_split("#/#", $data->path);
		// check path
		if (isset($data->slug))
		{
			// some magic to ensure the end of the path is the labels slug
			if (end($path) != $data->slug) {
				$data->slug = strtolower($data->slug);
				// remove the old trailing slug
				array_pop($path);
				// add the new one
				array_push($path, $data->slug);
				// return to a string
				$path = implode("/", $path);
				// set the path
				$label->setPath($path);
				// fix all child paths
				if ($parent !== false) {
					$this->fixParent($parentpath, $path);
				}
			}
			// if name is present instead of slug
		} elseif (isset($data->name) && !isset($data->slug))
		{
			// lowercase for url paths
			$slug = strtolower($data->name);
			// if contains white space
			if (preg_match('/\s/', $data->name))
			{
				//Make alphanumeric (removes all other characters)
				$slug = preg_replace("/[^a-zA-Z0-9_\s-]+/", "", $slug);
				//Clean up multiple dashes or whitespaces
				$slug = preg_replace("/[\s-]+/", " ", $slug);
				//Convert whitespaces and underscore to dash
				$slug = preg_replace("/[\s_]/", "-", $slug);
			}

			// some magic to ensure the end of the path is the labels slug
			if (end($path) != $slug) {
				// remove the old trailing slug
				array_pop($path);
				// add the new one
				array_push($path, $slug);
				$path = implode("/", $path);
				$label->setPath($path);
				// fix all child paths
				if ($parent !== false) {
					$this->fixParent($parentpath, $path);
				}
			}
		}

		if (isset($data->name))
		{
			// as they both have to match
			$label->setName($data->name);
			// Sort out slug
			if (preg_match('/\s/', $data->name))
			{
				$data->name = strtolower($data->name);
				//Make alphanumeric (removes all other characters)
				$data->name = preg_replace("/[^a-zA-Z0-9_\s-]+/", "", $data->name);
				//Clean up multiple dashes or whitespaces
				$data->name = preg_replace("/[\s-]+/", " ", $data->name);
				//Convert whitespaces and underscore to dash
				$data->name = preg_replace("/[\s_]/", "-", $data->name);
			}
			$label->setSlug($data->name);
		}
		if (isset($data->slug))
		{
			// Sort out slug
			if (!preg_match('/\s/', $data->slug))
			{
				// return to a *normal* string ie no dashes
				$data->name = str_replace("-"," ",$data->slug);
				// capitalize
				$data->name = ucwords($data->name);
				//Clean up multiple dashes or whitespaces
				$data->name = preg_replace("/[\s-]+/", " ", $data->name);
			}
			$label->setSlug($data->slug);
			// as they both have to match
			$label->setName($data->name);
		}
		if (isset($data->textcolor))
		{
			// change child color values if parents is updated
			if ($parent !== false || count(preg_split("#/#", $label->getPath())) == 1) {
				$label->setTextcolor($data->textcolor);
				$this->fixParent($parentpath, null, $data);
			}
		}
		if (isset($data->backgroundcolor))
		{
			if ($parent !== false || count(preg_split("#/#", $label->getPath())) == 1) {
				$label->setBackgroundcolor($data->backgroundcolor);
				$this->fixParent($parentpath, null, $data);
			}
		}

        // edit the label
        $database->flush();

		// pass back as an array without id value
		$response = $label->sterilize();

        return $response;
	}

	public function labelDelete($data)
    {
		// check we have a path
		if (!isset($data->path))
		{
			return 1009;
		}

		$database = $this->getDoctrine()->getManager();
		$repository = $this->getDoctrine()->getRepository(Label::class);

		$path = preg_split("#/#", $data->path);
		// remove any empty, for example test//happy -> test/happy
		$path = array_filter($path);
		$path = implode("/", $path);

		// find label to be deleted
		$label = $repository->findOneBy(['path' => $path]);
		if (!$label) {
			return 1021;
		}

		// find of this label is a parent
		$parent = $this->parent($label);

		if ($parent !== false) {
			// This removes the deleted label from all paths of its children
			// /label/deleted/child will become /label/child
			$path = preg_split("#/#", $path);
			$toBePath = array_filter($path);
			array_pop($toBePath);
			$toBePath = implode("/", $toBePath);
			$path = implode("/", $path);
			$this->fixParent($path, $toBePath,[],true);
		}

		// delete label
		$database->remove($label);
		$database->flush();

		// pass back as an array without id value
		$response = $label->sterilize();

        return $response;
	}

	public function labelFetch($data)
    {
		// check we have a path
		if (!isset($data->path))
		{
			return 1009;
		}

		$database = $this->getDoctrine()->getManager();
		$repository = $this->getDoctrine()->getRepository(Label::class);

		$path = preg_split("#/#", $data->path);
		// remove any empty, for example test//happy -> test/happy
		$path = array_filter($path);
		$path = implode("/", $path);

		// find label by path
		$label = $repository->findOneBy(['path' => $path]);
		if (!$label) {
			return 1021;
		}

		// check if its a label
		$parent = $this->parent($label);

		// Convert to an array without id value
		$response = $label->sterilize();

		// create an empty nested array
		$nested = [];

		// fetch all children
		if ($parent !== false) {

			// for each child
			foreach ($parent as $child)
			{
				// sterilize and push to an array
				$array = $child->sterilize();
				// if it is its self ignore - as it will always return its self
				if ($array['path'] !== $path)
				{
					array_push($nested, $array);
				}
			}
		}
		// if the nested array is null (or a length of 0) don't add the nested parameter
		if (count($nested) > 0)
		{
			$response['nested'] = $nested;
		}

        return $response;
    }

	/*
		/api/...
	*/

	// The actual endpoints

    public function index() : Response
    {
        return new Response('Please use the entry point `/api/label` for the demo.');
	}

	// was gonna be used for the GUI but didn't get time
	public function api_label_not_given() : Response
    {
        return $this->error(1004);
	}

	// Called over an HTTP GET method
	public function api_get(Request $request) : Response
    {
		// Get HTTP request body and decode
		$json = $request->getContent();
		$data = json_decode($json);

		// Fetch label
		$label = $this->labelFetch($data);

		// if error
		if (is_int($label)) {
			return $this->error($label);
		}

		// return found label
		$response = json_encode($label);
		// send encased within a success response
        return $this->success($response);
	}

	public function api_create(Request $request) : Response
    {
		// Get HTTP request body and decode
		$json = $request->getContent();
		$data = json_decode($json);

		// check that it is valid
		$valid = $this->isValid($data);
		if ($valid !== true)
		{
			return $this->error($valid);
		}

		// This will create a label and pass it back
		$label = $this->labelCreate($data);

		// There shouldn't be any errors from validating it, however, there might be a duplicate error etc.
		if (is_int($label)) {
			return $this->error($label);
		}

		// encode the response
		$response = json_encode($data);

		// send encased within a success response
        return $this->success($response);
	}

	public function api_update(Request $request) : Response
    {
		// Get HTTP request body and decode
        $json = $request->getContent();
		$data = json_decode($json);

		// Update label with new data
		$label = $this->labelUpdate($data);

		// if error
		if (is_int($label)) {
			return $this->error($label);
		}

		// return updated label
		$response = json_encode($label);
		// send encased within a success response
        return $this->success($response);
	}

	public function api_delete(Request $request) : Response
    {
		// Get HTTP request body and decode
        $json = $request->getContent();
		$data = json_decode($json);

		// Fetch label
		$label = $this->labelDelete($data);

		// if error
		if (is_int($label)) {
			return $this->error($label);
		}

		// return found label
		$response = json_encode($label);
        return $this->success($response);
	}
}

/*
	Wew! That was quite an adventure.
*/

?>