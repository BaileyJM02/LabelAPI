<?php declare(strict_types=1);

namespace App\Tests\Controller;

use App\Controller;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/*
I started to get this done but I couldn't get my head around while I kept getting
this error:

App\Tests\Controller\LabelControllerTest::testPost with data set #0
(array('{"name": "test","slug": "test...ffff"}', 200, 200, '{"name": "test","slug": "test...ffff"}'))
Illegal string offset 'code'

and I'm not sure why because I though I had already fixed it.

I was attempting to use a system where I define a data set with the data to use and the data that should
be received, allowing me to call $data['payload'] and $data['return'] to see if they matched the output of
the function.

-*-*-*-*-*-

I managed to fix the error by adding another 'array()' function around the array as it was sending each
"payload" and "code" for example as the $data array
*/

/*
-- Weird Findings --
*	First Thought:
		If the path or slug contains 3 or more dashes eg. "will-not-error-2" the
		path becomes null *but* is somehow still saved.

	Actual problem:
		It turns out, I was matching the start of the paths meaning instead of
		matching after the '/' it matched after the word, therefor the value "will-not-error"
		became the parent of "will-not-error-a" as the start matched.
		The database value was nulled as it deleted the first (and only) path value thinking it
		was the parent.

	Fix:
		Change setParameter('1', ($path).'%') to setParameter('1', ($path).'/%')

	Note:
		This now no longer returns itself within the results

*/

