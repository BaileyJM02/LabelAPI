<?php declare(strict_types=1);

namespace App\Label;

use Symfony\Component\HttpFoundation\Response;

/*
# LABEL
Name (string) - Name of the Label
Slug (string: part of a url ) - The /label_name/
Path (string) - This is unique and holds all parent slugs /parent_name/label_name/
Text color (String: Hex Code RGB)
Background color (String: Hex Code RGB)
*/


// This acts as a standard for label structures
class LabelStruct {
	public $name;
	public $slug;
	public $path;
	public $textColor;
	public $backgroundColor;
}

class DefaultLabel
{

	public function isValidHex($hex) : bool
	{
		return (preg_match('/^#?(([a-f0-9]{3}){1,2})$/i', $hex) ? true : false);
	}

	public function isValid($data)
	{

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
			} elseif (strlen($value) < 2 || $value == null) {
				return 1006;
			}
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

		// If everything passes
		return true;

	}

	public function create($data)
    {
		$valid = $this->isValid($data);
		if ($valid !== true)
		{
			return $valid;
		}

		$label = new LabelStruct;
		$label->name = $data->name;
		$label->slug = $data->slug;
		$label->path = $data->path;
		$label->textColor = $data->textcolor;
		$label->backgroundColor = $data->backgroundcolor;


		// Call the success response with data
        return $label;
    }
}

?>