class LabelControllerTest extends WebTestCase
{
	// This is the same data for all tests, with a couple changes for return codes
	public function providedDataPost()
	{
		return array(
			// test 1 - add a new label
			array(array("payload" => array(
				"name" => "test",
				"slug" => "test",
				"path" => "test",
				"textcolor" => "#000000",
				"backgroundcolor" => "#ffffff"
			),
			"response" => 200,
			"code" => 200,
			"return" => array(
				"name" => "test",
				"slug" => "test",
				"path" => "test",
				"textcolor" => "#000000",
				"backgroundcolor" => "#ffffff"
				)
			)),
			// test 2 - add a new label with whitespace
			array(array("payload" => array(
				"name" => "Two Words",
				"slug" => "two-words",
				"path" => "two-words",
				"textcolor" => "#000000",
				"backgroundcolor" => "#ffffff"
			),
			"response" => 200,
			"code" => 200,
			"return" => array(
				"name" => "Two Words",
				"slug" => "two-words",
				"path" => "two-words",
				"textcolor" => "#000000",
				"backgroundcolor" => "#ffffff"
				)
			)),
			// test 3 pt.1 - add a new label that will be attempted again
			array(array("payload" => array(
				"name" => "Will Be a Dupe",
				"slug" => "will-be-a-dupe",
				"path" => "will-be-a-dupe",
				"textcolor" => "#000000",
				"backgroundcolor" => "#ffffff"
			),
			"response" => 200,
			"code" => 200,
			"return" => array(
				"name" => "Will Be a Dupe",
				"slug" => "will-be-a-dupe",
				"path" => "will-be-a-dupe",
				"textcolor" => "#000000",
				"backgroundcolor" => "#ffffff"
				)
			)),
			// test 3 pt.2 - add the same label again, should fail
			array(array("payload" => array(
				"name" => "Will Be a Dupe",
				"slug" => "will-be-a-dupe",
				"path" => "will-be-a-dupe",
				"textcolor" => "#000000",
				"backgroundcolor" => "#ffffff"
			),
			"response" => 400,
			"code" => 1016,
			"return" => false
			)),
			// test 4 - attempt to create a label with no values
			array(array("payload" => array(),
			"response" => 400,
			"code" => 1004,
			"return" => false
			)),
			// test 5 - attempt to create a label with only the name value
			array(array("payload" => array(
				"name" => "Will Error",
			),
			"response" => 400,
			"code" => 1008,
			"return" => false
			)),
			// test 6 - attempt to create a label with only the name and slug values
			array(array("payload" => array(
				"name" => "Will Error",
				"slug" => "will-error",
			),
			"response" => 400,
			"code" => 1009,
			"return" => false
			)),
			// test 7 - attempt to create a label with only the name, slug and path values
			array(array("payload" => array(
				"name" => "Will Error",
				"slug" => "will-error",
				"path" => "will-error",
			),
			"response" => 400,
			"code" => 1010,
			"return" => false
			)),
			// test 8 - attempt to create a label with only the name, slug, path and background color values
			array(array("payload" => array(
				"name" => "Will Error",
				"slug" => "will-error",
				"path" => "will-error",
				"backgroundcolor" => "#ffffff",
			),
			"response" => 400,
			"code" => 1011,
			"return" => false
			)),
			// test 9 - attempt to create a label with all values again
			array(array("payload" => array(
				"name" => "Will Not Error",
				"slug" => "will-not-error",
				"path" => "will-not-error",
				"backgroundcolor" => "#ffffff",
				"textcolor" => "#000000",
			),
			"response" => 200,
			"code" => 200,
			"return" => array(
				"name" => "Will Not Error",
				"slug" => "will-not-error",
				"path" => "will-not-error",
				"backgroundcolor" => "#ffffff",
				"textcolor" => "#000000",
			)
			)),
			// test 10 - check that this does not register as a child to test 9
			array(array("payload" => array(
				"name" => "Will Not Error a",
				"slug" => "will-not-error-a",
				"path" => "will-not-error-a",
				"backgroundcolor" => "#ffffff",
				"textcolor" => "#000000",
			),
			"response" => 200,
			"code" => 200,
			"return" => array(
				"name" => "Will Not Error a",
				"slug" => "will-not-error-a",
				"path" => "will-not-error-a",
				"backgroundcolor" => "#ffffff",
				"textcolor" => "#000000",
			)
			)),
			// test 11 pt.1 - create a parent value while mixing capital letters, should return lowercase slug
			array(array("payload" => array(
				"name" => "Testing parent",
				"slug" => "Testing-PaRent",
				"path" => "testing-parent",
				"backgroundcolor" => "#ffffff",
				"textcolor" => "#000000",
			),
			"response" => 200,
			"code" => 200,
			"return" => array(
				"name" => "Testing parent",
				"slug" => "testing-parent",
				"path" => "testing-parent",
				"backgroundcolor" => "#ffffff",
				"textcolor" => "#000000",
			)
			)),
			// test 11 pt.2 - Check that adding a child works
			array(array("payload" => array(
				"name" => "Child",
				"slug" => "child",
				"path" => "testing-parent/child",
				"backgroundcolor" => "#ffffff",
				"textcolor" => "#000000",
			),
			"response" => 200,
			"code" => 200,
			"return" => array(
				"name" => "Child",
				"slug" => "child",
				"path" => "testing-parent/child",
				"backgroundcolor" => "#ffffff",
				"textcolor" => "#000000",
			)
			)),
			// test 11 pt.3 - Check that adding a child to a label that does not exists does not work
			array(array("payload" => array(
				"name" => "Child",
				"slug" => "child",
				"path" => "testing-non-parent/child",
				"backgroundcolor" => "#ffffff",
				"textcolor" => "#000000",
			),
			"response" => 400,
			"code" => 1016,
			"return" => false
			))
		);
	}

	public function providedDataGet()
	{
		return array(
			// test 1
			array(array("payload" => array(
				"name" => "test",
				"slug" => "test",
				"path" => "test",
				"textcolor" => "#000000",
				"backgroundcolor" => "#ffffff"
			),
			"response" => 200,
			"code" => 200,
			"return" => array(
				"name" => "test",
				"slug" => "test",
				"path" => "test",
				"textcolor" => "#000000",
				"backgroundcolor" => "#ffffff"
				)
			)),
			// test 2
			array(array("payload" => array(
				"name" => "Two Words",
				"slug" => "two-words",
				"path" => "two-words",
				"textcolor" => "#000000",
				"backgroundcolor" => "#ffffff"
			),
			"response" => 200,
			"code" => 200,
			"return" => array(
				"name" => "Two Words",
				"slug" => "two-words",
				"path" => "two-words",
				"textcolor" => "#000000",
				"backgroundcolor" => "#ffffff"
				)
			)),
			// test 3 pt.1
			array(array("payload" => array(
				"name" => "Will Be a Dupe",
				"slug" => "will-be-a-dupe",
				"path" => "will-be-a-dupe",
				"textcolor" => "#000000",
				"backgroundcolor" => "#ffffff"
			),
			"response" => 200,
			"code" => 200,
			"return" => array(
				"name" => "Will Be a Dupe",
				"slug" => "will-be-a-dupe",
				"path" => "will-be-a-dupe",
				"textcolor" => "#000000",
				"backgroundcolor" => "#ffffff"
				)
			)),
			// test 3 pt.2
			// no need for pt.2 as it just fetches it twice.

			// test 4
			array(array("payload" => array(),
			"response" => 400,
			"code" => 1009,
			"return" => false
			)),
			// test 5
			array(array("payload" => array(
				"name" => "Will Error",
			),
			"response" => 400,
			"code" => 1009,
			"return" => false
			)),
			// test 6
			array(array("payload" => array(
				"name" => "Will Error",
				"slug" => "will-error",
			),
			"response" => 400,
			"code" => 1009,
			"return" => false
			)),
			// test 7
			array(array("payload" => array(
				"name" => "Will Error",
				"slug" => "will-error",
				"path" => "will-error",
			),
			"response" => 400,
			"code" => 1021,
			"return" => false
			)),
			// test 8
			array(array("payload" => array(
				"name" => "Will Error",
				"slug" => "will-error",
				"path" => "will-error",
				"backgroundcolor" => "#ffffff",
			),
			"response" => 400,
			"code" => 1021,
			"return" => false
			)),
			// test 9
			array(array("payload" => array(
				"name" => "Will Not Error",
				"slug" => "will-not-error",
				"path" => "will-not-error",
				"backgroundcolor" => "#ffffff",
				"textcolor" => "#000000",
			),
			"response" => 200,
			"code" => 200,
			"return" => array(
				"name" => "Will Not Error",
				"slug" => "will-not-error",
				"path" => "will-not-error",
				"backgroundcolor" => "#ffffff",
				"textcolor" => "#000000",
			)
			)),
			// test 10
			array(array("payload" => array(
				"name" => "Will Not Error a",
				"slug" => "will-not-error-a",
				"path" => "will-not-error-a",
				"backgroundcolor" => "#ffffff",
				"textcolor" => "#000000",
			),
			"response" => 200,
			"code" => 200,
			"return" => array(
				"name" => "Will Not Error a",
				"slug" => "will-not-error-a",
				"path" => "will-not-error-a",
				"backgroundcolor" => "#ffffff",
				"textcolor" => "#000000",
			)
			)),
			// test 11 pt.1
			array(array("payload" => array(
				"name" => "Testing parent",
				"slug" => "Testing-PaRent",
				"path" => "testing-parent",
				"backgroundcolor" => "#ffffff",
				"textcolor" => "#000000",
			),
			"response" => 200,
			"code" => 200,
			"return" => array(
				"name" => "Testing parent",
				"slug" => "testing-parent",
				"path" => "testing-parent",
				"backgroundcolor" => "#ffffff",
				"textcolor" => "#000000",
				"nested" => array(array(
					"name" => "Child",
					"slug" => "child",
					"path" => "testing-parent/child",
					"backgroundcolor" => "#ffffff",
					"textcolor" => "#000000",
				))
			)
			)),
			// test 11 pt.2
			array(array("payload" => array(
				"name" => "Child",
				"slug" => "child",
				"path" => "testing-parent/child",
				"backgroundcolor" => "#ffffff",
				"textcolor" => "#000000",
			),
			"response" => 200,
			"code" => 200,
			"return" => array(
				"name" => "Child",
				"slug" => "child",
				"path" => "testing-parent/child",
				"backgroundcolor" => "#ffffff",
				"textcolor" => "#000000",
			)
			)),
			// test 12
			array(array("payload" => array(
				"path" => "testing-parent/child",
			),
			"response" => 200,
			"code" => 200,
			"return" => array(
				"name" => "Child",
				"slug" => "child",
				"path" => "testing-parent/child",
				"backgroundcolor" => "#ffffff",
				"textcolor" => "#000000",
			)
			))
		);
	}

	public function providedDataUpdate()
	{
		return array(
			// test 1 - update the label using only the name and path values
			array(array("payload" => array(
				"name" => "test2",
				"path" => "test",
			),
			"response" => 200,
			"code" => 200,
			"return" => array(
				"name" => "test2",
				"slug" => "test2",
				"path" => "test2",
				"textcolor" => "#000000",
				"backgroundcolor" => "#ffffff"
				)
			)),
			// test 2 - update label only using path and slug values
			array(array("payload" => array(
				"slug" => "two-words-now-four",
				"path" => "two-words",
			),
			"response" => 200,
			"code" => 200,
			"return" => array(
				"name" => "Two Words Now Four",
				"slug" => "two-words-now-four",
				"path" => "two-words-now-four",
				"textcolor" => "#000000",
				"backgroundcolor" => "#ffffff"
				)
			)),
			// test 3 pt.1 - ensure than we can not update a label if the given name and slug values are different
			array(array("payload" => array(
				"name" => "Won't work",
				"slug" => "different-slug-and-name",
				"path" => "will-be-a-dupe",
			),
			"response" => 400,
			"code" => 1017,
			"return" => false
			)),

			// test 4 - attempt to update a value without a path
			array(array("payload" => array(),
			"response" => 400,
			"code" => 1009,
			"return" => false
			)),
			// test 5 - attempt to update a value without a path, with only name
			array(array("payload" => array(
				"name" => "Will Error",
			),
			"response" => 400,
			"code" => 1009,
			"return" => false
			)),
			// test 6 - attempt to update a value without a path, with only name and slug
			array(array("payload" => array(
				"name" => "Will Error",
				"slug" => "will-error",
			),
			"response" => 400,
			"code" => 1009,
			"return" => false
			)),
			// test 7 - check that if the label does not exist, a not found error is returned
			array(array("payload" => array(
				"name" => "Will Error",
				"slug" => "will-error",
				"path" => "will-error",
			),
			"response" => 400,
			"code" => 1021,
			"return" => false
			)),
			// test 8 - update a color
			array(array("payload" => array(
				"path" => "will-not-error",
				"backgroundcolor" => "#aaaaaa",
			),
			"response" => 200,
			"code" => 200,
			"return" => array(
				"name" => "Will Not Error",
				"slug" => "will-not-error",
				"path" => "will-not-error",
				"backgroundcolor" => "#aaaaaa",
				"textcolor" => "#000000",
			)
			)),
			// test 9 pt.1 - test changing parent color and path, we will later check this updates child
			array(array("payload" => array(
				"slug" => "Testing-PaRent2",
				"path" => "testing-parent",
				"backgroundcolor" => "#ffffff",
				"textcolor" => "#111111",
			),
			"response" => 200,
			"code" => 200,
			"return" => array(
				"name" => "Testing Parent2",
				"slug" => "testing-parent2",
				"path" => "testing-parent2",
				"backgroundcolor" => "#ffffff",
				"textcolor" => "#111111",
			)
			))
		);
	}

	public function providedDataDelete()
	{
		return array(
			// test 1 pt.1 - attempt to delete a label which has been updated
			array(array("payload" => array(
				"name" => "test",
				"slug" => "test",
				"path" => "test",
				"textcolor" => "#000000",
				"backgroundcolor" => "#ffffff"
			),
			"response" => 400,
			"code" => 1021,
			"return" => false
			)),
			// test 1 pt.2 - delete the label with the correct (updated) path
			array(array("payload" => array(
				"path" => "test2",
			),
			"response" => 200,
			"code" => 200,
			"return" => array(
				"name" => "test2",
				"slug" => "test2",
				"path" => "test2",
				"textcolor" => "#000000",
				"backgroundcolor" => "#ffffff"
				)
			)),
			// test 2 pt.1 - attempt to delete a label which has been updated, where path contains dash
			array(array("payload" => array(
				"name" => "Two Words",
				"slug" => "two-words",
				"path" => "two-words",
				"textcolor" => "#000000",
				"backgroundcolor" => "#ffffff"
			),
			"response" => 400,
			"code" => 1021,
			"return" => false
			)),
			// test 2 pt.2 - delete the label with the correct (updated) path, where path contains dash
			array(array("payload" => array(
				"path" => "two-words-now-four",
			),
			"response" => 200,
			"code" => 200,
			"return" => array(
				"name" => "Two Words Now Four",
				"slug" => "two-words-now-four",
				"path" => "two-words-now-four",
				"textcolor" => "#000000",
				"backgroundcolor" => "#ffffff"
				)
			)),
			// test 3 pt.1 - attempt to delete a label twice
			array(array("payload" => array(
				"name" => "Will Be a Dupe",
				"slug" => "will-be-a-dupe",
				"path" => "will-be-a-dupe",
				"textcolor" => "#000000",
				"backgroundcolor" => "#ffffff"
			),
			"response" => 200,
			"code" => 200,
			"return" => array(
				"name" => "Will Be a Dupe",
				"slug" => "will-be-a-dupe",
				"path" => "will-be-a-dupe",
				"textcolor" => "#000000",
				"backgroundcolor" => "#ffffff"
				)
			)),
			// test 3 pt.2 - fails on second as the label no longer exists
			array(array("payload" => array(
				"name" => "Will Be a Dupe",
				"slug" => "will-be-a-dupe",
				"path" => "will-be-a-dupe",
				"textcolor" => "#000000",
				"backgroundcolor" => "#ffffff"
			),
			"response" => 400,
			"code" => 1021,
			"return" => false
			)),
			// test 4 - attempt to delete a label while sending now values
			array(array("payload" => array(),
			"response" => 400,
			"code" => 1009,
			"return" => false
			)),
			// test 5 - attempt to delete a label while sending only a name value
			array(array("payload" => array(
				"name" => "Will Error",
			),
			"response" => 400,
			"code" => 1009,
			"return" => false
			)),
			// test 6 - attempt to delete a label while sending only a name and slug value
			array(array("payload" => array(
				"name" => "Will Error",
				"slug" => "will-error",
			),
			"response" => 400,
			"code" => 1009,
			"return" => false
			)),
			// test 7 - attempt to delete a label while sending only a path value
			array(array("payload" => array(
				"path" => "will-not-error",
			),
			"response" => 200,
			"code" => 200,
			"return" => array(
				"name" => "Will Not Error",
				"slug" => "will-not-error",
				"path" => "will-not-error",
				"backgroundcolor" => "#aaaaaa",
				"textcolor" => "#000000",
			)
			)),
			// test 8 - delete a longer string where it may have been mistakenly classed as a child
			array(array("payload" => array(
				"path" => "will-not-error-a",
			),
			"response" => 200,
			"code" => 200,
			"return" => array(
				"name" => "Will Not Error a",
				"slug" => "will-not-error-a",
				"path" => "will-not-error-a",
				"backgroundcolor" => "#ffffff",
				"textcolor" => "#000000",
			)
			)),
			// test 9 pt.1 - delete a parent label
			array(array("payload" => array(
				"path" => "testing-parent2",
			),
			"response" => 200,
			"code" => 200,
			"return" => array(
				"name" => "Testing Parent2",
				"slug" => "testing-parent2",
				"path" => "testing-parent2",
				"backgroundcolor" => "#ffffff",
				"textcolor" => "#111111",
			)
			)),
			// test 9 pt.2 - check as the parent label was deleted the path was shortened for children
			array(array("payload" => array(
				"path" => "child", // from testing-parent2/child as it was deleted
			),
			"response" => 200,
			"code" => 200,
			"return" => array(
				"name" => "Child",
				"slug" => "child",
				"path" => "child",
				"backgroundcolor" => "#ffffff",
				"textcolor" => "#000000",
			)
			))
		);
	}



	/**
	 * @dataProvider providedDataPost
	 */
    public function testPost($data)
    {
		// create a new client
        $client = static::createClient();

        // submits a raw JSON string in the request body
		$client->request(
			'POST',
			'/api/label',
			[],
			[],
			['CONTENT_TYPE' => 'application/json'],
			json_encode($data['payload'])
		);
		// fetch response
		$response = json_decode($client->getResponse()->getContent(), true);

		// check response / error code is correct
		$this->assertEquals($data['code'], $response['code']);
		$this->assertEquals($data['response'], $client->getResponse()->getStatusCode());

		// on error no data is returned
		if (isset($response['data']))
		{
			$this->assertEquals($data['return'], $response['data']);
		} else {
			$this->assertEquals($data['return'], false);
		}

	}


	/**
	 * @dataProvider providedDataGet
	 */
    public function testGet($data)
    {
		// create a new client
        $client = static::createClient();

        // submits a raw JSON string in the request body
		$client->request(
			'GET',
			'/api/label',
			[],
			[],
			['CONTENT_TYPE' => 'application/json'],
			json_encode($data['payload'])
		);
		// fetch response
		$response = json_decode($client->getResponse()->getContent(), true);

		// check response / error code is correct
		$this->assertEquals($data['code'], $response['code']);
		$this->assertEquals($data['response'], $client->getResponse()->getStatusCode());

		// on error no data is returned
		if (isset($response['data']))
		{
			$this->assertEquals($data['return'], $response['data']);
		} else {
			$this->assertEquals($data['return'], false);
		}

	}

	/**
	 * @dataProvider providedDataUpdate
	 */
    public function testUpdate($data)
    {
		// create a new client
        $client = static::createClient();

        // submits a raw JSON string in the request body
		$client->request(
			'PUT',
			'/api/label',
			[],
			[],
			['CONTENT_TYPE' => 'application/json'],
			json_encode($data['payload'])
		);
		// fetch response
		$response = json_decode($client->getResponse()->getContent(), true);

		// check response / error code is correct
		$this->assertEquals($data['code'], $response['code']);
		$this->assertEquals($data['response'], $client->getResponse()->getStatusCode());

		// on error no data is returned
		if (isset($response['data']))
		{
			$this->assertEquals($data['return'], $response['data']);
		} else {
			$this->assertEquals($data['return'], false);
		}

	}

	/**
	 * @dataProvider providedDataDelete
	 */
    public function testDelete($data)
    {
		// create a new client
        $client = static::createClient();

        // submits a raw JSON string in the request body
		$client->request(
			'DELETE',
			'/api/label',
			[],
			[],
			['CONTENT_TYPE' => 'application/json'],
			json_encode($data['payload'])
		);
		// fetch response
		$response = json_decode($client->getResponse()->getContent(), true);

		// check response / error code is correct
		$this->assertEquals($data['code'], $response['code']);
		$this->assertEquals($data['response'], $client->getResponse()->getStatusCode());

		// on error no data is returned
		if (isset($response['data']))
		{
			$this->assertEquals($data['return'], $response['data']);
		} else {
			$this->assertEquals($data['return'], false);
		}

	}

}